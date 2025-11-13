<?php

namespace App\Services\Schedule;

use App\DTO\Schedule\UpdateScheduleCollectionDTO;
use App\DTO\Schedule\UpdateScheduleDTO;
use App\Models\User;
use App\Repositories\UserSchedule\UserScheduleRepositoryInterface;
use App\Traits\ThrowsExceptions;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UserScheduleService
{
    use ThrowsExceptions;
    public function __construct(
        protected UserScheduleRepositoryInterface $repository
    ) {}

    public function getAllPaginate(string|null $search = null, int $page, int $perPage, string $order): LengthAwarePaginator
    {
        return $this->repository->paginate($search, $page, $perPage, $order);
    }

    public function getAll(): Collection
    {
        return $this->repository->getAll();
    }

    public function getByUser(): Collection
    {
        return $this->repository->getByUser(Auth::id());
    }

    public function update(int $id, UpdateScheduleDTO $dto)
    {
        return $this->repository->update($id, $dto);
    }

    public function destroy(int $id): void
    {
        $this->repository->deleteById($id);
    }

    public function createDefaultUserSchedule(User $user): void
    {
        $defaultSchedules = [
            ['day_of_week' => 1, 'start_time' => '09:00:00', 'end_time' => '17:00:00', 'is_work' => true], // Segunda-feira
            ['day_of_week' => 2, 'start_time' => '09:00:00', 'end_time' => '17:00:00', 'is_work' => true], // Terça-feira
            ['day_of_week' => 3, 'start_time' => '09:00:00', 'end_time' => '17:00:00', 'is_work' => true], // Quarta-feira
            ['day_of_week' => 4, 'start_time' => '09:00:00', 'end_time' => '17:00:00', 'is_work' => true], // Quinta-feira
            ['day_of_week' => 5, 'start_time' => '09:00:00', 'end_time' => '17:00:00', 'is_work' => true], // Sexta-feira
            ['day_of_week' => 6, 'start_time' => '10:00:00', 'end_time' => '14:00:00', 'is_work' => true], // Sábado
            ['day_of_week' => 0, 'start_time' => '10:00:00', 'end_time' => '14:00:00', 'is_work' => false], // Domingo

        ];

        foreach ($defaultSchedules as $schedule) {
            $dto = new UpdateScheduleDTO(
                id: null,
                id_user: $user->id,
                day_of_week: $schedule['day_of_week'],
                start_time: $schedule['start_time'],
                end_time: $schedule['end_time'],
                is_work: $schedule['is_work'],
                id_company: $user->id_company,
            );

            $this->repository->store($dto);
        }
    }

    public function updateMySchedules(UpdateScheduleCollectionDTO $dto): array
    {
        $userId = Auth::id();
        $updates = [];

        DB::transaction(function () use ($dto, $userId, &$updates) {
            // 1. Buscar IDs atuais do usuário no banco
            $existingIds = $this->repository->getByUser($userId)->pluck('id')->toArray();

            // 2. IDs enviados no DTO
            $sentIds = array_filter(array_map(fn($s) => $s->id, $dto->schedules));

            // 3. IDs para deletar (existem no banco, mas não foram enviados)
            $idsToDelete = array_diff($existingIds, $sentIds);

            // 4. Deletar horários removidos na UI
            foreach ($idsToDelete as $idToDelete) {
                $schedule = $this->repository->findById($idToDelete);

                if ($schedule && $schedule->id_user === $userId) {
                    $this->repository->deleteById($idToDelete);
                }
            }

            // 5. Atualizar ou criar novos horários enviados
            foreach ($dto->schedules as $scheduleDto) {
                $scheduleDto->id_user = $userId;

                if ($scheduleDto->id) {
                    $schedule = $this->repository->findById($scheduleDto->id);

                    if (!$schedule || $schedule->id_user !== $userId) {
                        $this->throwNotFound('Horário não encontrado ou não pertence ao usuário.');
                    }

                    $updates[] = $this->repository->update($scheduleDto->id, $scheduleDto);
                } else {
                    $updates[] = $this->repository->store($scheduleDto);
                }
            }
        });

        return $updates;
    }

    public function professionalWorksInThisDayAndTime(int $idUser, string $date, string $startTime, string $endTime): bool
    {
        return $this->repository->professionalWorksInThisDayAndTime(
            $idUser,
            $date,
            $startTime,
            $endTime
        );
    }

    public function getWorkingHoursForDate(int $userId, string $date): ?array
    {
        return $this->repository->getWorkingHoursForDate($userId, $date);
    }
}
