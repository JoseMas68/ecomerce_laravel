<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property-read \App\Models\Category $resource
 */
final class CategoryResource extends JsonResource
{
    /**
     * Transforma el recurso en un array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'is_active' => (bool) $this->is_active,
            'parent' => $this->when($this->relationLoaded('parent') && $this->parent !== null, fn() => CategoryResource::make($this->parent)),
            'children' => $this->when($this->relationLoaded('children'), fn() => CategoryResource::collection($this->children)),
            'products_count' => $this->whenCounted('products'),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
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
