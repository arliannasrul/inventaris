<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Item;
use App\Models\StockMovement;
use App\Models\Notification;
use App\Models\AuditLog;
use App\Services\KiriminajaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function __construct(private KiriminajaService $kiriminaja)
    {
    }

    /**
     * Tampilkan daftar order seller
     */
    public function index(Request $request): View
    {
        $status = $request->input('status', 'all');
        $query = Order::with('items');

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $orders = $query->latest()->get();

        // Penghitungan jumlah untuk badge
        $counts = [
            'all'        => Order::count(),
            'pending'    => Order::where('status', 'pending')->count(),
            'processing' => Order::where('status', 'processing')->count(),
            'shipping'   => Order::where('status', 'shipping')->count(),
            'delivered'  => Order::where('status', 'delivered')->count(),
        ];

        return view('orders.index', compact('orders', 'counts', 'status'));
    }

    /**
     * Tampilkan halaman detail order & print label
     */
    public function show(string $id): View
    {
        $order = Order::with('items')->findOrFail($id);
        return view('orders.show', compact('order'));
    }

    /**
     * Form simulasi pemesanan pelanggan (Customer Check Out)
     */
    public function showSimulation(): View
    {
        $items = Item::where('quantity', '>', 0)->orderBy('name', 'asc')->get();
        
        // Daftar Kota Simulasi
        $cities = [
            ['id' => 501, 'name' => 'Sleman, D.I. Yogyakarta'],
            ['id' => 152, 'name' => 'Jakarta Barat, DKI Jakarta'],
            ['id' => 153, 'name' => 'Jakarta Selatan, DKI Jakarta'],
            ['id' => 444, 'name' => 'Surabaya, Jawa Timur'],
            ['id' => 23, 'name' => 'Bandung, Jawa Barat'],
            ['id' => 256, 'name' => 'Medan, Sumatera Utara'],
            ['id' => 278, 'name' => 'Makassar, Sulawesi Selatan'],
        ];

        return view('orders.simulation', compact('items', 'cities'));
    }

    /**
     * Simpan pesanan simulasi & potong stok inventori
     */
    public function storeSimulation(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'customer_name' => ['required', 'string', 'max:120'],
            'customer_phone' => ['required', 'string', 'max:20'],
            'customer_address' => ['required', 'string', 'max:500'],
            'destination_city' => ['required', 'integer'],
            'courier' => ['required', 'string', 'in:jne,jnt,sicepat,anteraja'],
            'shipping_service' => ['required', 'string'],
            'shipping_cost' => ['required', 'numeric', 'min:0'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.id' => ['required', 'exists:items,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ]);

        // Detail Kota
        $cities = [
            501 => 'Sleman, D.I. Yogyakarta',
            152 => 'Jakarta Barat, DKI Jakarta',
            153 => 'Jakarta Selatan, DKI Jakarta',
            444 => 'Surabaya, Jawa Timur',
            23 => 'Bandung, Jawa Barat',
            256 => 'Medan, Sumatera Utara',
            278 => 'Makassar, Sulawesi Selatan',
        ];
        $cityName = $cities[$validated['destination_city']] ?? 'Indonesia';

        // Hitung total berat dan validasi stok produk
        $totalWeightGrams = 0;
        $orderItemsData = [];
        
        foreach ($validated['items'] as $itemInput) {
            $item = Item::findOrFail($itemInput['id']);
            if ($item->quantity < $itemInput['quantity']) {
                return back()->withErrors(["items.{$itemInput['id']}.quantity" => "Stok untuk barang '{$item->name}' tidak mencukupi."])->withInput();
            }
            // Estimasi berat per item (default 250 gram jika tidak ada field berat)
            $totalWeightGrams += ($itemInput['quantity'] * 250); 
            $orderItemsData[] = [
                'item' => $item,
                'qty' => $itemInput['quantity'],
                'price' => $item->unit_price,
            ];
        }

        $order = DB::transaction(function () use ($validated, $cityName, $totalWeightGrams, $orderItemsData) {
            // 1. Buat records Order
            $orderNumber = 'ORD-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -5));
            $order = Order::create([
                'order_number' => $orderNumber,
                'customer_name' => $validated['customer_name'],
                'customer_phone' => $validated['customer_phone'],
                'customer_address' => $validated['customer_address'],
                'destination_city_id' => $validated['destination_city'],
                'destination_city_name' => $cityName,
                'weight_grams' => $totalWeightGrams,
                'courier' => $validated['courier'],
                'shipping_service' => $validated['shipping_service'],
                'shipping_cost' => $validated['shipping_cost'],
                'status' => 'pending',
            ]);

            // 2. Buat records Order Items & Kurangi Stok Barang
            foreach ($orderItemsData as $data) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'item_id' => $data['item']->id,
                    'quantity' => $data['qty'],
                    'price' => $data['price'],
                ]);

                // Kurangi stok barang
                $oldQty = $data['item']->quantity;
                $newQty = $oldQty - $data['qty'];
                $data['item']->update(['quantity' => $newQty]);

                // Catat pergerakan stok
                StockMovement::create([
                    'item_id' => $data['item']->id,
                    'type' => 'OUT',
                    'quantity' => $data['qty'],
                    'reference' => $orderNumber,
                    'actor' => 'E-commerce Simulation',
                    'notes' => 'Pembelian produk oleh ' . $validated['customer_name'],
                ]);

                // Audit Log
                AuditLog::create([
                    'item_id' => $data['item']->id,
                    'action' => 'STOCK_OUT',
                    'actor' => 'E-commerce Simulation',
                    'payload' => ['before' => $oldQty, 'after' => $newQty, 'order' => $orderNumber],
                ]);
            }

            // 3. Notifikasi
            Notification::create([
                'type' => 'MOVEMENT',
                'title' => "Pesanan Baru Masuk: {$orderNumber}",
                'body' => "Pemesanan oleh {$validated['customer_name']} sebanyak " . count($orderItemsData) . " jenis barang.",
            ]);

            return $order;
        });

        return redirect()->route('orders.index')->with('status', 'Simulasi pesanan berhasil dibuat dengan nomor: ' . $order->order_number);
    }

    /**
     * Proses pengiriman (Hubungi API Kiriminaja untuk generate RESI/AWB)
     */
    public function processShipment(string $id): RedirectResponse
    {
        $order = Order::with('items')->findOrFail($id);

        if ($order->status !== 'pending' && $order->status !== 'processing') {
            return back()->withErrors(['status' => 'Order ini sudah diproses pengirimannya.']);
        }

        // Susun nama barang untuk deskripsi
        $itemDesc = $order->items->map(fn($i) => "{$i->name} (x{$i->pivot->quantity})")->implode(', ');
        if (strlen($itemDesc) > 100) {
            $itemDesc = substr($itemDesc, 0, 97) . '...';
        }

        // Panggil Kiriminaja Service
        $shipmentResult = $this->kiriminaja->createShipment([
            'destination_city_id' => $order->destination_city_id,
            'weight_grams' => $order->weight_grams,
            'courier' => $order->courier,
            'shipping_service' => $order->shipping_service,
            'customer_name' => $order->customer_name,
            'customer_phone' => $order->customer_phone,
            'customer_address' => $order->customer_address,
            'item_description' => $itemDesc,
        ]);

        if ($shipmentResult['success']) {
            $order->update([
                'status' => 'shipping',
                'waybill' => $shipmentResult['waybill'],
                'kiriminaja_order_id' => $shipmentResult['kiriminaja_order_id'],
            ]);

            Notification::create([
                'type' => 'SYSTEM',
                'title' => "Resi Pengiriman Diterbitkan: {$order->order_number}",
                'body' => "Ekspedisi: " . strtoupper($order->courier) . " | No Resi: " . $shipmentResult['waybill'],
            ]);

            return redirect()->route('orders.index')->with('status', 'Pesanan telah diproses ke ekspedisi. Nomor Resi: ' . $shipmentResult['waybill']);
        }

        return back()->withErrors(['api' => 'Gagal memproses pengiriman melalui API Kiriminaja.']);
    }

    /**
     * Cek status pelacakan secara real-time via API Kiriminaja
     */
    public function trackShipment(string $id): View
    {
        $order = Order::findOrFail($id);

        if (!$order->waybill) {
            abort(404, 'Pesanan ini belum memiliki nomor resi pengiriman.');
        }

        $trackingData = $this->kiriminaja->trackShipment($order->waybill, $order->courier);

        return view('orders.tracking_modal', compact('order', 'trackingData'));
    }

    /**
     * Selesaikan pemesanan (Telah diterima pelanggan)
     */
    public function completeOrder(string $id): RedirectResponse
    {
        $order = Order::findOrFail($id);
        
        $order->update(['status' => 'delivered']);

        Notification::create([
            'type' => 'SYSTEM',
            'title' => "Pesanan Selesai: {$order->order_number}",
            'body' => "Paket telah sukses diterima oleh pelanggan {$order->customer_name}.",
        ]);

        return redirect()->route('orders.index')->with('status', 'Pesanan berhasil diselesaikan.');
    }

    /**
     * API untuk mengambil tarif ekspedisi Kiriminaja (AJAX)
     */
    public function apiGetRates(Request $request): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validate([
            'destination_city_id' => ['required', 'integer'],
            'weight_grams' => ['required', 'integer'],
            'courier' => ['required', 'string'],
        ]);

        $rates = $this->kiriminaja->getShippingRates(
            501, // Sleman, Yogyakarta (default origin)
            $validated['destination_city_id'],
            $validated['weight_grams'],
            [$validated['courier']]
        );

        return response()->json($rates);
    }
}
