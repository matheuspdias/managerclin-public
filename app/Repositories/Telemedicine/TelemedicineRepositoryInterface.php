<?php

namespace App\Repositories\Telemedicine;

use App\Models\TelemedicineSession;
use App\Repositories\BaseRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface TelemedicineRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Buscar última sessão por appointment_id
     */
    public function findLatestByAppointment(int $appointmentId): ?TelemedicineSession;

    /**
     * Buscar sessão ativa por appointment_id
     */
    public function findActiveByAppointment(int $appointmentId): ?TelemedicineSession;

    /**
     * Listar sessões ativas paginadas
     */
    public function getActiveSessions(?int $doctorId = null, int $page = 1, int $perPage = 15): LengthAwarePaginator;

    /**
     * Buscar sessões em espera ou ativas por appointment_id
     */
    public function findWaitingOrActiveByAppointment(int $appointmentId): ?TelemedicineSession;

    /**
     * Verificar se existe room_name duplicado
     */
    public function roomNameExists(string $roomName): bool;
}
