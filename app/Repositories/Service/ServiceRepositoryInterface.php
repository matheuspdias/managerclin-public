<?php

namespace App\Repositories\Service;

use App\Repositories\BaseRepositoryInterface;

interface ServiceRepositoryInterface extends BaseRepositoryInterface
{
    public function createMany(int $idCompany, array $services): array;
}
