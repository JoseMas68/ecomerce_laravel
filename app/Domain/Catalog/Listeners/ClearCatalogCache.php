<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Listeners;

use App\Domain\Catalog\Events\BrandCreated;
use App\Domain\Catalog\Events\BrandUpdated;
use App\Domain\Catalog\Events\BrandDeleted;
use App\Domain\Catalog\Events\CategoryCreated;
use App\Domain\Catalog\Events\CategoryUpdated;
use App\Domain\Catalog\Events\ProductCreated;
use App\Domain\Catalog\Events\ProductUpdated;
use Illuminate\Support\Facades\Cache;

/**
 * Listener to clear catalog cache when entities are modified
 */
class ClearCatalogCache
{
    /**
     * Handle the event.
     *
     * @param BrandCreated|BrandUpdated|BrandDeleted|CategoryCreated|CategoryUpdated|ProductCreated|ProductUpdated $event
     * @return void
     */
    public function handle($event): void
    {
        // Clear all catalog-related caches
        Cache::forget('catalog.tree');
        Cache::forget('catalog.brands.all');
        Cache::forget('catalog.categories.all');
        Cache::forget('catalog.products.featured');
        Cache::forget('catalog.stats');

        // You can also clear cache by pattern if using a cache driver that supports it
        // For Redis or Memcached, you can use:
        // Cache::store()->flush(); // Clears all cache (be careful!)

        // Or use cache tags if your driver supports it:
        // Cache::tags(['catalog'])->flush();

        // Log cache clearing for debugging
        \Log::info('Catalog cache cleared', [
            'event' => get_class($event),
            'entity_id' => $event->brand->id ?? $event->category->id ?? $event->product->id ?? null,
        ]);
    }
}
