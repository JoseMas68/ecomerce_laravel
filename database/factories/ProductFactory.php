<?php

namespace Database\Factories;

use App\Domain\Catalog\Models\Product;
use App\Domain\Catalog\Models\Brand;
use App\Domain\Catalog\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        $name = $this->faker->words(3, true);
        $price = $this->faker->randomFloat(2, 5, 100);

        return [
            'name' => $name,
            'slug' => str($name)->slug(),
            'sku' => 'SKU-' . $this->faker->unique()->numerify('######'),
            'description' => $this->faker->paragraphs(3, true),
            'price' => $price,
            'compare_at_price' => $this->faker->boolean(30) ? $price * 1.2 : null,
            'cost' => $price * 0.6,
            'stock' => $this->faker->numberBetween(0, 100),
            'brand_id' => Brand::factory(),
            'category_id' => Category::factory(),
            'is_active' => true,
            'image_url' => $this->faker->imageUrl(400, 400, 'animals'),
        ];
    }

    /**
     * Indicate that the product is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }

    /**
     * Indicate that the product is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Indicate that the product is in stock.
     */
    public function inStock(): static
    {
        return $this->state(fn (array $attributes) => [
            'stock' => $this->faker->numberBetween(11, 100),
        ]);
    }

    /**
     * Indicate that the product has low stock.
     */
    public function lowStock(): static
    {
        return $this->state(fn (array $attributes) => [
            'stock' => $this->faker->numberBetween(1, 10),
        ]);
    }

    /**
     * Indicate that the product is out of stock.
     */
    public function outOfStock(): static
    {
        return $this->state(fn (array $attributes) => [
            'stock' => 0,
        ]);
    }

    /**
     * Indicate that the product belongs to a specific brand.
     */
    public function forBrand(Brand $brand): static
    {
        return $this->state(fn (array $attributes) => [
            'brand_id' => $brand->id,
        ]);
    }

    /**
     * Indicate that the product belongs to a specific category.
     */
    public function forCategory(Category $category): static
    {
        return $this->state(fn (array $attributes) => [
            'category_id' => $category->id,
        ]);
    }

    /**
     * Indicate that the product has a specific price.
     */
    public function withPrice(float $price): static
    {
        return $this->state(fn (array $attributes) => [
            'price' => $price,
            'cost' => $price * 0.6,
        ]);
    }

    /**
     * Indicate that the product has a specific stock.
     */
    public function withStock(int $stock): static
    {
        return $this->state(fn (array $attributes) => [
            'stock' => $stock,
        ]);
    }
}
