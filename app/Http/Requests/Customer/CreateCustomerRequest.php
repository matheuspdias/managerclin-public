<?php

namespace App\Http\Requests\Customer;

use Illuminate\Foundation\Http\FormRequest;

class CreateCustomerRequest extends FormRequest
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
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'birthdate' => 'nullable|date_format:Y-m-d',
            'cpf' => 'nullable|string|max:14',
            'notes' => 'nullable|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'O campo nome é obrigatório.',
            'email.email' => 'O campo email deve ser um endereço de email válido.',
            'phone.string' => 'O campo telefone deve ser uma string.',
            'birthdate.date_format' => 'O campo data de nascimento deve estar no formato YYYY-MM-DD.',
            'cpf.string' => 'O campo CPF deve ser uma string.',
            'notes.string' => 'O campo observações deve ser uma string.',
        ];
    }
}
