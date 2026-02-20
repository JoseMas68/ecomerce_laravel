<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Actions\Category;

use App\Domain\Catalog\DTOs\UpdateCategoryData;
use App\Domain\Catalog\Exceptions\CategoryAlreadyExistsException;
use App\Domain\Catalog\Exceptions\CategoryNotFoundException;
use App\Domain\Catalog\Models\Category;
use App\Domain\Catalog\Repositories\CategoryRepositoryInterface;

/**
 * Action for updating an existing category.
 */
readonly class UpdateCategoryAction
{
    /**
     * Create a new UpdateCategoryAction instance.
     *
     * @param CategoryRepositoryInterface $categoryRepository
     */
    public function __construct(
        private CategoryRepositoryInterface $categoryRepository,
    ) {
    }

    /**
     * Execute the action to update a category.
     *
     * @param int $categoryId
     * @param UpdateCategoryData $data
     * @return Category
     * @throws CategoryNotFoundException
     * @throws CategoryAlreadyExistsException
     * @throws RuntimeException
     */
    public function __invoke(int $categoryId, UpdateCategoryData $data): Category
    {
        // Verificar que la categoría existe
        $category = $this->categoryRepository->find($categoryId);

        if ($category === null) {
            throw new CategoryNotFoundException(
                sprintf('No se encontró la categoría con ID "%d".', $categoryId)
            );
        }

        // Si se está actualizando el slug, verificar que no esté duplicado
        if ($data->slug !== null && $data->slug !== $category->slug) {
            $existingCategory = $this->categoryRepository->findBySlug($data->slug);

            if ($existingCategory !== null && $existingCategory->id !== $categoryId) {
                throw new CategoryAlreadyExistsException(
                    sprintf('Ya existe otra categoría con el slug "%s".', $data->slug)
                );
            }
        }

        // Si se está actualizando el padre, validar que no sea sí mismo o descendiente
        if ($data->parent_id !== null) {
            if ($data->parent_id === $categoryId) {
                throw new RuntimeException(
                    'Una categoría no puede ser su propia padre.'
                );
            }

            // Verificar que la nueva categoría padre existe
            $newParent = $this->categoryRepository->find($data->parent_id);

            if ($newParent === null) {
                throw new CategoryNotFoundException(
                    sprintf('No se encontró la categoría padre con ID "%d".', $data->parent_id)
                );
            }

            // Verificar que la nueva padre no sea descendiente de la categoría actual
            if ($this->isDescendant($categoryId, $data->parent_id)) {
                throw new RuntimeException(
                    'La nueva categoría padre no puede ser una descendiente de la categoría actual.'
                );
            }
        }

        // Actualizar la categoría usando el repositorio
        $updatedCategory = $this->categoryRepository->update($categoryId, $data->toArray());

        if ($updatedCategory === null) {
            throw new CategoryNotFoundException(
                sprintf('No se pudo actualizar la categoría con ID "%d".', $categoryId)
            );
        }

        return $updatedCategory;
    }

    /**
     * Check if a category is a descendant of another category.
     *
     * @param int $categoryId
     * @param int $potentialDescendantId
     * @return bool
     */
    private function isDescendant(int $categoryId, int $potentialDescendantId): bool
    {
        $descendant = $this->categoryRepository->find($potentialDescendantId);

        if ($descendant === null || $descendant->parent_id === null) {
            return false;
        }

        if ($descendant->parent_id === $categoryId) {
            return true;
        }

        return $this->isDescendant($categoryId, $descendant->parent_id);
    }
}
