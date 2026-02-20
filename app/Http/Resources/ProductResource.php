<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Enums\StockStatus;

/**
 * @property-read \App\Models\Product $resource
 */
final class ProductResource extends JsonResource
{
    /**
     * Transforma el recurso en un array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $stockStatus = StockStatus::fromStock($this->stock ?? 0);

        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'sku' => $this->sku,
            'description' => $this->description,
            'price' => $this->price,
            'compare_at_price' => $this->compare_at_price,
            'cost' => $this->cost,
            'stock' => $this->stock,
            'stock_status' => $stockStatus->value,
            'is_active' => (bool) $this->is_active,
            'brand' => $this->when($this->relationLoaded('brand'), fn() => BrandResource::make($this->brand)),
            'category' => $this->when($this->relationLoaded('category'), fn() => CategoryResource::make($this->category)),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            'price_formatted' => $this->formatPrice($this->price),
        ];
    }

    /**
     * Formatea el precio con el símbolo de euro.
     */
    private function formatPrice(float $price): string
    {
        return '€'.number_format($price, 2, '.', ',');
    }

    /**
     * Obtiene datos adicionales que deben devolverse con el recurso.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array<string, mixed>
     */
    public function with(Request $request): array
    {
        return [
            'success' => true,
        ];
    }
}
