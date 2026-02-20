<?php

declare(strict_types=1);

namespace App\Http\Requests\V1\Users;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @property string|null $first_name
 * @property string|null $last_name
 * @property string|null $email
 * @property string|null $phone
 * @property string|null $avatar
 */
class UpdateProfileRequest extends FormRequest
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
        $userId = auth()->id();

        return [
            'first_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'string', 'email', 'max:255', 'unique:users,email,' . $userId],
            'phone' => ['nullable', 'string', 'max:20'],
            'avatar' => ['nullable', 'string', 'url'],
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
            'first_name.max' => 'The first name cannot exceed 255 characters.',
            'last_name.max' => 'The last name cannot exceed 255 characters.',
            'email.email' => 'The email must be a valid email address.',
            'email.max' => 'The email cannot exceed 255 characters.',
            'email.unique' => 'The email is already in use.',
            'phone.max' => 'The phone number cannot exceed 20 characters.',
            'avatar.url' => 'The avatar must be a valid URL.',
        ];
    }
}
