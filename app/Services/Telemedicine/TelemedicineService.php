<?php

namespace App\Services\Telemedicine;

use App\DTO\Telemedicine\CreateTelemedicineSessionDTO;
use App\DTO\Telemedicine\UpdateTelemedicineSessionDTO;
use App\Jobs\SendWhatsappAppointmentNotification;
use App\Models\Appointment;
use App\Models\TelemedicineSession;
use App\Repositories\Telemedicine\TelemedicineRepositoryInterface;
use App\Traits\ThrowsExceptions;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TelemedicineService
{
    use ThrowsExceptions;

    public function __construct(
        protected TelemedicineRepositoryInterface $repository
    ) {}

    /**
     * Criar nova sessão de telemedicina
     */
    public function createSession(CreateTelemedicineSessionDTO $dto): TelemedicineSession
    {
        // Buscar agendamento
        $appointment = Appointment::with(['user', 'customer', 'company'])->find($dto->appointmentId);

        if (!$appointment) {
            $this->throwNotFound('Agendamento não encontrado.');
        }

        // Verificar se já existe sessão ativa para este agendamento
        $existingSession = $this->repository->findWaitingOrActiveByAppointment($dto->appointmentId);

        if ($existingSession) {
            return $existingSession;
        }

        // Obter empresa do agendamento
        $company = $appointment->company;

        // Verificar se a empresa tem créditos disponíveis
        if (!$company->hasTelemedicineCredits()) {
            $creditsRemaining = $company->telemedicine_credits ?? 0;

            $this->throwDomain(
                "Créditos de telemedicina insuficientes. Você possui {$creditsRemaining} crédito(s) disponível(is). " .
                "Para continuar usando a telemedicina, aguarde o próximo ciclo de faturamento, faça upgrade do seu plano ou compre créditos adicionais."
            );
        }

        // Gerar room_name único
        $roomName = $this->generateUniqueRoomName($dto->appointmentId);

        // Obter server_url da configuração ou do DTO
        $serverUrl = $dto->serverUrl ?? config('telemedicine.server_url', 'https://meet.jit.si');

        // Criar sessão em transação
        $session = DB::transaction(function () use ($dto, $roomName, $serverUrl, $company) {
            $sessionData = [
                'appointment_id' => $dto->appointmentId,
                'room_name' => $roomName,
                'server_url' => $serverUrl,
                'status' => TelemedicineSession::STATUS_ACTIVE,
                'started_at' => now(),
                'last_credit_check_at' => now(),
            ];

            $session = $this->repository->store((object) $sessionData);

            // Consumir 1 crédito de telemedicina
            $consumed = $company->consumeTelemedicineCredit();

            if (!$consumed) {
                $this->throwDomain('Não foi possível consumir o crédito de telemedicina. Tente novamente.');
            }

            Log::info('Sessão de telemedicina criada', [
                'session_id' => $session->id,
                'appointment_id' => $dto->appointmentId,
                'room_name' => $roomName,
                'company_id' => $company->id,
                'credits_remaining' => $company->telemedicine_credits,
                'user_id' => auth()->id(),
            ]);

            return $session;
        });

        // Recarregar com relacionamentos
        return $this->repository->findById($session->id);
    }

    /**
     * Buscar sessão por appointment_id
     */
    public function getSessionByAppointment(int $appointmentId): TelemedicineSession
    {
        $session = $this->repository->findLatestByAppointment($appointmentId);

        if (!$session) {
            $this->throwNotFound('Sessão de telemedicina não encontrada para este agendamento.');
        }

        return $session;
    }

    /**
     * Listar sessões ativas
     */
    public function getActiveSessions(?int $doctorId = null, int $page = 1, int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->getActiveSessions($doctorId, $page, $perPage);
    }

    /**
     * Atualizar status da sessão
     */
    public function updateSessionStatus(int $sessionId, UpdateTelemedicineSessionDTO $dto): TelemedicineSession
    {
        $session = $this->repository->findById($sessionId);

        if (!$session) {
            $this->throwNotFound('Sessão de telemedicina não encontrada.');
        }

        DB::transaction(function () use ($session, $dto) {
            $newStatus = $dto->status;

            // Se status = ACTIVE e started_at é null → seta started_at = now()
            if ($newStatus === TelemedicineSession::STATUS_ACTIVE && !$session->started_at) {
                $session->started_at = now();
                $session->last_credit_check_at = now();

                Log::info('Sessão de telemedicina iniciada', [
                    'session_id' => $session->id,
                    'started_at' => $session->started_at,
                ]);
            }

            // Se status = COMPLETED ou CANCELLED → seta ended_at e calcula duração
            if (in_array($newStatus, [TelemedicineSession::STATUS_COMPLETED, TelemedicineSession::STATUS_CANCELLED])) {
                $session->ended_at = now();

                // Calcular duração se a sessão foi iniciada
                if ($session->started_at) {
                    $session->duration_minutes = $session->started_at->diffInMinutes($session->ended_at);
                }

                Log::info('Sessão de telemedicina finalizada', [
                    'session_id' => $session->id,
                    'status' => $newStatus,
                    'duration_minutes' => $session->duration_minutes,
                ]);
            }

            $session->status = $newStatus;

            // Atualizar notes se fornecido
            if ($dto->notes) {
                $session->notes = $dto->notes;
            }

            $session->save();
        });

        return $session->fresh();
    }

    /**
     * Finalizar sessão
     */
    public function endSession(int $sessionId, ?string $endReason = null, ?string $notes = null): TelemedicineSession
    {
        $session = $this->repository->findById($sessionId);

        if (!$session) {
            $this->throwNotFound('Sessão de telemedicina não encontrada.');
        }

        DB::transaction(function () use ($session, $endReason, $notes) {
            $endedAt = now();

            // Calcular duração se a sessão foi iniciada
            $durationMinutes = 0;
            if ($session->started_at) {
                $durationMinutes = $session->started_at->diffInMinutes($endedAt);
            }

            // Montar notes com end_reason se fornecido
            $finalNotes = $notes ?? $session->notes;
            if ($endReason) {
                $finalNotes = $finalNotes
                    ? $finalNotes . "\n\nMotivo do encerramento: " . $endReason
                    : "Motivo do encerramento: " . $endReason;
            }

            $session->update([
                'status' => TelemedicineSession::STATUS_COMPLETED,
                'ended_at' => $endedAt,
                'duration_minutes' => $durationMinutes,
                'notes' => $finalNotes,
            ]);

            Log::info('Sessão de telemedicina finalizada manualmente', [
                'session_id' => $session->id,
                'duration_minutes' => $durationMinutes,
                'end_reason' => $endReason,
            ]);
        });

        return $session->fresh();
    }

    /**
     * Notificar paciente via WhatsApp
     */
    public function notifyPatient(int $sessionId): array
    {
        // Buscar sessão com relacionamentos
        $session = $this->repository->findById($sessionId);

        if (!$session) {
            $this->throwNotFound('Sessão de telemedicina não encontrada.');
        }

        $appointment = $session->appointment;
        $patient = $appointment->customer;
        $doctor = $appointment->user;
        $company = $appointment->company;

        // Validar se o paciente tem telefone
        if (!$patient || !$patient->phone) {
            $this->throwDomain('Paciente não possui telefone cadastrado.');
        }

        // Obter URL de entrada
        $joinUrl = $session->join_url;

        // Montar mensagem personalizada
        $doctorName = $doctor ? $doctor->name : 'Profissional';
        $message =
            "Olá {$patient->name}! 👋\n\n" .
            "Sua teleconsulta com {$doctorName} está pronta!\n\n" .
            "🔗 Clique no link para entrar:\n{$joinUrl}\n\n" .
            "⏰ A consulta já começou, entre agora!\n\n" .
            "📱 Certifique-se de permitir acesso à câmera e microfone.\n\n" .
            "Qualquer dúvida, entre em contato com a clínica.";

        // Determinar configuração de WhatsApp a usar
        $whatsappConfigArray = $this->getWhatsAppConfig($company);

        // Disparar job para enviar mensagem
        SendWhatsappAppointmentNotification::dispatch(
            $appointment->id,
            $message,
            'telemedicine',
            $whatsappConfigArray
        );

        Log::info('Notificação de teleconsulta enviada', [
            'session_id' => $session->id,
            'appointment_id' => $appointment->id,
            'patient_id' => $patient->id,
            'patient_name' => $patient->name,
            'patient_phone' => $patient->phone,
        ]);

        return [
            'session_id' => $session->id,
            'patient_name' => $patient->name,
            'patient_phone' => $patient->phone,
            'join_url' => $joinUrl,
            'message_sent' => true,
        ];
    }

    /**
     * Obter configurações do Jitsi/JaaS
     */
    public function getConfig(): array
    {
        $provider = config('telemedicine.provider', 'jitsi');
        $serverUrl = config('telemedicine.server_url', 'https://meet.jit.si');
        $appId = config('telemedicine.jaas_app_id');

        $config = [
            'provider' => $provider,
            'server_url' => $serverUrl,
            'jitsi_config' => config('telemedicine.jitsi_config', []),
            'interface_config' => config('telemedicine.interface_config', []),
        ];

        // Adicionar App ID apenas se for JaaS
        if ($provider === 'jaas' && $appId) {
            $config['app_id'] = $appId;
        }

        return $config;
    }

    /**
     * Obter configuração de WhatsApp (empresa própria ou global para trial)
     */
    protected function getWhatsAppConfig($company): array
    {
        // Verificar se empresa tem WhatsApp próprio configurado
        $whatsappConfig = $company->whatsappConfig;

        if ($whatsappConfig && $whatsappConfig->is_active) {
            // Empresa tem WhatsApp próprio ativo
            return [
                'instance_name' => $whatsappConfig->instance_name,
                'token' => $whatsappConfig->token,
                'is_active' => $whatsappConfig->is_active,
            ];
        }

        // Se não tem config ou está em trial, usar configuração global
        if (!$whatsappConfig || $company->isOnTrial()) {
            return [
                'instance_name' => 'managerclin-trial', // Nome da instância global
                'token' => env('WHATSAPP_API_TOKEN'),
                'is_active' => true,
            ];
        }

        // Se não tem config e não está em trial, lançar exceção
        $this->throwDomain('WhatsApp não está configurado ou habilitado para esta empresa.');
    }

    /**
     * Gerar nome único para a sala
     */
    protected function generateUniqueRoomName(int $appointmentId): string
    {
        do {
            $roomName = 'consultation-' . $appointmentId . '-' . uniqid();
        } while ($this->repository->roomNameExists($roomName));

        return $roomName;
    }
}
