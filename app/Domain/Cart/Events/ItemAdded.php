<?php

declare(strict_types=1);

namespace App\Domain\Cart\Events;

use App\Domain\Cart\Models\Cart;
use App\Domain\Cart\Models\CartItem;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Event dispatched when an item is added to the cart.
 */
class ItemAdded
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @param Cart $cart
     * @param CartItem $item
     * @param int $quantity
     */
    public function __construct(
        public readonly Cart $cart,
        public readonly CartItem $item,
        public readonly int $quantity = 1,
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
     * Get the cart item ID.
     *
     * @return int
     */
    public function getCartItemId(): int
    {
        return $this->item->id;
    }

    /**
     * Get the product ID.
     *
     * @return int
     */
    public function getProductId(): int
    {
        return $this->item->product_id;
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
     * Get the quantity added.
     *
     * @return int
     */
    public function getQuantity(): int
    {
        return $this->quantity;
    }

    /**
     * Get the unit price.
     *
     * @return float
     */
    public function getUnitPrice(): float
    {
        return (float) $this->item->unit_price;
    }

    /**
     * Get the subtotal.
     *
     * @return float
     */
    public function getSubtotal(): float
    {
        return (float) $this->item->subtotal;
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
}
