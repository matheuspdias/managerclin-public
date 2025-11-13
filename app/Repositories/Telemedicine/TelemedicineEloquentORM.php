<?php

namespace App\Repositories\Telemedicine;

use App\Models\TelemedicineSession;
use App\Repositories\BaseRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class TelemedicineEloquentORM extends BaseRepository implements TelemedicineRepositoryInterface
{
    protected array $searchable = [
        'room_name',
        'notes',
    ];

    protected array $sortable = [
        'id',
        'status',
        'started_at',
        'ended_at',
        'duration_minutes',
        'created_at',
    ];

    protected array $relations = [
        'appointment',
        'appointment.user',
        'appointment.customer',
        'appointment.service',
        'appointment.room',
        'appointment.company',
    ];

    public function __construct()
    {
        parent::__construct(new TelemedicineSession());
    }

    /**
     * Buscar última sessão por appointment_id
     */
    public function findLatestByAppointment(int $appointmentId): ?TelemedicineSession
    {
        return $this->getQuery()
            ->with($this->relations)
            ->where('appointment_id', $appointmentId)
            ->orderBy('created_at', 'desc')
            ->first();
    }

    /**
     * Buscar sessão ativa por appointment_id
     */
    public function findActiveByAppointment(int $appointmentId): ?TelemedicineSession
    {
        return $this->getQuery()
            ->where('appointment_id', $appointmentId)
            ->where('status', TelemedicineSession::STATUS_ACTIVE)
            ->first();
    }

    /**
     * Listar sessões ativas paginadas
     */
    public function getActiveSessions(?int $doctorId = null, int $page = 1, int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->getQuery()
            ->with($this->relations)
            ->where('status', TelemedicineSession::STATUS_ACTIVE)
            ->orderBy('started_at', 'desc');

        // Filtrar por médico se fornecido
        if ($doctorId) {
            $query->whereHas('appointment', function ($q) use ($doctorId) {
                $q->where('id_user', $doctorId);
            });
        }

        return $query->paginate($perPage, ['*'], 'page', $page);
    }

    /**
     * Buscar sessões em espera ou ativas por appointment_id
     */
    public function findWaitingOrActiveByAppointment(int $appointmentId): ?TelemedicineSession
    {
        return $this->getQuery()
            ->where('appointment_id', $appointmentId)
            ->whereIn('status', [
                TelemedicineSession::STATUS_WAITING,
                TelemedicineSession::STATUS_ACTIVE
            ])
            ->first();
    }

    /**
     * Verificar se existe room_name duplicado
     */
    public function roomNameExists(string $roomName): bool
    {
        return $this->getQuery()
            ->where('room_name', $roomName)
            ->exists();
    }
}
