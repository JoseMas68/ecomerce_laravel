<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

/**
 * Servicio de Recolección de Métricas
 *
 * Proporciona métodos para recolectar y reportar métricas
 * del sistema, aplicación y negocio para monitoreo.
 *
 * Métricas disponibles:
 * - Sistema: CPU, RAM, Disco
 * - Aplicación: Requests, Queries, Cache
 * - Negocio: Ordenes, Ingresos, Usuarios
 */
class MetricsService
{
    /**
     * Obtiene métricas del sistema (CPU, RAM, Disco)
     *
     * @return array Métricas del sistema con estado de salud
     */
    public function getSystemMetrics(): array
    {
        return [
            'timestamp' => now()->toIso8601String(),
            'cpu' => $this->getCpuUsage(),
            'memory' => $this->getMemoryUsage(),
            'disk' => $this->getDiskUsage(),
            'load_average' => $this->getLoadAverage(),
        ];
    }

    /**
     * Obtiene métricas de la aplicación
     *
     * @return array Métricas de rendimiento de la aplicación
     */
    public function getApplicationMetrics(): array
    {
        return [
            'timestamp' => now()->toIso8601String(),
            'requests' => $this->getRequestMetrics(),
            'database' => $this->getDatabaseMetrics(),
            'cache' => $this->getCacheMetrics(),
            'queue' => $this->getQueueMetrics(),
        ];
    }

    /**
     * Obtiene métricas de negocio
     *
     * @return array Métricas clave del negocio
     */
    public function getBusinessMetrics(): array
    {
        return [
            'timestamp' => now()->toIso8601String(),
            'orders' => $this->getOrderMetrics(),
            'revenue' => $this->getRevenueMetrics(),
            'users' => $this->getUserMetrics(),
            'products' => $this->getProductMetrics(),
        ];
    }

    /**
     * Obtiene todas las métricas consolidadas
     *
     * @return array Todas las métricas en un solo array
     */
    public function getAllMetrics(): array
    {
        return [
            'system' => $this->getSystemMetrics(),
            'application' => $this->getApplicationMetrics(),
            'business' => $this->getBusinessMetrics(),
        ];
    }

    // ──────────────────────────────────────────────
    // MÉTODOS PRIVADOS - MÉTRICAS DE SISTEMA
    // ──────────────────────────────────────────────

    /**
     * Obtiene el uso de CPU
     */
    private function getCpuUsage(): array
    {
        // En sistemas UNIX/Linux
        if (function_exists('sys_getloadavg')) {
            $load = sys_getloadavg();
            return [
                'load_1min' => $load[0] ?? 0,
                'load_5min' => $load[1] ?? 0,
                'load_15min' => $load[2] ?? 0,
                'status' => ($load[0] ?? 0) < 2.0 ? 'ok' : 'high',
            ];
        }

        // Fallback para Windows
        return [
            'status' => 'unknown',
            'message' => 'CPU metrics not available on Windows',
        ];
    }

    /**
     * Obtiene el uso de memoria
     */
    private function getMemoryUsage(): array
    {
        $memoryUsage = memory_get_usage(true);
        $memoryPeak = memory_get_peak_usage(true);

        return [
            'current_mb' => round($memoryUsage / 1024 / 1024, 2),
            'peak_mb' => round($memoryPeak / 1024 / 1024, 2),
            'limit_mb' => $this->getMemoryLimit(),
            'usage_percent' => $this->getMemoryUsagePercent(),
        ];
    }

    /**
     * Obtiene el límite de memoria de PHP
     */
    private function getMemoryLimit(): string
    {
        $memoryLimit = ini_get('memory_limit');
        return $memoryLimit === '-1' ? 'unlimited' : $memoryLimit;
    }

    /**
     * Calcula el porcentaje de uso de memoria
     */
    private function getMemoryUsagePercent(): float
    {
        $limit = ini_get('memory_limit');

        if ($limit === '-1') {
            return 0.0;
        }

        $memoryUsage = memory_get_usage(true);
        $limitBytes = $this->convertToBytes($limit);

        return $limitBytes > 0 ? round(($memoryUsage / $limitBytes) * 100, 2) : 0.0;
    }

