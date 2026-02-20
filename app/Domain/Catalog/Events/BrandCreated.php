<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Events;

use App\Domain\Catalog\Models\Brand;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * Event when a brand is created
 */
class BrandCreated
{
    use Dispatchable;

    /**
     * Create a new event instance.
     *
     * @param Brand $brand
     */
    public function __construct(
        public readonly Brand $brand
    ) {}
}
