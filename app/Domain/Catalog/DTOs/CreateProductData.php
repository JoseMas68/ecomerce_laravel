<?php

declare(strict_types=1);

namespace App\Domain\Catalog\DTOs;

/**
 * Data Transfer Object for creating a product.
 */
readonly class CreateProductData
{
    /**
     * Create a new CreateProductData instance.
     *
     * @param string $name
     * @param string $slug
     * @param string $sku
     * @param int $brand_id
     * @param int $category_id
     * @param float $price
     * @param string|null $description
     * @param float|null $compare_at_price
     * @param float|null $cost
     * @param int $stock
     * @param bool $is_active
     */
    public function __construct(
        public string $name,
        public string $slug,
        public string $sku,
        public int $brand_id,
        public int $category_id,
        public float $price,
        public ?string $description = null,
        public ?float $compare_at_price = null,
        public ?float $cost = null,
        public int $stock = 0,
        public bool $is_active = true,
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
            name: $data['name'],
            slug: $data['slug'],
            sku: $data['sku'],
            brand_id: $data['brand_id'],
            category_id: $data['category_id'],
            price: $data['price'],
            description: $data['description'] ?? null,
            compare_at_price: $data['compare_at_price'] ?? null,
            cost: $data['cost'] ?? null,
            stock: $data['stock'] ?? 0,
            is_active: $data['is_active'] ?? true,
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
            brand_id: (int) $request->input('brand_id'),
            category_id: (int) $request->input('category_id'),
            price: (float) $request->input('price'),
            description: $request->input('description'),
            compare_at_price: $request->input('compare_at_price') ? (float) $request->input('compare_at_price') : null,
            cost: $request->input('cost') ? (float) $request->input('cost') : null,
            stock: (int) $request->input('stock', 0),
            is_active: $request->boolean('is_active', true),
        );
    }

    /**
     * Convert DTO to array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
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
        ];
    }
}
