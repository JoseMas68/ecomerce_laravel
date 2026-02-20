<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\MetricsService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

/**
 * Comando de Monitoreo - Dashboard Interactivo
 *
 * Muestra un dashboard en tiempo real con métricas del sistema,
 * aplicación y negocio para PawfectShop.
 *
 * Uso:
 *   php artisan monitoring:dashboard
 *   php artisan monitoring:dashboard --refresh=5
 */
class MonitoringDashboard extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'monitoring:dashboard
                            {--refresh=5 : Intervalo de refresco en segundos}
                            {--once : Mostrar solo una vez y salir}
                            {--metrics=full : Tipo de métricas: full|system|app|business}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Muestra dashboard de monitoreo en tiempo real para PawfectShop';

    /**
     * Metrics service instance
     */
    protected MetricsService $metricsService;

    /**
     * Create a new command instance.
     */
    public function __construct(MetricsService $metricsService)
    {
        parent::__construct();
        $this->metricsService = $metricsService;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $refresh = (int) $this->option('refresh');
        $once = $this->option('once');
        $metricsType = $this->option('metrics');

        $this->info("🔍 PawfectShop - Monitoring Dashboard");
        $this->info("====================================");
        $this->newLine();

        do {
            // Limpiar pantalla si no es la primera iteración
            if (!$once) {
                $this->newLine();
                $this->info("🔄 Actualizando: " . now()->toDateTimeString());
                $this->info("Presiona Ctrl+C para salir");
                $this->newLine();
            }

            // Mostrar métricas según el tipo seleccionado
            match($metricsType) {
                'system' => $this->showSystemMetrics(),
                'app' => $this->showApplicationMetrics(),
                'business' => $this->showBusinessMetrics(),
                default => $this->showAllMetrics(),
            };

            $this->newLine();

            // Mostrar errores recientes si existen
            $this->showRecentErrors();

            // Mostrar queries lentas si existen
            $this->showSlowQueries();

            // Si no es modo single-run, esperar antes de refrescar
            if (!$once) {
                sleep($refresh);
            }

        } while (!$once);

        return Command::SUCCESS;
    }

    /**
     * Muestra todas las métricas
     */
    protected function showAllMetrics(): void
    {
        $this->showSystemMetrics();
        $this->newLine();
        $this->showApplicationMetrics();
        $this->newLine();
        $this->showBusinessMetrics();
    }

    /**
     * Muestra métricas del sistema
     */
    protected function showSystemMetrics(): void
    {
        $this->info("📊 MÉTRICAS DEL SISTEMA");
        $this->line("────────────────────────────────────");

        $metrics = $this->metricsService->getSystemMetrics();

        // CPU
        if (isset($metrics['load_average'])) {
            $load = $metrics['load_average']['1min'] ?? 0;
            $status = $load < 2.0 ? '✅' : '⚠️';
            $this->line("{$status} CPU Load (1m):  {$load}");
        }

        // Memoria
        $memory = $metrics['memory'];
        $memoryPercent = $memory['usage_percent'];
        $memoryStatus = $memoryPercent < 80 ? '✅' : '⚠️';
        $this->line("{$memoryStatus} Memoria:         {$memoryPercent}% ({$memory['current_mb']}MB / {$memory['limit_mb']})");

        // Disco
        $disk = $metrics['disk'];
        $diskPercent = $disk['usage_percent'];
        $diskStatus = $disk['status'] === 'ok' ? '✅' : '⚠️';
        $this->line("{$diskStatus} Disco:           {$diskPercent}% ({$disk['used_gb']}GB usado de {$disk['total_gb']}GB)");
    }

    /**
     * Muestra métricas de la aplicación
     */
    protected function showApplicationMetrics(): void
    {
        $this->info("⚡ MÉTRICAS DE LA APLICACIÓN");
        $this->line("────────────────────────────────────");

        $metrics = $this->metricsService->getApplicationMetrics();

        // Requests
        $requests = $metrics['requests'];
        $this->line("📈 Requests/min:   {$requests['per_minute']}");
        $this->line("📈 Requests/hour:  {$requests['per_hour']}");
        $this->line("⏱️  Avg Response:  {$requests['avg_response_time_ms']}ms");

        // Database
        $db = $metrics['database'];
        $dbStatus = $db['status'] === 'connected' ? '✅' : '❌';
        $this->line("{$dbStatus} Database:       {$db['status']}");
        $this->line("   Queries/s:      {$db['queries_per_second']}");
        $this->line("   Slow queries:   {$db['slow_queries']}");

        // Cache
        $cache = $metrics['cache'];
        $cacheStatus = $cache['status'] === 'connected' ? '✅' : '❌';
        $this->line("{$cacheStatus} Cache (Redis):  {$cache['status']}");
        if (isset($cache['hit_rate'])) {
            $this->line("   Hit Rate:       {$cache['hit_rate']}%");
            $this->line("   Total Keys:     {$cache['total_keys']}");
        }

        // Queue
        $queue = $metrics['queue'];
        $queueStatus = $queue['pending_jobs'] < 100 ? '✅' : '⚠️';
        $this->line("{$queueStatus} Queue:          {$queue['pending_jobs']} jobs pendientes");
        $this->line("   Failed jobs:    {$queue['failed_jobs']}");
    }

    /**
     * Muestra métricas de negocio
     */
    protected function showBusinessMetrics(): void
    {
        $this->info("💰 MÉTRICAS DE NEGOCIO");
        $this->line("────────────────────────────────────");

        $metrics = $this->metricsService->getBusinessMetrics();

        // Órdenes
        $orders = $metrics['orders'];
        $this->line("📦 Órdenes (1h):   {$orders['last_hour']}");
        $this->line("📦 Órdenes (24h):  {$orders['last_day']}");

        // Ingresos
        $revenue = $metrics['revenue'];
        $this->line("💵 Ingresos hoy:  \${$revenue['today']} {$revenue['currency']}");
        $this->line("💵 Ingresos sem:  \${$revenue['this_week']} {$revenue['currency']}");

        // Usuarios
        $users = $metrics['users'];
        $this->line("👥 Usuarios activos: {$users['active_last_24h']} (24h)");
        $this->line("👥 Nuevos hoy:      {$users['new_today']}");
        $this->line("👥 Total:           {$users['total']}");

        // Productos
        $products = $metrics['products'];
        $stockWarning = $products['low_stock'] > 0 ? '⚠️' : '✅';
        $stockCritical = $products['out_of_stock'] > 0 ? '❌' : '✅';
        $this->line("📦 Productos activos: {$products['active']}");
        $this->line("{$stockWarning} Stock bajo:       {$products['low_stock']}");
        $this->line("{$stockCritical} Sin stock:        {$products['out_of_stock']}");
    }

    /**
     * Muestra errores recientes del log estructurado
     */
    protected function showRecentErrors(): void
    {
        $logFile = storage_path('logs/pawfectshop.log');

        if (!File::exists($logFile)) {
            return;
        }

        $this->info("🚨 ERRORES RECIENTES");
        $this->line("────────────────────────────────────");

        // Leer últimas 100 líneas del log
        $lines = $this->tailCustom($logFile, 100);
        $errors = [];

        // Buscar errores en las líneas (formato JSON)
        foreach ($lines as $line) {
            $data = json_decode($line, true);

            if (json_last_error() === JSON_ERROR_NONE && isset($data['message'])) {
                if (
                    ($data['level'] ?? '') === 'ERROR' ||
                    ($data['level'] ?? '') === 'CRITICAL' ||
                    ($data['level_name'] ?? '') === 'ERROR' ||
                    ($data['level_name'] ?? '') === 'CRITICAL'
                ) {
                    $errors[] = $data;

                    if (count($errors) >= 5) {
                        break;
                    }
                }
            }
        }

        if (empty($errors)) {
            $this->line("✅ No hay errores recientes");
        } else {
            foreach ($errors as $error) {
                $message = substr($error['message'] ?? 'Unknown error', 0, 60);
                $this->line("❌ {$message}...");

                if (isset($error['context']['url'])) {
                    $this->line("   URL: {$error['context']['url']}");
                }
            }
        }
    }

    /**
     * Muestra queries lentas del log
     */
    protected function showSlowQueries(): void
    {
        $logFile = storage_path('logs/pawfectshop.log');

        if (!File::exists($logFile)) {
            return;
        }

        // Leer líneas del log
        $lines = $this->tailCustom($logFile, 100);
        $slowRequests = [];

        foreach ($lines as $line) {
            $data = json_decode($line, true);

            if (json_last_error() === JSON_ERROR_NONE) {
                if (
                    isset($data['message']) &&
                    $data['message'] === 'Slow request detected' &&
                    isset($data['context']['duration_ms'])
                ) {
                    $slowRequests[] = $data['context'];

                    if (count($slowRequests) >= 3) {
                        break;
                    }
                }
            }
        }

        if (!empty($slowRequests)) {
            $this->info("␣ REQUESTS LENTOS");
            $this->line("────────────────────────────────────");

            foreach ($slowRequests as $request) {
                $url = substr($request['url'] ?? 'unknown', 0, 50);
                $duration = $request['duration_ms'] ?? 0;
                $this->line("⏱️  {$url}... ({$duration}ms)");
            }
        }
    }

    /**
     * Función helper para leer las últimas N líneas de un archivo
     * Similar al comando 'tail -n' de Unix
     */
    protected function tailCustom(string $filename, int $lines = 10): array
    {
        if (!File::exists($filename)) {
            return [];
        }

        $file = new \SplFileObject($filename, 'r');
        $file->seek(PHP_INT_MAX);
        $lastLine = $file->key();

        $result = [];
        $startLine = max(0, $lastLine - $lines + 1);

        for ($i = $startLine; $i <= $lastLine; $i++) {
            $file->seek($i);
            $result[] = trim($file->current());
        }

        return array_reverse($result);
    }
}
