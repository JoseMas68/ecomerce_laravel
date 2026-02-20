<?php

declare(strict_types=1);

namespace App\Domain\Catalog\ValueObjects;

/**
 * Enumeration of stock statuses for products.
 */
enum StockStatus: string
{
    case IN_STOCK = 'in_stock';
    case LOW_STOCK = 'low_stock';
    case OUT_OF_STOCK = 'out_of_stock';

    /**
     * Determine stock status based on quantity.
     *
     * @param int $quantity
     * @param int $lowStockThreshold
     * @return self
     */
    public static function fromQuantity(int $quantity, int $lowStockThreshold = 10): self
    {
        if ($quantity <= 0) {
            return self::OUT_OF_STOCK;
        }

        if ($quantity <= $lowStockThreshold) {
            return self::LOW_STOCK;
        }

        return self::IN_STOCK;
    }

    /**
     * Get human-readable label.
     *
     * @return string
     */
    public function label(): string
    {
        return match ($this) {
            self::IN_STOCK => 'In Stock',
            self::LOW_STOCK => 'Low Stock',
            self::OUT_OF_STOCK => 'Out of Stock',
        };
    }

    /**
     * Get CSS class for UI display.
     *
     * @return string
     */
    public function cssClass(): string
    {
        return match ($this) {
            self::IN_STOCK => 'text-green-600',
            self::LOW_STOCK => 'text-yellow-600',
            self::OUT_OF_STOCK => 'text-red-600',
        };
    }

    /**
     * Check if stock allows ordering.
     *
     * @return bool
     */
    public function isOrderable(): bool
    {
        return $this !== self::OUT_OF_STOCK;
    }

    /**
     * Get all available statuses as array.
     *
     * @return array<string, string>
     */
    public static function toArray(): array
    {
        return [
            self::IN_STOCK->value => self::IN_STOCK->label(),
            self::LOW_STOCK->value => self::LOW_STOCK->label(),
            self::OUT_OF_STOCK->value => self::OUT_OF_STOCK->label(),
        ];
    }
}
