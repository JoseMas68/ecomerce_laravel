<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Actions\Product;

use App\Domain\Catalog\Events\StockLowUpdated;
use App\Domain\Catalog\Exceptions\ProductNotFoundException;
use App\Domain\Catalog\Models\Product;
use App\Domain\Catalog\Repositories\ProductRepositoryInterface;
use Illuminate\Support\Facades\Event;
use RuntimeException;

/**
 * Action for updating product stock.
 */
readonly class UpdateProductStockAction
{
    /**
     * Stock threshold for low stock warning.
     */
    private const int LOW_STOCK_THRESHOLD = 10;

    /**
     * Create a new UpdateProductStockAction instance.
     *
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(
        private ProductRepositoryInterface $productRepository,
    ) {
    }

    /**
     * Execute the action to update product stock.
     *
     * @param int $productId
     * @param int $newStock
     * @return Product
     * @throws ProductNotFoundException
     * @throws RuntimeException
     */
    public function __invoke(int $productId, int $newStock): Product
    {
        // Validar que el stock no sea negativo
        if ($newStock < 0) {
            throw new RuntimeException(
                sprintf('El stock no puede ser negativo. Valor proporcionado: %d.', $newStock)
            );
        }

        // Verificar que el producto existe
        $product = $this->productRepository->find($productId);

        if ($product === null) {
            throw new ProductNotFoundException(
                sprintf('No se encontró el producto con ID "%d".', $productId)
            );
        }

        // Guardar el stock anterior para el evento
        $previousStock = $product->stock;

        // Actualizar el stock
        $updatedProduct = $this->productRepository->update(
            $productId,
            ['stock' => $newStock]
        );

        if ($updatedProduct === null) {
            throw new ProductNotFoundException(
                sprintf('No se pudo actualizar el stock del producto con ID "%d".', $productId)
            );
        }

        // Disparar evento si el stock es bajo (<= 10)
        if ($newStock <= self::LOW_STOCK_THRESHOLD) {
            Event::dispatch(
                new StockLowUpdated(
                    product: $updatedProduct,
                    previousStock: $previousStock,
                    newStock: $newStock
                )
            );
        }

        return $updatedProduct;
    }

    /**
     * Get the low stock threshold.
     *
     * @return int
     */
    public static function getLowStockThreshold(): int
    {
        return self::LOW_STOCK_THRESHOLD;
    }
}
