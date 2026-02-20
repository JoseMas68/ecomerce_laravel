<?php

declare(strict_types=1);

namespace App\Domain\Catalog\DTOs;

/**
 * Data Transfer Object for updating a category.
 */
readonly class UpdateCategoryData
{
    /**
     * Create a new UpdateCategoryData instance.
     *
     * @param string|null $name
     * @param string|null $slug
     * @param string|null $description
     * @param int|null $parent_id
     * @param bool|null $is_active
     */
    public function __construct(
        public ?string $name = null,
        public ?string $slug = null,
        public ?string $description = null,
        public ?int $parent_id = null,
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
            parent_id: $data['parent_id'] ?? null,
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
            parent_id: $request->input('parent_id') ? (int) $request->input('parent_id') : null,
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
            'parent_id' => $this->parent_id,
            'is_active' => $this->is_active,
        ], fn ($value) => $value !== null);
    }
}
