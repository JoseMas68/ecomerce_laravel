<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Actions\Category;

use App\Domain\Catalog\DTOs\CreateCategoryData;
use App\Domain\Catalog\Exceptions\CategoryAlreadyExistsException;
use App\Domain\Catalog\Exceptions\CategoryNotFoundException;
use App\Domain\Catalog\Models\Category;
use App\Domain\Catalog\Repositories\CategoryRepositoryInterface;

/**
 * Action for creating a new category.
 */
readonly class CreateCategoryAction
{
    /**
     * Create a new CreateCategoryAction instance.
     *
     * @param CategoryRepositoryInterface $categoryRepository
     */
    public function __construct(
        private CategoryRepositoryInterface $categoryRepository,
    ) {
    }

    /**
     * Execute the action to create a category.
     *
     * @param CreateCategoryData $data
     * @return Category
     * @throws CategoryAlreadyExistsException
     * @throws CategoryNotFoundException
     */
    public function __invoke(CreateCategoryData $data): Category
    {
        // Verificar si ya existe una categoría con el mismo slug
        $existingCategory = $this->categoryRepository->findBySlug($data->slug);

        if ($existingCategory !== null) {
            throw new CategoryAlreadyExistsException(
                sprintf('Ya existe una categoría con el slug "%s".', $data->slug)
            );
        }

        // Si se especifica un padre, verificar que exista
        if ($data->parent_id !== null) {
            $parentCategory = $this->categoryRepository->find($data->parent_id);

            if ($parentCategory === null) {
                throw new CategoryNotFoundException(
                    sprintf('No se encontró la categoría padre con ID "%d".', $data->parent_id)
                );
            }
        }

        // Crear la categoría usando el repositorio
        return $this->categoryRepository->create($data->toArray());
    }
}
