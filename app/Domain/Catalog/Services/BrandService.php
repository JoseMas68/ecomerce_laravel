<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Services;

use App\Domain\Catalog\DTOs\CreateBrandData;
use App\Domain\Catalog\DTOs\UpdateBrandData;
use App\Domain\Catalog\Exceptions\BrandNotFoundException;
use App\Domain\Catalog\Exceptions\BrandAlreadyExistsException;
use App\Domain\Catalog\Models\Brand;
use App\Domain\Catalog\Repositories\BrandRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

/**
 * Service for Brand business logic.
 *
 * Handles all brand-related operations including validation,
 * business rules, and data persistence through the repository.
 */
class BrandService
{
    /**
     * Create a new BrandService instance.
     *
     * @param BrandRepositoryInterface $brandRepository
     */
    public function __construct(
        private BrandRepositoryInterface $brandRepository,
    ) {
    }

    /**
     * Get all brands with optional filters.
     *
     * @param array<string, mixed> $filters
     * @return Collection<int, Brand>
     */
    public function getAllBrands(array $filters = []): Collection
    {
        $brands = $this->brandRepository->all();

        // Apply filters if provided
        if (isset($filters['is_active']) && is_bool($filters['is_active'])) {
            $brands = $brands->filter(fn (Brand $brand) => $brand->is_active === $filters['is_active']);
        }

        if (isset($filters['search']) && is_string($filters['search'])) {
            $searchTerm = strtolower($filters['search']);
            $brands = $brands->filter(
                fn (Brand $brand) => str_contains(strtolower($brand->name), $searchTerm)
                    || str_contains(strtolower($brand->description ?? ''), $searchTerm)
            );
        }

        return $brands->values();
    }

    /**
     * Get a brand by ID.
     *
     * @param int $id
     * @return Brand|null
     */
    public function getBrandById(int $id): ?Brand
    {
        return $this->brandRepository->find($id);
    }

    /**
     * Get a brand by slug.
     *
     * @param string $slug
     * @return Brand|null
     */
    public function getBrandBySlug(string $slug): ?Brand
    {
        return $this->brandRepository->findBySlug($slug);
    }

    /**
     * Create a new brand.
     *
     * @param CreateBrandData $data
     * @return Brand
     * @throws BrandAlreadyExistsException
     */
    public function createBrand(CreateBrandData $data): Brand
    {
        // Validate slug uniqueness
        $existingBrand = $this->brandRepository->findBySlug($data->slug);
        if ($existingBrand !== null) {
            throw new BrandAlreadyExistsException(
                sprintf('A brand with slug "%s" already exists.', $data->slug)
            );
        }

        // Validate brand name uniqueness
        $allBrands = $this->brandRepository->all();
        $nameExists = $allBrands->contains(
            fn (Brand $brand) => strtolower($brand->name) === strtolower($data->name)
        );

        if ($nameExists) {
            throw new BrandAlreadyExistsException(
                sprintf('A brand with name "%s" already exists.', $data->name)
            );
        }

        return DB::transaction(function () use ($data) {
            return $this->brandRepository->create($data->toArray());
        });
    }

    /**
     * Update an existing brand.
     *
     * @param int $id
     * @param UpdateBrandData $data
     * @return Brand|null
     * @throws BrandNotFoundException
     * @throws BrandAlreadyExistsException
     */
    public function updateBrand(int $id, UpdateBrandData $data): ?Brand
    {
        $brand = $this->brandRepository->find($id);

        if ($brand === null) {
            throw new BrandNotFoundException(
                sprintf('Brand with ID %d not found.', $id)
            );
        }

        $updateData = $data->toArray();

        // Validate slug uniqueness if slug is being updated
        if ($data->slug !== null && $data->slug !== $brand->slug) {
            $existingBrand = $this->brandRepository->findBySlug($data->slug);
            if ($existingBrand !== null && $existingBrand->id !== $id) {
                throw new BrandAlreadyExistsException(
                    sprintf('A brand with slug "%s" already exists.', $data->slug)
                );
            }
        }

        // Validate name uniqueness if name is being updated
        if ($data->name !== null && strtolower($data->name) !== strtolower($brand->name)) {
            $allBrands = $this->brandRepository->all();
            $nameExists = $allBrands->contains(
                fn (Brand $b) => $b->id !== $id && strtolower($b->name) === strtolower($data->name)
            );

            if ($nameExists) {
                throw new BrandAlreadyExistsException(
                    sprintf('A brand with name "%s" already exists.', $data->name)
                );
            }
        }

        return DB::transaction(function () use ($id, $updateData) {
            return $this->brandRepository->update($id, $updateData);
        });
    }

