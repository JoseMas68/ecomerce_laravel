<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\Cart\CartController;
use Illuminate\Support\Facades\Route;

// ──────────────────────────────────────────────
// Cart API Routes v1
// ──────────────────────────────────────────────

// Todas las rutas de carrito requieren autenticación
Route::middleware('auth:sanctum')->group(function () {
    // Obtener carrito actual del usuario
    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');

    // Agregar item al carrito
    Route::post('/cart/items', [CartController::class, 'addItem'])->name('cart.items.add');

    // Actualizar cantidad de un item
    Route::put('/cart/items/{itemId}', [CartController::class, 'updateItem'])->name('cart.items.update');
    Route::patch('/cart/items/{itemId}', [CartController::class, 'updateItem'])->name('cart.items.update.patch');

    // Remover item del carrito
    Route::delete('/cart/items/{itemId}', [CartController::class, 'removeItem'])->name('cart.items.remove');

    // Limpiar carrito completo
    Route::delete('/cart', [CartController::class, 'clear'])->name('cart.clear');

    // Obtener total del carrito
    Route::get('/cart/total', [CartController::class, 'total'])->name('cart.total');

    // Aplicar cupón de descuento
    Route::post('/cart/coupon', [CartController::class, 'applyCoupon'])->name('cart.coupon.apply');

    // Remover cupón del carrito
    Route::delete('/cart/coupon', [CartController::class, 'removeCoupon'])->name('cart.coupon.remove');

    // Obtener resumen del carrito (totales)
    Route::get('/cart/summary', [CartController::class, 'summary'])->name('cart.summary');
});
