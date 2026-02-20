<?php

declare(strict_types=1);

namespace App\Domain\Orders\Models;

use App\Domain\Catalog\Models\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $order_id
 * @property int $product_id
 * @property string $product_name
 * @property string $product_slug
 * @property int $quantity
 * @property float $unit_price
 * @property float $subtotal
 * @property float $tax
 * @property float $total
 * @property string|null $product_image
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Order $order
 * @property-read Product $product
 */
class OrderItem extends Model
{
    protected $fillable = [
        'order_id',
        'product_id',
        'product_name',
        'product_slug',
        'quantity',
        'unit_price',
        'subtotal',
        'tax',
        'total',
        'product_image',
    ];

    protected $casts = [
        'order_id' => 'integer',
        'product_id' => 'integer',
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'tax' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($item) {
            if (empty($item->subtotal) || empty($item->tax) || empty($item->total)) {
                $item->calculateTotals();
            }
        });
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function calculateTotals(): void
    {
        $this->subtotal = $this->quantity * (float) $this->unit_price;

        $taxRate = 0.21;
        $this->tax = $this->subtotal * $taxRate;
        $this->total = $this->subtotal + $this->tax;
    }

    public function getTotalWeight(): float
    {
        return $this->product ? $this->product->weight * $this->quantity : 0;
    }
}
