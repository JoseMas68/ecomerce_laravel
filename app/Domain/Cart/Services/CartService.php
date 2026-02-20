<?php

namespace App\Domain\Cart\Services;

use App\Domain\Cart\Models\Cart;
use App\Domain\Cart\Models\CartItem;
use App\Domain\Catalog\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CartService
{
    /**
     * Get or create a cart for the given user or session
     *
     * @param int|null $userId
     * @param string|null $sessionId
     * @return Cart
     */
    public function getOrCreateCart($userId = null, $sessionId = null): Cart
    {
        $query = Cart::with('items.product');

        if ($userId) {
            $cart = $query->where('user_id', $userId)->first();
        } elseif ($sessionId) {
            $cart = $query->where('session_id', $sessionId)->first();
        } else {
            $cart = null;
        }

        if (!$cart) {
            $cart = Cart::create([
                'user_id' => $userId,
                'session_id' => $sessionId ?: $this->generateSessionId(),
                'status' => 'active',
            ]);
            $cart->load('items.product');
        }

        return $cart;
    }

    /**
     * Add an item to the cart
     *
     * @param Cart $cart
     * @param int $productId
     * @param int $quantity
     * @return CartItem
     */
    public function addItem(Cart $cart, $productId, $quantity = 1): CartItem
    {
        $product = Product::findOrFail($productId);

        if ($product->stock < $quantity) {
            throw new \Exception("Insufficient stock for product: {$product->name}");
        }

        $existingItem = $cart->items()->where('product_id', $productId)->first();

        if ($existingItem) {
            $existingItem->quantity += $quantity;
            $existingItem->subtotal = $existingItem->quantity * $product->price;
            $existingItem->save();

            $cart->load('items.product');

            event(new \App\Domain\Cart\Events\ItemAdded($existingItem));

            return $existingItem;
        }

        $cartItem = CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $productId,
            'quantity' => $quantity,
            'unit_price' => $product->price,
            'subtotal' => $quantity * $product->price,
        ]);

        $cart->load('items.product');

        event(new \App\Domain\Cart\Events\ItemAdded($cartItem));

        return $cartItem;
    }

    /**
     * Update item quantity in the cart
     *
     * @param int $cartId
     * @param int $itemId
     * @param int $quantity
     * @return bool
     */
    public function updateItemQuantity($cartId, $itemId, $quantity): bool
    {
        $cart = Cart::findOrFail($cartId);
        $item = $cart->items()->findOrFail($itemId);

        if ($quantity <= 0) {
            return $this->removeItem($cartId, $itemId);
        }

        $product = $item->product;

        if ($product->stock < $quantity) {
            throw new \Exception("Insufficient stock for product: {$product->name}");
        }

        $item->quantity = $quantity;
        $item->subtotal = $quantity * $item->unit_price;
        $item->save();

        $cart->load('items.product');

        event(new \App\Domain\Cart\Events\CartUpdated($cart));

        return true;
    }

    /**
     * Remove an item from the cart
     *
     * @param int $cartId
     * @param int $itemId
     * @return bool
     */
    public function removeItem($cartId, $itemId): bool
    {
        $cart = Cart::findOrFail($cartId);
        $item = $cart->items()->findOrFail($itemId);

        $item->delete();

        $cart->load('items.product');

        event(new \App\Domain\Cart\Events\ItemRemoved($item));

        return true;
    }

    /**
     * Clear all items from the cart
     *
     * @param int $cartId
     * @return bool
     */
    public function clearCart($cartId): bool
    {
        $cart = Cart::findOrFail($cartId);

        $cart->items()->delete();

        event(new \App\Domain\Cart\Events\CartUpdated($cart));

        return true;
    }

    /**
     * Calculate the total amount for the cart
     *
     * @param Cart $cart
     * @return float
     */
    public function getCartTotal(Cart $cart): float
    {
        return (float) $cart->items->sum('subtotal');
    }

    /**
     * Get the total count of items in the cart
     *
     * @param Cart $cart
     * @return int
     */
    public function getCartItemsCount(Cart $cart): int
    {
        return (int) $cart->items->sum('quantity');
    }

    /**
     * Merge session cart into user cart
     *
     * @param string $sessionId
     * @param int $userId
     * @return Cart
     */
    public function mergeSessionCartToUser($sessionId, $userId): Cart
    {
        return DB::transaction(function () use ($sessionId, $userId) {
            $sessionCart = Cart::where('session_id', $sessionId)->first();

            if (!$sessionCart) {
                return $this->getOrCreateCart($userId);
            }

            $userCart = $this->getOrCreateCart($userId);

            foreach ($sessionCart->items as $sessionItem) {
                $existingItem = $userCart->items()
                    ->where('product_id', $sessionItem->product_id)
                    ->first();

                if ($existingItem) {
                    $existingItem->quantity += $sessionItem->quantity;
                    $existingItem->subtotal = $existingItem->quantity * $existingItem->unit_price;
                    $existingItem->save();
                } else {
                    CartItem::create([
                        'cart_id' => $userCart->id,
                        'product_id' => $sessionItem->product_id,
                        'quantity' => $sessionItem->quantity,
                        'unit_price' => $sessionItem->unit_price,
                        'subtotal' => $sessionItem->subtotal,
                    ]);
                }
            }

            $sessionCart->delete();

            $userCart->load('items.product');

            event(new \App\Domain\Cart\Events\CartUpdated($userCart));

            return $userCart;
        });
    }

    /**
     * Generate a unique session ID
     *
     * @return string
     */
    protected function generateSessionId(): string
    {
        return uniqid('cart_', true) . '_' . bin2hex(random_bytes(8));
    }

    /**
     * Validate cart stock availability
     *
     * @param Cart $cart
     * @return array
     */
    public function validateCartStock(Cart $cart): array
    {
        $unavailableItems = [];

        foreach ($cart->items as $item) {
            if ($item->product->stock < $item->quantity) {
                $unavailableItems[] = [
                    'item' => $item,
                    'requested' => $item->quantity,
                    'available' => $item->product->stock,
                ];
            }
        }

        return $unavailableItems;
    }

    /**
     * Apply coupon to cart
     *
     * @param Cart $cart
     * @param string $couponCode
     * @return bool
     */
    public function applyCoupon(Cart $cart, $couponCode): bool
    {
        // Coupon logic would be implemented here
        // This is a placeholder for future implementation

        $cart->coupon_code = $couponCode;
        $cart->save();

        event(new \App\Domain\Cart\Events\CartUpdated($cart));

        return true;
    }

    /**
     * Remove coupon from cart
     *
     * @param Cart $cart
     * @return bool
     */
    public function removeCoupon(Cart $cart): bool
    {
        $cart->coupon_code = null;
        $cart->discount_amount = 0;
        $cart->save();

        event(new \App\Domain\Cart\Events\CartUpdated($cart));

        return true;
    }

    /**
     * Get cart summary
     *
     * @param Cart $cart
     * @return array
     */
    public function getCartSummary(Cart $cart): array
    {
        return [
            'cart_id' => $cart->id,
            'items_count' => $this->getCartItemsCount($cart),
            'subtotal' => $this->getCartTotal($cart),
            'discount' => $cart->discount_amount ?? 0,
            'total' => $this->getCartTotal($cart) - ($cart->discount_amount ?? 0),
            'items' => $cart->items->map(function ($item) {
                return [
                    'product_id' => $item->product_id,
                    'product_name' => $item->product->name,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'subtotal' => $item->subtotal,
                ];
            }),
        ];
    }
}
