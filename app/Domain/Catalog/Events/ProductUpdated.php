<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Events;

use App\Domain\Catalog\Models\Product;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * Event when a product is updated
 */
class ProductUpdated
{
    use Dispatchable;

    /**
     * Create a new event instance.
     *
     * @param Product $product
     */
    public function __construct(
        public readonly Product $product
    ) {}
}
