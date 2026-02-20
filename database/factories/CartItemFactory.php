<?php

namespace Database\Factories;

use App\Domain\Cart\Models\Cart;
use App\Domain\Cart\Models\CartItem;
use App\Domain\Catalog\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class CartItemFactory extends Factory
{
    protected $model = CartItem::class;

    public function definition(): array
    {
        $unitPrice = $this->faker->randomFloat(2, 5, 100);
        $quantity = $this->faker->numberBetween(1, 5);

        return [
            'cart_id' => Cart::factory(),
            'product_id' => Product::factory(),
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'subtotal' => $unitPrice * $quantity,
        ];
    }

    /**
     * Indicate that the cart item belongs to a specific cart.
     */
    public function forCart(Cart $cart): static
    {
        return $this->state(fn (array $attributes) => [
            'cart_id' => $cart->id,
        ]);
    }

    /**
     * Indicate that the cart item is for a specific product.
     */
    public function forProduct(Product $product): static
    {
        $unitPrice = $product->price;
        $quantity = $this->faker->numberBetween(1, min(5, $product->stock));

        return $this->state(fn (array $attributes) => [
            'product_id' => $product->id,
            'unit_price' => $unitPrice,
            'quantity' => $quantity,
            'subtotal' => $unitPrice * $quantity,
        ]);
    }

    /**
     * Indicate that the cart item has a specific quantity.
     */
    public function withQuantity(int $quantity): static
    {
        return $this->state(fn (array $attributes) => [
            'quantity' => $quantity,
            'subtotal' => $attributes['unit_price'] * $quantity,
        ]);
    }
}
