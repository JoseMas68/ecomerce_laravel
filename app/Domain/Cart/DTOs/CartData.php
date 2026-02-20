<?php

declare(strict_types=1);

namespace App\Domain\Cart\DTOs;

use Illuminate\Http\Request;

/**
 * Data Transfer Object for Cart operations
 */
class CartData
{
    public function __construct(
        public readonly ?int $userId,
        public readonly ?string $sessionId,
    ) {}

    /**
     * Create a new CartData instance from a request.
     *
     * @param Request $request
     * @return self
     */
    public static function fromRequest(Request $request): self
    {
        return new self(
            userId: $request->input('user_id') ? (int) $request->input('user_id') : null,
            sessionId: $request->input('session_id'),
        );
    }

    /**
     * Create a new CartData instance from an array.
     *
     * @param array $data
     * @return self
     */
    public static function fromArray(array $data): self
    {
        return new self(
            userId: isset($data['user_id']) ? (int) $data['user_id'] : null,
            sessionId: $data['session_id'] ?? null,
        );
    }

    /**
     * Create a new CartData instance for a guest cart.
     *
     * @param string $sessionId
     * @return self
     */
    public static function forGuest(string $sessionId): self
    {
        return new self(
            userId: null,
            sessionId: $sessionId,
        );
    }

    /**
     * Create a new CartData instance for an authenticated user.
     *
     * @param int $userId
     * @return self
     */
    public static function forUser(int $userId): self
    {
        return new self(
            userId: $userId,
            sessionId: null,
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
            'user_id' => $this->userId,
            'session_id' => $this->sessionId,
        ];
    }

    /**
     * Validate the cart data.
     *
     * @return array<string, string>
     */
    public function validate(): array
    {
        $errors = [];

        if ($this->userId === null && empty($this->sessionId)) {
            $errors['general'] = 'Debe proporcionar user_id o session_id.';
        }

        if ($this->userId !== null && $this->userId <= 0) {
            $errors['user_id'] = 'El user_id debe ser un número positivo.';
        }

        if ($this->sessionId !== null && strlen($this->sessionId) < 10) {
            $errors['session_id'] = 'El session_id debe tener al menos 10 caracteres.';
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

    /**
     * Check if the cart is for a guest user.
     *
     * @return bool
     */
    public function isGuestCart(): bool
    {
        return $this->userId === null;
    }

    /**
     * Check if the cart is for an authenticated user.
     *
     * @return bool
     */
    public function isUserCart(): bool
    {
        return $this->userId !== null;
    }
}
