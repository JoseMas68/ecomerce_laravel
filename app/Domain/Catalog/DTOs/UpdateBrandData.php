<?php

declare(strict_types=1);

namespace App\Domain\Catalog\DTOs;

/**
 * Data Transfer Object for updating a brand.
 */
readonly class UpdateBrandData
{
    /**
     * Create a new UpdateBrandData instance.
     *
     * @param string|null $name
     * @param string|null $slug
     * @param string|null $description
     * @param string|null $logo
     * @param bool|null $is_active
     */
    public function __construct(
        public ?string $name = null,
        public ?string $slug = null,
        public ?string $description = null,
        public ?string $logo = null,
        public ?bool $is_active = null,
    ) {
    }

    /**
     * Create DTO from array.
     *
     * @param array<string, mixed> $data
     * @return self
     */
    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'] ?? null,
            slug: $data['slug'] ?? null,
            description: $data['description'] ?? null,
            logo: $data['logo'] ?? null,
            is_active: $data['is_active'] ?? null,
        );
    }

    /**
     * Create DTO from Request.
     *
     * @param \Illuminate\Http\Request $request
     * @return self
     */
    public static function fromRequest(\Illuminate\Http\Request $request): self
    {
        return new self(
            name: $request->input('name'),
            slug: $request->input('slug'),
            description: $request->input('description'),
            logo: $request->input('logo'),
            is_active: $request->has('is_active') ? $request->boolean('is_active') : null,
        );
    }

    /**
     * Convert DTO to array (excluding null values).
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'logo' => $this->logo,
            'is_active' => $this->is_active,
        ], fn ($value) => $value !== null);
    }
}
