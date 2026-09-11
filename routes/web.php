<?php

use App\Http\Controllers\PosController;
use App\Http\Controllers\SettingController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PosController::class, 'index'])->name('pos.index');
Route::post('/checkout', [PosController::class, 'checkout'])->name('pos.checkout');
Route::get('/history', [PosController::class, 'history'])->name('pos.history');
Route::patch('/orders/{order}/status', [PosController::class, 'updateStatus'])->name('pos.orders.status');
Route::get('/orders/{order}/invoice', [PosController::class, 'invoice'])->name('pos.invoice');

Route::get('/settings', [SettingController::class, 'index'])->name('pos.settings');
Route::post('/settings/products', [SettingController::class, 'store'])->name('pos.settings.products.store');
Route::put('/settings/products/{product}', [SettingController::class, 'update'])->name('pos.settings.products.update');
