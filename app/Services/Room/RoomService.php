<?php

namespace App\Services\Room;

use App\DTO\Room\CreateRoomDTO;
use App\DTO\Room\UpdateRoomDTO;
use App\Models\Room;
use App\Repositories\Room\RoomRepositoryInterface;
use App\Traits\ThrowsExceptions;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class RoomService
{
    use ThrowsExceptions;
    public function __construct(
        protected RoomRepositoryInterface $repository
    ) {}

    public function getAllPaginate(string|null $search = null, int $page, int $perPage, string $order): LengthAwarePaginator
    {
        return $this->repository->paginate($search, $page, $perPage, $order);
    }

    public function getAll(): Collection
    {
        return $this->repository->getAll();
    }

    public function getById(int $id): Room
    {
        $room = $this->repository->findById($id);
        if (!$room) {
            $this->throwNotFound('Sala/Consultório não encontrado');
        }
        return $room;
    }

    public function store(CreateRoomDTO $dto): Model
    {
        return $this->repository->store($dto);
    }

    public function update(int $id, UpdateRoomDTO $dto): Room
    {
        $room = $this->repository->findById($id);
        if (!$room) {
            $this->throwNotFound('Sala/Consultório não encontrado');
        }

        return $this->repository->update($id, $dto);
    }

    public function destroy(int $id): void
    {
        $this->repository->deleteById($id);
    }

    public function createDefaultRooms(int $idCompany): array
    {
        $defaultRooms = [
            'Sala de Espera',
            'Consultório 1',
            'Consultório 2',
        ];

        $createdRooms = $this->repository->createMany(
            $idCompany,
            $defaultRooms
        );

        return $createdRooms;
    }
}
