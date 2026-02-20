<?php

declare(strict_types=1);

namespace App\Domain\Cart\Actions;

use App\Domain\Catalog\Models\Product;
use App\Domain\Cart\DTOs\CartItemData;
use App\Domain\Cart\Events\ItemAdded;
use App\Domain\Cart\Models\Cart;
use Illuminate\Support\Facades\DB;

/**
 * Action to add an item to the cart.
 */
class AddItemToCart
{
    /**
     * Execute the action to add an item to the cart.
     *
     * @param Cart $cart
     * @param CartItemData $data
     * @return Cart
     * @throws \Exception
     */
    public function execute(Cart $cart, CartItemData $data): Cart
    {
        // Validate the data
        if (!$data->isValid()) {
            throw new \InvalidArgumentException('Invalid cart item data: ' . implode(', ', $data->validate()));
        }

        // Find the product
        $product = Product::findOrFail($data->productId);

        // Check if product is active
        if (!$product->is_active) {
            throw new \Exception('El producto no está disponible.');
        }

        // Check stock
        if ($product->stock < $data->quantity) {
            throw new \Exception('No hay suficiente stock disponible.');
        }

        // Use transaction to ensure data integrity
        return DB::transaction(function () use ($cart, $product, $data) {
            $item = $cart->addItem($product, $data->quantity);

            // Refresh the cart to get updated totals
            $cart->load('items');

            // Dispatch the event
            event(new ItemAdded($cart, $item, $data->quantity));

            return $cart;
        });
    }
}
