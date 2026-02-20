<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Actions\Product;

use App\Domain\Catalog\DTOs\UpdateProductData;
use App\Domain\Catalog\Exceptions\InvalidProductDataException;
use App\Domain\Catalog\Exceptions\ProductNotFoundException;
use App\Domain\Catalog\Models\Product;
use App\Domain\Catalog\Repositories\ProductRepositoryInterface;

/**
 * Action for updating an existing product.
 */
readonly class UpdateProductAction
{
    /**
     * Create a new UpdateProductAction instance.
     *
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(
        private ProductRepositoryInterface $productRepository,
    ) {
    }

    /**
     * Execute the action to update a product.
     *
     * @param int $productId
     * @param UpdateProductData $data
     * @return Product
     * @throws ProductNotFoundException
     * @throws InvalidProductDataException
     */
    public function __invoke(int $productId, UpdateProductData $data): Product
    {
        // Verificar que el producto existe
        $product = $this->productRepository->find($productId);

        if ($product === null) {
            throw new ProductNotFoundException(
                sprintf('No se encontró el producto con ID "%d".', $productId)
            );
        }

        // Si se está actualizando el slug, verificar que no esté duplicado
        if ($data->slug !== null && $data->slug !== $product->slug) {
            $existingProduct = $this->productRepository->findBySlug($data->slug);

            if ($existingProduct !== null && $existingProduct->id !== $productId) {
                throw new InvalidProductDataException(
                    sprintf('Ya existe otro producto con el slug "%s".', $data->slug)
                );
            }
        }

        // Si se está actualizando el SKU, verificar que no esté duplicado
        if ($data->sku !== null && $data->sku !== $product->sku) {
            $productBySku = \App\Domain\Catalog\Models\Product::where('sku', $data->sku)
                ->where('id', '!=', $productId)
                ->first();

            if ($productBySku !== null) {
                throw new InvalidProductDataException(
                    sprintf('Ya existe otro producto con el SKU "%s".', $data->sku)
                );
            }
        }

        // Actualizar el producto usando el repositorio
        $updatedProduct = $this->productRepository->update($productId, $data->toArray());

        if ($updatedProduct === null) {
            throw new ProductNotFoundException(
                sprintf('No se pudo actualizar el producto con ID "%d".', $productId)
            );
        }

        return $updatedProduct;
    }
}
