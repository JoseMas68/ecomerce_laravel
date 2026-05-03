<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use App\Helpers\HealthCheckHelpers;

/*
|--------------------------------------------------------------------------
| Health Check Routes
|--------------------------------------------------------------------------
|
| Rutas para monitoreo de salud de la aplicación PawfectShop.
| Estas rutas proveen endpoints para verificar el estado de los
| servicios críticos de la aplicación.
|
| IMPORTANTE: Estos endpoints NO requieren autenticación.
| En producción, proteger con IP whitelist o API tokens.
|
*/

// ──────────────────────────────────────────────
// Health Check Endpoint - Verificación completa de servicios
// ──────────────────────────────────────────────
Route::get('/health', function () {
    $health = [
        'status' => 'healthy',
        'timestamp' => now()->toIso8601String(),
        'version' => '1.0.0',
        'environment' => app()->environment(),
        'services' => [
            'database' => HealthCheckHelpers::checkDatabase(),
            'redis' => HealthCheckHelpers::checkRedis(),
            'storage' => HealthCheckHelpers::checkStorage(),
        ],
    ];

    // Determinar el estado general basado en los servicios
    $allHealthy = collect($health['services'])->every(function ($service) {
        return $service['status'] === 'ok';
    });

    $health['status'] = $allHealthy ? 'healthy' : 'degraded';

    // Loggear health check (útil para análisis de disponibilidad)
    Log::info('Health check performed', [
        'status' => $health['status'],
        'services' => $health['services'],
    ]);

    return response()->json(
        $health,
        $allHealthy ? 200 : 503
    );
})->name('api.health');

// ──────────────────────────────────────────────
// Health Check Detallado - Incluye métricas
// ──────────────────────────────────────────────
Route::get('/health/detailed', function () {
    $metricsService = new \App\Services\MetricsService();

    $health = [
        'status' => 'healthy',
        'timestamp' => now()->toIso8601String(),
        'version' => '1.0.0',
        'environment' => app()->environment(),
        'services' => [
            'database' => HealthCheckHelpers::checkDatabase(),
            'redis' => HealthCheckHelpers::checkRedis(),
            'storage' => HealthCheckHelpers::checkStorage(),
        ],
        'metrics' => [
            'system' => $metricsService->getSystemMetrics(),
        ],
    ];

    // Determinar el estado general
    $allHealthy = collect($health['services'])->every(function ($service) {
        return $service['status'] === 'ok';
    });

    $health['status'] = $allHealthy ? 'healthy' : 'degraded';

    return response()->json(
        $health,
        $allHealthy ? 200 : 503
    );
})->name('api.health.detailed');

// ──────────────────────────────────────────────
// Liveness Probe - Para Kubernetes/orchestration
// ──────────────────────────────────────────────
Route::get('/health/live', function () {
    return response()->json([
        'status' => 'alive',
        'timestamp' => now()->toIso8601String(),
    ]);
})->name('api.health.live');

// ──────────────────────────────────────────────
// Readiness Probe - Para Kubernetes/orchestration
// ──────────────────────────────────────────────
Route::get('/health/ready', function () {
    // Verificar que la aplicación pueda aceptar tráfico
    $ready = HealthCheckHelpers::checkDatabase()['status'] === 'ok' &&
             HealthCheckHelpers::checkRedis()['status'] === 'ok';

    return response()->json([
        'status' => $ready ? 'ready' : 'not_ready',
        'timestamp' => now()->toIso8601String(),
    ], $ready ? 200 : 503);
})->name('api.health.ready');
