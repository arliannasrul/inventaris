<?php

use App\Http\Controllers\InventoryController;
use Illuminate\Support\Facades\Route;

Route::get('/', [InventoryController::class, 'dashboard'])->name('dashboard');
Route::get('/items', [InventoryController::class, 'items'])->name('items.index');
Route::post('/items', [InventoryController::class, 'storeItem'])->name('items.store');
Route::get('/items/{id}', [InventoryController::class, 'showItem'])->name('items.show');
Route::get('/items/{id}/edit', [InventoryController::class, 'editItem'])->name('items.edit');
Route::post('/items/{id}/update', [InventoryController::class, 'updateItem'])->name('items.update');
Route::post('/items/{id}/movements', [InventoryController::class, 'storeMovement'])->name('movements.store');
Route::post('/items/{id}/messages', [InventoryController::class, 'storeMessage'])->name('messages.store');
Route::get('/reports', [InventoryController::class, 'reports'])->name('reports.index');
Route::get('/reports/print', [InventoryController::class, 'printReport'])->name('reports.print');
Route::get('/notifications', [InventoryController::class, 'notifications'])->name('notifications.index');
Route::post('/notifications/{id}/read', [InventoryController::class, 'markNotificationRead'])->name('notifications.read');
