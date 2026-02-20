<?php

declare(strict_types=1);

namespace App\Http\Resources\Catalog;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

/**
 * @mixin \Illuminate\Database\Eloquent\Collection
 */
class ProductCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'data' => ProductResource::collection($this->collection),
            'meta' => [
                'total' => $this->total() ?? $this->collection->count(),
                'per_page' => $this->perPage() ?? $this->collection->count(),
                'current_page' => $this->currentPage() ?? 1,
                'last_page' => $this->lastPage() ?? 1,
                'from' => $this->firstItem() ?? 1,
                'to' => $this->lastItem() ?? $this->collection->count(),
            ],
        ];
    }
}
