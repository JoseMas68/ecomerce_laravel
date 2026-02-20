<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Repositories;

use App\Domain\Catalog\Models\Category;
use Illuminate\Database\Eloquent\Collection;

/**
 * Interface for Category repository operations.
 */
interface CategoryRepositoryInterface
{
    /**
     * Get all categories.
     *
     * @return Collection<int, Category>
     */
    public function all(): Collection;

    /**
     * Find a category by ID.
     *
     * @param int $id
     * @return Category|null
     */
    public function find(int $id): ?Category;

    /**
     * Find a category by slug.
     *
     * @param string $slug
     * @return Category|null
     */
    public function findBySlug(string $slug): ?Category;

    /**
     * Create a new category.
     *
     * @param array<string, mixed> $data
     * @return Category
     */
    public function create(array $data): Category;

    /**
     * Update a category.
     *
     * @param int $id
     * @param array<string, mixed> $data
     * @return Category|null
     */
    public function update(int $id, array $data): ?Category;

    /**
     * Delete a category.
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool;
}
