<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\StockMovement;
use App\Models\Notification;
use App\Models\AuditLog;
use App\Services\KiriminajaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ApiController extends Controller
{
    public function __construct(private KiriminajaService $kiriminaja)
    {
    }

    /**
     * GET /api/ecommerce/products
     * Kembalikan daftar produk aktif untuk e-commerce
     */
    public function getProducts(): JsonResponse
    {
        $products = Item::where('quantity', '>', 0)
            ->orderBy('name', 'asc')
            ->get(['id', 'sku', 'name', 'category', 'unit', 'quantity', 'unit_price', 'image_url', 'notes']);

        return response()->json([
            'success' => true,
            'data' => $products
        ]);
    }

    /**
     * POST /api/ecommerce/checkout
     * Endpoint untuk menerima order baru dari E-commerce (Customer checkout)
     */
    public function placeOrder(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'customer_name' => ['required', 'string', 'max:120'],
            'customer_phone' => ['required', 'string', 'max:20'],
            'customer_address' => ['required', 'string', 'max:500'],
            'destination_city_id' => ['required', 'integer'],
            'destination_city_name' => ['required', 'string', 'max:120'],
            'courier' => ['required', 'string'],
            'shipping_service' => ['required', 'string'],
            'shipping_cost' => ['required', 'numeric', 'min:0'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.id' => ['required', 'exists:items,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal.',
                'errors' => $validator->errors()
            ], 422);
        }

        $validated = $validator->validated();

        // Hitung total berat dan validasi stok produk
        $totalWeightGrams = 0;
        $orderItemsData = [];

        foreach ($validated['items'] as $itemInput) {
            $item = Item::find($itemInput['id']);
            if ($item->quantity < $itemInput['quantity']) {
                return response()->json([
                    'success' => false,
                    'message' => "Stok produk '{$item->name}' tidak mencukupi untuk transaksi ini."
                ], 400);
            }
            $totalWeightGrams += ($itemInput['quantity'] * 250); // Estimasi 250 gram
            $orderItemsData[] = [
                'item' => $item,
                'qty' => $itemInput['quantity'],
                'price' => $item->unit_price,
            ];
        }

        try {
            $order = DB::transaction(function () use ($validated, $totalWeightGrams, $orderItemsData) {
                // 1. Generate Order Number
                $orderNumber = 'ORD-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -5));

                // 2. Create Order
                $order = Order::create([
                    'order_number' => $orderNumber,
                    'customer_name' => $validated['customer_name'],
                    'customer_phone' => $validated['customer_phone'],
                    'customer_address' => $validated['customer_address'],
                    'destination_city_id' => $validated['destination_city_id'],
                    'destination_city_name' => $validated['destination_city_name'],
                    'weight_grams' => $totalWeightGrams,
                    'courier' => $validated['courier'],
                    'shipping_service' => $validated['shipping_service'],
                    'shipping_cost' => $validated['shipping_cost'],
                    'status' => 'pending',
                ]);

                // 3. Create Order Items & potong stok
                foreach ($orderItemsData as $data) {
                    OrderItem::create([
                        'order_id' => $order->id,
                        'item_id' => $data['item']->id,
                        'quantity' => $data['qty'],
                        'price' => $data['price'],
                    ]);

                    $oldQty = $data['item']->quantity;
                    $newQty = $oldQty - $data['qty'];
                    $data['item']->update(['quantity' => $newQty]);

                    // Catat Stock Movement
                    StockMovement::create([
                        'item_id' => $data['item']->id,
                        'type' => 'OUT',
                        'quantity' => $data['qty'],
                        'reference' => $orderNumber,
                        'actor' => 'E-commerce API Gateway',
                        'notes' => 'Pembelian produk oleh customer: ' . $validated['customer_name'],
                    ]);

                    // Audit Log
                    AuditLog::create([
                        'item_id' => $data['item']->id,
                        'action' => 'STOCK_OUT',
                        'actor' => 'E-commerce API Gateway',
                        'payload' => ['before' => $oldQty, 'after' => $newQty, 'order' => $orderNumber],
                    ]);
                }

                // 4. Buat Notifikasi Dashboard
                Notification::create([
                    'type' => 'MOVEMENT',
                    'title' => "Pesanan Masuk dari E-commerce: {$orderNumber}",
                    'body' => "Pesanan oleh {$validated['customer_name']} total Rp " . number_format($order->shipping_cost + collect($orderItemsData)->sum(fn($i) => $i['qty'] * $i['price']), 0, ',', '.') . ".",
                ]);

                return $order;
            });

            return response()->json([
                'success' => true,
                'message' => 'Pesanan berhasil dibuat.',
                'data' => [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'status' => $order->status
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem saat memproses checkout: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET /api/ecommerce/tracking/{order_number}
     * Melacak status pengiriman dan data kurir
     */
    public function trackOrder(string $orderNumber): JsonResponse
    {
        $order = Order::where('order_number', $orderNumber)->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Nomor order tidak ditemukan.'
            ], 404);
        }

        $trackingInfo = null;
        if ($order->waybill) {
            $trackingInfo = $this->kiriminaja->trackShipment($order->waybill, $order->courier);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'order_number' => $order->order_number,
                'status' => $order->status,
                'courier' => $order->courier,
                'shipping_service' => $order->shipping_service,
                'waybill' => $order->waybill,
                'tracking' => $trackingInfo
            ]
        ]);
    }
}
