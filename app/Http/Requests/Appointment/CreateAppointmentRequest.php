<?php

namespace App\Http\Requests\Appointment;

use Illuminate\Foundation\Http\FormRequest;

class CreateAppointmentRequest extends FormRequest
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
            'id_user' => 'required|integer|exists:users,id',
            'id_customer' => 'required|integer|exists:customers,id',
            'id_room' => 'required|integer|exists:rooms,id',
            'id_service' => 'required|integer|exists:services,id',
            'date' => 'required|date_format:Y-m-d',
            'start_time' => 'required|string',
            'end_time' => 'string|after:start_time',
            'price' => 'nullable|numeric|min:0',
            'status' => 'nullable|string|in:SCHEDULED,IN_PROGRESS,CANCELLED,COMPLETED',
            'notes' => 'nullable|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'id_user.required' => 'O campo usuário é obrigatório.',
            'id_customer.required' => 'O campo cliente é obrigatório.',
            'id_room.required' => 'O campo sala é obrigatório.',
            'id_service.required' => 'O campo serviço é obrigatório.',
            'date.required' => 'O campo data é obrigatório.',
            'start_time.required' => 'O campo hora de início é obrigatório.',
            'end_time.string' => 'O campo hora de término deve ser uma string.',
            'price.numeric' => 'O campo preço deve ser um número.',
            'end_time.after' => 'A hora de término deve ser posterior à hora de início.',
            'status.in' => 'O status deve ser um dos seguintes: SCHEDULED, IN_PROGRESS, CANCELLED, COMPLETED.',
        ];
    }
}
