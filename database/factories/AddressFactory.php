<?php

namespace Database\Factories;

use App\Domain\Users\Models\Address;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AddressFactory extends Factory
{
    protected $model = Address::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'label' => fake()->randomElement(['Casa', 'Trabajo', 'Otros']),
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'company' => fake()->boolean(30) ? fake()->company() : null,
            'address_line_1' => fake()->streetAddress(),
            'address_line_2' => fake()->boolean(20) ? fake()->secondaryAddress() : null,
            'city' => fake()->city(),
            'postal_code' => fake()->postcode(),
            'province' => fake()->state(),
            'country' => 'España',
            'phone' => fake()->phoneNumber(),
            'is_default_shipping' => false,
            'is_default_billing' => false,
        ];
    }

    public function defaultShipping(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_default_shipping' => true,
        ]);
    }

    public function defaultBilling(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_default_billing' => true,
        ]);
    }

    public function forUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->id,
        ]);
    }
}
