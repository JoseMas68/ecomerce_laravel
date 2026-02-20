<?php

declare(strict_types=1);

namespace App\Domain\Cart\Events;

use App\Domain\Cart\Models\Cart;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Event dispatched when an item is removed from the cart.
 */
class ItemRemoved
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @param Cart $cart
     * @param int $cartItemId
     * @param int $productId
     * @param int $quantity
     * @param float $subtotal
     */
    public function __construct(
        public readonly Cart $cart,
        public readonly int $cartItemId,
        public readonly int $productId,
        public readonly int $quantity,
        public readonly float $subtotal,
    ) {}

    /**
     * Create a new event instance from a cart item being removed.
     *
     * @param Cart $cart
     * @param array{cartItemId: int, productId: int, quantity: int, subtotal: float} $itemData
     * @return self
     */
    public static function fromItemData(Cart $cart, array $itemData): self
    {
        return new self(
            cart: $cart,
            cartItemId: $itemData['cartItemId'],
            productId: $itemData['productId'],
            quantity: $itemData['quantity'],
            subtotal: $itemData['subtotal'],
        );
    }

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
     * Get the cart item ID that was removed.
     *
     * @return int
     */
    public function getCartItemId(): int
    {
        return $this->cartItemId;
    }

    /**
     * Get the product ID.
     *
     * @return int
     */
    public function getProductId(): int
    {
        return $this->productId;
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
     * Get the quantity that was removed.
     *
     * @return int
     */
    public function getQuantity(): int
    {
        return $this->quantity;
    }

    /**
     * Get the subtotal that was removed.
     *
     * @return float
     */
    public function getSubtotal(): float
    {
        return $this->subtotal;
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
     * Get the remaining items count in the cart.
     *
     * @return int
     */
    public function getRemainingItemsCount(): int
    {
        return $this->cart->total_items;
    }

    /**
     * Get the new cart total after removal.
     *
     * @return float
     */
    public function getNewCartTotal(): float
    {
        return $this->cart->total;
    }
}
