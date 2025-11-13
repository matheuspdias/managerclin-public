<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateTelemedicineSessionRequest extends FormRequest
{
    /**
     * Determina se o usuário está autorizado a fazer esta requisição
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Obtém as regras de validação aplicáveis à requisição
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'appointment_id' => [
                'required',
                'integer',
                'exists:appointments,id',
            ],
            'server_url' => [
                'nullable',
                'url',
            ],
        ];
    }

    /**
     * Obtém as mensagens de validação personalizadas
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'appointment_id.required' => 'O ID do agendamento é obrigatório.',
            'appointment_id.integer' => 'O ID do agendamento deve ser um número inteiro.',
            'appointment_id.exists' => 'O agendamento informado não existe.',
            'server_url.url' => 'A URL do servidor deve ser uma URL válida.',
        ];
    }
}
