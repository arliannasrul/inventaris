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
