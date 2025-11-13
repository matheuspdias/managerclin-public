<?php

namespace App\Services\Whatsapp;

use App\Models\Appointment;
use Carbon\Carbon;

class WhatsappMessageTemplateService
{
    /**
     * Substitui as variáveis da mensagem pelos valores reais.
     *
     * @param string $template
     * @param Appointment $appointment
     * @return string
     */
    public function replaceVariables(string $template, Appointment $appointment): string
    {
        $variables = $this->getAvailableVariables($appointment);

        foreach ($variables as $key => $value) {
            $template = str_replace("{{{$key}}}", $value, $template);
        }

        return $template;
    }

    /**
     * Retorna as variáveis disponíveis para um agendamento.
     *
     * @param Appointment $appointment
     * @return array
     */
    public function getAvailableVariables(Appointment $appointment): array
    {
        $startTime = Carbon::parse($appointment->start_time)->format('H:i');
        $endTime = Carbon::parse($appointment->end_time)->format('H:i');

        return [
            'nome_usuario' => $appointment->customer->name,
            'profissional' => $appointment->user->name,
            'inicio_atendimento' => $startTime,
            'fim_atendimento' => $endTime,
        ];
    }

    /**
     * Retorna a mensagem padrão para 1 dia antes.
     *
     * @return string
     */
    public function getDefaultDayBeforeMessage(): string
    {
        return "Olá {{nome_usuario}}, lembrando que você tem um agendamento amanhã às {{inicio_atendimento}} com Dr. {{profissional}}.";
    }

    /**
     * Retorna a mensagem padrão para 3 horas antes.
     *
     * @return string
     */
    public function getDefault3HoursBeforeMessage(): string
    {
        return "Olá {{nome_usuario}}, lembrando que você tem um agendamento hoje às {{inicio_atendimento}} com Dr. {{profissional}}.";
    }

    /**
     * Retorna a lista de variáveis disponíveis com suas descrições.
     *
     * @return array
     */
    public function getVariableDescriptions(): array
    {
        return [
            'nome_usuario' => 'Nome completo do cliente/paciente',
            'profissional' => 'Nome do profissional que realizará o atendimento',
            'inicio_atendimento' => 'Horário de início do atendimento (ex: 14:30)',
            'fim_atendimento' => 'Horário de término do atendimento (ex: 15:30)',
        ];
    }
}
