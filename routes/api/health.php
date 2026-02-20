<?php

declare(strict_types=1);

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

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
            'database' => checkDatabase(),
            'redis' => checkRedis(),
            'storage' => checkStorage(),
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
            'database' => checkDatabase(),
            'redis' => checkRedis(),
            'storage' => checkStorage(),
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
    $ready = checkDatabase()['status'] === 'ok' &&
             checkRedis()['status'] === 'ok';

    return response()->json([
        'status' => $ready ? 'ready' : 'not_ready',
        'timestamp' => now()->toIso8601String(),
    ], $ready ? 200 : 503);
})->name('api.health.ready');

// ──────────────────────────────────────────────
// FUNCIONES HELPER - Checks de servicios
// ──────────────────────────────────────────────

/**
 * Verifica la conexión a la base de datos
 */
function checkDatabase(): array
{
    try {
        $startTime = microtime(true);

        DB::connection()->getPdo();

        $duration = (microtime(true) - $startTime) * 1000;

        return [
            'status' => 'ok',
            'message' => 'Database connection successful',
            'connection' => DB::connection()->getName(),
            'database' => DB::connection()->getDatabaseName(),
            'response_time_ms' => round($duration, 2),
        ];
    } catch (\Exception $e) {
        Log::error('Database health check failed', [
            'error' => $e->getMessage(),
        ]);

        return [
            'status' => 'error',
            'message' => $e->getMessage(),
        ];
    }
}

/**
 * Verifica la conexión a Redis
 */
function checkRedis(): array
{
    try {
        $startTime = microtime(true);

        Redis::ping();

        $duration = (microtime(true) - $startTime) * 1000;

        // Obtener información de Redis
        $info = Redis::info('server');

        return [
            'status' => 'ok',
            'message' => 'Redis connection successful',
            'version' => $info['redis_version'] ?? 'unknown',
            'response_time_ms' => round($duration, 2),
        ];
    } catch (\Exception $e) {
        Log::error('Redis health check failed', [
            'error' => $e->getMessage(),
        ]);

        return [
            'status' => 'error',
            'message' => $e->getMessage(),
        ];
    }
}

/**
 * Verifica que el storage funcione correctamente
 */
function checkStorage(): array
{
    try {
        // Verificar escritura en storage local
        $testFile = storage_path('app/health_check.txt');

        file_put_contents($testFile, 'health_check_' . time());

        if (!file_exists($testFile)) {
            throw new \Exception('Cannot write to storage');
        }

        $content = file_get_contents($testFile);

        if ($content !== file_get_contents($testFile)) {
            throw new \Exception('Cannot read from storage');
        }

        // Limpiar archivo de prueba
        unlink($testFile);

        return [
            'status' => 'ok',
            'message' => 'Storage is writable',
        ];
    } catch (\Exception $e) {
        Log::error('Storage health check failed', [
            'error' => $e->getMessage(),
        ]);

        return [
            'status' => 'error',
            'message' => $e->getMessage(),
        ];
    }
}
