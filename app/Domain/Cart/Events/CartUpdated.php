<?php

declare(strict_types=1);

namespace App\Domain\Cart\Events;

use App\Domain\Cart\Models\Cart;
use App\Domain\Cart\Models\CartItem;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Event dispatched when a cart is updated.
 */
class CartUpdated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @param Cart $cart
     * @param array<string, mixed> $changes
     */
    public function __construct(
        public readonly Cart $cart,
        public readonly array $changes = [],
    ) {}

    /**
     * Get the cart ID.
     *
     * @return int
     */
    public function getCartId(): int
    {
        return $this->cart->id;
    }

    /**
     * Get the user ID if the cart belongs to a user.
     *
     * @return int|null
     */
    public function getUserId(): ?int
    {
        return $this->cart->user_id;
    }

    /**
     * Get the session ID if the cart belongs to a guest.
     *
     * @return string|null
     */
    public function getSessionId(): ?string
    {
        return $this->cart->session_id;
    }

    /**
     * Check if the cart belongs to a guest.
     *
     * @return bool
     */
    public function isGuestCart(): bool
    {
        return $this->cart->isGuestCart();
    }

    /**
     * Get the total items in the cart.
     *
     * @return int
     */
    public function getTotalItems(): int
    {
        return $this->cart->total_items;
    }

    /**
     * Get the cart total.
     *
     * @return float
     */
    public function getTotal(): float
    {
        return $this->cart->total;
    }
}
