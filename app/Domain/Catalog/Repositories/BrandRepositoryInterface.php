<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Repositories;

use App\Domain\Catalog\Models\Brand;
use Illuminate\Database\Eloquent\Collection;

/**
 * Interface for Brand repository operations.
 */
interface BrandRepositoryInterface
{
    /**
     * Get all brands.
     *
     * @return Collection<int, Brand>
     */
    public function all(): Collection;

    /**
     * Find a brand by ID.
     *
     * @param int $id
     * @return Brand|null
     */
    public function find(int $id): ?Brand;

    /**
     * Find a brand by slug.
     *
     * @param string $slug
     * @return Brand|null
     */
    public function findBySlug(string $slug): ?Brand;

    /**
     * Create a new brand.
     *
     * @param array<string, mixed> $data
     * @return Brand
     */
    public function create(array $data): Brand;

    /**
     * Update a brand.
     *
     * @param int $id
     * @param array<string, mixed> $data
     * @return Brand|null
     */
    public function update(int $id, array $data): ?Brand;

    /**
     * Delete a brand.
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool;
}
