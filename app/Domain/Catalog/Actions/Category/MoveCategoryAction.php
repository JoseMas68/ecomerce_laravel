<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Actions\Category;

use App\Domain\Catalog\Exceptions\CategoryNotFoundException;
use App\Domain\Catalog\Models\Category;
use App\Domain\Catalog\Repositories\CategoryRepositoryInterface;
use RuntimeException;

/**
 * Action for moving a category to a new parent.
 */
readonly class MoveCategoryAction
{
    /**
     * Create a new MoveCategoryAction instance.
     *
     * @param CategoryRepositoryInterface $categoryRepository
     */
    public function __construct(
        private CategoryRepositoryInterface $categoryRepository,
    ) {
    }

    /**
     * Execute the action to move a category to a new parent.
     *
     * @param int $categoryId The category to move
     * @param int|null $newParentId The new parent category ID (null for root level)
     * @return Category
     * @throws CategoryNotFoundException
     * @throws RuntimeException
     */
    public function __invoke(int $categoryId, ?int $newParentId): Category
    {
        // Verificar que la categoría existe
        $category = $this->categoryRepository->find($categoryId);

        if ($category === null) {
            throw new CategoryNotFoundException(
                sprintf('No se encontró la categoría con ID "%d".', $categoryId)
            );
        }

        // Si el nuevo padre es null, mover a raíz
        if ($newParentId === null) {
            return $this->moveToRoot($categoryId);
        }

        // Verificar que la nueva categoría padre existe
        $newParent = $this->categoryRepository->find($newParentId);

        if ($newParent === null) {
            throw new CategoryNotFoundException(
                sprintf('No se encontró la nueva categoría padre con ID "%d".', $newParentId)
            );
        }

        // Validar que no sea su propia descendiente (evitar ciclos)
        if ($this->isDescendant($categoryId, $newParentId)) {
            throw new RuntimeException(
                'No se puede mover la categoría. La nueva categoría padre es una descendiente de la categoría actual.'
            );
        }

        // Validar que no sea sí misma
        if ($categoryId === $newParentId) {
            throw new RuntimeException(
                'Una categoría no puede ser su propia padre.'
            );
        }

        // Actualizar el parent_id usando el repositorio
        $updatedCategory = $this->categoryRepository->update(
            $categoryId,
            ['parent_id' => $newParentId]
        );

        if ($updatedCategory === null) {
            throw new RuntimeException(
                sprintf('No se pudo mover la categoría con ID "%d".', $categoryId)
            );
        }

        return $updatedCategory;
    }

    /**
     * Move category to root level (no parent).
     *
     * @param int $categoryId
     * @return Category
     * @throws RuntimeException
     */
    private function moveToRoot(int $categoryId): Category
    {
        $updatedCategory = $this->categoryRepository->update(
            $categoryId,
            ['parent_id' => null]
        );

        if ($updatedCategory === null) {
            throw new RuntimeException(
                sprintf('No se pudo mover la categoría con ID "%d" al nivel raíz.', $categoryId)
            );
        }

        return $updatedCategory;
    }

    /**
     * Check if a category is a descendant of another category.
     *
     * @param int $ancestorId The potential ancestor category ID
     * @param int $categoryId The category to check
     * @return bool True if $categoryId is a descendant of $ancestorId
     */
    private function isDescendant(int $ancestorId, int $categoryId): bool
    {
        $category = $this->categoryRepository->find($categoryId);

        if ($category === null || $category->parent_id === null) {
            return false;
        }

        if ($category->parent_id === $ancestorId) {
            return true;
        }

        return $this->isDescendant($ancestorId, $category->parent_id);
    }
}
