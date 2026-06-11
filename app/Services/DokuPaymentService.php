<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Service untuk integrasi DOKU Payment Gateway.
 *
 * Menggunakan DOKU Checkout (Hosted Payment Page) — cara paling aman karena
 * data sensitif diproses langsung oleh DOKU, bukan server kita.
 *
 * Referensi: https://developer.doku.com/
 */
class DokuPaymentService
{
    /**
     * Harga paket dalam Rupiah.
     */
    public const PLANS = [
        'monthly' => [
            'label'  => 'Premium Bulanan',
            'amount' => 99000,
            'months' => 1,
        ],
        'yearly' => [
            'label'  => 'Premium Tahunan',
            'amount' => 899000,
            'months' => 12,
        ],
    ];

    private string $clientId;
    private string $secretKey;
    private string $baseUrl;

    public function __construct()
    {
        $this->clientId  = config('services.doku.client_id', '');
        $this->secretKey = config('services.doku.secret_key', '');
        $this->baseUrl   = config('services.doku.base_url', 'https://sandbox.doku.com');
    }

    /**
     * Membuat sesi DOKU Checkout dan menyimpan payment record.
     *
     * @return array{payment: Payment, checkout_url: string}
     */
    public function createCheckout(User $user, string $plan): array
    {
        if (!array_key_exists($plan, self::PLANS)) {
            throw new \InvalidArgumentException("Plan '{$plan}' tidak valid.");
        }

        $planData  = self::PLANS[$plan];
        $orderId   = 'INV-' . strtoupper(Str::random(12)) . '-' . time();
        $amount    = $planData['amount'];

        // Request-Timestamp HARUS waktu sekarang dalam UTC
        $requestTimestamp = now()->utc()->format('Y-m-d\TH:i:s\Z');

        // 1. Bangun body request dulu
        $requestBody = [
            'client' => [
                'id' => $this->clientId,
            ],
            'order' => [
                'invoice_number'      => $orderId,
                'line_items'          => [
                    [
                        'name'     => $planData['label'],
                        'price'    => $amount,
                        'quantity' => 1,
                    ],
                ],
                'amount'              => $amount,
                'currency'            => 'IDR',
                'callback_url'        => route('premium.success', ['invoice_number' => $orderId]),
                'callback_url_cancel' => route('premium.failed', ['invoice_number' => $orderId]),
            ],
            'payment' => [
                'payment_due_date' => 60, // menit
            ],
            'customer' => [
                'name'  => $user->name,
                'email' => $user->email,
            ],
        ];

        $bodyJson = json_encode($requestBody, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        // 2. Hitung Digest dari SELURUH body JSON
        $digest = $this->generateDigest($bodyJson);

        // 3. Bangun string yang akan di-sign
        $componentToSign = "Client-Id:{$this->clientId}\n"
            . "Request-Id:{$orderId}\n"
            . "Request-Timestamp:{$requestTimestamp}\n"
            . "Request-Target:/checkout/v1/payment\n"
            . "Digest:{$digest}";

        $signature = base64_encode(hash_hmac('sha256', $componentToSign, $this->secretKey, true));

        Log::info('DOKU Checkout Request', [
            'order_id'  => $orderId,
            'user_id'   => $user->id,
            'plan'      => $plan,
            'timestamp' => $requestTimestamp,
        ]);

        // 4. Kirim request ke DOKU
        $response = Http::withHeaders([
            'Client-Id'         => $this->clientId,
            'Request-Id'        => $orderId,
            'Request-Timestamp' => $requestTimestamp,
            'Signature'         => 'HMACSHA256=' . $signature,
            'Content-Type'      => 'application/json',
        ])->withBody($bodyJson, 'application/json')
          ->post($this->baseUrl . '/checkout/v1/payment');

        if ($response->failed()) {
            Log::error('DOKU Checkout Gagal', [
                'status'   => $response->status(),
                'response' => $response->body(),
            ]);
            throw new \RuntimeException('Gagal membuat sesi pembayaran DOKU: ' . $response->body());
        }

        $responseData = $response->json();
        $checkoutUrl  = $responseData['response']['payment']['url'] ?? null;

        if (!$checkoutUrl) {
            Log::error('DOKU tidak mengembalikan URL', ['response' => $responseData]);
            throw new \RuntimeException('DOKU tidak mengembalikan URL pembayaran. Response: ' . json_encode($responseData));
        }

        // Simpan payment record ke database
        $payment = Payment::create([
            'user_id'          => $user->id,
            'order_id'         => $orderId,
            'invoice_number'   => $responseData['order']['invoice_number'] ?? $orderId,
            'amount'           => $amount,
            'plan'             => $plan,
            'status'           => 'pending',
            'doku_payment_url' => $checkoutUrl,
            'doku_response'    => $responseData,
        ]);

        return [
            'payment'      => $payment,
            'checkout_url' => $checkoutUrl,
        ];
    }

    /**
     * Memproses notifikasi webhook dari DOKU.
     * DOKU mengirim notifikasi saat status pembayaran berubah.
     *
     * @return bool true jika berhasil diproses
     */
    public function handleWebhook(array $payload, string $signature, string $rawBody): bool
    {
        // Verifikasi signature dari DOKU menggunakan raw body
        if (!$this->verifyWebhookSignature($payload, $signature, $rawBody)) {
            Log::warning('DOKU Webhook: Signature tidak valid', ['payload' => $payload]);
            return false;
        }

        $orderId      = $payload['order']['invoice_number'] ?? null;
        $paymentStatus = $payload['transaction']['status'] ?? null;

        if (!$orderId) {
            Log::warning('DOKU Webhook: Order ID tidak ditemukan', ['payload' => $payload]);
            return false;
        }

        $payment = Payment::where('order_id', $orderId)->first();

        if (!$payment) {
            Log::warning('DOKU Webhook: Payment tidak ditemukan', ['order_id' => $orderId]);
            return false;
        }

        // Update status payment
        if (in_array($paymentStatus, ['SUCCESS', 'DONE', 'PAID'])) {
            $payment->update([
                'status'        => 'paid',
                'paid_at'       => now(),
                'doku_response' => $payload,
            ]);

            // Aktifkan premium untuk user
            $this->activatePremium($payment);

            Log::info('DOKU Webhook: Pembayaran berhasil', [
                'order_id' => $orderId,
                'user_id'  => $payment->user_id,
            ]);
        } elseif (in_array($paymentStatus, ['FAILED', 'EXPIRED', 'CANCELLED'])) {
            $payment->update([
                'status'        => strtolower($paymentStatus),
                'doku_response' => $payload,
            ]);

            Log::info('DOKU Webhook: Pembayaran gagal/expired', [
                'order_id' => $orderId,
                'status'   => $paymentStatus,
            ]);
        }

        return true;
    }

    /**
     * Aktivasi status Premium pada user.
     */
    public function activatePremium(Payment $payment): void
    {
        $planData   = self::PLANS[$payment->plan] ?? self::PLANS['monthly'];
        $expiresAt  = now()->addMonths($planData['months']);

        // Jika user sudah premium & belum expired, perpanjang dari tanggal expiry
        $user = $payment->user;
        if ($user->isPremium() && $user->premium_expires_at && $user->premium_expires_at->isFuture()) {
            $expiresAt = $user->premium_expires_at->addMonths($planData['months']);
        }

        $user->update([
            'is_premium'          => true,
            'premium_plan'        => $payment->plan,
            'premium_expires_at'  => $expiresAt,
        ]);
    }


    /**
     * Generate SHA-256 digest dari body request.
     * Digest = Base64(SHA-256(requestBody))
     */
    private function generateDigest(string $body): string
    {
        return base64_encode(hash('sha256', $body, true));
    }

    /**
     * Verifikasi signature webhook dari DOKU.
     */
    private function verifyWebhookSignature(array $payload, string $receivedSignature, string $rawBody): bool
    {
        if (empty($this->secretKey)) {
            // Jika secret key belum dikonfigurasi, skip verifikasi (dev mode)
            return true;
        }

        $orderId   = $payload['order']['invoice_number'] ?? '';
        $timestamp = $payload['request']['timestamp'] ?? '';

        // Gunakan rawBody JSON mentah untuk menghitung digest
        $digest = $this->generateDigest($rawBody);

        // Request-Target untuk webhook callback Doku adalah /webhook/doku
        $componentToSign = "Client-Id:{$this->clientId}\n"
            . "Request-Id:{$orderId}\n"
            . "Request-Timestamp:{$timestamp}\n"
            . "Request-Target:/webhook/doku\n"
            . "Digest:{$digest}";

        $expected = 'HMACSHA256=' . base64_encode(
            hash_hmac('sha256', $componentToSign, $this->secretKey, true)
        );

        return hash_equals($expected, $receivedSignature);
    }
}
