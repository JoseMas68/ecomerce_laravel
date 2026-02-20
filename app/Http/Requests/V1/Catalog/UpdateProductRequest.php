<?php

declare(strict_types=1);

namespace App\Http\Requests\V1\Catalog;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * @property string $name
 * @property string $slug
 * @property string $sku
 * @property string|null $description
 * @property float $price
 * @property float|null $compare_at_price
 * @property float|null $cost
 * @property int $stock
 * @property int $brand_id
 * @property int $category_id
 * @property bool $is_active
 */
class UpdateProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // For now, only authenticated users can update products
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
            'slug' => ['sometimes', 'required', 'string', 'max:255', Rule::unique('products', 'slug')->ignore($this->route('id'))],
            'sku' => ['sometimes', 'required', 'string', 'max:100', Rule::unique('products', 'sku')->ignore($this->route('id'))],
            'description' => ['nullable', 'string'],
            'price' => ['sometimes', 'required', 'numeric', 'min:0', 'regex:/^\d+(\.\d{1,2})?$/'],
            'compare_at_price' => ['nullable', 'numeric', 'min:0', 'regex:/^\d+(\.\d{1,2})?$/'],
            'cost' => ['nullable', 'numeric', 'min:0', 'regex:/^\d+(\.\d{1,2})?$/'],
            'stock' => ['sometimes', 'required', 'integer', 'min:0'],
            'brand_id' => ['sometimes', 'required', 'integer', 'exists:brands,id'],
            'category_id' => ['sometimes', 'required', 'integer', 'exists:categories,id'],
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
            'name.required' => 'The product name is required.',
            'slug.required' => 'The slug is required.',
            'slug.unique' => 'This slug is already in use.',
            'sku.required' => 'The SKU is required.',
            'sku.unique' => 'This SKU is already in use.',
            'price.required' => 'The price is required.',
            'price.regex' => 'The price must have maximum 2 decimal places.',
            'stock.required' => 'The stock quantity is required.',
            'brand_id.required' => 'The brand is required.',
            'brand_id.exists' => 'The selected brand does not exist.',
            'category_id.required' => 'The category is required.',
            'category_id.exists' => 'The selected category does not exist.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('is_active')) {
            $this->merge([
                'is_active' => $this->boolean('is_active'),
            ]);
        }
    }
}
