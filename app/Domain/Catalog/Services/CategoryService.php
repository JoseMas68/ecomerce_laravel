<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Services;

use App\Domain\Catalog\DTOs\CreateCategoryData;
use App\Domain\Catalog\DTOs\UpdateCategoryData;
use App\Domain\Catalog\Exceptions\CategoryNotFoundException;
use App\Domain\Catalog\Exceptions\CategoryAlreadyExistsException;
use App\Domain\Catalog\Models\Category;
use App\Domain\Catalog\Repositories\CategoryRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

/**
 * Service for Category business logic.
 *
 * Handles all category-related operations including validation,
 * business rules, hierarchy management, and data persistence.
 */
class CategoryService
{
    /**
     * Create a new CategoryService instance.
     *
     * @param CategoryRepositoryInterface $categoryRepository
     */
    public function __construct(
        private CategoryRepositoryInterface $categoryRepository,
    ) {
    }

    /**
     * Get all categories with optional filters.
     *
     * @param array<string, mixed> $filters
     * @return Collection<int, Category>
     */
    public function getAllCategories(array $filters = []): Collection
    {
        $categories = $this->categoryRepository->all();

        // Apply filters if provided
        if (isset($filters['is_active']) && is_bool($filters['is_active'])) {
            $categories = $categories->filter(fn (Category $category) => $category->is_active === $filters['is_active']);
        }

        if (isset($filters['parent_id'])) {
            $parentId = $filters['parent_id'];
            $categories = $categories->filter(
                fn (Category $category) => $category->parent_id === $parentId
            );
        }

        if (isset($filters['search']) && is_string($filters['search'])) {
            $searchTerm = strtolower($filters['search']);
            $categories = $categories->filter(
                fn (Category $category) => str_contains(strtolower($category->name), $searchTerm)
                    || str_contains(strtolower($category->description ?? ''), $searchTerm)
            );
        }

        return $categories->values();
    }

    /**
     * Get a category by ID.
     *
     * @param int $id
     * @return Category|null
     */
    public function getCategoryById(int $id): ?Category
    {
        return $this->categoryRepository->find($id);
    }

    /**
     * Get a category by slug.
     *
     * @param string $slug
     * @return Category|null
     */
    public function getCategoryBySlug(string $slug): ?Category
    {
        return $this->categoryRepository->findBySlug($slug);
    }

    /**
     * Create a new category.
     *
     * @param CreateCategoryData $data
     * @return Category
     * @throws CategoryAlreadyExistsException
     * @throws CategoryNotFoundException
     */
    public function createCategory(CreateCategoryData $data): Category
    {
        // Validate slug uniqueness
        $existingCategory = $this->categoryRepository->findBySlug($data->slug);
        if ($existingCategory !== null) {
            throw new CategoryAlreadyExistsException(
                sprintf('A category with slug "%s" already exists.', $data->slug)
            );
        }

        // Validate category name uniqueness within same parent
        $allCategories = $this->categoryRepository->all();
        $nameExists = $allCategories->contains(
            fn (Category $category) => strtolower($category->name) === strtolower($data->name)
                && $category->parent_id === $data->parent_id
        );

        if ($nameExists) {
            throw new CategoryAlreadyExistsException(
                sprintf('A category with name "%s" already exists in this level.', $data->name)
            );
        }

        // Validate parent category if provided
        if ($data->parent_id !== null) {
            $parentCategory = $this->categoryRepository->find($data->parent_id);
            if ($parentCategory === null) {
                throw new CategoryNotFoundException(
                    sprintf('Parent category with ID %d not found.', $data->parent_id)
                );
            }
        }

        return DB::transaction(function () use ($data) {
            return $this->categoryRepository->create($data->toArray());
        });
    }

