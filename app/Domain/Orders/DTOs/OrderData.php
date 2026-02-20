<?php

declare(strict_types=1);

namespace App\Domain\Orders\DTOs;

use App\Domain\Orders\DTOs\ShippingAddressData;
use App\Domain\Orders\DTOs\BillingAddressData;

class OrderData
{
    public function __construct(
        public readonly int $userId,
        public readonly string $shippingMethod,
        public readonly string $paymentMethod,
        public readonly ShippingAddressData $shippingAddress,
        public readonly BillingAddressData $billingAddress,
        public readonly float $shippingCost,
        public readonly float $taxRate = 0.21,
        public readonly ?float $discount = null,
        public readonly ?string $notes = null,
        public readonly string $currency = 'EUR'
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            userId: $data['user_id'],
            shippingMethod: $data['shipping_method'],
            paymentMethod: $data['payment_method'],
            shippingAddress: ShippingAddressData::fromArray($data['shipping_address']),
            billingAddress: BillingAddressData::fromArray($data['billing_address']),
            shippingCost: (float) $data['shipping_cost'],
            taxRate: (float) ($data['tax_rate'] ?? 0.21),
            discount: isset($data['discount']) ? (float) $data['discount'] : null,
            notes: $data['notes'] ?? null,
            currency: $data['currency'] ?? 'EUR'
        );
    }

    public function toArray(): array
    {
        return [
            'user_id' => $this->userId,
            'shipping_method' => $this->shippingMethod,
            'payment_method' => $this->paymentMethod,
            'shipping_address' => $this->shippingAddress->toArray(),
            'billing_address' => $this->billingAddress->toArray(),
            'shipping_cost' => $this->shippingCost,
            'tax_rate' => $this->taxRate,
            'discount' => $this->discount,
            'notes' => $this->notes,
            'currency' => $this->currency,
        ];
    }
}
