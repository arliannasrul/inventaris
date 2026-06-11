<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\PremiumController;
use Illuminate\Support\Facades\Route;

// Auth Routes (Guest Only)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::get('/auth/google', [AuthController::class, 'redirectToGoogle'])->name('auth.google');
    Route::get('/auth/api/callback', [AuthController::class, 'handleGoogleCallback'])->name('auth.google.callback');
});

// Logout Route (Auth Only)
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// DOKU Webhook — dikecualikan dari CSRF, tidak perlu auth
Route::post('/webhook/doku', [PremiumController::class, 'webhook'])
    ->name('webhook.doku')
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);

// Protected Inventory Routes (Auth Only)
Route::middleware('auth')->group(function () {
    Route::get('/', [InventoryController::class, 'dashboard'])->name('dashboard');
    Route::get('/items', [InventoryController::class, 'items'])->name('items.index');
    Route::post('/items', [InventoryController::class, 'storeItem'])->name('items.store');
    Route::get('/items/{id}', [InventoryController::class, 'showItem'])->name('items.show');
    Route::get('/items/{id}/edit', [InventoryController::class, 'editItem'])->name('items.edit');
    Route::post('/items/{id}/update', [InventoryController::class, 'updateItem'])->name('items.update');
    Route::post('/items/{id}/movements', [InventoryController::class, 'storeMovement'])->name('movements.store');
    Route::post('/items/{id}/messages', [InventoryController::class, 'storeMessage'])->name('messages.store');
    Route::get('/notifications', [InventoryController::class, 'notifications'])->name('notifications.index');
    Route::post('/notifications/{id}/read', [InventoryController::class, 'markNotificationRead'])->name('notifications.read');

    // Laporan — khusus Premium
    Route::get('/reports', [InventoryController::class, 'reports'])->name('reports.index')->middleware('premium');
    Route::get('/reports/print', [InventoryController::class, 'printReport'])->name('reports.print')->middleware('premium');

    // Premium Routes
    Route::get('/premium', [PremiumController::class, 'index'])->name('premium.index');
    Route::post('/premium/checkout', [PremiumController::class, 'checkout'])->name('premium.checkout');
    Route::get('/premium/success', [PremiumController::class, 'success'])->name('premium.success');
    Route::get('/premium/failed', [PremiumController::class, 'failed'])->name('premium.failed');
    if (app()->environment('local')) {
        Route::post('/premium/simulate-webhook/{order_id}', [PremiumController::class, 'simulateWebhook'])->name('premium.simulate_webhook');
    }
});
