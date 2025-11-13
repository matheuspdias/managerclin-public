<?php

namespace App\Repositories\Room;

use App\Models\Room;
use App\Repositories\BaseRepository;
use App\Repositories\Room\RoomRepositoryInterface;

class RoomEloquentORM extends BaseRepository implements RoomRepositoryInterface
{
    protected array $searchable = [
        'name',
        'location',
    ];

    protected array $sortable = [
        'id',
        'name',
        'created_at',
    ];

    public function __construct()
    {
        parent::__construct(new Room());
    }

    public function createMany(int $idCompany, array $rooms): array
    {
        $createdRooms = [];
        foreach ($rooms as $room) {
            $createdRooms[] = $this->model->withoutGlobalScopes()->create([
                'name' => $room,
                'location' => '1 Andar',
                'id_company' => $idCompany,
            ]);
        }
        return $createdRooms;
    }
}
