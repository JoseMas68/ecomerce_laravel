<?php

declare(strict_types=1);

namespace App\Domain\Cart\Models;

use App\Domain\Catalog\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int|null $user_id
 * @property string|null $session_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read User|null $user
 * @property-read \Illuminate\Database\Eloquent\Collection<int, CartItem> $items
 * @property-read int|null $items_count
 */
class Cart extends Model
{
    protected $fillable = [
        'user_id',
        'session_id',
    ];

    protected $casts = [
        'user_id' => 'integer',
    ];

    /**
     * Get the user that owns the cart.
     *
     * @return BelongsTo<User, Cart>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the items for the cart.
     *
     * @return HasMany<CartItem>
     */
    public function items(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    /**
     * Get the total amount of the cart.
     *
     * @return float
     */
    public function getTotalAttribute(): float
    {
        return (float) $this->items->sum('subtotal');
    }

    /**
     * Get the total number of items in the cart.
     *
     * @return int
     */
    public function getTotalItemsAttribute(): int
    {
        return $this->items->sum('quantity');
    }

    /**
     * Check if the cart belongs to a user.
     *
     * @return bool
     */
    public function isGuestCart(): bool
    {
        return $this->user_id === null;
    }

    /**
     * Add an item to the cart.
     *
     * @param Product $product
     * @param int $quantity
     * @return CartItem
     */
    public function addItem(Product $product, int $quantity = 1): CartItem
    {
        $cartItem = $this->items()->where('product_id', $product->id)->first();

        if ($cartItem) {
            $cartItem->increment('quantity', $quantity);
            $cartItem->calculateSubtotal();
            $cartItem->save();
        } else {
            $cartItem = $this->items()->create([
                'product_id' => $product->id,
                'quantity' => $quantity,
                'unit_price' => $product->price,
            ]);
            $cartItem->calculateSubtotal();
            $cartItem->save();
        }

        return $cartItem;
    }

    /**
     * Remove an item from the cart.
     *
     * @param int $cartItemId
     * @return bool
     */
    public function removeItem(int $cartItemId): bool
    {
        return $this->items()->where('id', $cartItemId)->delete();
    }

    /**
     * Clear all items from the cart.
     *
     * @return bool
     */
    public function clear(): bool
    {
        return $this->items()->delete() !== false;
    }

    /**
     * Merge a guest cart into a user cart.
     *
     * @param Cart $guestCart
     * @return void
     */
    public function merge(Cart $guestCart): void
    {
        foreach ($guestCart->items as $item) {
            $existingItem = $this->items()->where('product_id', $item->product_id)->first();

            if ($existingItem) {
                $existingItem->increment('quantity', $item->quantity);
                $existingItem->calculateSubtotal();
                $existingItem->save();
            } else {
                $this->items()->create([
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                ]);
            }
        }

        $guestCart->delete();
    }
}
