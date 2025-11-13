<?php

namespace App\Repositories\Company;

use App\Models\Company;
use App\Repositories\BaseRepository;
use App\Repositories\Company\CompanyRepositoryInterface;

class CompanyEloquentORM extends BaseRepository implements CompanyRepositoryInterface
{
    protected array $searchable = [
        'name',
    ];

    protected array $sortable = [
        'id',
        'name',
    ];

    protected array $relations = [];

    public function __construct()
    {
        parent::__construct(new Company());
    }
}
