<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
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
            'id_role' => 'sometimes|integer|exists:roles,id',
            'name' => 'sometimes|string|max:255',
            'email' => 'nullable|email',
            'phone' => 'sometimes|string',
        ];
    }

    /**
     * Get the validation messages that apply to the request.
     */
    public function messages(): array
    {
        return [
            'id_role.integer' => 'O campo função deve ser um número inteiro.',
            'name.string' => 'O campo nome deve ser uma string.',
            'name.max' => 'O campo nome não pode ter mais de 255 caracteres.',
            'email.email' => 'O campo e-mail deve ser um endereço de e-mail válido.',
            'phone.string' => 'O campo telefone deve ser uma string.',
        ];
    }
}
