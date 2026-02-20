<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Events;

use App\Domain\Catalog\Models\Product;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * Event when product stock is low (<= 10)
 */
class StockLowUpdated
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