    /**
     * Update an existing category.
     *
     * @param int $id
     * @param UpdateCategoryData $data
     * @return Category|null
     * @throws CategoryNotFoundException
     * @throws CategoryAlreadyExistsException
     */
    public function updateCategory(int $id, UpdateCategoryData $data): ?Category
    {
        $category = $this->categoryRepository->find($id);

        if ($category === null) {
            throw new CategoryNotFoundException(
                sprintf('Category with ID %d not found.', $id)
            );
        }

        $updateData = $data->toArray();

        // Validate slug uniqueness if slug is being updated
        if ($data->slug !== null && $data->slug !== $category->slug) {
            $existingCategory = $this->categoryRepository->findBySlug($data->slug);
            if ($existingCategory !== null && $existingCategory->id !== $id) {
                throw new CategoryAlreadyExistsException(
                    sprintf('A category with slug "%s" already exists.', $data->slug)
                );
            }
        }

        // Validate name uniqueness if name is being updated
        if ($data->name !== null && strtolower($data->name) !== strtolower($category->name)) {
            $allCategories = $this->categoryRepository->all();
            $parentId = $data->parent_id ?? $category->parent_id;
            $nameExists = $allCategories->contains(
                fn (Category $c) => $c->id !== $id
                    && strtolower($c->name) === strtolower($data->name)
                    && $c->parent_id === $parentId
            );

            if ($nameExists) {
                throw new CategoryAlreadyExistsException(
                    sprintf('A category with name "%s" already exists in this level.', $data->name)
                );
            }
        }

        // Validate parent category if being updated
        if ($data->parent_id !== null && $data->parent_id !== $category->parent_id) {
            // Prevent setting self as parent
            if ($data->parent_id === $id) {
                throw new \InvalidArgumentException('A category cannot be its own parent.');
            }

            // Prevent creating circular references
            if ($this->wouldCreateCircularReference($id, $data->parent_id)) {
                throw new \InvalidArgumentException(
                    'Cannot set parent: would create a circular reference in the category tree.'
                );
            }

            $parentCategory = $this->categoryRepository->find($data->parent_id);
            if ($parentCategory === null) {
                throw new CategoryNotFoundException(
                    sprintf('Parent category with ID %d not found.', $data->parent_id)
                );
            }
        }

        return DB::transaction(function () use ($id, $updateData) {
            return $this->categoryRepository->update($id, $updateData);
        });
    }

    /**
     * Delete a category (soft delete).
     *
     * @param int $id
     * @return bool
     * @throws CategoryNotFoundException
     */
    public function deleteCategory(int $id): bool
    {
        $category = $this->categoryRepository->find($id);

        if ($category === null) {
            throw new CategoryNotFoundException(
                sprintf('Category with ID %d not found.', $id)
            );
        }

        // Check if category has child categories
        if ($category->children()->count() > 0) {
            throw new \InvalidArgumentException(
                sprintf('Cannot delete category "%s" because it has child categories.', $category->name)
            );
        }

        // Check if category has products
        if ($category->products()->count() > 0) {
            throw new \InvalidArgumentException(
                sprintf('Cannot delete category "%s" because it has associated products.', $category->name)
            );
        }

        return DB::transaction(function () use ($id) {
            return $this->categoryRepository->delete($id);
        });
    }

    /**
     * Restore a soft-deleted category.
     *
     * @param int $id
     * @return bool
     * @throws CategoryNotFoundException
     */
    public function restoreCategory(int $id): bool
    {
        $category = Category::withTrashed()->find($id);

        if ($category === null) {
            throw new CategoryNotFoundException(
                sprintf('Category with ID %d not found.', $id)
            );
        }

        if ($category->trashed()) {
            return $category->restore();
        }

        return false;
    }

    /**
     * Permanently delete a category.
     *
     * @param int $id
     * @return bool
     * @throws CategoryNotFoundException
     */
    public function forceDeleteCategory(int $id): bool
    {
        $category = Category::withTrashed()->find($id);

        if ($category === null) {
            throw new CategoryNotFoundException(
                sprintf('Category with ID %d not found.', $id)
            );
        }

        // Check if category has child categories (including soft deleted)
        $childrenCount = $category->children()->withTrashed()->count();
        if ($childrenCount > 0) {
            throw new \InvalidArgumentException(
                sprintf('Cannot permanently delete category "%s" because it has child categories.', $category->name)
            );
        }

        // Check if category has products (including soft deleted)
        $productsCount = $category->products()->withTrashed()->count();
        if ($productsCount > 0) {
            throw new \InvalidArgumentException(
                sprintf('Cannot permanently delete category "%s" because it has associated products.', $category->name)
            );
        }

        return $category->forceDelete();
    }

    /**
     * Get category tree with hierarchical structure.
     *
     * @return Collection<int, Category>
     */
    public function getCategoryTree(): Collection
    {
        $categories = $this->categoryRepository->all();

        // Build tree structure
        return $this->buildTree($categories);
    }

    /**
     * Get only root categories (no parent).
     *
     * @return Collection<int, Category>
     */
    public function getRootCategories(): Collection
    {
        return $this->getAllCategories(['parent_id' => null]);
    }

    /**
     * Get child categories of a specific parent.
     *
     * @param int $parentId
     * @return Collection<int, Category>
     * @throws CategoryNotFoundException
     */
    public function getChildCategories(int $parentId): Collection
    {
        $parent = $this->categoryRepository->find($parentId);

        if ($parent === null) {
            throw new CategoryNotFoundException(
                sprintf('Parent category with ID %d not found.', $parentId)
            );
        }

        return $this->getAllCategories(['parent_id' => $parentId]);
    }

    /**
     * Get only active categories.
     *
     * @return Collection<int, Category>
     */
    public function getActiveCategories(): Collection
    {
        return $this->getAllCategories(['is_active' => true]);
    }