    /**
     * Convierte notación de memoria a bytes
     */
    private function convertToBytes(string $value): int
    {
        $value = trim($value);
        $unit = strtolower($value[strlen($value) - 1]);
        $value = (int) $value;

        return match($unit) {
            'g' => $value * 1024 * 1024 * 1024,
            'm' => $value * 1024 * 1024,
            'k' => $value * 1024,
            default => $value,
        };
    }

    /**
     * Obtiene el uso de disco
     */
    private function getDiskUsage(): array
    {
        $path = base_path();

        if (!file_exists($path)) {
            return ['status' => 'error', 'message' => 'Path not found'];
        }

        $freeBytes = disk_free_space($path);
        $totalBytes = disk_total_space($path);
        $usedBytes = $totalBytes - $freeBytes;

        return [
            'total_gb' => round($totalBytes / 1024 / 1024 / 1024, 2),
            'used_gb' => round($usedBytes / 1024 / 1024 / 1024, 2),
            'free_gb' => round($freeBytes / 1024 / 1024 / 1024, 2),
            'usage_percent' => round(($usedBytes / $totalBytes) * 100, 2),
            'status' => ($usedBytes / $totalBytes) < 0.9 ? 'ok' : 'critical',
        ];
    }

    /**
     * Obtiene el promedio de carga del sistema
     */
    private function getLoadAverage(): ?array
    {
        if (function_exists('sys_getloadavg')) {
            $load = sys_getloadavg();
            return [
                '1min' => $load[0] ?? null,
                '5min' => $load[1] ?? null,
                '15min' => $load[2] ?? null,
            ];
        }

        return null;
    }

    // ──────────────────────────────────────────────
    // MÉTODOS PRIVADOS - MÉTRICAS DE APLICACIÓN
    // ──────────────────────────────────────────────

    /**
     * Obtiene métricas de requests
     */
    private function getRequestMetrics(): array
    {
        // Obtener contadores de Redis
        $requestsPerMinute = $this->getRedisCounter('requests:per_minute', 60);
        $requestsPerHour = $this->getRedisCounter('requests:per_hour', 3600);

        return [
            'per_minute' => $requestsPerMinute,
            'per_hour' => $requestsPerHour,
            'avg_response_time_ms' => $this->getAverageResponseTime(),
        ];
    }

