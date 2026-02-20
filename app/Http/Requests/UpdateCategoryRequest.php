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
 * @property-read int|null $parent_id
 */
final class UpdateCategoryRequest extends FormRequest
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
        $categoryId = $this->route('category');

        return [
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:categories,slug,'.$categoryId,
            'description' => 'nullable|string|max:1000',
            'parent_id' => 'nullable|integer|exists:categories,id',
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
            'parent_id.integer' => 'El campo categoría padre debe ser un número entero.',
            'parent_id.exists' => 'La categoría padre seleccionada no existe.',
            'parent_id.not_in' => 'Una categoría no puede ser su propia padre.',
        ];
    }

    /**
     * Configura el validador.
     *
     * @param  \Illuminate\Contracts\Validation\Validator  $validator
     * @return void
     */
    protected function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            $parentId = $this->input('parent_id');
            $categoryId = $this->route('category');

            // Validar que parent_id no sea el mismo ID de la categoría que se está actualizando
            if ($parentId !== null && is_numeric($parentId) && $categoryId) {
                if ((int) $parentId === (int) $categoryId) {
                    $validator->errors()->add('parent_id', 'Una categoría no puede ser su propia padre.');
                }
            }
        });
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
