<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\Payments\PaymentController;
use App\Http\Controllers\Api\V1\Payments\WebhookController;
use Illuminate\Support\Facades\Route;

// ──────────────────────────────────────────────
// Payments API Routes v1
// ──────────────────────────────────────────────

Route::middleware('auth:sanctum')->group(function () {
    // Crear intención de pago
    Route::post('/payments', [PaymentController::class, 'create'])->name('payments.create');

    // Confirmar pago
    Route::post('/payments/{id}/confirm', [PaymentController::class, 'confirm'])->name('payments.confirm');

    // Obtener estado del pago
    Route::get('/payments/{id}', [PaymentController::class, 'show'])->name('payments.show');

    // Listar métodos de pago disponibles
    Route::get('/payments/methods', [PaymentController::class, 'methods'])->name('payments.methods');
});

// Webhooks (externos - sin autenticación)
Route::post('/payments/webhooks/stripe', [WebhookController::class, 'stripe'])->name('webhooks.stripe');
Route::post('/payments/webhooks/paypal', [WebhookController::class, 'paypal'])->name('webhooks.paypal');
