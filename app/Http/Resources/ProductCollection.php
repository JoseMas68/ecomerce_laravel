<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

/**
 * @property-read \Illuminate\Support\Collection<int, \App\Models\Product> $collection
 */
final class ProductCollection extends ResourceCollection
{
    /**
     * Transforma la colección de recursos en un array.
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
            ],
            'links' => $this->when(
                $this->resource instanceof \Illuminate\Pagination\LengthAwarePaginator,
                [
                    'first' => $this->url(1),
                    'last' => $this->url($this->lastPage()),
                    'prev' => $this->previousPageUrl(),
                    'next' => $this->nextPageUrl(),
                ]
            ),
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
