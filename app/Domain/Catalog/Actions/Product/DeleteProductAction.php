<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Actions\Product;

use App\Domain\Catalog\Exceptions\ProductNotFoundException;
use App\Domain\Catalog\Repositories\ProductRepositoryInterface;

/**
 * Action for deleting a product.
 */
readonly class DeleteProductAction
{
    /**
     * Create a new DeleteProductAction instance.
     *
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(
        private ProductRepositoryInterface $productRepository,
    ) {
    }

    /**
     * Execute the action to delete a product.
     *
     * @param int $productId
     * @return bool
     * @throws ProductNotFoundException
     */
    public function __invoke(int $productId): bool
    {
        // Verificar que el producto existe
        $product = $this->productRepository->find($productId);

        if ($product === null) {
            throw new ProductNotFoundException(
                sprintf('No se encontró el producto con ID "%d".', $productId)
            );
        }

        // Nota: No validamos si el producto está asociado a pedidos u otras entidades
        // porque eso depende de la lógica de negocio específica de la aplicación.
        // Si se necesita, se puede agregar validación adicional aquí.

        // Eliminar el producto usando el repositorio
        $deleted = $this->productRepository->delete($productId);

        if (!$deleted) {
            throw new ProductNotFoundException(
                sprintf('No se pudo eliminar el producto con ID "%d".', $productId)
            );
        }

        return true;
    }
}
