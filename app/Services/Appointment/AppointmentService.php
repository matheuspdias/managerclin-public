<?php

namespace App\Services\Appointment;

use App\DTO\Appointment\CreateAppointmentDTO;
use App\DTO\Appointment\UpdateAppointmentDTO;
use App\Enums\AppointmentStatusEnum;
use App\Models\Appointment;
use App\Repositories\Appointment\AppointmentRepositoryInterface;
use App\Services\ClinicService\ClinicService;
use App\Services\Schedule\UserScheduleService;
use App\Traits\ThrowsExceptions;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;

class AppointmentService
{
    use ThrowsExceptions;
    public function __construct(
        protected AppointmentRepositoryInterface $repository,
        protected ClinicService $clinicService,
        protected UserScheduleService $userScheduleService,
    ) {}

    public function getTotalSales(array $period = []): float
    {
        if (empty($period)) {
            $period = [
                'start_date' => now()->startOfMonth()->format('Y-m-d'),
                'end_date' => now()->endOfMonth()->format('Y-m-d'),
            ];
        }

        return $this->repository->getTotalSales($period);
    }

    public function getAll(): Collection
    {
        if (Auth::user()->isAdmin()) {
            return $this->repository->getAll();
        } else {
            return $this->repository->getByUser(Auth::id());
        }
    }

    public function findById(int $id): ?Appointment
    {
        $appointment = $this->repository->findById($id);

        if (!$appointment) {
            $this->throwNotFound('Agendamento não encontrado');
        }

        // Verifica se o usuário tem permissão para ver este agendamento
        $user = Auth::user();
        if (!$user->isAdmin() && $appointment->id_user !== Auth::id()) {
            $this->throwForbidden('Você não tem permissão para acessar este agendamento');
        }

        return $appointment;
    }

    public function getAllPaginated(array $filters = [], int $page = 1, int $perPage = 15): LengthAwarePaginator
    {
        $user = Auth::user();
        // Admin pode ver todos os agendamentos, médico só vê os próprios
        $userId = ($user && $user->isAdmin()) ? null : Auth::id();
        return $this->repository->getAllPaginated($filters, $page, $perPage, $userId);
    }

    public function getForPeriod(string $startDate, string $endDate): Collection
    {
        $user = Auth::user();
        $userId = ($user && $user->isAdmin()) ? null : Auth::id();
        return $this->repository->getForPeriod($startDate, $endDate, $userId);
    }

    public function getStatsForPeriod(string $startDate, string $endDate): array
    {
        $user = Auth::user();
        $userId = ($user && $user->isAdmin()) ? null : Auth::id();

        $appointments = $this->repository->getForPeriod($startDate, $endDate, $userId);

        $total = $appointments->count();
        $scheduled = $appointments->where('status', AppointmentStatusEnum::SCHEDULED)->count();
        $inProgress = $appointments->where('status', AppointmentStatusEnum::IN_PROGRESS)->count();
        $completed = $appointments->where('status', AppointmentStatusEnum::COMPLETED)->count();
        $cancelled = $appointments->where('status', AppointmentStatusEnum::CANCELLED)->count();

        $today = $appointments->filter(function ($appointment) {
            return $appointment->date === now()->format('Y-m-d');
        })->count();

        $weekStart = now()->startOfWeek()->format('Y-m-d');
        $weekEnd = now()->endOfWeek()->format('Y-m-d');
        $week = $appointments->filter(function ($appointment) use ($weekStart, $weekEnd) {
            return $appointment->date >= $weekStart && $appointment->date <= $weekEnd;
        })->count();

        $revenue = $appointments->where('status', AppointmentStatusEnum::COMPLETED)
            ->sum('price') ?? 0;

        return [
            'total' => $total,
            'scheduled' => $scheduled,
            'in_progress' => $inProgress,
            'completed' => $completed,
            'cancelled' => $cancelled,
            'today' => $today,
            'week' => $week,
            'revenue' => $revenue,
        ];
    }

    public function updateStatus(int $id, string $status): Appointment
    {
        $appointment = $this->repository->findById($id);
        if (!$appointment) {
            $this->throwNotFound('Agendamento não encontrado');
        }

        $validStatuses = [
            AppointmentStatusEnum::SCHEDULED,
            AppointmentStatusEnum::IN_PROGRESS,
            AppointmentStatusEnum::COMPLETED,
            AppointmentStatusEnum::CANCELLED,
        ];

        if (!in_array($status, $validStatuses)) {
            $this->throwDomain('Status inválido');
        }

        $updatedAppointment = $this->repository->updateStatus($id, $status);

        // Se o status mudou para COMPLETED ou CANCELLED, finalizar sessão de telemedicina ativa
        if (in_array($status, [AppointmentStatusEnum::COMPLETED, AppointmentStatusEnum::CANCELLED])) {
            $this->finalizeTelemedicineSessionIfExists($appointment);
        }

        return $updatedAppointment;
    }

