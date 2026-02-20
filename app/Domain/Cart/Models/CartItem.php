<?php

declare(strict_types=1);

namespace App\Domain\Cart\Models;

use App\Domain\Catalog\Models\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $cart_id
 * @property int $product_id
 * @property int $quantity
 * @property float $unit_price
 * @property float $subtotal
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Cart $cart
 * @property-read Product $product
 */
class CartItem extends Model
{
    protected $fillable = [
        'cart_id',
        'product_id',
        'quantity',
        'unit_price',
        'subtotal',
    ];

    protected $casts = [
        'cart_id' => 'integer',
        'product_id' => 'integer',
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    /**
     * Get the cart that owns the item.
     *
     * @return BelongsTo<Cart, CartItem>
     */
    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    /**
     * Get the product that owns the item.
     *
     * @return BelongsTo<Product, CartItem>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Calculate and update the subtotal for this cart item.
     *
     * @return void
     */
    public function calculateSubtotal(): void
    {
        $this->subtotal = $this->quantity * (float) $this->unit_price;
    }

    /**
     * Update the quantity of the item.
     *
     * @param int $quantity
     * @return bool
     */
    public function updateQuantity(int $quantity): bool
    {
        if ($quantity <= 0) {
            return false;
        }

        $this->quantity = $quantity;
        $this->calculateSubtotal();
        return $this->save();
    }

    /**
     * Increment the quantity of the item.
     *
     * @param int $amount
     * @return bool
     */
    public function incrementQuantity(int $amount = 1): bool
    {
        $this->increment('quantity', $amount);
        $this->calculateSubtotal();
        return $this->save();
    }

    /**
     * Decrement the quantity of the item.
     *
     * @param int $amount
     * @return bool
     */
    public function decrementQuantity(int $amount = 1): bool
    {
        $newQuantity = $this->quantity - $amount;

        if ($newQuantity <= 0) {
            return false;
        }

        $this->decrement('quantity', $amount);
        $this->calculateSubtotal();
        return $this->save();
    }

    /**
     * Check if the item is in stock.
     *
     * @return bool
     */
    public function isInStock(): bool
    {
        return $this->product && $this->product->stock >= $this->quantity;
    }

    /**
     * Get the stock status of the item.
     *
     * @return string
     */
    public function getStockStatus(): string
    {
        if (!$this->product) {
            return 'product_not_found';
        }

        if ($this->product->stock === 0) {
            return 'out_of_stock';
        }

        if ($this->product->stock < $this->quantity) {
            return 'insufficient_stock';
        }

        return 'in_stock';
    }
}
