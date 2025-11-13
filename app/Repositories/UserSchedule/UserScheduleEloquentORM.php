<?php

namespace App\Repositories\UserSchedule;

use App\Models\UserSchedule;
use App\Repositories\BaseRepository;
use App\Repositories\UserSchedule\UserScheduleRepositoryInterface;
use Carbon\Carbon;
use \Illuminate\Database\Eloquent\Collection;

class UserScheduleEloquentORM extends BaseRepository implements UserScheduleRepositoryInterface
{
    protected array $searchable = [];

    protected array $sortable = [
        'created_at',
    ];

    public function __construct()
    {
        parent::__construct(new UserSchedule());
    }

    public function getByUser(int $userId): Collection
    {
        return $this->model->where('id_user', $userId)->get();
    }

    public function professionalWorksInThisDayAndTime(int $idUser, string $date, string $startTime, string $endTime): bool
    {
        $dayOfWeek = date('w', strtotime($date));

        $schedule = $this->model
            ->where('id_user', $idUser)
            ->where('day_of_week', $dayOfWeek)
            ->where('is_work', true)
            ->first();

        if (!$schedule) {
            return false;
        }

        // Normaliza horários
        $start = Carbon::createFromFormat('Y-m-d H:i', "$date $startTime");
        $end = Carbon::createFromFormat('Y-m-d H:i', "$date $endTime");
        $workStart = Carbon::createFromFormat('Y-m-d H:i:s', "$date {$schedule->start_time}");
        $workEnd = Carbon::createFromFormat('Y-m-d H:i:s', "$date {$schedule->end_time}");

        return $start->greaterThanOrEqualTo($workStart)
            && $end->lessThanOrEqualTo($workEnd);
    }

    public function getWorkingHoursForDate(int $userId, string $date): ?array
    {
        $dayOfWeek = date('w', strtotime($date)); // 0 (for Sunday) through 6 (for Saturday)

        $schedule = $this->model
            ->where('id_user', $userId)
            ->where('day_of_week', $dayOfWeek)
            ->where('is_work', true)
            ->first();

        if (!$schedule) {
            return null;
        }

        return [
            'start_time' => $schedule->start_time,
            'end_time' => $schedule->end_time,
            'day_of_week' => $schedule->day_of_week,
            'is_work' => $schedule->is_work,
        ];
    }
}