    /**
     * Finaliza sessão de telemedicina ativa se existir
     */
    protected function finalizeTelemedicineSessionIfExists(\App\Models\Appointment $appointment): void
    {
        // Buscar sessão ativa para este agendamento
        $activeSession = \App\Models\TelemedicineSession::where('appointment_id', $appointment->id)
            ->whereIn('status', ['WAITING', 'ACTIVE'])
            ->first();

        if ($activeSession) {
            $notes = $appointment->status === AppointmentStatusEnum::CANCELLED
                ? 'Sessão finalizada automaticamente - Agendamento cancelado'
                : 'Sessão finalizada automaticamente - Atendimento concluído';

            $activeSession->complete($notes);

            \Illuminate\Support\Facades\Log::info('Sessão de telemedicina finalizada automaticamente', [
                'session_id' => $activeSession->id,
                'appointment_id' => $appointment->id,
                'appointment_status' => $appointment->status,
                'reason' => $notes,
            ]);
        }
    }

    public function checkTimeConflicts(string $date, string $startTime, string $endTime, int $userId, int $roomId, ?int $appointmentId = null): array
    {
        return $this->repository->getTimeConflicts($date, $startTime, $endTime, $userId, $roomId, $appointmentId);
    }

    public function getAvailableTimeSlots(string $date, int $userId, int $duration = 60): array
    {
        // Buscar horários de trabalho reais do profissional
        $workingHours = $this->userScheduleService->getWorkingHoursForDate($userId, $date);

        if (!$workingHours) {
            return []; // Profissional não trabalha neste dia
        }

        // Buscar agendamentos existentes para o dia
        $existingAppointments = $this->repository->getAppointmentsForDate($date, $userId);

        $availableSlots = [];
        $currentTime = substr($workingHours['start_time'], 0, 5); // Formato HH:MM
        $endTime = substr($workingHours['end_time'], 0, 5); // Formato HH:MM

        while ($currentTime < $endTime) {
            $slotEnd = date('H:i', strtotime($currentTime . ' +' . $duration . ' minutes'));

            // Verificar se o slot não excede o horário de trabalho
            if ($slotEnd > $endTime) {
                break;
            }

            // Verificar se o slot não conflita com agendamentos existentes
            $hasConflict = $existingAppointments->filter(function ($appointment) use ($currentTime, $slotEnd) {
                $appointmentStart = substr($appointment->start_time, 0, 5);
                $appointmentEnd = substr($appointment->end_time, 0, 5);

                return !(
                    $slotEnd <= $appointmentStart ||
                    $currentTime >= $appointmentEnd
                );
            })->count() > 0;

            if (!$hasConflict) {
                $availableSlots[] = [
                    'start_time' => $currentTime,
                    'end_time' => $slotEnd,
                    'duration_minutes' => $duration,
                ];
            }

            // Próximo slot de 30 minutos
            $currentTime = date('H:i', strtotime($currentTime . ' +30 minutes'));
        }

        return $availableSlots;
    }

    public function getAppointmentsInPeriodCount(array $period): array
    {
        $total =  $this->repository->getAppointmentsInPeriodCount($period);
        $completed = $this->repository->getAppointmentsInPeriodCompletedCount($period);
        $cancelled = $this->repository->getAppointmentsInPeriodCount($period, AppointmentStatusEnum::CANCELLED);
        $pending = $total - $completed - $cancelled;
        $percent = $total > 0 ? round(($completed / $total) * 100, 2) : 0;

        return [
            'count' => $total,
            'completedCount' => $completed,
            'cancelledCount' => $cancelled,
            'pendingCount' => $pending,
            'completedPercent' => $percent,
        ];
    }

    public function getAppointmentsInPeriod(array $period, string|null $search = null, int $page, int $perPage, string $order): LengthAwarePaginator
    {
        return $this->repository->getAppointmentsInPeriod($period, $search, $page, $perPage, $order);
    }

    public function getAppointmentsInPeriodFromDash(array $period): Collection
    {
        return $this->repository->getAppointmentsInPeriodFromDash($period);
    }


    public function getAppointmentsToNotify(string $date, int $idCompany, ?string $notificationType = null): Collection
    {
        return $this->repository->getAppointmentsToNotify($date, $idCompany, $notificationType);
    }

    public function getMostPopularServices(array $period): array
    {
        $data = $this->repository->getMostPopularServices($period);

        $formattedData = [];
        foreach ($data as $service) {
            $formattedData[] = [
                'name' => $service->service->name,
                'totalAppointments' => $service->total_appointments,
            ];
        }

        return $formattedData;
    }

