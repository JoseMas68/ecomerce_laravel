<?php

declare(strict_types=1);

namespace App\Http\Requests\V1\Users;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @property string|null $label
 * @property string|null $first_name
 * @property string|null $last_name
 * @property string|null $address_line_1
 * @property string|null $address_line_2
 * @property string|null $city
 * @property string|null $postal_code
 * @property string|null $province
 * @property string|null $phone
 * @property bool|null $is_default_shipping
 * @property bool|null $is_default_billing
 */
class UpdateAddressRequest extends FormRequest
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
            'label' => ['nullable', 'string', 'max:100'],
            'first_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'],
            'address_line_1' => ['nullable', 'string', 'max:255'],
            'address_line_2' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'province' => ['nullable', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:20'],
            'is_default_shipping' => ['nullable', 'boolean'],
            'is_default_billing' => ['nullable', 'boolean'],
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
            'label.max' => 'The label cannot exceed 100 characters.',
            'first_name.max' => 'The first name cannot exceed 255 characters.',
            'last_name.max' => 'The last name cannot exceed 255 characters.',
            'address_line_1.max' => 'The address line 1 cannot exceed 255 characters.',
            'address_line_2.max' => 'The address line 2 cannot exceed 255 characters.',
            'city.max' => 'The city cannot exceed 100 characters.',
            'postal_code.max' => 'The postal code cannot exceed 20 characters.',
            'province.max' => 'The province cannot exceed 100 characters.',
            'phone.max' => 'The phone number cannot exceed 20 characters.',
            'is_default_shipping.boolean' => 'The is default shipping must be a boolean.',
            'is_default_billing.boolean' => 'The is default billing must be a boolean.',
        ];
    }
}
