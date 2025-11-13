<?php

namespace App\Http\Requests\MedicalRecord;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateMedicalRecordRequest extends FormRequest
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
            'id_appointment' => 'nullable|integer|exists:appointments,id',
            'chief_complaint' => 'required|string|max:1000',
            'physical_exam' => 'required|string|max:2000',
            'diagnosis' => 'required|string|max:1000',
            'treatment_plan' => 'required|string|max:2000',
            'prescriptions' => 'nullable|string|max:2000',
            'observations' => 'nullable|string|max:1000',
            'follow_up_date' => 'nullable|date|after:today',
            'medical_history' => 'nullable|string',
            'allergies' => 'nullable|string',
            'medications' => 'nullable|string'
        ];
    }

    public function messages(): array
    {
        return [
            'id_appointment.exists' => 'Consulta não encontrada.',
            'chief_complaint.required' => 'A queixa principal é obrigatória.',
            'chief_complaint.max' => 'A queixa principal não pode exceder 1000 caracteres.',
            'physical_exam.required' => 'O exame físico é obrigatório.',
            'physical_exam.max' => 'O exame físico não pode exceder 2000 caracteres.',
            'diagnosis.required' => 'O diagnóstico é obrigatório.',
            'diagnosis.max' => 'O diagnóstico não pode exceder 1000 caracteres.',
            'treatment_plan.required' => 'O plano de tratamento é obrigatório.',
            'treatment_plan.max' => 'O plano de tratamento não pode exceder 2000 caracteres.',
            'prescriptions.max' => 'As prescrições não podem exceder 2000 caracteres.',
            'observations.max' => 'As observações não podem exceder 1000 caracteres.',
            'follow_up_date.date' => 'A data de retorno deve ser uma data válida.',
            'follow_up_date.after' => 'A data de retorno deve ser posterior a hoje.',
            'medical_history.max' => 'O histórico médico não pode exceder 2000 caracteres.',
            'allergies.max' => 'As alergias não podem exceder 500 caracteres.',
            'medications.max' => 'Os medicamentos não podem exceder 500 caracteres.',
        ];
    }
}
