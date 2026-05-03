<?php

declare(strict_types=1);

namespace App\Helpers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

/**
 * Helper functions for health checks
 */
class HealthCheckHelpers
{
    /**
     * Verifica la conexión a la base de datos
     */
    public static function checkDatabase(): array
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
    public static function checkRedis(): array
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
    public static function checkStorage(): array
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
}
