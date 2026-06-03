<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ImageUploadService
{
    private ?string $cloudName;
    private ?string $apiKey;
    private ?string $apiSecret;

    public function __construct()
    {
        $this->cloudName = env('CLOUDINARY_CLOUD_NAME');
        $this->apiKey = env('CLOUDINARY_API_KEY');
        $this->apiSecret = env('CLOUDINARY_API_SECRET');
    }

    /**
     * Upload an image to Cloudinary (if configured) or local storage (as fallback).
     *
     * @param UploadedFile $file
     * @return string URL path or secure Cloudinary URL
     */
    public function upload(UploadedFile $file): string
    {
        if ($this->isCloudinaryConfigured()) {
            try {
                return $this->uploadToCloudinary($file);
            } catch (\Exception $e) {
                Log::error('Cloudinary upload failed, falling back to local storage: ' . $e->getMessage());
            }
        }

        return $this->uploadToLocal($file);
    }

    /**
     * Check if Cloudinary is fully configured.
     *
     * @return bool
     */
    private function isCloudinaryConfigured(): bool
    {
        return !empty($this->cloudName) && !empty($this->apiKey) && !empty($this->apiSecret);
    }

    /**
     * Upload to Cloudinary using their REST API.
     *
     * @param UploadedFile $file
     * @return string
     * @throws \Exception
     */
    private function uploadToCloudinary(UploadedFile $file): string
    {
        $timestamp = time();
        $serialized = "timestamp={$timestamp}";
        $signature = sha1($serialized . $this->apiSecret);

        $url = "https://api.cloudinary.com/v1_1/{$this->cloudName}/image/upload";

        $response = Http::asMultipart()
            ->attach('file', fopen($file->getRealPath(), 'r'), $file->getClientOriginalName())
            ->attach('timestamp', $timestamp)
            ->attach('api_key', $this->apiKey)
            ->attach('signature', $signature)
            ->post($url);

        if ($response->failed()) {
            throw new \Exception($response->body());
        }

        $data = $response->json();
        if (isset($data['secure_url'])) {
            return $data['secure_url'];
        }

        throw new \Exception('Cloudinary did not return a secure_url');
    }

    /**
     * Upload to local public disk.
     *
     * @param UploadedFile $file
     * @return string
     */
    private function uploadToLocal(UploadedFile $file): string
    {
        $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
        
        // Ensure the directory exists
        $targetDir = public_path('uploads/items');
        if (!file_exists($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        $file->move($targetDir, $filename);

        return '/uploads/items/' . $filename;
    }
}
