<?php

declare(strict_types=1);

namespace App\Domain\Orders\DTOs;

class ShippingAddressData
{
    public function __construct(
        public readonly string $firstName,
        public readonly string $lastName,
        public readonly string $address,
        public readonly string $city,
        public readonly string $state,
        public readonly string $postalCode,
        public readonly string $country,
        public readonly ?string $phone = null,
        public readonly ?string $company = null
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            firstName: $data['first_name'],
            lastName: $data['last_name'],
            address: $data['address'],
            city: $data['city'],
            state: $data['state'],
            postalCode: $data['postal_code'],
            country: $data['country'],
            phone: $data['phone'] ?? null,
            company: $data['company'] ?? null
        );
    }

    public function toArray(): array
    {
        return [
            'first_name' => $this->firstName,
            'last_name' => $this->lastName,
            'address' => $this->address,
            'city' => $this->city,
            'state' => $this->state,
            'postal_code' => $this->postalCode,
            'country' => $this->country,
            'phone' => $this->phone,
            'company' => $this->company,
        ];
    }

    public function getFullName(): string
    {
        return trim($this->firstName . ' ' . $this->lastName);
    }

    public function getFullAddress(): string
    {
        $parts = array_filter([
            $this->address,
            $this->company,
            $this->city,
            $this->state,
            $this->postalCode,
            $this->country,
        ]);

        return implode(', ', $parts);
    }
}
