<?php

namespace App\Repositories\UserSchedule;

use App\Repositories\BaseRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

interface UserScheduleRepositoryInterface extends BaseRepositoryInterface
{
    public function getByUser(int $userId): Collection;
    public function professionalWorksInThisDayAndTime(int $idUser, string $date, string $startTime, string $endTime): bool;
    public function getWorkingHoursForDate(int $userId, string $date): ?array;
}