    public function store(CreateAppointmentDTO $dto): Appointment
    {
        $this->verifyProfessionalWorksInThisDayAndTime(
            $dto->id_user,
            $dto->date,
            $dto->start_time,
            $dto->end_time
        );

        $this->hasAppointmentConflict(
            $dto->id_user,
            $dto->date,
            $dto->start_time,
            $dto->end_time
        );

        $service = $this->clinicService->getById($dto->id_service);

        $dto->price = $service->price ?? $dto->price;

        return $this->repository->store($dto);
    }

    public function update(int $id, UpdateAppointmentDTO $dto): Appointment
    {
        // Busca o agendamento existente
        $appointment = $this->repository->findById($id);
        if (!$appointment) {
            $this->throwNotFound('Agendamento não encontrado');
        }

        // Usa valores existentes se os novos não foram fornecidos (update parcial)
        $idUser = $dto->id_user ?? $appointment->id_user;
        $date = $dto->date ?? $appointment->date->format('Y-m-d');
        $startTime = $dto->start_time ?? $appointment->start_time;
        $endTime = $dto->end_time ?? $appointment->end_time;
        $idService = $dto->id_service ?? $appointment->id_service;

        // Valida horário de trabalho do profissional
        $this->verifyProfessionalWorksInThisDayAndTime(
            $idUser,
            $date,
            $startTime,
            $endTime
        );

        // Verifica conflitos de agendamento
        $this->hasAppointmentConflict(
            $idUser,
            $date,
            $startTime,
            $endTime,
            $id
        );

        // Atualiza o preço baseado no serviço se foi alterado
        if ($dto->id_service !== null) {
            $service = $this->clinicService->getById($idService);
            $dto->price = $service->price ?? $dto->price;
        }

        return $this->repository->update($id, $dto);
    }

    public function destroy(int $id): void
    {
        $appointment = $this->repository->findById($id);
        if (!$appointment) {
            $this->throwNotFound('Agendamento não encontrado');
        }

        // Verifica se o usuário tem permissão para deletar este agendamento
        $user = Auth::user();
        if (!$user->isAdmin() && $appointment->id_user !== Auth::id()) {
            $this->throwForbidden('Você não tem permissão para excluir este agendamento');
        }

        // Não permite deletar agendamentos em progresso, completos ou cancelados
        if (!in_array($appointment->status, [AppointmentStatusEnum::SCHEDULED])) {
            $this->throwDomain('Apenas agendamentos com status SCHEDULED (agendado) podem ser excluídos. Agendamentos em andamento, concluídos ou cancelados não podem ser removidos.');
        }

        $this->repository->deleteById($id);
    }

    public function createDefaultAppointments(int $idCompany, int $idUser, int $idCustomer, int $idRoom, int $idService): array
    {
        $defaultAppointments = [
            [
                'date' => now()->format('Y-m-d'),
                'id_user' => $idUser,
                'id_customer' => $idCustomer,
                'id_room' => $idRoom,
                'id_service' => $idService,
                'start_time' => '17:00:00',
                'end_time' => '18:00:00',
                'status' => AppointmentStatusEnum::SCHEDULED,
                'notes' => 'Consulta de rotina',
                'id_company' => $idCompany,
            ],
            [
                'date' => now()->addDays(1)->format('Y-m-d'),
                'id_user' => $idUser,
                'id_customer' => $idCustomer,
                'id_room' => $idRoom,
                'id_service' => $idService,
                'start_time' => '10:00:00',
                'end_time' => '11:00:00',
                'status' => AppointmentStatusEnum::SCHEDULED,
                'notes' => 'Exame de sangue',
                'id_company' => $idCompany,
            ],
        ];

        return $this->repository->createMany(
            $defaultAppointments
        );
    }

    /**
     * verifica se o profissional trabalha nesse dia e horario
     */
    private function verifyProfessionalWorksInThisDayAndTime(int $idUser, string $date, string $startTime, string $endTime): void
    {
        $verify = $this->userScheduleService->professionalWorksInThisDayAndTime(
            $idUser,
            $date,
            $startTime,
            $endTime
        );

        if (!$verify) {
            $this->throwDomain('O profissional não trabalha neste dia e horário');
        }
    }

    /**
     * verifica se ja existe algum agendamento no mesmo horario para o mesmo profissional
     */
    private function hasAppointmentConflict(int $idUser, string $date, string $startTime, string $endTime, ?int $excludeAppointmentId = null): void
    {
        $hasConflict = $this->repository->hasAppointmentConflict(
            $idUser,
            $date,
            $startTime,
            $endTime,
            $excludeAppointmentId
        );

        if ($hasConflict) {
            $this->throwDomain('Já existe um agendamento para este profissional neste horário');
        }
    }
}
