<?php

declare(strict_types=1);

namespace App\Http\Requests\V1\Catalog;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * @property string $name
 * @property string $slug
 * @property string|null $description
 * @property int|null $parent_id
 * @property bool $is_active
 */
class UpdateCategoryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // For now, only authenticated users can update categories
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'slug' => ['sometimes', 'required', 'string', 'max:255', Rule::unique('categories', 'slug')->ignore($this->route('id'))],
            'description' => ['nullable', 'string'],
            'parent_id' => ['nullable', 'integer', 'exists:categories,id', Rule::notIn($this->route('id'))],
            'is_active' => ['boolean'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'The category name is required.',
            'slug.required' => 'The slug is required.',
            'slug.unique' => 'This slug is already in use.',
            'parent_id.exists' => 'The selected parent category does not exist.',
            'parent_id.not_in' => 'A category cannot be its own parent.',
        ];
    }
}
