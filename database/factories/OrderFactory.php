<?php

namespace Database\Factories;

use App\Domain\Orders\Models\Order;
use App\Domain\Orders\Enums\OrderStatus;
use App\Domain\Orders\Enums\PaymentStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        $subtotal = $this->faker->randomFloat(2, 20, 500);
        $shippingCost = $this->faker->randomFloat(2, 0, 20);
        $tax = $subtotal * 0.21;
        $total = $subtotal + $shippingCost + $tax;

        return [
            'user_id' => User::factory(),
            'order_number' => Order::generateOrderNumber(),
            'status' => OrderStatus::PENDING,
            'subtotal' => $subtotal,
            'shipping_cost' => $shippingCost,
            'tax' => $tax,
            'discount' => 0,
            'total' => $total,
            'currency' => 'EUR',
            'shipping_method' => $this->faker->randomElement(['standard', 'express']),
            'payment_method' => $this->faker->randomElement(['credit_card', 'paypal', 'bank_transfer']),
            'payment_status' => PaymentStatus::PENDING,
            'shipping_address' => [
                'first_name' => $this->faker->firstName(),
                'last_name' => $this->faker->lastName(),
                'address_line_1' => $this->faker->streetAddress(),
                'address_line_2' => $this->faker->secondaryAddress(),
                'city' => $this->faker->city(),
                'postal_code' => $this->faker->postcode(),
                'province' => $this->faker->state(),
                'country' => 'España',
                'phone' => $this->faker->phoneNumber(),
            ],
            'billing_address' => [
                'first_name' => $this->faker->firstName(),
                'last_name' => $this->faker->lastName(),
                'address_line_1' => $this->faker->streetAddress(),
                'address_line_2' => $this->faker->secondaryAddress(),
                'city' => $this->faker->city(),
                'postal_code' => $this->faker->postcode(),
                'province' => $this->faker->state(),
                'country' => 'España',
                'phone' => $this->faker->phoneNumber(),
            ],
            'notes' => $this->faker->boolean(30) ? $this->faker->sentence() : null,
        ];
    }

    /**
     * Indicate that the order belongs to a specific user.
     */
    public function forUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->id,
        ]);
    }

    /**
     * Indicate that the order is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => OrderStatus::PENDING,
        ]);
    }

    /**
     * Indicate that the order is processing.
     */
    public function processing(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => OrderStatus::PROCESSING,
        ]);
    }

    /**
     * Indicate that the order is shipped.
     */
    public function shipped(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => OrderStatus::SHIPPED,
            'shipped_at' => now(),
        ]);
    }

    /**
     * Indicate that the order is delivered.
     */
    public function delivered(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => OrderStatus::DELIVERED,
            'shipped_at' => now()->subDays(3),
            'delivered_at' => now(),
        ]);
    }

    /**
     * Indicate that the order is cancelled.
     */
    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => OrderStatus::CANCELLED,
            'cancelled_at' => now(),
        ]);
    }

    /**
     * Indicate that the order is refunded.
     */
    public function refunded(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => OrderStatus::REFUNDED,
            'delivered_at' => now()->subDays(5),
        ]);
    }

    /**
     * Indicate that the order payment is completed.
     */
    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'payment_status' => PaymentStatus::COMPLETED,
        ]);
    }

    /**
     * Indicate that the order payment is pending.
     */
    public function unpaid(): static
    {
        return $this->state(fn (array $attributes) => [
            'payment_status' => PaymentStatus::PENDING,
        ]);
    }

    /**
     * Indicate that the order has a specific total.
     */
    public function withTotal(float $total): static
    {
        $subtotal = $total * 0.79;
        $tax = $subtotal * 0.21;

        return $this->state(fn (array $attributes) => [
            'subtotal' => $subtotal,
            'tax' => $tax,
            'total' => $total,
        ]);
    }
}
