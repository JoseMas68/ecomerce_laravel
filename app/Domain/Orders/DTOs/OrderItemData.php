<?php

declare(strict_types=1);

namespace App\Domain\Orders\DTOs;

class OrderItemData
{
    public function __construct(
        public readonly int $productId,
        public readonly string $productName,
        public readonly string $productSlug,
        public readonly int $quantity,
        public readonly float $unitPrice,
        public readonly ?string $productImage = null
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            productId: $data['product_id'],
            productName: $data['product_name'],
            productSlug: $data['product_slug'],
            quantity: (int) $data['quantity'],
            unitPrice: (float) $data['unit_price'],
            productImage: $data['product_image'] ?? null
        );
    }

    public static function fromCartItem(\App\Domain\Cart\Models\CartItem $cartItem): self
    {
        return new self(
            productId: $cartItem->product_id,
            productName: $cartItem->product->name,
            productSlug: $cartItem->product->slug,
            quantity: $cartItem->quantity,
            unitPrice: (float) $cartItem->unit_price,
            productImage: $cartItem->product->image?->first()?->url ?? null
        );
    }

    public function toArray(): array
    {
        return [
            'product_id' => $this->productId,
            'product_name' => $this->productName,
            'product_slug' => $this->productSlug,
            'quantity' => $this->quantity,
            'unit_price' => $this->unitPrice,
            'product_image' => $this->productImage,
        ];
    }

    public function getSubtotal(): float
    {
        return $this->quantity * $this->unitPrice;
    }
}
