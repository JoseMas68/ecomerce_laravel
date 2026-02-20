<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\Shipping\ShippingController;
use Illuminate\Support\Facades\Route;

// ──────────────────────────────────────────────
// Shipping API Routes v1
// ──────────────────────────────────────────────

Route::middleware('auth:sanctum')->group(function () {
    // Calcular costos de envío para el carrito actual
    Route::post('/shipping/calculate', [ShippingController::class, 'calculate'])->name('shipping.calculate');

    // Listar métodos de envío disponibles
    Route::get('/shipping/methods', [ShippingController::class, 'methods'])->name('shipping.methods');

    // Obtener zonas de envío disponibles
    Route::get('/shipping/zones', [ShippingController::class, 'zones'])->name('shipping.zones');

    // Validar código postal
    Route::post('/shipping/validate-postal-code', [ShippingController::class, 'validatePostalCode'])->name('shipping.validate-postal-code');
});
