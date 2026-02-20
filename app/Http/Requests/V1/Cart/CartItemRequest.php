<?php

declare(strict_types=1);

namespace App\Http\Requests\V1\Cart;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @property int $product_id
 * @property int|null $quantity
 */
class CartItemRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
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
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'quantity' => ['nullable', 'integer', 'min:1', 'max:999'],
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
            'product_id.required' => 'The product ID is required.',
            'product_id.exists' => 'The selected product does not exist.',
            'quantity.integer' => 'The quantity must be an integer.',
            'quantity.min' => 'The quantity must be at least 1.',
            'quantity.max' => 'The quantity cannot exceed 999.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'quantity' => $this->integer('quantity', 1),
        ]);
    }

    /**
     * Get the quantity with default value.
     *
     * @return int
     */
    public function getQuantity(): int
    {
        return $this->input('quantity', 1);
    }
}
