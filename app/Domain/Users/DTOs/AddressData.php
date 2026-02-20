<?php

namespace App\Domain\Users\DTOs;

class AddressData
{
    public function __construct(
        public readonly int $userId,
        public readonly string $label,
        public readonly string $firstName,
        public readonly string $lastName,
        public readonly ?string $company = null,
        public readonly string $addressLine1,
        public readonly ?string $addressLine2 = null,
        public readonly string $city,
        public readonly string $postalCode,
        public readonly string $province,
        public readonly string $country = 'España',
        public readonly string $phone,
        public readonly bool $isDefaultShipping = false,
        public readonly bool $isDefaultBilling = false,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            userId: $data['user_id'],
            label: $data['label'],
            firstName: $data['first_name'],
            lastName: $data['last_name'],
            company: $data['company'] ?? null,
            addressLine1: $data['address_line_1'],
            addressLine2: $data['address_line_2'] ?? null,
            city: $data['city'],
            postalCode: $data['postal_code'],
            province: $data['province'],
            country: $data['country'] ?? 'España',
            phone: $data['phone'],
            isDefaultShipping: $data['is_default_shipping'] ?? false,
            isDefaultBilling: $data['is_default_billing'] ?? false,
        );
    }

    public function toArray(): array
    {
        return [
            'user_id' => $this->userId,
            'label' => $this->label,
            'first_name' => $this->firstName,
            'last_name' => $this->lastName,
            'company' => $this->company,
            'address_line_1' => $this->addressLine1,
            'address_line_2' => $this->addressLine2,
            'city' => $this->city,
            'postal_code' => $this->postalCode,
            'province' => $this->province,
            'country' => $this->country,
            'phone' => $this->phone,
            'is_default_shipping' => $this->isDefaultShipping,
            'is_default_billing' => $this->isDefaultBilling,
        ];
    }
}
