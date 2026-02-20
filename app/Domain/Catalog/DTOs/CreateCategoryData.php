<?php

declare(strict_types=1);

namespace App\Domain\Catalog\DTOs;

/**
 * Data Transfer Object for creating a category.
 */
readonly class CreateCategoryData
{
    /**
     * Create a new CreateCategoryData instance.
     *
     * @param string $name
     * @param string $slug
     * @param string|null $description
     * @param int|null $parent_id
     * @param bool $is_active
     */
    public function __construct(
        public string $name,
        public string $slug,
        public ?string $description = null,
        public ?int $parent_id = null,
        public bool $is_active = true,
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
            name: $data['name'],
            slug: $data['slug'],
            description: $data['description'] ?? null,
            parent_id: $data['parent_id'] ?? null,
            is_active: $data['is_active'] ?? true,
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
            is_active: $request->boolean('is_active', true),
        );
    }

    /**
     * Convert DTO to array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'parent_id' => $this->parent_id,
            'is_active' => $this->is_active,
        ];
    }
}
