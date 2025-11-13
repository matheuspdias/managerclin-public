<?php

namespace App\Http\Requests\Customer;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCustomerRequest extends FormRequest
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
            'email' => 'nullable|email',
            'phone' => 'sometimes|string',
            'birth_date' => 'sometimes|date',
            'cpf' => 'nullable|string',
            'notes' => 'sometimes|string|nullable',
        ];
    }

    public function messages(): array
    {
        return [
            'name.string' => 'O campo nome deve ser uma string.',
            'email.email' => 'O campo email deve ser um endereço de email válido.',
            'phone.string' => 'O campo telefone deve ser uma string.',
            'birth_date.date' => 'O campo data de nascimento deve ser uma data válida.',
            'cpf.string' => 'O campo CPF deve ser uma string.',
            'notes.string' => 'O campo observações deve ser uma string.',
        ];
    }
}
