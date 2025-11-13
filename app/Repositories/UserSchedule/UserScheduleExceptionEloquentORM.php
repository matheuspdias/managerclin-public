<?php

namespace App\Repositories\UserSchedule;

use App\Models\UserScheduleException;
use App\Repositories\BaseRepository;
use App\Repositories\UserSchedule\UserScheduleExceptionRepositoryInterface;

class UserScheduleExceptionEloquentORM extends BaseRepository implements UserScheduleExceptionRepositoryInterface
{
    protected array $searchable = [];

    protected array $sortable = [
        'created_at',
    ];

    public function __construct()
    {
        parent::__construct(new UserScheduleException());
    }
}
