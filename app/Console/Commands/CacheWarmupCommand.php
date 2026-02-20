<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Domain\Catalog\Services\CatalogCacheService;

/**
 * Comando para precargar el caché del catálogo.
 *
 * Este comando "calienta" el caché cargando los datos más utilizados
 * en Redis, mejorando el tiempo de respuesta de las primeras peticiones.
 *
 * Uso:
 * - php artisan cache:warmup (precarga todo el catálogo)
 * - php artisan cache:warmup --categories (solo categorías)
 * - php artisan cache:warmup --products (solo productos destacados)
 * - php artisan cache:warmup --clear (limpia todo el caché antes de precargar)
 *
 * Se recomienda ejecutar después de:
 * - Deployments
 * - Cambios masivos en productos/categorías
 * - Limpieza de caché
 */
class CacheWarmupCommand extends Command
{
    /**
     * Nombre y firma del comando.
     */
    protected $signature = 'cache:warmup
                            {--categories : Precargar solo categorías}
                            {--products : Precargar solo productos}
                            {--brands : Precargar solo marcas}
                            {--all : Precargar todo (default)}
                            {--clear : Limpiar caché existente antes de precargar}';

    /**
     * Descripción del comando.
     */
    protected $description = 'Precarga el caché del catálogo para mejorar tiempos de respuesta iniciales';

    /**
     * Instancia del servicio de caché.
     */
    protected CatalogCacheService $cacheService;

    /**
     * Estadísticas de la precarga.
     */
    protected array $stats = [
        'categories' => false,
        'brands' => false,
        'featured_products' => false,
        'config' => false,
        'errors' => [],
    ];

    /**
     * Crear nueva instancia del comando.
     */
    public function __construct(CatalogCacheService $cacheService)
    {
        parent::__construct();
        $this->cacheService = $cacheService;
    }

    /**
     * Ejecutar el comando.
     */
    public function handle(): int
    {
        $startTime = microtime(true);

        $this->info('🔥 Iniciando precarga de caché...');
        $this->newLine();

        // Limpiar caché si se solicitó
        if ($this->option('clear')) {
            $this->warn('Limpiando caché existente...');
            $this->clearCache();
            $this->info('✓ Caché limpiado');
            $this->newLine();
        }

        // Ejecutar precargas según opciones
        $this->warmup();

        // Mostrar resumen
        $this->showSummary($startTime);

        return self::SUCCESS;
    }

    /**
     * Ejecutar la lógica de precarga.
     */
    protected function warmup(): void
    {
        $options = [
            'categories' => $this->option('categories') || $this->option('all') || (!$this->option('products') && !$this->option('brands')),
            'products' => $this->option('products') || $this->option('all') || (!$this->option('categories') && !$this->option('brands')),
            'brands' => $this->option('brands') || $this->option('all') || (!$this->option('categories') && !$this->option('products')),
        ];

        // Precargar categorías
        if ($options['categories']) {
            $this->warmupCategories();
        }

        // Precargar marcas
        if ($options['brands']) {
            $this->warmupBrands();
        }

        // Precargar productos
        if ($options['products']) {
            $this->warmupProducts();
        }

        // Precargar configuración
        $this->warmupConfig();
    }

    /**
     * Precargar categorías en caché.
     */
    protected function warmupCategories(): void
    {
        $this->info('Precargando categorías...');

        try {
            $bar = $this->output->createProgressBar(1);
            $bar->start();

            $categories = $this->cacheService->rememberCategories();

            $bar->finish();
            $this->newLine();

            $this->stats['categories'] = true;
            $this->info("✓ Categorías precargadas: {$categories->count()} categorías");
        } catch (\Exception $e) {
            $this->error('✗ Error al precargar categorías: ' . $e->getMessage());
            $this->stats['errors'][] = 'categories: ' . $e->getMessage();
        }

        $this->newLine();
    }

