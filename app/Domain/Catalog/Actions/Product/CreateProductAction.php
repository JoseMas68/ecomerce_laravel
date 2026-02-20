<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Actions\Product;

use App\Domain\Catalog\DTOs\CreateProductData;
use App\Domain\Catalog\Exceptions\InvalidProductDataException;
use App\Domain\Catalog\Models\Product;
use App\Domain\Catalog\Repositories\ProductRepositoryInterface;

/**
 * Action for creating a new product.
 */
readonly class CreateProductAction
{
    /**
     * Create a new CreateProductAction instance.
     *
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(
        private ProductRepositoryInterface $productRepository,
    ) {
    }

    /**
     * Execute the action to create a product.
     *
     * @param CreateProductData $data
     * @return Product
     * @throws InvalidProductDataException
     */
    public function __invoke(CreateProductData $data): Product
    {
        // Verificar si ya existe un producto con el mismo slug
        $existingProduct = $this->productRepository->findBySlug($data->slug);

        if ($existingProduct !== null) {
            throw new InvalidProductDataException(
                sprintf('Ya existe un producto con el slug "%s".', $data->slug)
            );
        }

        // Verificar si ya existe un producto con el mismo SKU
        // Nota: Necesitamos agregar findBySku al repository o buscar directamente
        $productBySku = \App\Domain\Catalog\Models\Product::where('sku', $data->sku)->first();

        if ($productBySku !== null) {
            throw new InvalidProductDataException(
                sprintf('Ya existe un producto con el SKU "%s".', $data->sku)
            );
        }

        // Crear el producto usando el repositorio
        return $this->productRepository->create($data->toArray());
    }
}
