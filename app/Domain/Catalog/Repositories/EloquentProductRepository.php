<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Repositories;

use App\Domain\Catalog\Models\Product;
use Illuminate\Database\Eloquent\Collection;

/**
 * Eloquent implementation of Product repository.
 */
class EloquentProductRepository implements ProductRepositoryInterface
{
    /**
     * {@inheritDoc}
     */
    public function all(): Collection
    {
        return Product::all();
    }

    /**
     * {@inheritDoc}
     */
    public function find(int $id): ?Product
    {
        return Product::find($id);
    }

    /**
     * {@inheritDoc}
     */
    public function findBySlug(string $slug): ?Product
    {
        return Product::where('slug', $slug)->first();
    }

    /**
     * {@inheritDoc}
     */
    public function create(array $data): Product
    {
        return Product::create($data);
    }

    /**
     * {@inheritDoc}
     */
    public function update(int $id, array $data): ?Product
    {
        $product = $this->find($id);

        if (!$product) {
            return null;
        }

        $product->update($data);

        return $product->fresh();
    }

    /**
     * {@inheritDoc}
     */
    public function delete(int $id): bool
    {
        $product = $this->find($id);

        if (!$product) {
            return false;
        }

        return $product->delete() !== false;
    }
}
