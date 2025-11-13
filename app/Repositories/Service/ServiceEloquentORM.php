<?php

namespace App\Repositories\Service;

use App\Models\Service;
use App\Repositories\BaseRepository;
use App\Repositories\Service\ServiceRepositoryInterface;

class ServiceEloquentORM extends BaseRepository implements ServiceRepositoryInterface
{
    protected array $searchable = [
        'name',
    ];

    protected array $sortable = [
        'id',
        'name',
        'created_at',
        'updated_at',
    ];

    public function __construct()
    {
        parent::__construct(new Service());
    }

    public function createMany(int $idCompany, array $services): array
    {
        $createdServices = [];
        foreach ($services as $serviceName) {
            $createdServices[] = $this->model->withoutGlobalScopes()->create([
                'name' => $serviceName,
                'price' => 100.00,
                'id_company' => $idCompany,
            ]);
        }
        return $createdServices;
    }
}
