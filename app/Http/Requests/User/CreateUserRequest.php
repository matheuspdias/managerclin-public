<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class CreateUserRequest extends FormRequest
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
            'id_role' => 'required|integer|exists:roles,id',
            'name' => 'required|string|max:100',
            'email' => 'required|email',
            'phone' => 'sometimes|string',
        ];
    }

    /**
     * Get the validation messages that apply to the request.
     */
    public function messages(): array
    {
        return [
            'id_role.required' => 'O campo função é obrigatório.',
            'id_role.integer' => 'O campo função deve ser um número inteiro.',
            'name.required' => 'O campo nome é obrigatório.',
            'name.string' => 'O campo nome deve ser uma string.',
            'name.max' => 'O campo nome deve ter no máximo 100 caracteres.',
            'email.required' => 'O campo e-mail é obrigatório.',
            'email.email' => 'O campo e-mail deve ser um endereço de e-mail válido.',
            'phone.string' => 'O campo telefone deve ser uma string.',
        ];
    }
}
