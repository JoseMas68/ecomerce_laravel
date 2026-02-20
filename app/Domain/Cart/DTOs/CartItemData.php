<?php

declare(strict_types=1);

namespace App\Domain\Cart\DTOs;

use Illuminate\Http\Request;

/**
 * Data Transfer Object for Cart Item operations
 */
class CartItemData
{
    public function __construct(
        public readonly int $productId,
        public readonly int $quantity,
    ) {}

    /**
     * Create a new CartItemData instance from a request.
     *
     * @param Request $request
     * @return self
     */
    public static function fromRequest(Request $request): self
    {
        return new self(
            productId: (int) $request->input('product_id'),
            quantity: (int) $request->input('quantity', 1),
        );
    }

    /**
     * Create a new CartItemData instance from an array.
     *
     * @param array $data
     * @return self
     */
    public static function fromArray(array $data): self
    {
        return new self(
            productId: (int) $data['product_id'],
            quantity: (int) ($data['quantity'] ?? 1),
        );
    }

    /**
     * Convert the DTO to an array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'product_id' => $this->productId,
            'quantity' => $this->quantity,
        ];
    }

    /**
     * Validate the cart item data.
     *
     * @return array<string, string>
     */
    public function validate(): array
    {
        $errors = [];

        if ($this->productId <= 0) {
            $errors['product_id'] = 'El product_id debe ser un número positivo.';
        }

        if ($this->quantity <= 0) {
            $errors['quantity'] = 'La cantidad debe ser al menos 1.';
        }

        if ($this->quantity > 100) {
            $errors['quantity'] = 'La cantidad no puede ser mayor a 100.';
        }

        return $errors;
    }

    /**
     * Check if the data is valid.
     *
     * @return bool
     */
    public function isValid(): bool
    {
        return empty($this->validate());
    }
}
