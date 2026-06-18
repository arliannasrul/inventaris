<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CrmController extends Controller
{
    /**
     * Tampilkan database pelanggan internal
     */
    public function index(): View
    {
        $customers = Order::select(
            'orders.customer_phone',
            DB::raw('MAX(orders.customer_name) as customer_name'),
            DB::raw('MAX(orders.customer_address) as customer_address'),
            DB::raw('COUNT(DISTINCT orders.id) as total_orders'),
            DB::raw('MAX(orders.created_at) as last_order_date'),
            DB::raw('SUM(orders.shipping_cost + COALESCE((SELECT SUM(quantity * price) FROM order_items WHERE order_items.order_id = orders.id), 0)) as total_spent')
        )
        ->groupBy('orders.customer_phone')
        ->orderBy('last_order_date', 'desc')
        ->get()
        ->toArray();

        return view('crm.index', compact('customers'));
    }

    /**
     * Halaman template pesan WhatsApp CRM
     */
    public function showTemplates(Request $request): View
    {
        $orderId = $request->input('order_id');
        $order = null;
        if ($orderId) {
            $order = Order::with('items')->find($orderId);
        }

        // Template default
        $templates = [
            'process' => [
                'title' => 'Pesanan Diproses',
                'body' => "Halo *{customer_name}*, terima kasih telah berbelanja di MitraSpace. Pesanan Anda *{order_number}* saat ini sedang kami siapkan untuk dikirim. Terima kasih!",
            ],
            'shipping' => [
                'title' => 'Resi Pengiriman',
                'body' => "Halo *{customer_name}*, pesanan Anda *{order_number}* telah diserahkan ke kurir *{courier}* (*{service}*). Nomor resi pengiriman Anda adalah *{waybill}*. Silakan lacak paket Anda secara berkala. Terima kasih!",
            ],
            'thankyou' => [
                'title' => 'Terima Kasih / Selesai',
                'body' => "Halo *{customer_name}*, pesanan Anda *{order_number}* telah sukses terkirim dan diterima. Semoga menyukai produk kami! Ulasan Anda sangat berarti bagi kami. Selamat berbelanja kembali di MitraSpace!",
            ],
        ];

        // Format pesan jika order dipilih
        $formattedTemplates = [];
        if ($order) {
            foreach ($templates as $key => $tpl) {
                $text = $tpl['body'];
                $text = str_replace('{customer_name}', $order->customer_name, $text);
                $text = str_replace('{order_number}', $order->order_number, $text);
                $text = str_replace('{courier}', strtoupper($order->courier), $text);
                $text = str_replace('{service}', $order->shipping_service, $text);
                $text = str_replace('{waybill}', $order->waybill ?? '[BELUM TERBIT]', $text);
                
                $formattedTemplates[$key] = [
                    'title' => $tpl['title'],
                    'original' => $tpl['body'],
                    'formatted' => $text,
                    'wa_link' => 'https://wa.me/' . preg_replace('/[^0-9]/', '', $order->customer_phone) . '?text=' . urlencode($text),
                ];
            }
        }

        return view('crm.templates', compact('order', 'templates', 'formattedTemplates'));
    }
}
