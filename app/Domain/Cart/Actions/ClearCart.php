<?php

declare(strict_types=1);

namespace App\Domain\Cart\Actions;

use App\Domain\Cart\Events\CartUpdated;
use App\Domain\Cart\Models\Cart;
use Illuminate\Support\Facades\DB;

/**
 * Action to clear all items from a cart.
 */
class ClearCart
{
    /**
     * Execute the action to clear the cart.
     *
     * @param Cart $cart
     * @return Cart
     */
    public function execute(Cart $cart): Cart
    {
        if ($cart->items->isEmpty()) {
            return $cart;
        }

        // Store old values for event
        $oldTotalItems = $cart->total_items;
        $oldTotal = $cart->total;

        // Use transaction to ensure data integrity
        return DB::transaction(function () use ($cart, $oldTotalItems, $oldTotal) {
            // Clear the cart
            $cart->clear();

            // Refresh the cart to get updated totals
            $cart->load('items');

            // Dispatch the event
            event(new CartUpdated(
                cart: $cart,
                changes: [
                    'action' => 'cart_cleared',
                    'old_total_items' => $oldTotalItems,
                    'new_total_items' => 0,
                    'old_total' => $oldTotal,
                    'new_total' => 0.0,
                ]
            ));

            return $cart;
        });
    }
}
