<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\InventoryController;
use Illuminate\Support\Facades\Route;

// Auth Routes (Guest Only)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
    Route::get('/auth/google', [AuthController::class, 'redirectToGoogle'])->name('auth.google');
    Route::get('/auth/api/callback', [AuthController::class, 'handleGoogleCallback'])->name('auth.google.callback');
});

// Logout Route (Auth Only)
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Protected Inventory, Order, and CRM Routes (Auth Only)
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

    // Laporan — Terbuka untuk semua seller
    Route::get('/reports', [InventoryController::class, 'reports'])->name('reports.index');
    Route::get('/reports/print', [InventoryController::class, 'printReport'])->name('reports.print');

    // Order & Tracking (API Kiriminaja)
    Route::get('/orders', [\App\Http\Controllers\OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/simulation', [\App\Http\Controllers\OrderController::class, 'showSimulation'])->name('orders.simulation');
    Route::post('/orders/simulate', [\App\Http\Controllers\OrderController::class, 'storeSimulation'])->name('orders.simulate');
    Route::post('/orders/api/rates', [\App\Http\Controllers\OrderController::class, 'apiGetRates'])->name('orders.api.rates');
    Route::get('/orders/{id}', [\App\Http\Controllers\OrderController::class, 'show'])->name('orders.show');
    Route::post('/orders/{id}/process', [\App\Http\Controllers\OrderController::class, 'processShipment'])->name('orders.process');
    Route::get('/orders/{id}/tracking', [\App\Http\Controllers\OrderController::class, 'trackShipment'])->name('orders.tracking');
    Route::post('/orders/{id}/complete', [\App\Http\Controllers\OrderController::class, 'completeOrder'])->name('orders.complete');

    // CRM Routes
    Route::get('/crm', [\App\Http\Controllers\CrmController::class, 'index'])->name('crm.index');
    Route::get('/crm/templates', [\App\Http\Controllers\CrmController::class, 'showTemplates'])->name('crm.templates');
});

// API endpoints for E-commerce Microservice (Public / Tokenless / Simple cross-origin)
Route::prefix('api/ecommerce')->group(function () {
    Route::get('/products', [\App\Http\Controllers\ApiController::class, 'getProducts']);
    Route::post('/checkout', [\App\Http\Controllers\ApiController::class, 'placeOrder']);
    Route::get('/tracking/{order_number}', [\App\Http\Controllers\ApiController::class, 'trackOrder']);
});
