<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

// ──────────────────────────────────────────────
// API Routes - Main Entry Point
// ──────────────────────────────────────────────

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// ──────────────────────────────────────────────
// Health Check Routes - Monitoreo de servicios
// ──────────────────────────────────────────────
// Incluir rutas de health check separadas
require __DIR__ . '/api/health.php';

// ──────────────────────────────────────────────
// API v1 Routes - Organized by Domain
// ──────────────────────────────────────────────
Route::prefix('v1')->group(function () {
    // Catalog Domain
    require __DIR__ . '/api/v1/catalog.php';

    // Cart Domain
    require __DIR__ . '/api/v1/cart.php';

    // Orders Domain
    require __DIR__ . '/api/v1/orders.php';

    // Users Domain
    require __DIR__ . '/api/v1/users.php';

    // Payments Domain
    require __DIR__ . '/api/v1/payments.php';

    // Shipping Domain
    require __DIR__ . '/api/v1/shipping.php';
});

// ──────────────────────────────────────────────
// API Versioning Information
// ──────────────────────────────────────────────
Route::get('/', function () {
    return response()->json([
        'name' => config('app.name'),
        'version' => '1.0.0',
        'description' => 'eCommerce API - Laravel 12 DDD Architecture',
        'docs' => [
            'v1' => '/api/v1',
            'health' => '/api/health',
        ],
    ]);
})->name('api.info');
