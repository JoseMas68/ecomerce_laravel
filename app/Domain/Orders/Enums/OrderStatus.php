<?php

declare(strict_types=1);

namespace App\Domain\Orders\Enums;

enum OrderStatus: string
{
    case PENDING = 'pending';
    case PROCESSING = 'processing';
    case SHIPPED = 'shipped';
    case DELIVERED = 'delivered';
    case CANCELLED = 'cancelled';
    case REFUNDED = 'refunded';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Pending',
            self::PROCESSING => 'Processing',
            self::SHIPPED => 'Shipped',
            self::DELIVERED => 'Delivered',
            self::CANCELLED => 'Cancelled',
            self::REFUNDED => 'Refunded',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PENDING => 'warning',
            self::PROCESSING => 'info',
            self::SHIPPED => 'primary',
            self::DELIVERED => 'success',
            self::CANCELLED => 'danger',
            self::REFUNDED => 'secondary',
        };
    }

    public function canTransitionTo(OrderStatus $status): bool
    {
        return match ($this) {
            self::PENDING => in_array($status, [self::PROCESSING, self::CANCELLED]),
            self::PROCESSING => in_array($status, [self::SHIPPED, self::CANCELLED]),
            self::SHIPPED => in_array($status, [self::DELIVERED, self::REFUNDED]),
            self::DELIVERED => in_array($status, [self::REFUNDED]),
            self::CANCELLED, self::REFUNDED => false,
        };
    }
}
