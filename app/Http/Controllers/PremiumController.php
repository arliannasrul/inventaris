<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Services\DokuPaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class PremiumController extends Controller
{
    public function __construct(private DokuPaymentService $doku)
    {
    }

    /**
     * Halaman pricing — tampilkan paket Premium.
     */
    public function index(): View
    {
        $user     = auth()->user();
        $plans    = DokuPaymentService::PLANS;
        $payments = $user ? $user->payments()->take(5)->get() : collect();

        return view('premium.index', compact('user', 'plans', 'payments'));
    }

    /**
     * Inisiasi pembayaran — redirect ke DOKU Checkout.
     */
    public function checkout(Request $request): RedirectResponse
    {
        $request->validate([
            'plan' => ['required', 'in:monthly,yearly'],
        ]);

        $user = auth()->user();

        try {
            $result = $this->doku->createCheckout($user, $request->input('plan'));

            return redirect()->away($result['checkout_url']);
        } catch (\Exception $e) {
            return redirect()->route('premium.index')
                ->with('error', 'Gagal membuat sesi pembayaran: ' . $e->getMessage());
        }
    }

    /**
     * Halaman sukses — ditampilkan setelah redirect dari DOKU setelah pembayaran.
     * CATATAN: Aktivasi premium dilakukan via webhook, bukan di sini.
     * Halaman ini hanya sebagai konfirmasi visual untuk user.
     */
    public function success(Request $request): View
    {
        // DOKU mengirim parameter di query string
        $orderId = $request->query('order.invoice_number')
                ?? $request->query('invoice_number')
                ?? null;

        $payment = null;
        if ($orderId) {
            $payment = Payment::where('order_id', $orderId)
                ->where('user_id', auth()->id())
                ->first();
        }

        return view('premium.success', compact('payment'));
    }

    /**
     * Halaman gagal/batal — ditampilkan jika user membatalkan pembayaran.
     */
    public function failed(Request $request): View
    {
        return view('premium.failed');
    }

    /**
     * Endpoint webhook dari DOKU.
     * DOKU mengirim notifikasi POST ke sini saat status transaksi berubah.
     * Route ini dikecualikan dari CSRF.
     */
    public function webhook(Request $request): Response
    {
        $payload   = $request->all();
        $signature = $request->header('Signature', '');
        $rawBody   = $request->getContent(); // Ambil raw JSON body

        try {
            $success = $this->doku->handleWebhook($payload, $signature, $rawBody);

            if ($success) {
                return response('OK', 200);
            }

            return response('Webhook processing failed', 422);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('DOKU Webhook Exception: ' . $e->getMessage());
            return response('Internal Server Error', 500);
        }
    }

    /**
     * Simulasikan webhook DOKU untuk testing di local.
     */
    public function simulateWebhook(string $orderId): RedirectResponse
    {
        if (!app()->environment('local')) {
            abort(403);
        }

        $payment = Payment::where('order_id', $orderId)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        if ($payment->status === 'pending') {
            $payment->update([
                'status'  => 'paid',
                'paid_at' => now(),
            ]);

            $this->doku->activatePremium($payment);

            return redirect()->route('premium.success', ['order.invoice_number' => $orderId])
                ->with('success', 'Simulasi pembayaran berhasil! Status premium Anda telah aktif.');
        }

        return redirect()->route('premium.success', ['order.invoice_number' => $orderId])
            ->with('info', 'Pembayaran sudah berstatus: ' . $payment->status);
    }
}
