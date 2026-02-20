<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

/**
 * Comando para cachear configuración específica del catálogo.
 *
 * Este comando precarga configuraciones frecuentemente usadas
 * reduciendo el tiempo de acceso a Config::get().
 *
 * Uso:
 * - php artisan config:warmup (precarga toda la config)
 * - php artisan config:warmup --clear (limpia cache de config primero)
 *
 * Se recomienda ejecutar en el deployment después de config:cache
 */
class ConfigCacheCommand extends Command
{
    /**
     * Nombre y firma del comando.
     */
    protected $signature = 'config:warmup
                            {--clear : Limpiar caché de configuración antes de precargar}';

    /**
     * Descripción del comando.
     */
    protected $description = 'Precarga configuración del catálogo en caché para mejorar acceso';

    /**
     * Configuraciones a precargar.
     */
    protected array $configs = [
        'app.name',
        'app.url',
        'app.env',
        'app.debug',
        'app.locale',
        'cache.default',
        'cache.prefix',
        'database.default',
        'database.connections.mysql',
        'session.driver',
        'session.lifetime',
        'queue.default',
        'filesystems.default',
    ];

    /**
     * Ejecutar el comando.
     */
    public function handle(): int
    {
        $startTime = microtime(true);

        $this->info('⚙️  Precargando configuración...');
        $this->newLine();

        // Limpiar cache si se solicitó
        if ($this->option('clear')) {
            $this->call('config:clear');
            $this->info('✓ Caché de configuración limpiada');
            $this->newLine();
        }

        // Precargar configuraciones
        $loaded = $this->warmupConfig();

        // Mostrar resumen
        $this->showSummary($loaded, $startTime);

        return self::SUCCESS;
    }

    /**
     * Precargar configuraciones en caché.
     */
    protected function warmupConfig(): array
    {
        $loaded = [];

        $this->info('Cargando configuraciones en memoria...');

        $bar = $this->output->createProgressBar(count($this->configs));
        $bar->start();

        foreach ($this->configs as $key) {
            try {
                // Acceder a la configuración la carga en caché
                Config::get($key);
                $loaded[] = $key;
            } catch (\Exception $e) {
                // Ignorar configuraciones que no existen
                $this->newLine();
                $this->warn("Configuración no encontrada: {$key}");
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();

        return $loaded;
    }

    /**
     * Mostrar resumen de la precarga.
     */
    protected function showSummary(array $loaded, float $startTime): void
    {
        $endTime = microtime(true);
        $duration = round(($endTime - $startTime) * 1000, 2);

        $this->newLine();
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->info('📊 RESUMEN DE PRECARGA DE CONFIGURACIÓN');
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

        $this->info("✓ Configuraciones cargadas: " . count($loaded));

        foreach ($loaded as $config) {
            $this->line("  - {$config}");
        }

        $this->newLine();
        $this->info("⏱️  Tiempo de ejecución: {$duration}ms");
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

        // Log del resultado
        Log::info('Config warmup completed', [
            'duration' => $duration,
            'loaded' => $loaded,
        ]);
    }
}
