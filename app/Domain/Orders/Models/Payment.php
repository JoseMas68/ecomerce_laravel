<?php

declare(strict_types=1);

namespace App\Domain\Orders\Models;

use App\Domain\Orders\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $order_id
 * @property string|null $payment_gateway
 * @property string|null $transaction_id
 * @property float $amount
 * @property string $currency
 * @property PaymentStatus $status
 * @property string|null $failure_reason
 * @property array|null $metadata
 * @property \Illuminate\Support\Carbon|null $paid_at
 * @property \Illuminate\Support\Carbon|null $refunded_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Order $order
 */
class Payment extends Model
{
    protected $fillable = [
        'order_id',
        'payment_gateway',
        'transaction_id',
        'amount',
        'currency',
        'status',
        'failure_reason',
        'metadata',
        'paid_at',
        'refunded_at',
    ];

    protected $casts = [
        'order_id' => 'integer',
        'amount' => 'decimal:2',
        'status' => PaymentStatus::class,
        'metadata' => 'array',
        'paid_at' => 'datetime',
        'refunded_at' => 'datetime',
    ];

    protected $attributes = [
        'currency' => 'EUR',
        'status' => PaymentStatus::PENDING,
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function markAsPaid(string $transactionId = null): bool
    {
        $this->status = PaymentStatus::COMPLETED;
        $this->paid_at = now();

        if ($transactionId) {
            $this->transaction_id = $transactionId;
        }

        return $this->save();
    }

    public function markAsFailed(string $reason): bool
    {
        $this->status = PaymentStatus::FAILED;
        $this->failure_reason = $reason;

        return $this->save();
    }

    public function markAsRefunded(): bool
    {
        $this->status = PaymentStatus::REFUNDED;
        $this->refunded_at = now();

        return $this->save();
    }

    public function markAsPartiallyRefunded(): bool
    {
        $this->status = PaymentStatus::PARTIALLY_REFUNDED;
        $this->refunded_at = now();

        return $this->save();
    }

    public function isPaid(): bool
    {
        return $this->status === PaymentStatus::COMPLETED;
    }

    public function isFailed(): bool
    {
        return $this->status === PaymentStatus::FAILED;
    }

    public function isRefunded(): bool
    {
        return in_array($this->status, [
            PaymentStatus::REFUNDED,
            PaymentStatus::PARTIALLY_REFUNDED,
        ]);
    }
}
