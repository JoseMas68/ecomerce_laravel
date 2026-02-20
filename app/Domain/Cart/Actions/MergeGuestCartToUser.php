<?php

declare(strict_types=1);

namespace App\Domain\Cart\Actions;

use App\Domain\Cart\DTOs\CartData;
use App\Domain\Cart\Events\CartUpdated;
use App\Domain\Cart\Models\Cart;
use Illuminate\Support\Facades\DB;

/**
 * Action to merge a guest cart into a user cart.
 */
class MergeGuestCartToUser
{
    /**
     * Execute the action to merge a guest cart into a user cart.
     *
     * @param Cart $userCart
     * @param Cart $guestCart
     * @return Cart
     */
    public function execute(Cart $userCart, Cart $guestCart): Cart
    {
        // Validate that one cart is for user and the other is for guest
        if ($userCart->user_id === null) {
            throw new \InvalidArgumentException('El primer carrito debe pertenecer a un usuario.');
        }

        if ($guestCart->user_id !== null) {
            throw new \InvalidArgumentException('El segundo carrito debe ser un carrito de invitado.');
        }

        if ($guestCart->items->isEmpty()) {
            return $userCart;
        }

        // Use transaction to ensure data integrity
        return DB::transaction(function () use ($userCart, $guestCart) {
            // Store old total items count for event
            $oldTotalItems = $userCart->total_items;
            $oldTotal = $userCart->total;

            // Merge the carts
            $userCart->merge($guestCart);

            // Refresh the cart to get updated totals
            $userCart->load('items');

            // Dispatch the event
            event(new CartUpdated(
                cart: $userCart,
                changes: [
                    'action' => 'carts_merged',
                    'guest_session_id' => $guestCart->session_id,
                    'old_total_items' => $oldTotalItems,
                    'new_total_items' => $userCart->total_items,
                    'old_total' => $oldTotal,
                    'new_total' => $userCart->total,
                ]
            ));

            return $userCart;
        });
    }
}
