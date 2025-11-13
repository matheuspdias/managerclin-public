<?php

namespace App\Repositories\Appointment;

use App\Repositories\BaseRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface AppointmentRepositoryInterface extends BaseRepositoryInterface
{
    public function getTotalSales(array $period): float;
    public function getAppointmentsInPeriodCount(array $period, ?string $status = null): int;
    public function getAppointmentsInPeriodCompletedCount(array $period): int;
    public function getAppointmentsInPeriod(array $period, string|null $search = null, int $page, int $perPage, string $order): LengthAwarePaginator;
    public function getAppointmentsInPeriodFromDash(array $period): Collection;
    public function getAppointmentsToNotify(string $date, int $idCompany, ?string $notificationType = null): Collection;
    public function getMostPopularServices(array $period): Collection;
    public function hasAppointmentConflict(int $idUser, string $date, string $startTime, string $endTime, ?int $excludeId = null): bool;
    public function createMany(array $appointments): array;
    public function getAllPaginated(array $filters = [], int $page = 1, int $perPage = 15, ?int $userId = null): LengthAwarePaginator;
    public function getForPeriod(string $startDate, string $endDate, ?int $userId = null): Collection;
    public function updateStatus(int $id, string $status): \App\Models\Appointment;
    public function getTimeConflicts(string $date, string $startTime, string $endTime, int $userId, int $roomId, ?int $appointmentId = null): array;
    public function getAppointmentsForDate(string $date, int $userId): Collection;
    public function getByUser(int $userId): Collection;
}
