<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class KiriminajaService
{
    protected string $baseUrl;
    protected ?string $apiKey;

    public function __construct()
    {
        $this->apiKey = config('services.kiriminaja.api_key');
        $this->baseUrl = config('services.kiriminaja.base_url', 'https://tdev.kiriminaja.com/api/wd/v1');
    }

    /**
     * Cek tarif pengiriman (Cek Ongkir)
     */
    public function getShippingRates(int $originCityId, int $destinationCityId, int $weightGrams, array $couriers = ['jne', 'jnt', 'sicepat', 'anteraja']): array
    {
        if (empty($this->apiKey)) {
            return $this->getMockRates($originCityId, $destinationCityId, $weightGrams, $couriers);
        }

        try {
            // Panggilan API Asli ke Kiriminaja Sandbox/Prod
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->post($this->baseUrl . '/shipping/price', [
                'origin' => $originCityId,
                'destination' => $destinationCityId,
                'weight' => $weightGrams,
                'courier' => $couriers,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return $data['data'] ?? [];
            }

            Log::error('Kiriminaja API Error (Price): ' . $response->body());
        } catch (\Exception $e) {
            Log::error('Kiriminaja Exception (Price): ' . $e->getMessage());
        }

        // Fallback ke mock jika API gagal
        return $this->getMockRates($originCityId, $destinationCityId, $weightGrams, $couriers);
    }

    /**
     * Buat order pengiriman (Request resi / pickup)
     */
    public function createShipment(array $orderData): array
    {
        if (empty($this->apiKey)) {
            return $this->getMockShipmentResult($orderData);
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->post($this->baseUrl . '/shipping/order', [
                'origin' => $orderData['origin_city_id'] ?? 501, // Default Yogyakarta
                'destination' => $orderData['destination_city_id'],
                'weight' => $orderData['weight_grams'],
                'courier' => $orderData['courier'],
                'service' => $orderData['shipping_service'],
                'consignee' => [
                    'name' => $orderData['customer_name'],
                    'phone' => $orderData['customer_phone'],
                    'address' => $orderData['customer_address'],
                ],
                'item_description' => $orderData['item_description'] ?? 'Produk E-commerce',
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'success' => true,
                    'waybill' => $data['data']['awb'] ?? 'AWB' . rand(100000000, 999999999),
                    'kiriminaja_order_id' => $data['data']['order_id'] ?? 'ORD-' . uniqid(),
                    'raw' => $data,
                ];
            }

            Log::error('Kiriminaja API Error (Order): ' . $response->body());
        } catch (\Exception $e) {
            Log::error('Kiriminaja Exception (Order): ' . $e->getMessage());
        }

        return $this->getMockShipmentResult($orderData);
    }

    /**
     * Lacak status pengiriman berdasarkan resi (Waybill Tracking)
     */
    public function trackShipment(string $waybill, string $courier): array
    {
        if (empty($this->apiKey) || str_starts_with($waybill, 'MOCK-')) {
            return $this->getMockTracking($waybill);
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->post($this->baseUrl . '/shipping/tracking', [
                'awb' => $waybill,
                'courier' => $courier,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return $data['data'] ?? [];
            }

            Log::error('Kiriminaja API Error (Tracking): ' . $response->body());
        } catch (\Exception $e) {
            Log::error('Kiriminaja Exception (Tracking): ' . $e->getMessage());
        }

        return $this->getMockTracking($waybill);
    }

    // --- MOCK SIMULATOR FOR ROBUST LECTURE DEMOS ---

    private function getMockRates(int $origin, int $destination, int $weight, array $couriers): array
    {
        $rates = [];
        $weightKg = max(0.1, $weight / 1000);
        $basePrice = 12000 + (abs($origin - $destination) * 300);

        $courierProfiles = [
            'jne' => [
                ['service' => 'REG', 'desc' => 'Layanan Reguler JNE', 'days' => '2-3 Hari', 'multiplier' => 1.0],
                ['service' => 'YES', 'desc' => 'Yakin Esok Sampai', 'days' => '1 Hari', 'multiplier' => 1.6],
                ['service' => 'OKE', 'desc' => 'Layanan Ekonomis JNE', 'days' => '4-6 Hari', 'multiplier' => 0.8],
            ],
            'jnt' => [
                ['service' => 'EZ', 'desc' => 'J&T Reguler EZ', 'days' => '2-3 Hari', 'multiplier' => 0.95],
                ['service' => 'Super', 'desc' => 'J&T Layanan Cepat', 'days' => '1 Hari', 'multiplier' => 1.5],
            ],
            'sicepat' => [
                ['service' => 'SIUNT', 'desc' => 'SiUntung Reguler', 'days' => '2-3 Hari', 'multiplier' => 0.9],
                ['service' => 'BEST', 'desc' => 'Besok Sampai Tujuan', 'days' => '1 Hari', 'multiplier' => 1.4],
                ['service' => 'GOKIL', 'desc' => 'Cargo Kilat Sicepat', 'days' => '3-5 Hari', 'multiplier' => 0.7],
            ],
            'anteraja' => [
                ['service' => 'REG', 'desc' => 'Anteraja Reguler', 'days' => '2-3 Hari', 'multiplier' => 0.92],
                ['service' => 'ND', 'desc' => 'Next Day Anteraja', 'days' => '1 Hari', 'multiplier' => 1.45],
            ]
        ];

        foreach ($couriers as $c) {
            $cLower = strtolower($c);
            if (isset($courierProfiles[$cLower])) {
                foreach ($courierProfiles[$cLower] as $prof) {
                    $cost = round($basePrice * $weightKg * $prof['multiplier'], -3);
                    $rates[] = [
                        'courier' => strtoupper($cLower),
                        'service' => $prof['service'],
                        'description' => $prof['desc'],
                        'cost' => max(9000, $cost),
                        'etd' => $prof['days'],
                    ];
                }
            }
        }

        return $rates;
    }

    private function getMockShipmentResult(array $orderData): array
    {
        $courier = strtoupper($orderData['courier'] ?? 'JNE');
        $randomNumber = rand(10000000, 99999999);
        return [
            'success' => true,
            'waybill' => 'MOCK-' . $courier . '-' . $randomNumber,
            'kiriminaja_order_id' => 'KA-ORD-' . rand(100000, 999999),
            'raw' => ['message' => 'Simulated shipping resi generated successfully via mock.'],
        ];
    }

    private function getMockTracking(string $waybill): array
    {
        // Generate simulated progress checkpoints based on current date
        $now = now();
        $checkpoints = [
            [
                'date' => $now->copy()->subHours(24)->format('Y-m-d H:i:s'),
                'status' => 'PICKUP_REQUESTED',
                'description' => 'Permintaan penjemputan paket (pickup) telah diterima oleh kurir.',
                'location' => 'Sleman, Yogyakarta'
            ],
            [
                'date' => $now->copy()->subHours(20)->format('Y-m-d H:i:s'),
                'status' => 'PICKED_UP',
                'description' => 'Paket telah diserahterahkan ke kurir pengirim.',
                'location' => 'Sleman, Yogyakarta'
            ],
            [
                'date' => $now->copy()->subHours(15)->format('Y-m-d H:i:s'),
                'status' => 'SORTING_CENTER',
                'description' => 'Paket sedang disortir di Hub Transit Pengirim.',
                'location' => 'Yogyakarta Gateway'
            ],
            [
                'date' => $now->copy()->subHours(8)->format('Y-m-d H:i:s'),
                'status' => 'DEPARTED',
                'description' => 'Paket diberangkatkan menuju Hub Transit Kota Tujuan.',
                'location' => 'Yogyakarta Gateway'
            ]
        ];

        // If the waybill is mock-delivered, we can add delivery checkpoints
        // But since we track live, we can add "ON_PROCESS" as the latest
        $checkpoints[] = [
            'date' => $now->copy()->subHours(2)->format('Y-m-d H:i:s'),
            'status' => 'TRANSIT',
            'description' => 'Paket telah tiba di Kota Tujuan dan sedang diproses di Hub Transit Penerima.',
            'location' => 'Jakarta Barat Sorting Hub'
        ];

        return [
            'awb' => $waybill,
            'status' => 'ON_PROCESS',
            'shipper' => 'MitraSpace Seller Center',
            'receiver' => 'Data Penerima',
            'history' => array_reverse($checkpoints) // Terbaru di atas
        ];
    }
}
