<?php

declare(strict_types=1);

namespace App\Infrastructure\Providers;

use App\Domain\Catalog\Repositories\EloquentProductRepository;
use App\Domain\Catalog\Repositories\ProductRepositoryInterface;
use Illuminate\Support\ServiceProvider;

class CatalogServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Repository bindings
        $this->app->bind(
            ProductRepositoryInterface::class,
            EloquentProductRepository::class
        );
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Load migrations for this domain
        $this->loadMigrationsFrom(database_path('migrations/domain/catalog'));

        // Load routes (optional - already loaded via routes/api.php)
        // $this->loadRoutesFrom(base_path('routes/api/v1/catalog.php'));

        // Load views for this domain (if needed)
        // $this->loadViewsFrom(resource_path('views/domains/catalog'), 'catalog');
    }
}
