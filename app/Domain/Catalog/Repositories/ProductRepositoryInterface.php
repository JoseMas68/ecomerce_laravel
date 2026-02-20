<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Repositories;

use App\Domain\Catalog\Models\Product;
use Illuminate\Database\Eloquent\Collection;

/**
 * Interface for Product repository operations.
 */
interface ProductRepositoryInterface
{
    /**
     * Get all products.
     *
     * @return Collection<int, Product>
     */
    public function all(): Collection;

    /**
     * Find a product by ID.
     *
     * @param int $id
     * @return Product|null
     */
    public function find(int $id): ?Product;

    /**
     * Find a product by slug.
     *
     * @param string $slug
     * @return Product|null
     */
    public function findBySlug(string $slug): ?Product;

    /**
     * Create a new product.
     *
     * @param array<string, mixed> $data
     * @return Product
     */
    public function create(array $data): Product;

    /**
     * Update a product.
     *
     * @param int $id
     * @param array<string, mixed> $data
     * @return Product|null
     */
    public function update(int $id, array $data): ?Product;

    /**
     * Delete a product.
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool;
}
