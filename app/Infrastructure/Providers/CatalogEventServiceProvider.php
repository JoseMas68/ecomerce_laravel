<?php

declare(strict_types=1);

namespace App\Infrastructure\Providers;

use App\Domain\Catalog\Events\BrandCreated;
use App\Domain\Catalog\Events\BrandUpdated;
use App\Domain\Catalog\Events\BrandDeleted;
use App\Domain\Catalog\Events\CategoryCreated;
use App\Domain\Catalog\Events\CategoryUpdated;
use App\Domain\Catalog\Events\ProductCreated;
use App\Domain\Catalog\Events\ProductUpdated;
use App\Domain\Catalog\Events\StockLowUpdated;
use App\Domain\Catalog\Listeners\ClearCatalogCache;
use App\Domain\Catalog\Listeners\NotifyLowStock;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

/**
 * Catalog Domain Event Service Provider
 *
 * Registers all event listeners for the Catalog domain
 */
class CatalogEventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the Catalog domain.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        // Brand Events
        BrandCreated::class => [
            ClearCatalogCache::class,
        ],
        BrandUpdated::class => [
            ClearCatalogCache::class,
        ],
        BrandDeleted::class => [
            ClearCatalogCache::class,
        ],

        // Category Events
        CategoryCreated::class => [
            ClearCatalogCache::class,
        ],
        CategoryUpdated::class => [
            ClearCatalogCache::class,
        ],

        // Product Events
        ProductCreated::class => [
            ClearCatalogCache::class,
        ],
        ProductUpdated::class => [
            ClearCatalogCache::class,
        ],

        // Stock Events
        StockLowUpdated::class => [
            NotifyLowStock::class,
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot(): void
    {
        parent::boot();
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     *
     * @return bool
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
