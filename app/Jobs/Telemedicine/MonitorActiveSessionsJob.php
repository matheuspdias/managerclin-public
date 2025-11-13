<?php

namespace App\Jobs\Telemedicine;

use App\Models\TelemedicineSession;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

/**
 * Job para monitorar sessões ativas de telemedicina e consumir créditos
 * baseado no tempo de uso.
 *
 * Regras:
 * - 1 crédito inicial ao criar a sessão
 * - Cada crédito vale 30 minutos
 * - Se passar de 35 minutos (margem de 5min), consome mais 1 crédito
 * - Continua monitorando a cada 30 minutos
 */
class MonitorActiveSessionsJob implements ShouldQueue
{
    use Queueable;

    /**
     * Tempo máximo por crédito em minutos
     */
    private const CREDIT_DURATION_MINUTES = 30;

    /**
     * Margem de tolerância para consumir próximo crédito (5 minutos)
     */
    private const GRACE_PERIOD_MINUTES = 5;

    /**
     * Tempo total considerado para consumir novo crédito (35 min)
     */
    private const CREDIT_THRESHOLD_MINUTES = self::CREDIT_DURATION_MINUTES + self::GRACE_PERIOD_MINUTES;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('Iniciando monitoramento de sessões ativas de telemedicina');

        // Buscar todas as sessões ativas
        $activeSessions = TelemedicineSession::active()
            ->with(['appointment.customer', 'appointment.user'])
            ->whereNotNull('started_at')
            ->get();

        if ($activeSessions->isEmpty()) {
            Log::info('Nenhuma sessão ativa encontrada');
            return;
        }

        Log::info('Encontradas ' . $activeSessions->count() . ' sessões ativas para monitorar');

        foreach ($activeSessions as $session) {
            $this->checkAndConsumeCredits($session);
        }

        Log::info('Monitoramento de sessões concluído');
    }

    /**
     * Verifica e consome créditos baseado no tempo de sessão ativa
     */
    private function checkAndConsumeCredits(TelemedicineSession $session): void
    {
        // Calcular tempo total desde o início da sessão
        $totalMinutes = $session->started_at->diffInMinutes(now());

        // Calcular quantos créditos DEVEM ter sido consumidos até agora
        // Exemplo: 65 minutos = 2 créditos (0-35min = 1 crédito, 35-65min = 2 créditos)
        $expectedCredits = 1; // Crédito inicial
        if ($totalMinutes > self::CREDIT_THRESHOLD_MINUTES) {
            // Calcula créditos adicionais após os primeiros 35 minutos
            $additionalMinutes = $totalMinutes - self::CREDIT_THRESHOLD_MINUTES;
            $additionalCredits = (int) ceil($additionalMinutes / self::CREDIT_DURATION_MINUTES);
            $expectedCredits += $additionalCredits;
        }

        // Verificar se já consumimos os créditos necessários
        if ($session->credits_consumed < $expectedCredits) {
            $creditsToConsume = $expectedCredits - $session->credits_consumed;

            Log::info('Consumindo créditos adicionais para sessão', [
                'session_id' => $session->id,
                'appointment_id' => $session->appointment_id,
                'total_minutes' => $totalMinutes,
                'current_credits' => $session->credits_consumed,
                'expected_credits' => $expectedCredits,
                'credits_to_consume' => $creditsToConsume,
            ]);

            // Consumir créditos da empresa
            $company = $session->appointment->user->company;

            if ($company && $company->telemedicine_credits >= $creditsToConsume) {
                // Atualizar créditos da empresa
                $company->decrement('telemedicine_credits', $creditsToConsume);

                // Atualizar sessão
                $session->update([
                    'credits_consumed' => $expectedCredits,
                    'last_credit_check_at' => now(),
                ]);

                Log::info('Créditos consumidos com sucesso', [
                    'session_id' => $session->id,
                    'credits_consumed' => $creditsToConsume,
                    'total_credits_session' => $expectedCredits,
                    'company_remaining_credits' => $company->telemedicine_credits,
                ]);
            } else {
                Log::warning('Empresa sem créditos suficientes - Finalizando sessão', [
                    'session_id' => $session->id,
                    'appointment_id' => $session->appointment_id,
                    'company_id' => $company?->id,
                    'available_credits' => $company?->telemedicine_credits ?? 0,
                    'required_credits' => $creditsToConsume,
                ]);

                // Finalizar sessão por falta de créditos
                $session->complete('Sessão finalizada automaticamente por falta de créditos');
            }
        } else {
            // Apenas atualizar timestamp da última verificação
            $session->update([
                'last_credit_check_at' => now(),
            ]);

            Log::debug('Sessão verificada - Créditos OK', [
                'session_id' => $session->id,
                'total_minutes' => $totalMinutes,
                'credits_consumed' => $session->credits_consumed,
            ]);
        }
    }
}
