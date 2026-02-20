<?php

declare(strict_types=1);

namespace App\Http\Requests\V1\Orders;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @property array $shipping_address
 * @property array $billing_address
 * @property string $shipping_method
 * @property string $payment_method
 * @property string|null $notes
 */
class CreateOrderRequest extends FormRequest
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
            'shipping_address' => ['required', 'array'],
            'shipping_address.first_name' => ['required', 'string', 'max:255'],
            'shipping_address.last_name' => ['required', 'string', 'max:255'],
            'shipping_address.address_line_1' => ['required', 'string', 'max:255'],
            'shipping_address.address_line_2' => ['nullable', 'string', 'max:255'],
            'shipping_address.city' => ['required', 'string', 'max:100'],
            'shipping_address.state' => ['required', 'string', 'max:100'],
            'shipping_address.postal_code' => ['required', 'string', 'max:20'],
            'shipping_address.country' => ['required', 'string', 'max:100'],
            'shipping_address.phone' => ['required', 'string', 'max:20'],

            'billing_address' => ['required', 'array'],
            'billing_address.first_name' => ['required', 'string', 'max:255'],
            'billing_address.last_name' => ['required', 'string', 'max:255'],
            'billing_address.address_line_1' => ['required', 'string', 'max:255'],
            'billing_address.address_line_2' => ['nullable', 'string', 'max:255'],
            'billing_address.city' => ['required', 'string', 'max:100'],
            'billing_address.state' => ['required', 'string', 'max:100'],
            'billing_address.postal_code' => ['required', 'string', 'max:20'],
            'billing_address.country' => ['required', 'string', 'max:100'],
            'billing_address.phone' => ['required', 'string', 'max:20'],

            'shipping_method' => ['required', 'string', 'in:standard,express,free'],
            'payment_method' => ['required', 'string', 'in:card,paypal,transfer'],
            'notes' => ['nullable', 'string', 'max:500'],
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
            'shipping_address.required' => 'The shipping address is required.',
            'shipping_address.first_name.required' => 'The shipping first name is required.',
            'shipping_address.last_name.required' => 'The shipping last name is required.',
            'shipping_address.address_line_1.required' => 'The shipping address line 1 is required.',
            'shipping_address.city.required' => 'The shipping city is required.',
            'shipping_address.state.required' => 'The shipping state is required.',
            'shipping_address.postal_code.required' => 'The shipping postal code is required.',
            'shipping_address.country.required' => 'The shipping country is required.',
            'shipping_address.phone.required' => 'The shipping phone is required.',

            'billing_address.required' => 'The billing address is required.',
            'billing_address.first_name.required' => 'The billing first name is required.',
            'billing_address.last_name.required' => 'The billing last name is required.',
            'billing_address.address_line_1.required' => 'The billing address line 1 is required.',
            'billing_address.city.required' => 'The billing city is required.',
            'billing_address.state.required' => 'The billing state is required.',
            'billing_address.postal_code.required' => 'The billing postal code is required.',
            'billing_address.country.required' => 'The billing country is required.',
            'billing_address.phone.required' => 'The billing phone is required.',

            'shipping_method.required' => 'The shipping method is required.',
            'shipping_method.in' => 'The shipping method must be one of: standard, express, free.',

            'payment_method.required' => 'The payment method is required.',
            'payment_method.in' => 'The payment method must be one of: card, paypal, transfer.',

            'notes.max' => 'The notes cannot exceed 500 characters.',
        ];
    }
}
