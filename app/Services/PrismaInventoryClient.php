<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

class PrismaInventoryClient
{
    private PendingRequest $http;

    public function __construct()
    {
        $this->http = Http::baseUrl(rtrim(config('services.prisma.url'), '/'))
            ->acceptJson()
            ->asJson()
            ->withToken(config('services.prisma.token'))
            ->timeout(15)
            ->retry(2, 150);
    }

    public function dashboard(): array
    {
        return $this->get('/dashboard');
    }

    public function items(array $filters = []): array
    {
        return $this->get('/items', $filters);
    }

    public function item(string $id): array
    {
        return $this->get("/items/{$id}");
    }

    public function createItem(array $payload): array
    {
        return $this->post('/items', $payload);
    }

    public function createMovement(string $itemId, array $payload): array
    {
        return $this->post("/items/{$itemId}/movements", $payload);
    }

    public function createMessage(string $itemId, array $payload): array
    {
        return $this->post("/items/{$itemId}/messages", $payload);
    }

    public function reports(array $filters = []): array
    {
        return $this->get('/reports', $filters);
    }

    public function notifications(): array
    {
        return $this->get('/notifications');
    }

    public function markNotificationRead(string $id): array
    {
        return $this->post("/notifications/{$id}/read", []);
    }

    private function get(string $path, array $query = []): array
    {
        return $this->http->get($path, $query)->throw()->json();
    }

    private function post(string $path, array $payload): array
    {
        return $this->http->post($path, $payload)->throw()->json();
    }
}
