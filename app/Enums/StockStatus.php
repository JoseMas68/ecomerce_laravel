<?php

declare(strict_types=1);

namespace App\Enums;

enum StockStatus: string
{
    case IN_STOCK = 'in_stock';
    case LOW_STOCK = 'low_stock';
    case OUT_OF_STOCK = 'out_of_stock';

    /**
     * Obtiene el estado del stock basado en la cantidad disponible.
     */
    public static function fromStock(int $stock, int $lowStockThreshold = 10): self
    {
        if ($stock === 0) {
            return self::OUT_OF_STOCK;
        }

        if ($stock <= $lowStockThreshold) {
            return self::LOW_STOCK;
        }

        return self::IN_STOCK;
    }

    /**
     * Obtiene la etiqueta traducida del estado.
     */
    public function getLabel(): string
    {
        return match ($this) {
            self::IN_STOCK => 'En stock',
            self::LOW_STOCK => 'Stock bajo',
            self::OUT_OF_STOCK => 'Agotado',
        };
    }
}
