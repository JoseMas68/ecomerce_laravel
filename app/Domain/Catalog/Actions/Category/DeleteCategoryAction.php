<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Actions\Category;

use App\Domain\Catalog\Exceptions\CategoryNotFoundException;
use App\Domain\Catalog\Repositories\CategoryRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use RuntimeException;

/**
 * Action for deleting a category.
 */
readonly class DeleteCategoryAction
{
    /**
     * Create a new DeleteCategoryAction instance.
     *
     * @param CategoryRepositoryInterface $categoryRepository
     */
    public function __construct(
        private CategoryRepositoryInterface $categoryRepository,
    ) {
    }

    /**
     * Execute the action to delete a category.
     *
     * @param int $categoryId
     * @return bool
     * @throws CategoryNotFoundException
     * @throws RuntimeException
     */
    public function __invoke(int $categoryId): bool
    {
        // Verificar que la categoría existe
        $category = $this->categoryRepository->find($categoryId);

        if ($category === null) {
            throw new CategoryNotFoundException(
                sprintf('No se encontró la categoría con ID "%d".', $categoryId)
            );
        }

        // Verificar si tiene subcategorías
        /** @var Collection<int, \App\Domain\Catalog\Models\Category> $children */
        $children = $category->children;

        if ($children->count() > 0) {
            throw new RuntimeException(
                sprintf(
                    'No se puede eliminar la categoría "%s" porque tiene %d subcategoría(s) asociada(s). ' .
                    'Elimine o reasigne las subcategorías primero.',
                    $category->name,
                    $children->count()
                )
            );
        }

        // Verificar si tiene productos asociados
        /** @var Collection<int, \App\Domain\Catalog\Models\Product> $products */
        $products = $category->products;

        if ($products->count() > 0) {
            throw new RuntimeException(
                sprintf(
                    'No se puede eliminar la categoría "%s" porque tiene %d producto(s) asociado(s). ' .
                    'Elimine o reasigne los productos primero.',
                    $category->name,
                    $products->count()
                )
            );
        }

        // Eliminar la categoría usando el repositorio
        $deleted = $this->categoryRepository->delete($categoryId);

        if (!$deleted) {
            throw new RuntimeException(
                sprintf('No se pudo eliminar la categoría con ID "%d".', $categoryId)
            );
        }

        return true;
    }
}
