<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

/**
 * @property-read string $name
 * @property-read string $slug
 * @property-read string $sku
 * @property-read string|null $description
 * @property-read float $price
 * @property-read float|null $compare_at_price
 * @property-read float|null $cost
 * @property-read int $stock
 * @property-read int $brand_id
 * @property-read int $category_id
 * @property-read bool|null $is_active
 */
final class StoreProductRequest extends FormRequest
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
            'slug' => 'required|string|max:255|unique:products,slug',
            'sku' => 'required|string|max:100|unique:products,sku',
            'description' => 'nullable|string|max:2000',
            'price' => 'required|numeric|min:0',
            'compare_at_price' => 'nullable|numeric|min:0|gt:price',
            'cost' => 'nullable|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'brand_id' => 'required|integer|exists:brands,id',
            'category_id' => 'required|integer|exists:categories,id',
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
            'sku.required' => 'El campo SKU es obligatorio.',
            'sku.string' => 'El campo SKU debe ser una cadena de texto.',
            'sku.max' => 'El campo SKU no puede tener más de 100 caracteres.',
            'sku.unique' => 'El SKU ya está en uso.',
            'description.string' => 'El campo descripción debe ser una cadena de texto.',
            'description.max' => 'El campo descripción no puede tener más de 2000 caracteres.',
            'price.required' => 'El campo precio es obligatorio.',
            'price.numeric' => 'El campo precio debe ser un número.',
            'price.min' => 'El campo precio debe ser mayor o igual a 0.',
            'compare_at_price.numeric' => 'El campo precio de comparación debe ser un número.',
            'compare_at_price.min' => 'El campo precio de comparación debe ser mayor o igual a 0.',
            'compare_at_price.gt' => 'El campo precio de comparación debe ser mayor que el precio.',
            'cost.numeric' => 'El campo costo debe ser un número.',
            'cost.min' => 'El campo costo debe ser mayor o igual a 0.',
            'stock.required' => 'El campo stock es obligatorio.',
            'stock.integer' => 'El campo stock debe ser un número entero.',
            'stock.min' => 'El campo stock debe ser mayor o igual a 0.',
            'brand_id.required' => 'El campo marca es obligatorio.',
            'brand_id.integer' => 'El campo marca debe ser un número entero.',
            'brand_id.exists' => 'La marca seleccionada no existe.',
            'category_id.required' => 'El campo categoría es obligatorio.',
            'category_id.integer' => 'El campo categoría debe ser un número entero.',
            'category_id.exists' => 'La categoría seleccionada no existe.',
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
