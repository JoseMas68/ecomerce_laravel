<?php

namespace Database\Factories;

use App\Domain\Cart\Models\Cart;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CartFactory extends Factory
{
    protected $model = Cart::class;

    public function definition(): array
    {
        return [
            'user_id' => null,
            'session_id' => 'cart_' . $this->faker->unique()->uuid() . '_' . bin2hex(random_bytes(8)),
        ];
    }

    /**
     * Indicate that the cart belongs to a user.
     */
    public function forUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->id,
            'session_id' => null,
        ]);
    }

    /**
     * Indicate that the cart is a guest cart.
     */
    public function forGuest(): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => null,
            'session_id' => 'cart_' . $this->faker->unique()->uuid() . '_' . bin2hex(random_bytes(8)),
        ]);
    }

    /**
     * Indicate that the cart has items.
     */
    public function withItems(int $count = 3): static
    {
        return $this->hasMany(CartItem::class, $count);
    }
}
