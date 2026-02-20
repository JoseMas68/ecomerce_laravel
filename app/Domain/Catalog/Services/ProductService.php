<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Services;

use App\Domain\Catalog\DTOs\CreateProductData;
use App\Domain\Catalog\DTOs\UpdateProductData;
use App\Domain\Catalog\Exceptions\ProductNotFoundException;
use App\Domain\Catalog\Exceptions\InvalidProductDataException;
use App\Domain\Catalog\Models\Product;
use App\Domain\Catalog\Repositories\ProductRepositoryInterface;
use App\Domain\Catalog\Repositories\BrandRepositoryInterface;
use App\Domain\Catalog\Repositories\CategoryRepositoryInterface;
use App\Domain\Catalog\ValueObjects\Money;
use App\Domain\Catalog\ValueObjects\StockStatus;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

/**
 * Service for Product business logic.
 *
 * Handles all product-related operations including validation,
 * business rules, stock management, pricing calculations, and data persistence.
 */
class ProductService
{
    private const int LOW_STOCK_THRESHOLD = 10;

    /**
     * Create a new ProductService instance.
     *
     * @param ProductRepositoryInterface $productRepository
     * @param BrandRepositoryInterface $brandRepository
     * @param CategoryRepositoryInterface $categoryRepository
     */
    public function __construct(
        private ProductRepositoryInterface $productRepository,
        private BrandRepositoryInterface $brandRepository,
        private CategoryRepositoryInterface $categoryRepository,
    ) {
    }

