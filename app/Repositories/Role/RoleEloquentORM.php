<?php

namespace App\Repositories\Role;

use App\Models\Role;
use App\Repositories\BaseRepository;
use App\Repositories\Role\RoleRepositoryInterface;

class RoleEloquentORM extends BaseRepository implements RoleRepositoryInterface
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
        parent::__construct(new Role());
    }
}
