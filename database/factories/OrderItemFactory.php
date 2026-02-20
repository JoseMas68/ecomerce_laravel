<?php

namespace Database\Factories;

use App\Domain\Orders\Models\Order;
use App\Domain\Orders\Models\OrderItem;
use App\Domain\Catalog\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderItemFactory extends Factory
{
    protected $model = OrderItem::class;

    public function definition(): array
    {
        $unitPrice = $this->faker->randomFloat(2, 5, 100);
        $quantity = $this->faker->numberBetween(1, 5);
        $subtotal = $unitPrice * $quantity;
        $tax = $subtotal * 0.21;
        $total = $subtotal + $tax;

        return [
            'order_id' => Order::factory(),
            'product_id' => Product::factory(),
            'product_name' => $this->faker->words(3, true),
            'product_slug' => $this->faker->slug(),
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'subtotal' => $subtotal,
            'tax' => $tax,
            'total' => $total,
            'product_image' => $this->faker->imageUrl(400, 400, 'animals'),
        ];
    }

    /**
     * Indicate that the order item belongs to a specific order.
     */
    public function forOrder(Order $order): static
    {
        return $this->state(fn (array $attributes) => [
            'order_id' => $order->id,
        ]);
    }

    /**
     * Indicate that the order item is for a specific product.
     */
    public function forProduct(Product $product): static
    {
        $unitPrice = $product->price;
        $quantity = $this->faker->numberBetween(1, 5);
        $subtotal = $unitPrice * $quantity;
        $tax = $subtotal * 0.21;
        $total = $subtotal + $tax;

        return $this->state(fn (array $attributes) => [
            'product_id' => $product->id,
            'product_name' => $product->name,
            'product_slug' => $product->slug,
            'unit_price' => $unitPrice,
            'quantity' => $quantity,
            'subtotal' => $subtotal,
            'tax' => $tax,
            'total' => $total,
            'product_image' => $product->image_url,
        ]);
    }

    /**
     * Indicate that the order item has a specific quantity.
     */
    public function withQuantity(int $quantity): static
    {
        return $this->state(fn (array $attributes) => [
            'quantity' => $quantity,
            'subtotal' => $attributes['unit_price'] * $quantity,
            'tax' => ($attributes['unit_price'] * $quantity) * 0.21,
        ]);
    }
}
