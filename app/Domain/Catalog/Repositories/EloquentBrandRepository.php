<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Repositories;

use App\Domain\Catalog\Models\Brand;
use Illuminate\Database\Eloquent\Collection;

/**
 * Eloquent implementation of Brand repository.
 */
class EloquentBrandRepository implements BrandRepositoryInterface
{
    /**
     * {@inheritDoc}
     */
    public function all(): Collection
    {
        return Brand::all();
    }

    /**
     * {@inheritDoc}
     */
    public function find(int $id): ?Brand
    {
        return Brand::find($id);
    }

    /**
     * {@inheritDoc}
     */
    public function findBySlug(string $slug): ?Brand
    {
        return Brand::where('slug', $slug)->first();
    }

    /**
     * {@inheritDoc}
     */
    public function create(array $data): Brand
    {
        return Brand::create($data);
    }

    /**
     * {@inheritDoc}
     */
    public function update(int $id, array $data): ?Brand
    {
        $brand = $this->find($id);

        if (!$brand) {
            return null;
        }

        $brand->update($data);

        return $brand->fresh();
    }

    /**
     * {@inheritDoc}
     */
    public function delete(int $id): bool
    {
        $brand = $this->find($id);

        if (!$brand) {
            return false;
        }

        return $brand->delete() !== false;
    }
}
