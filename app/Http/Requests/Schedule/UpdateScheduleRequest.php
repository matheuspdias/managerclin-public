<?php

namespace App\Http\Requests\Schedule;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'schedules' => ['required', 'array', 'min:1'],
            'schedules.*.id' => ['nullable', 'integer', 'exists:user_schedules,id'],
            'schedules.*.day_of_week' => ['required', 'integer', 'between:0,6'],
            'schedules.*.start_time' => ['required', 'date_format:H:i:s'],
            'schedules.*.end_time' => ['required', 'date_format:H:i:s', 'after:schedules.*.start_time'],
            'schedules.*.is_work' => ['required', 'boolean'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $schedules = $this->input('schedules', []);

            // Agrupar horários por dia da semana
            $groupedSchedules = [];
            foreach ($schedules as $schedule) {
                $dayOfWeek = $schedule['day_of_week'];
                if (!isset($groupedSchedules[$dayOfWeek])) {
                    $groupedSchedules[$dayOfWeek] = [];
                }
                $groupedSchedules[$dayOfWeek][] = $schedule;
            }

            // Validar cada dia
            foreach ($groupedSchedules as $dayOfWeek => $daySchedules) {
                $hasValidSchedule = false;

                foreach ($daySchedules as $schedule) {
                    // Se is_work é true, deve ter horários válidos
                    if (
                        $schedule['is_work'] &&
                        !empty($schedule['start_time']) &&
                        !empty($schedule['end_time']) &&
                        $schedule['start_time'] !== '00:00:00' &&
                        $schedule['end_time'] !== '00:00:00'
                    ) {
                        $hasValidSchedule = true;
                        break;
                    }
                }

                // Se algum schedule do dia tem is_work true mas não tem horário válido
                $anyIsWork = collect($daySchedules)->contains('is_work', true);
                if ($anyIsWork && !$hasValidSchedule) {
                    $dayNames = [
                        0 => 'Domingo',
                        1 => 'Segunda-feira',
                        2 => 'Terça-feira',
                        3 => 'Quarta-feira',
                        4 => 'Quinta-feira',
                        5 => 'Sexta-feira',
                        6 => 'Sábado'
                    ];

                    $validator->errors()->add(
                        'schedules',
                        "O dia {$dayNames[$dayOfWeek]} está marcado como dia de trabalho mas não possui horários válidos configurados."
                    );
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'schedules.required' => 'É necessário informar os horários de trabalho.',
            'schedules.min' => 'É necessário ter pelo menos um horário configurado.',
            'schedules.*.day_of_week.required' => 'O dia da semana é obrigatório.',
            'schedules.*.day_of_week.between' => 'O dia da semana deve estar entre 0 (Domingo) e 6 (Sábado).',
            'schedules.*.start_time.required' => 'O horário de início é obrigatório.',
            'schedules.*.start_time.date_format' => 'O horário de início deve estar no formato HH:MM:SS.',
            'schedules.*.end_time.required' => 'O horário de término é obrigatório.',
            'schedules.*.end_time.date_format' => 'O horário de término deve estar no formato HH:MM:SS.',
            'schedules.*.end_time.after' => 'O horário de término deve ser após o horário de início.',
            'schedules.*.is_work.required' => 'O campo is_work é obrigatório.',
            'schedules.*.is_work.boolean' => 'O campo is_work deve ser verdadeiro ou falso.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'message' => 'Erro de validação',
            'errors' => $validator->errors(),
        ], 422));
    }
}
