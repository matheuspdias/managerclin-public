<?php

namespace App\Repositories\Room;

use App\Repositories\BaseRepositoryInterface;

interface RoomRepositoryInterface extends BaseRepositoryInterface
{
    public function createMany(int $idCompany, array $rooms): array;
}
