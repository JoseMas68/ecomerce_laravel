<?php

declare(strict_types=1);

namespace App\Domain\Orders\Models;

use App\Domain\Orders\Enums\OrderStatus;
use App\Domain\Orders\Enums\PaymentStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $user_id
 * @property string $order_number
 * @property OrderStatus $status
 * @property float $subtotal
 * @property float $shipping_cost
 * @property float $tax
 * @property float $discount
 * @property float $total
 * @property string $currency
 * @property string|null $shipping_method
 * @property string|null $payment_method
 * @property PaymentStatus $payment_status
 * @property array $shipping_address
 * @property array $billing_address
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon|null $cancelled_at
 * @property \Illuminate\Support\Carbon|null $shipped_at
 * @property \Illuminate\Support\Carbon|null $delivered_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read User $user
 * @property-read \Illuminate\Database\Eloquent\Collection<int, OrderItem> $items
 * @property-read int|null $items_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Payment> $payments
 * @property-read int|null $payments_count
 * @property-read string $status_label
 * @property-read string $status_color
 */
class Order extends Model
{
    protected $fillable = [
        'user_id',
        'order_number',
        'status',
        'subtotal',
        'shipping_cost',
        'tax',
        'discount',
        'total',
        'currency',
        'shipping_method',
        'payment_method',
        'payment_status',
        'shipping_address',
        'billing_address',
        'notes',
        'cancelled_at',
        'shipped_at',
        'delivered_at',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'status' => OrderStatus::class,
        'payment_status' => PaymentStatus::class,
        'subtotal' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'tax' => 'decimal:2',
        'discount' => 'decimal:2',
        'total' => 'decimal:2',
        'shipping_address' => 'array',
        'billing_address' => 'array',
        'cancelled_at' => 'datetime',
        'shipped_at' => 'datetime',
        'delivered_at' => 'datetime',
    ];

    protected $attributes = [
        'currency' => 'EUR',
        'status' => OrderStatus::PENDING,
        'payment_status' => PaymentStatus::PENDING,
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($order) {
            if (empty($order->order_number)) {
                $order->order_number = self::generateOrderNumber();
            }
        });
    }

    public static function generateOrderNumber(): string
    {
        $prefix = 'ORD';
        $date = now()->format('Ymd');
        $lastOrder = self::where('order_number', 'like', "{$prefix}{$date}%")
            ->orderBy('order_number', 'desc')
            ->first();

        $sequence = $lastOrder
            ? (int) substr($lastOrder->order_number, -4) + 1
            : 1;

        return sprintf('%s%s%04d', $prefix, $date, $sequence);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function scopeByStatus(Builder $query, OrderStatus $status): Builder
    {
        return $query->where('status', $status);
    }

    public function scopeByUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', OrderStatus::PENDING);
    }

    public function scopeProcessing(Builder $query): Builder
    {
        return $query->where('status', OrderStatus::PROCESSING);
    }

    public function scopeShipped(Builder $query): Builder
    {
        return $query->where('status', OrderStatus::SHIPPED);
    }

    public function scopeDelivered(Builder $query): Builder
    {
        return $query->where('status', OrderStatus::DELIVERED);
    }

    public function scopeCancelled(Builder $query): Builder
    {
        return $query->where('status', OrderStatus::CANCELLED);
    }

    public function scopeRefunded(Builder $query): Builder
    {
        return $query->where('status', OrderStatus::REFUNDED);
    }

    public function getStatusLabelAttribute(): string
    {
        return $this->status->label();
    }

    public function getStatusColorAttribute(): string
    {
        return $this->status->color();
    }

    public function canBeCancelled(): bool
    {
        return in_array($this->status, [
            OrderStatus::PENDING,
            OrderStatus::PROCESSING,
        ]);
    }

    public function canBeRefunded(): bool
    {
        return in_array($this->status, [
            OrderStatus::DELIVERED,
            OrderStatus::SHIPPED,
        ]) && $this->payment_status === PaymentStatus::COMPLETED;
    }

    public function canBeShipped(): bool
    {
        return $this->status === OrderStatus::PROCESSING
            && $this->payment_status === PaymentStatus::COMPLETED;
    }

    public function canBeDelivered(): bool
    {
        return $this->status === OrderStatus::SHIPPED;
    }

    public function updateStatus(OrderStatus $status): bool
    {
        if (!$this->status->canTransitionTo($status)) {
            return false;
        }

        $this->status = $status;

        return $this->save();
    }

    public function calculateTotals(): void
    {
        $this->subtotal = $this->items->sum('subtotal');
        $this->tax = $this->items->sum('tax');
        $this->total = $this->subtotal + $this->shipping_cost + $this->tax - $this->discount;
    }
}