    /**
     * Obtiene métricas de base de datos
     */
    private function getDatabaseMetrics(): array
    {
        try {
            $connection = DB::connection();
            $pdo = $connection->getPdo();

            return [
                'status' => 'connected',
                'connection_name' => $connection->getName(),
                'database' => $connection->getDatabaseName(),
                'queries_per_second' => $this->getQueriesPerSecond(),
                'slow_queries' => $this->getSlowQueriesCount(),
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Obtiene métricas de cache
     */
    private function getCacheMetrics(): array
    {
        try {
            // Test de conexión a Redis
            Redis::ping();

            // Obtener estadísticas
            $info = Redis::info('stats');

            return [
                'status' => 'connected',
                'driver' => 'redis',
                'hit_rate' => $this->calculateCacheHitRate($info),
                'total_keys' => Redis::dbsize(),
                'memory_usage' => $this->getRedisMemoryUsage(),
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Obtiene métricas de cola
     */
    private function getQueueMetrics(): array
    {
        $queues = config('queue.default', 'redis');
        $queueSizes = [];

        if ($queues === 'redis') {
            foreach (['default', 'emails', 'notifications'] as $queue) {
                $queueSizes[$queue] = Redis::connection('default')->llen('queues:' . $queue);
            }
        }

        return [
            'driver' => $queues,
            'pending_jobs' => array_sum($queueSizes),
            'queues' => $queueSizes,
            'failed_jobs' => $this->getFailedJobsCount(),
        ];
    }

    // ──────────────────────────────────────────────
    // MÉTODOS PRIVADOS - MÉTRICAS DE NEGOCIO
    // ──────────────────────────────────────────────

    /**
     * Obtiene métricas de órdenes
     */
    private function getOrderMetrics(): array
    {
        try {
            $now = now();
            $oneHourAgo = $now->copy()->subHour();

            // Métricas de la última hora
            $ordersLastHour = DB::table('orders')
                ->whereBetween('created_at', [$oneHourAgo, $now])
                ->count();

            $ordersLastDay = DB::table('orders')
                ->whereDate('created_at', $now->toDateString())
                ->count();

            return [
                'last_hour' => $ordersLastHour,
                'last_day' => $ordersLastDay,
                'status' => $ordersLastHour > 0 ? 'active' : 'normal',
            ];
        } catch (\Exception $e) {
            return [
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Obtiene métricas de ingresos
     */
    private function getRevenueMetrics(): array
    {
        try {
            $now = now();
            $today = $now->toDateString();

            $revenueToday = DB::table('orders')
                ->whereDate('created_at', $today)
                ->where('status', 'completed')
                ->sum('total');

            $revenueWeek = DB::table('orders')
                ->whereBetween('created_at', [$now->copy()->startOfWeek(), $now])
                ->where('status', 'completed')
                ->sum('total');

            return [
                'today' => (float) number_format($revenueToday, 2, '.', ''),
                'this_week' => (float) number_format($revenueWeek, 2, '.', ''),
                'currency' => 'USD',
            ];
        } catch (\Exception $e) {
            return [
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Obtiene métricas de usuarios
     */
    private function getUserMetrics(): array
    {
        try {
            $now = now();
            $oneDayAgo = $now->copy()->subDay();

            $activeUsers = DB::table('sessions')
                ->where('last_activity', '>=', $oneDayAgo->timestamp)
                ->count();

            $newUsersToday = DB::table('users')
                ->whereDate('created_at', $now->toDateString())
                ->count();

            return [
                'active_last_24h' => $activeUsers,
                'new_today' => $newUsersToday,
                'total' => DB::table('users')->count(),
            ];
        } catch (\Exception $e) {
            return [
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Obtiene métricas de productos
     */
    private function getProductMetrics(): array
    {
        try {
            return [
                'total' => DB::table('products')->count(),
                'active' => DB::table('products')->where('is_active', true)->count(),
                'low_stock' => DB::table('products')
                    ->where('stock', '<', 10)
                    ->where('stock', '>', 0)
                    ->count(),
                'out_of_stock' => DB::table('products')
                    ->where('stock', '<=', 0)
                    ->count(),
            ];
        } catch (\Exception $e) {
            return [
                'error' => $e->getMessage(),
            ];
        }
    }

    // ──────────────────────────────────────────────
    // MÉTODOS HELPER
    // ──────────────────────────────────────────────

    /**
     * Obtiene contador de Redis para un periodo
     */
    private function getRedisCounter(string $key, int $seconds): int
    {
        try {
            return (int) Redis::get($key) ?: 0;
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Obtiene el tiempo promedio de respuesta
     */
    private function getAverageResponseTime(): float
    {
        try {
            $total = Redis::get('metrics:response_time:total') ?: 0;
            $count = Redis::get('metrics:response_time:count') ?: 0;

            return $count > 0 ? (float) ($total / $count) : 0.0;
        } catch (\Exception $e) {
            return 0.0;
        }
    }

    /**
     * Obtiene queries por segundo
     */
    private function getQueriesPerSecond(): int
    {
        try {
            return (int) Redis::get('metrics:queries:count') ?: 0;
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Obtiene conteo de queries lentas
     */
    private function getSlowQueriesCount(): int
    {
        try {
            return (int) Redis::get('metrics:queries:slow') ?: 0;
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Calcula el hit rate de cache
     */
    private function calculateCacheHitRate(array $info): float
    {
        try {
            $hits = (int) ($info['keyspace_hits'] ?? 0);
            $misses = (int) ($info['keyspace_misses'] ?? 0);
            $total = $hits + $misses;

            return $total > 0 ? round(($hits / $total) * 100, 2) : 0.0;
        } catch (\Exception $e) {
            return 0.0;
        }
    }

    /**
     * Obtiene uso de memoria de Redis
     */
    private function getRedisMemoryUsage(): array
    {
        try {
            $info = Redis::info('memory');

            return [
                'used_mb' => round(($info['used_memory'] ?? 0) / 1024 / 1024, 2),
                'peak_mb' => round(($info['used_memory_peak'] ?? 0) / 1024 / 1024, 2),
                'max_mb' => isset($info['maxmemory']) && $info['maxmemory'] > 0
                    ? round($info['maxmemory'] / 1024 / 1024, 2)
                    : 'unlimited',
            ];
        } catch (\Exception $e) {
            return [
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Obtiene conteo de jobs fallidos
     */
    private function getFailedJobsCount(): int
    {
        try {
            return DB::table('failed_jobs')->count();
        } catch (\Exception $e) {
            return 0;
        }
    }
}