    /**
     * Get all products with pagination and filters.
     *
     * @param array<string, mixed> $filters
     * @return LengthAwarePaginator<Product>
     */
    public function getAllProducts(array $filters = []): LengthAwarePaginator
    {
        $query = Product::query();

        // Filter by active status
        if (isset($filters['is_active']) && is_bool($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        // Filter by brand
        if (isset($filters['brand_id']) && is_int($filters['brand_id'])) {
            $query->where('brand_id', $filters['brand_id']);
        }

        // Filter by category
        if (isset($filters['category_id']) && is_int($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        // Filter by price range
        if (isset($filters['min_price']) && is_numeric($filters['min_price'])) {
            $query->where('price', '>=', (float) $filters['min_price']);
        }

        if (isset($filters['max_price']) && is_numeric($filters['max_price'])) {
            $query->where('price', '<=', (float) $filters['max_price']);
        }

        // Filter by stock status
        if (isset($filters['in_stock']) && is_bool($filters['in_stock'])) {
            if ($filters['in_stock']) {
                $query->where('stock', '>', 0);
            } else {
                $query->where('stock', '<=', 0);
            }
        }

        // Search by name, SKU, or description
        if (isset($filters['search']) && is_string($filters['search'])) {
            $searchTerm = '%' . $filters['search'] . '%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', $searchTerm)
                    ->orWhere('sku', 'like', $searchTerm)
                    ->orWhere('description', 'like', $searchTerm);
            });
        }

        // Order by
        $orderBy = $filters['order_by'] ?? 'created_at';
        $orderDirection = $filters['order_direction'] ?? 'desc';
        $query->orderBy($orderBy, $orderDirection);

        // Pagination
        $perPage = $filters['per_page'] ?? 15;
        $page = $filters['page'] ?? 1;

        return $query->paginate($perPage, ['*'], 'page', $page);
    }

    /**
     * Get a product by ID.
     *
     * @param int $id
     * @return Product|null
     */
    public function getProductById(int $id): ?Product
    {
        return $this->productRepository->find($id);
    }

    /**
     * Get a product by slug.
     *
     * @param string $slug
     * @return Product|null
     */
    public function getProductBySlug(string $slug): ?Product
    {
        return $this->productRepository->findBySlug($slug);
    }

    /**
     * Get a product by SKU.
     *
     * @param string $sku
     * @return Product|null
     */
    public function getProductBySku(string $sku): ?Product
    {
        return Product::where('sku', $sku)->first();
    }

    /**
     * Create a new product.
     *
     * @param CreateProductData $data
     * @return Product
     * @throws InvalidProductDataException
     */
    public function createProduct(CreateProductData $data): Product
    {
        // Validate brand exists
        $brand = $this->brandRepository->find($data->brand_id);
        if ($brand === null) {
            throw new InvalidProductDataException(
                sprintf('Brand with ID %d not found.', $data->brand_id)
            );
        }

        // Validate category exists
        $category = $this->categoryRepository->find($data->category_id);
        if ($category === null) {
            throw new InvalidProductDataException(
                sprintf('Category with ID %d not found.', $data->category_id)
            );
        }

        // Validate SKU uniqueness
        $existingProduct = $this->getProductBySku($data->sku);
        if ($existingProduct !== null) {
            throw new InvalidProductDataException(
                sprintf('A product with SKU "%s" already exists.', $data->sku)
            );
        }

        // Validate slug uniqueness
        $existingProduct = $this->productRepository->findBySlug($data->slug);
        if ($existingProduct !== null) {
            throw new InvalidProductDataException(
                sprintf('A product with slug "%s" already exists.', $data->slug)
            );
        }

        // Validate price using Money value object
        $price = Money::fromDecimal($data->price);

        // Validate compare_at_price if provided
        if ($data->compare_at_price !== null) {
            $comparePrice = Money::fromDecimal($data->compare_at_price);
            if ($comparePrice->lessThan($price)) {
                throw new InvalidProductDataException(
                    'Compare at price must be greater than or equal to the regular price.'
                );
            }
        }

        // Validate stock
        if ($data->stock < 0) {
            throw new InvalidProductDataException(
                'Stock cannot be negative.'
            );
        }

        return DB::transaction(function () use ($data) {
            return $this->productRepository->create($data->toArray());
        });
    }

    /**
     * Update an existing product.
     *
     * @param int $id
     * @param UpdateProductData $data
     * @return Product|null
     * @throws ProductNotFoundException
     * @throws InvalidProductDataException
     */
    public function updateProduct(int $id, UpdateProductData $data): ?Product
    {
        $product = $this->productRepository->find($id);

        if ($product === null) {
            throw new ProductNotFoundException(
                sprintf('Product with ID %d not found.', $id)
            );
        }

        $updateData = $data->toArray();

        // Validate brand if being updated
        if ($data->brand_id !== null) {
            $brand = $this->brandRepository->find($data->brand_id);
            if ($brand === null) {
                throw new InvalidProductDataException(
                    sprintf('Brand with ID %d not found.', $data->brand_id)
                );
            }
        }

        // Validate category if being updated
        if ($data->category_id !== null) {
            $category = $this->categoryRepository->find($data->category_id);
            if ($category === null) {
                throw new InvalidProductDataException(
                    sprintf('Category with ID %d not found.', $data->category_id)
                );
            }
        }

        // Validate SKU uniqueness if being updated
        if ($data->sku !== null && $data->sku !== $product->sku) {
            $existingProduct = $this->getProductBySku($data->sku);
            if ($existingProduct !== null && $existingProduct->id !== $id) {
                throw new InvalidProductDataException(
                    sprintf('A product with SKU "%s" already exists.', $data->sku)
                );
            }
        }

        // Validate slug uniqueness if being updated
        if ($data->slug !== null && $data->slug !== $product->slug) {
            $existingProduct = $this->productRepository->findBySlug($data->slug);
            if ($existingProduct !== null && $existingProduct->id !== $id) {
                throw new InvalidProductDataException(
                    sprintf('A product with slug "%s" already exists.', $data->slug)
                );
            }
        }

        // Validate price relationships
        $currentPrice = $data->price ?? $product->price;
        $currentComparePrice = $data->compare_at_price ?? $product->compare_at_price;

        if ($data->price !== null) {
            Money::fromDecimal($data->price);
        }

        if ($data->compare_at_price !== null) {
            $price = Money::fromDecimal($currentPrice);
            $comparePrice = Money::fromDecimal($data->compare_at_price);

            if ($comparePrice->lessThan($price)) {
                throw new InvalidProductDataException(
                    'Compare at price must be greater than or equal to the regular price.'
                );
            }
        }

        // Validate stock if being updated
        if ($data->stock !== null && $data->stock < 0) {
            throw new InvalidProductDataException(
                'Stock cannot be negative.'
            );
        }

        return DB::transaction(function () use ($id, $updateData) {
            return $this->productRepository->update($id, $updateData);
        });
    }

    /**
     * Delete a product (soft delete).
     *
     * @param int $id
     * @return bool
     * @throws ProductNotFoundException
     */
    public function deleteProduct(int $id): bool
    {
        $product = $this->productRepository->find($id);

        if ($product === null) {
            throw new ProductNotFoundException(
                sprintf('Product with ID %d not found.', $id)
            );
        }

        return DB::transaction(function () use ($id) {
            return $this->productRepository->delete($id);
        });
    }

    /**
     * Restore a soft-deleted product.
     *
     * @param int $id
     * @return bool
     * @throws ProductNotFoundException
     */
    public function restoreProduct(int $id): bool
    {
        $product = Product::withTrashed()->find($id);

        if ($product === null) {
            throw new ProductNotFoundException(
                sprintf('Product with ID %d not found.', $id)
            );
        }

        if ($product->trashed()) {
            return $product->restore();
        }

        return false;
    }

    /**
     * Permanently delete a product.
     *
     * @param int $id
     * @return bool
     * @throws ProductNotFoundException
     */
    public function forceDeleteProduct(int $id): bool
    {
        $product = Product::withTrashed()->find($id);

        if ($product === null) {
            throw new ProductNotFoundException(
                sprintf('Product with ID %d not found.', $id)
            );
        }

        return $product->forceDelete();
    }

    /**
     * Search products with full-text search.
     *
     * @param string $query
     * @param array<string, mixed> $filters
     * @return LengthAwarePaginator<Product>
     */
    public function searchProducts(string $query, array $filters = []): LengthAwarePaginator
    {
        $filters['search'] = $query;

        return $this->getAllProducts($filters);
    }

    /**
     * Get products by brand.
     *
     * @param int $brandId
     * @return Collection<int, Product>
     */
    public function getProductsByBrand(int $brandId): Collection
    {
        return Product::where('brand_id', $brandId)->get();
    }

    /**
     * Get products by category.
     *
     * @param int $categoryId
     * @return Collection<int, Product>
     */
    public function getProductsByCategory(int $categoryId): Collection
    {
        return Product::where('category_id', $categoryId)->get();
    }

    /**
     * Get active products by brand.
     *
     * @param int $brandId
     * @return Collection<int, Product>
     */
    public function getActiveProductsByBrand(int $brandId): Collection
    {
        return Product::where('brand_id', $brandId)
            ->where('is_active', true)
            ->get();
    }

    /**
     * Get active products by category.
     *
     * @param int $categoryId
     * @return Collection<int, Product>
     */
    public function getActiveProductsByCategory(int $categoryId): Collection
    {
        return Product::where('category_id', $categoryId)
            ->where('is_active', true)
            ->get();
    }

    /**
     * Update product stock.
     *
     * @param int $productId
     * @param int $quantity
     * @return bool
     * @throws ProductNotFoundException
     * @throws InvalidProductDataException
     */
    public function updateStock(int $productId, int $quantity): bool
    {
        $product = $this->productRepository->find($productId);

        if ($product === null) {
            throw new ProductNotFoundException(
                sprintf('Product with ID %d not found.', $productId)
            );
        }

        if ($quantity < 0) {
            throw new InvalidProductDataException(
                'Stock quantity cannot be negative.'
            );
        }

        $result = $this->productRepository->update($productId, ['stock' => $quantity]);

        return $result !== null;
    }

    /**
     * Decrease product stock (for orders).
     *
     * @param int $productId
     * @param int $quantity
     * @return bool
     * @throws ProductNotFoundException
     * @throws InvalidProductDataException
     */
    public function decreaseStock(int $productId, int $quantity): bool
    {
        $product = $this->productRepository->find($productId);

        if ($product === null) {
            throw new ProductNotFoundException(
                sprintf('Product with ID %d not found.', $productId)
            );
        }

        if ($quantity <= 0) {
            throw new InvalidProductDataException(
                'Quantity to decrease must be positive.'
            );
        }

        $newStock = $product->stock - $quantity;

        if ($newStock < 0) {
            throw new InvalidProductDataException(
                sprintf(
                    'Insufficient stock. Current stock: %d, attempted to decrease: %d',
                    $product->stock,
                    $quantity
                )
            );
        }

        return $this->updateStock($productId, $newStock);
    }

    /**
     * Increase product stock (for returns/cancellations).
     *
     * @param int $productId
     * @param int $quantity
     * @return bool
     * @throws ProductNotFoundException
     * @throws InvalidProductDataException
     */
    public function increaseStock(int $productId, int $quantity): bool
    {
        $product = $this->productRepository->find($productId);

        if ($product === null) {
            throw new ProductNotFoundException(
                sprintf('Product with ID %d not found.', $productId)
            );
        }

        if ($quantity <= 0) {
            throw new InvalidProductDataException(
                'Quantity to increase must be positive.'
            );
        }

        $newStock = $product->stock + $quantity;

        return $this->updateStock($productId, $newStock);
    }

    /**
     * Get product stock status.
     *
     * @param int $productId
     * @return StockStatus
     * @throws ProductNotFoundException
     */
    public function getStockStatus(int $productId): StockStatus
    {
        $product = $this->productRepository->find($productId);

        if ($product === null) {
            throw new ProductNotFoundException(
                sprintf('Product with ID %d not found.', $productId)
            );
        }

        return StockStatus::fromQuantity($product->stock, self::LOW_STOCK_THRESHOLD);
    }

    /**
     * Check if product is available for ordering.
     *
     * @param int $productId
     * @param int $quantity
     * @return bool
     * @throws ProductNotFoundException
     */
    public function isAvailableForOrder(int $productId, int $quantity = 1): bool
    {
        $product = $this->productRepository->find($productId);

        if ($product === null) {
            throw new ProductNotFoundException(
                sprintf('Product with ID %d not found.', $productId)
            );
        }

        return $product->is_active && $product->stock >= $quantity;
    }

    /**
     * Calculate product profit margin.
     *
     * @param int $productId
     * @return float|null
     * @throws ProductNotFoundException
     */
    public function calculateProfitMargin(int $productId): ?float
    {
        $product = $this->productRepository->find($productId);

        if ($product === null) {
            throw new ProductNotFoundException(
                sprintf('Product with ID %d not found.', $productId)
            );
        }

        if ($product->cost === null || $product->cost === 0.0) {
            return null;
        }

        $price = Money::fromDecimal($product->price);
        $cost = Money::fromDecimal($product->cost);

        $profit = $price->subtract($cost)->toDecimal();
        $margin = ($profit / $cost->toDecimal()) * 100;

        return round($margin, 2);
    }

    /**
     * Get related products (same category, different product).
     *
     * @param int $productId
     * @param int $limit
     * @return Collection<int, Product>
     * @throws ProductNotFoundException
     */
    public function getRelatedProducts(int $productId, int $limit = 4): Collection
    {
        $product = $this->productRepository->find($productId);

        if ($product === null) {
            throw new ProductNotFoundException(
                sprintf('Product with ID %d not found.', $productId)
            );
        }

        return Product::where('category_id', $product->category_id)
            ->where('id', '!=', $productId)
            ->where('is_active', true)
            ->limit($limit)
            ->get();
    }

    /**
     * Toggle product active status.
     *
     * @param int $id
     * @return Product
     * @throws ProductNotFoundException
     */
    public function toggleProductStatus(int $id): Product
    {
        $product = $this->productRepository->find($id);

        if ($product === null) {
            throw new ProductNotFoundException(
                sprintf('Product with ID %d not found.', $id)
            );
        }

        $updatedProduct = $this->productRepository->update($id, [
            'is_active' => !$product->is_active,
        ]);

        return $updatedProduct ?? $product;
    }

    /**
     * Get product with detailed information.
     *
     * @param int $id
     * @return array<string, mixed>
     * @throws ProductNotFoundException
     */
    public function getProductWithDetails(int $id): array
    {
        $product = $this->productRepository->find($id);

        if ($product === null) {
            throw new ProductNotFoundException(
                sprintf('Product with ID %d not found.', $id)
            );
        }

        $stockStatus = $this->getStockStatus($id);
        $profitMargin = $this->calculateProfitMargin($id);

        return [
            'product' => $product,
            'stock_status' => $stockStatus,
            'profit_margin' => $profitMargin,
            'is_available' => $this->isAvailableForOrder($id),
            'has_discount' => $product->compare_at_price !== null
                && $product->compare_at_price > $product->price,
            'discount_percentage' => $product->compare_at_price !== null
                ? round((($product->compare_at_price - $product->price) / $product->compare_at_price) * 100, 2)
                : 0,
        ];
    }

    /**
     * Generate a unique slug for a product.
     *
     * @param string $name
     * @return string
     */
    public function generateSlug(string $name): string
    {
        $slug = Str::slug($name);
        $originalSlug = $slug;
        $counter = 1;

        while ($this->productRepository->findBySlug($slug) !== null) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Bulk update stock for multiple products.
     *
     * @param array<int, int> $stockUpdates [product_id => quantity]
     * @return array{success: int, failed: int}
     */
    public function bulkUpdateStock(array $stockUpdates): array
    {
        $success = 0;
        $failed = 0;

        foreach ($stockUpdates as $productId => $quantity) {
            try {
                $this->updateStock($productId, $quantity);
                $success++;
            } catch (ProductNotFoundException|InvalidProductDataException $e) {
                $failed++;
            }
        }

        return [
            'success' => $success,
            'failed' => $failed,
        ];
    }

    /**
     * Get products with low stock.
     *
     * @param int $threshold
     * @return Collection<int, Product>
     */
    public function getLowStockProducts(int $threshold = self::LOW_STOCK_THRESHOLD): Collection
    {
        return Product::where('stock', '<=', $threshold)
            ->where('stock', '>', 0)
            ->where('is_active', true)
            ->orderBy('stock', 'asc')
            ->get();
    }

    /**
     * Get out of stock products.
     *
     * @return Collection<int, Product>
     */
    public function getOutOfStockProducts(): Collection
    {
        return Product::where('stock', '<=', 0)
            ->where('is_active', true)
            ->orderBy('updated_at', 'desc')
            ->get();
    }

    /**
     * Duplicate a product.
     *
     * @param int $productId
     * @return Product
     * @throws ProductNotFoundException
     */
    public function duplicateProduct(int $productId): Product
    {
        $product = $this->productRepository->find($productId);

        if ($product === null) {
            throw new ProductNotFoundException(
                sprintf('Product with ID %d not found.', $productId)
            );
        }

        $newSlug = $this->generateSlug($product->name . ' copy');
        $newSku = $this->generateUniqueSku($product->sku);

        $newProductData = CreateProductData::fromArray([
            'name' => $product->name . ' (Copy)',
            'slug' => $newSlug,
            'sku' => $newSku,
            'brand_id' => $product->brand_id,
            'category_id' => $product->category_id,
            'price' => $product->price,
            'description' => $product->description,
            'compare_at_price' => $product->compare_at_price,
            'cost' => $product->cost,
            'stock' => 0,
            'is_active' => false,
        ]);

        return $this->createProduct($newProductData);
    }

    /**
     * Generate a unique SKU.
     *
     * @param string $baseSku
     * @return string
     */
    private function generateUniqueSku(string $baseSku): string
    {
        $sku = $baseSku;
        $counter = 1;

        while ($this->getProductBySku($sku) !== null) {
            $sku = $baseSku . '-' . $counter;
            $counter++;
        }

        return $sku;
    }
}