    /**
     * Get active category tree.
     *
     * @return Collection<int, Category>
     */
    public function getActiveCategoryTree(): Collection
    {
        $categories = $this->getActiveCategories();

        // Build tree structure with only active categories
        return $this->buildTree($categories);
    }

    /**
     * Get category path (breadcrumb) from root to the category.
     *
     * @param int $id
     * @return Collection<int, Category>
     * @throws CategoryNotFoundException
     */
    public function getCategoryPath(int $id): Collection
    {
        $category = $this->categoryRepository->find($id);

        if ($category === null) {
            throw new CategoryNotFoundException(
                sprintf('Category with ID %d not found.', $id)
            );
        }

        $path = collect([$category]);

        $currentCategory = $category;
        while ($currentCategory->parent !== null) {
            $path->prepend($currentCategory->parent);
            $currentCategory = $currentCategory->parent;
        }

        return $path;
    }

    /**
     * Get category with all descendants.
     *
     * @param int $id
     * @return array{category: Category, descendants: Collection<int, Category>}
     * @throws CategoryNotFoundException
     */
    public function getCategoryWithDescendants(int $id): array
    {
        $category = $this->categoryRepository->find($id);

        if ($category === null) {
            throw new CategoryNotFoundException(
                sprintf('Category with ID %d not found.', $id)
            );
        }

        $descendants = $this->getAllDescendants($id);

        return [
            'category' => $category,
            'descendants' => $descendants,
        ];
    }

    /**
     * Generate a unique slug for a category.
     *
     * @param string $name
     * @param int|null $parentId
     * @return string
     */
    public function generateSlug(string $name, ?int $parentId = null): string
    {
        $slug = Str::slug($name);
        $originalSlug = $slug;
        $counter = 1;

        while ($this->categoryRepository->findBySlug($slug) !== null) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Toggle category active status.
     *
     * @param int $id
     * @return Category
     * @throws CategoryNotFoundException
     */
    public function toggleCategoryStatus(int $id): Category
    {
        $category = $this->categoryRepository->find($id);

        if ($category === null) {
            throw new CategoryNotFoundException(
                sprintf('Category with ID %d not found.', $id)
            );
        }

        $updatedCategory = $this->categoryRepository->update($id, [
            'is_active' => !$category->is_active,
        ]);

        return $updatedCategory ?? $category;
    }

    /**
     * Get category with products count.
     *
     * @param int $id
     * @return array<string, mixed>
     * @throws CategoryNotFoundException
     */
    public function getCategoryWithStats(int $id): array
    {
        $category = $this->categoryRepository->find($id);

        if ($category === null) {
            throw new CategoryNotFoundException(
                sprintf('Category with ID %d not found.', $id)
            );
        }

        $directChildrenCount = $category->children()->count();
        $allDescendantsCount = $this->getAllDescendants($id)->count();

        return [
            'category' => $category,
            'products_count' => $category->products()->count(),
            'active_products_count' => $category->products()->where('is_active', true)->count(),
            'direct_children_count' => $directChildrenCount,
            'all_descendants_count' => $allDescendantsCount,
        ];
    }

    /**
     * Build tree structure from flat categories collection.
     *
     * @param Collection<int, Category> $categories
     * @return Collection<int, Category>
     */
    private function buildTree(Collection $categories): Collection
    {
        $rootCategories = $categories->filter(fn (Category $category) => $category->parent_id === null);

        foreach ($rootCategories as $root) {
            $this->loadChildren($root, $categories);
        }

        return $rootCategories->values();
    }

    /**
     * Recursively load children for a category.
     *
     * @param Category $category
     * @param Collection<int, Category> $allCategories
     * @return void
     */
    private function loadChildren(Category $category, Collection $allCategories): void
    {
        $children = $allCategories->filter(fn (Category $c) => $c->parent_id === $category->id);

        if ($children->isNotEmpty()) {
            $category->setRelation('children', $children);

            foreach ($children as $child) {
                $this->loadChildren($child, $allCategories);
            }
        }
    }

    /**
     * Get all descendants of a category recursively.
     *
     * @param int $categoryId
     * @return Collection<int, Category>
     */
    private function getAllDescendants(int $categoryId): Collection
    {
        $category = $this->categoryRepository->find($categoryId);
        if ($category === null) {
            return collect();
        }

        $descendants = collect();
        $children = $category->children;

        foreach ($children as $child) {
            $descendants->push($child);
            $descendants = $descendants->merge($this->getAllDescendants($child->id));
        }

        return $descendants;
    }

    /**
     * Check if setting a parent would create a circular reference.
     *
     * @param int $categoryId
     * @param int $potentialParentId
     * @return bool
     */
    private function wouldCreateCircularReference(int $categoryId, int $potentialParentId): bool
    {
        $current = $this->categoryRepository->find($potentialParentId);

        while ($current !== null) {
            if ($current->id === $categoryId) {
                return true;
            }

            $current = $current->parent;
        }

        return false;
    }
}
