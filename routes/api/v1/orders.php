<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\Orders\OrderController;
use Illuminate\Support\Facades\Route;

// ──────────────────────────────────────────────
// Orders API Routes v1
// ──────────────────────────────────────────────

Route::middleware('auth:sanctum')->group(function () {
    // Crear nuevo pedido (checkout)
    Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');

    // Listar pedidos del usuario autenticado
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');

    // Obtener estadisticas del usuario
    Route::get('/orders/stats', [OrderController::class, 'stats'])->name('orders.stats');

    // Ver detalle de un pedido
    Route::get('/orders/{id}', [OrderController::class, 'show'])->name('orders.show');

    // Cancelar un pedido
    Route::patch('/orders/{id}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');

    // Obtener tracking de envio
    Route::get('/orders/{id}/track', [OrderController::class, 'track'])->name('orders.track');

    // Solicitar reembolso
    Route::post('/orders/{id}/refund', [OrderController::class, 'refund'])->name('orders.refund');
});
