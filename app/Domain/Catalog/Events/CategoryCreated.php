<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Events;

use App\Domain\Catalog\Models\Category;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * Event when a category is created
 */
class CategoryCreated
{
    use Dispatchable;

    /**
     * Create a new event instance.
     *
     * @param Category $category
     */
    public function __construct(
        public readonly Category $category
    ) {}
}
