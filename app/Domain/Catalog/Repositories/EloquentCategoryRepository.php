<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Repositories;

use App\Domain\Catalog\Models\Category;
use Illuminate\Database\Eloquent\Collection;

/**
 * Eloquent implementation of Category repository.
 */
class EloquentCategoryRepository implements CategoryRepositoryInterface
{
    /**
     * {@inheritDoc}
     */
    public function all(): Collection
    {
        return Category::all();
    }

    /**
     * {@inheritDoc}
     */
    public function find(int $id): ?Category
    {
        return Category::find($id);
    }

    /**
     * {@inheritDoc}
     */
    public function findBySlug(string $slug): ?Category
    {
        return Category::where('slug', $slug)->first();
    }

    /**
     * {@inheritDoc}
     */
    public function create(array $data): Category
    {
        return Category::create($data);
    }

    /**
     * {@inheritDoc}
     */
    public function update(int $id, array $data): ?Category
    {
        $category = $this->find($id);

        if (!$category) {
            return null;
        }

        $category->update($data);

        return $category->fresh();
    }

    /**
     * {@inheritDoc}
     */
    public function delete(int $id): bool
    {
        $category = $this->find($id);

        if (!$category) {
            return false;
        }

        return $category->delete() !== false;
    }
}
