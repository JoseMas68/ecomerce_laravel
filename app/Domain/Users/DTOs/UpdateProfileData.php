<?php

namespace App\Domain\Users\DTOs;

class UpdateProfileData
{
    public function __construct(
        public readonly ?string $name = null,
        public readonly ?string $email = null,
        public readonly ?string $phone = null,
        public readonly ?string $currentPassword = null,
        public readonly ?string $newPassword = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'] ?? null,
            email: $data['email'] ?? null,
            phone: $data['phone'] ?? null,
            currentPassword: $data['current_password'] ?? null,
            newPassword: $data['new_password'] ?? null,
        );
    }

    public function hasPasswordChange(): bool
    {
        return $this->currentPassword !== null && $this->newPassword !== null;
    }

    public function toArray(): array
    {
        return array_filter([
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
        ], fn ($value) => $value !== null);
    }
}
