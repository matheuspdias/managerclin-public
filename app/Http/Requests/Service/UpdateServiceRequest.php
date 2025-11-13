<?php

namespace App\Http\Requests\Service;

use Illuminate\Foundation\Http\FormRequest;

class UpdateServiceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'sometimes|string|max:255',
            'description' => 'sometimes|string|max:500',
            'price' => 'sometimes|numeric|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'name.string' => 'O campo nome deve ser uma string.',
            'name.max' => 'O campo nome não pode ter mais de 255 caracteres.',
            'description.string' => 'O campo descrição deve ser uma string.',
            'description.max' => 'O campo descrição não pode ter mais de 500 caracteres.',
            'price.numeric' => 'O campo preço deve ser um número.',
            'price.min' => 'O campo preço não pode ser negativo.',
        ];
    }
}