    /**
     * Precargar marcas en caché.
     */
    protected function warmupBrands(): void
    {
        $this->info('Precargando marcas...');

        try {
            $bar = $this->output->createProgressBar(1);
            $bar->start();

            $brands = $this->cacheService->rememberBrands();

            $bar->finish();
            $this->newLine();

            $this->stats['brands'] = true;
            $this->info("✓ Marcas precargadas: {$brands->count()} marcas");
        } catch (\Exception $e) {
            $this->error('✗ Error al precargar marcas: ' . $e->getMessage());
            $this->stats['errors'][] = 'brands: ' . $e->getMessage();
        }

        $this->newLine();
    }

    /**
     * Precargar productos destacados en caché.
     */
    protected function warmupProducts(): void
    {
        $this->info('Precargando productos destacados...');

        try {
            $bar = $this->output->createProgressBar(1);
            $bar->start();

            $products = $this->cacheService->rememberFeaturedProducts(12);

            $bar->finish();
            $this->newLine();

            $this->stats['featured_products'] = true;
            $this->info("✓ Productos destacados precargados: {$products->count()} productos");
        } catch (\Exception $e) {
            $this->error('✗ Error al precargar productos: ' . $e->getMessage());
            $this->stats['errors'][] = 'products: ' . $e->getMessage();
        }

        $this->newLine();
    }

    /**
     * Precargar configuración de Laravel.
     */
    protected function warmupConfig(): void
    {
        $this->info('Precargando configuración...');

        try {
            // Cachear configuración
            \Illuminate\Support\Facades\Config::get('app.name');
            \Illuminate\Support\Facades\Config::get('app.url');
            \Illuminate\Support\Facades\Config::get('session');
            \Illuminate\Support\Facades\Config::get('cache');

            $this->stats['config'] = true;
            $this->info('✓ Configuración precargada');
        } catch (\Exception $e) {
            $this->error('✗ Error al precargar configuración: ' . $e->getMessage());
            $this->stats['errors'][] = 'config: ' . $e->getMessage();
        }

        $this->newLine();
    }

    /**
     * Limpiar todo el caché del catálogo.
     */
    protected function clearCache(): void
    {
        try {
            $this->cacheService->clearAllCatalogCache();

            // También limpiar caché general de Laravel
            Cache::flush();

            $this->info('✓ Caché del catálogo limpiado');
        } catch (\Exception $e) {
            $this->error('✗ Error al limpiar caché: ' . $e->getMessage());
            $this->stats['errors'][] = 'clear: ' . $e->getMessage();
        }

        $this->newLine();
    }

    /**
     * Mostrar resumen de la precarga.
     */
    protected function showSummary(float $startTime): void
    {
        $endTime = microtime(true);
        $duration = round(($endTime - $startTime) * 1000, 2);

        $this->newLine();
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->info('📊 RESUMEN DE PRECARGA DE CACHÉ');
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

        // Estado de cada componente
        $this->displayStatus('Categorías', $this->stats['categories']);
        $this->displayStatus('Marcas', $this->stats['brands']);
        $this->displayStatus('Productos destacados', $this->stats['featured_products']);
        $this->displayStatus('Configuración', $this->stats['config']);

        // Tiempo de ejecución
        $this->newLine();
        $this->info("⏱️  Tiempo de ejecución: {$duration}ms");

        // Errores si los hubo
        if (!empty($this->stats['errors'])) {
            $this->newLine();
            $this->warn('⚠️  Errores encontrados:');
            foreach ($this->stats['errors'] as $error) {
                $this->line("    - {$error}");
            }
        }

        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

        // Log del resultado
        Log::info('Cache warmup completed', [
            'duration' => $duration,
            'stats' => $this->stats,
        ]);
    }

    /**
     * Mostrar estado de un componente con color.
     */
    protected function displayStatus(string $label, bool $success): void
    {
        $icon = $success ? '✓' : '✗';
        $status = $success ? 'OK' : 'FALLÓ';

        if ($success) {
            $this->info("  {$icon} {$label}: <fg=green>{$status}</>");
        } else {
            $this->error("  {$icon} {$label}: <fg=red>{$status}</>");
        }
    }
}
