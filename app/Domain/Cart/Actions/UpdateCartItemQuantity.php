<?php

declare(strict_types=1);

namespace App\Domain\Cart\Actions;

use App\Domain\Cart\Events\CartUpdated;
use App\Domain\Cart\Models\Cart;
use App\Domain\Cart\Models\CartItem;
use Illuminate\Support\Facades\DB;

/**
 * Action to update the quantity of a cart item.
 */
class UpdateCartItemQuantity
{
    /**
     * Execute the action to update the quantity of a cart item.
     *
     * @param Cart $cart
     * @param int $cartItemId
     * @param int $quantity
     * @return Cart
     * @throws \Exception
     */
    public function execute(Cart $cart, int $cartItemId, int $quantity): Cart
    {
        if ($quantity <= 0) {
            throw new \InvalidArgumentException('La cantidad debe ser mayor que 0. Use removeItemFromCart para eliminar el item.');
        }

        // Find the cart item
        $item = $cart->items()->where('id', $cartItemId)->firstOrFail();

        // Check stock
        if ($item->product && $item->product->stock < $quantity) {
            throw new \Exception('No hay suficiente stock disponible.');
        }

        // Store old values for event
        $oldQuantity = $item->quantity;
        $oldSubtotal = (float) $item->subtotal;

        // Use transaction to ensure data integrity
        return DB::transaction(function () use ($cart, $item, $quantity, $oldQuantity, $oldSubtotal) {
            // Update the quantity
            $item->updateQuantity($quantity);

            // Refresh the cart to get updated totals
            $cart->load('items');

            // Dispatch the event
            event(new CartUpdated(
                cart: $cart,
                changes: [
                    'action' => 'quantity_updated',
                    'item_id' => $item->id,
                    'product_id' => $item->product_id,
                    'old_quantity' => $oldQuantity,
                    'new_quantity' => $item->quantity,
                    'old_subtotal' => $oldSubtotal,
                    'new_subtotal' => (float) $item->subtotal,
                ]
            ));

            return $cart;
        });
    }
}
