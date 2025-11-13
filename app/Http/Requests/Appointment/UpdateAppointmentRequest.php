<?php

namespace App\Http\Requests\Appointment;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAppointmentRequest extends FormRequest
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
            'id_user' => 'sometimes|integer|exists:users,id',
            'id_customer' => 'sometimes|integer|exists:customers,id',
            'id_room' => 'sometimes|integer|exists:rooms,id',
            'id_service' => 'sometimes|integer|exists:services,id',
            'date' => 'sometimes|date_format:Y-m-d',
            'start_time' => 'sometimes|string',
            'end_time' => 'string|after:start_time',
            'price' => 'nullable|numeric|min:0',
            'status' => 'string|in:SCHEDULED,IN_PROGRESS,CANCELLED,COMPLETED',
            'notes' => 'nullable|sometimes|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'id_user.integer' => 'O campo usuário deve ser um número inteiro.',
            'id_customer.integer' => 'O campo cliente deve ser um número inteiro.',
            'id_room.integer' => 'O campo sala deve ser um número inteiro.',
            'id_service.integer' => 'O campo serviço deve ser um número inteiro.',
            'date.date_format' => 'O campo data deve estar no formato YYYY-MM-DD.',
            'start_time.string' => 'O campo hora de início deve ser uma string.',
            'end_time.string' => 'O campo hora de término deve ser uma string.',
            'price.numeric' => 'O campo preço deve ser um número.',
            'end_time.after' => 'A hora de término deve ser posterior à hora de início.',
            'status.in' => 'O status deve ser um dos seguintes: SCHEDULED, IN_PROGRESS, CANCELLED, COMPLETED.',
            'notes.string' => 'O campo observações deve ser uma string.',
        ];
    }
}
