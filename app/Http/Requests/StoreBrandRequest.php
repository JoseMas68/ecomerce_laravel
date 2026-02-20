<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

/**
 * @property-read string $name
 * @property-read string $slug
 * @property-read string|null $description
 * @property-read string|null $logo
 * @property-read bool|null $is_active
 */
final class StoreBrandRequest extends FormRequest
{
    /**
     * Determina si el usuario está autorizado para hacer esta request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Obtiene las reglas de validación que aplican a la request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:brands,slug',
            'description' => 'nullable|string|max:1000',
            'logo' => 'nullable|string|max:500',
            'is_active' => 'nullable|boolean',
        ];
    }

    /**
     * Obtiene los mensajes de error personalizados.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'El campo nombre es obligatorio.',
            'name.string' => 'El campo nombre debe ser una cadena de texto.',
            'name.max' => 'El campo nombre no puede tener más de 255 caracteres.',
            'slug.required' => 'El campo slug es obligatorio.',
            'slug.string' => 'El campo slug debe ser una cadena de texto.',
            'slug.max' => 'El campo slug no puede tener más de 255 caracteres.',
            'slug.unique' => 'El slug ya está en uso.',
            'description.string' => 'El campo descripción debe ser una cadena de texto.',
            'description.max' => 'El campo descripción no puede tener más de 1000 caracteres.',
            'logo.string' => 'El campo logo debe ser una cadena de texto.',
            'logo.max' => 'El campo logo no puede tener más de 500 caracteres.',
            'is_active.boolean' => 'El campo activo debe ser un valor booleano.',
        ];
    }

    /**
     * Maneja una falla de validación.
     *
     * @throws HttpResponseException
     */
    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $validator->errors(),
            ], 422)
        );
    }
}
