<?php

declare(strict_types=1);

namespace App\Domain\Cart\Actions;

use App\Domain\Cart\Events\ItemRemoved;
use App\Domain\Cart\Models\Cart;
use App\Domain\Cart\Models\CartItem;
use Illuminate\Support\Facades\DB;

/**
 * Action to remove an item from the cart.
 */
class RemoveItemFromCart
{
    /**
     * Execute the action to remove an item from the cart.
     *
     * @param Cart $cart
     * @param int $cartItemId
     * @return Cart
     * @throws \Exception
     */
    public function execute(Cart $cart, int $cartItemId): Cart
    {
        // Find the cart item
        $item = $cart->items()->where('id', $cartItemId)->firstOrFail();

        // Store item data before deletion for the event
        $itemData = [
            'cartItemId' => $item->id,
            'productId' => $item->product_id,
            'quantity' => $item->quantity,
            'subtotal' => (float) $item->subtotal,
        ];

        // Use transaction to ensure data integrity
        return DB::transaction(function () use ($cart, $item, $itemData) {
            // Remove the item
            $item->delete();

            // Refresh the cart to get updated totals
            $cart->load('items');

            // Dispatch the event
            event(new ItemRemoved(
                cart: $cart,
                cartItemId: $itemData['cartItemId'],
                productId: $itemData['productId'],
                quantity: $itemData['quantity'],
                subtotal: $itemData['subtotal'],
            ));

            return $cart;
        });
    }
}
