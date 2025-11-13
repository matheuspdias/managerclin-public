<?php

namespace App\Http\Requests;

use App\Models\TelemedicineSession;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTelemedicineSessionRequest extends FormRequest
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
            'status' => [
                'required',
                'string',
                Rule::in([
                    TelemedicineSession::STATUS_ACTIVE,
                    TelemedicineSession::STATUS_COMPLETED,
                    TelemedicineSession::STATUS_CANCELLED,
                ]),
            ],
            'notes' => [
                'nullable',
                'string',
                'max:5000',
            ],
            'end_reason' => [
                'nullable',
                'string',
                'max:255',
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
            'status.required' => 'O status é obrigatório.',
            'status.string' => 'O status deve ser uma string.',
            'status.in' => 'O status informado não é válido. Valores permitidos: ACTIVE, COMPLETED, CANCELLED.',
            'notes.string' => 'As observações devem ser uma string.',
            'notes.max' => 'As observações não podem ter mais de 5000 caracteres.',
            'end_reason.string' => 'O motivo de encerramento deve ser uma string.',
            'end_reason.max' => 'O motivo de encerramento não pode ter mais de 255 caracteres.',
        ];
    }
}