    /**
     * Delete a brand (soft delete).
     *
     * @param int $id
     * @return bool
     * @throws BrandNotFoundException
     */
    public function deleteBrand(int $id): bool
    {
        $brand = $this->brandRepository->find($id);

        if ($brand === null) {
            throw new BrandNotFoundException(
                sprintf('Brand with ID %d not found.', $id)
            );
        }

        // Check if brand has products
        if ($brand->products()->count() > 0) {
            throw new \InvalidArgumentException(
                sprintf('Cannot delete brand "%s" because it has associated products.', $brand->name)
            );
        }

        return DB::transaction(function () use ($id) {
            return $this->brandRepository->delete($id);
        });
    }

    /**
     * Restore a soft-deleted brand.
     *
     * @param int $id
     * @return bool
     * @throws BrandNotFoundException
     */
    public function restoreBrand(int $id): bool
    {
        $brand = Brand::withTrashed()->find($id);

        if ($brand === null) {
            throw new BrandNotFoundException(
                sprintf('Brand with ID %d not found.', $id)
            );
        }

        if ($brand->trashed()) {
            return $brand->restore();
        }

        return false;
    }

    /**
     * Permanently delete a brand.
     *
     * @param int $id
     * @return bool
     * @throws BrandNotFoundException
     */
    public function forceDeleteBrand(int $id): bool
    {
        $brand = Brand::withTrashed()->find($id);

        if ($brand === null) {
            throw new BrandNotFoundException(
                sprintf('Brand with ID %d not found.', $id)
            );
        }

        // Check if brand has products (including soft deleted)
        $productsCount = $brand->products()->withTrashed()->count();
        if ($productsCount > 0) {
            throw new \InvalidArgumentException(
                sprintf('Cannot permanently delete brand "%s" because it has associated products.', $brand->name)
            );
        }

        return $brand->forceDelete();
    }

    /**
     * Get active brands only.
     *
     * @return Collection<int, Brand>
     */
    public function getActiveBrands(): Collection
    {
        return $this->getAllBrands(['is_active' => true]);
    }

    /**
     * Generate a unique slug for a brand.
     *
     * @param string $name
     * @return string
     */
    public function generateSlug(string $name): string
    {
        $slug = Str::slug($name);
        $originalSlug = $slug;
        $counter = 1;

        while ($this->brandRepository->findBySlug($slug) !== null) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Toggle brand active status.
     *
     * @param int $id
     * @return Brand
     * @throws BrandNotFoundException
     */
    public function toggleBrandStatus(int $id): Brand
    {
        $brand = $this->brandRepository->find($id);

        if ($brand === null) {
            throw new BrandNotFoundException(
                sprintf('Brand with ID %d not found.', $id)
            );
        }

        $updatedBrand = $this->brandRepository->update($id, [
            'is_active' => !$brand->is_active,
        ]);

        return $updatedBrand ?? $brand;
    }

    /**
     * Get brand with products count.
     *
     * @param int $id
     * @return array<string, mixed>
     * @throws BrandNotFoundException
     */
    public function getBrandWithStats(int $id): array
    {
        $brand = $this->brandRepository->find($id);

        if ($brand === null) {
            throw new BrandNotFoundException(
                sprintf('Brand with ID %d not found.', $id)
            );
        }

        return [
            'brand' => $brand,
            'products_count' => $brand->products()->count(),
            'active_products_count' => $brand->products()->where('is_active', true)->count(),
        ];
    }
}
