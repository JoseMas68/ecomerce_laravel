<?php

declare(strict_types=1);

namespace App\Domain\Catalog\DTOs;

/**
 * Data Transfer Object for updating a product.
 */
readonly class UpdateProductData
{
    /**
     * Create a new UpdateProductData instance.
     *
     * @param string|null $name
     * @param string|null $slug
     * @param string|null $sku
     * @param int|null $brand_id
     * @param int|null $category_id
     * @param float|null $price
     * @param string|null $description
     * @param float|null $compare_at_price
     * @param float|null $cost
     * @param int|null $stock
     * @param bool|null $is_active
     */
    public function __construct(
        public ?string $name = null,
        public ?string $slug = null,
        public ?string $sku = null,
        public ?int $brand_id = null,
        public ?int $category_id = null,
        public ?float $price = null,
        public ?string $description = null,
        public ?float $compare_at_price = null,
        public ?float $cost = null,
        public ?int $stock = null,
        public ?bool $is_active = null,
    ) {
    }

    /**
     * Create DTO from array.
     *
     * @param array<string, mixed> $data
     * @return self
     */
    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'] ?? null,
            slug: $data['slug'] ?? null,
            sku: $data['sku'] ?? null,
            brand_id: $data['brand_id'] ?? null,
            category_id: $data['category_id'] ?? null,
            price: $data['price'] ?? null,
            description: $data['description'] ?? null,
            compare_at_price: $data['compare_at_price'] ?? null,
            cost: $data['cost'] ?? null,
            stock: $data['stock'] ?? null,
            is_active: $data['is_active'] ?? null,
        );
    }

    /**
     * Create DTO from Request.
     *
     * @param \Illuminate\Http\Request $request
     * @return self
     */
    public static function fromRequest(\Illuminate\Http\Request $request): self
    {
        return new self(
            name: $request->input('name'),
            slug: $request->input('slug'),
            sku: $request->input('sku'),
            brand_id: $request->input('brand_id') ? (int) $request->input('brand_id') : null,
            category_id: $request->input('category_id') ? (int) $request->input('category_id') : null,
            price: $request->input('price') ? (float) $request->input('price') : null,
            description: $request->input('description'),
            compare_at_price: $request->input('compare_at_price') ? (float) $request->input('compare_at_price') : null,
            cost: $request->input('cost') ? (float) $request->input('cost') : null,
            stock: $request->has('stock') ? (int) $request->input('stock') : null,
            is_active: $request->has('is_active') ? $request->boolean('is_active') : null,
        );
    }

    /**
     * Convert DTO to array (excluding null values).
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'name' => $this->name,
            'slug' => $this->slug,
            'sku' => $this->sku,
            'brand_id' => $this->brand_id,
            'category_id' => $this->category_id,
            'price' => $this->price,
            'description' => $this->description,
            'compare_at_price' => $this->compare_at_price,
            'cost' => $this->cost,
            'stock' => $this->stock,
            'is_active' => $this->is_active,
        ], fn ($value) => $value !== null);
    }
}
