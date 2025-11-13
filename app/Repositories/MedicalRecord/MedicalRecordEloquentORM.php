<?php

namespace App\Repositories\MedicalRecord;

use App\Models\MedicalRecord;
use App\Repositories\BaseRepository;
use App\Repositories\MedicalRecord\MedicalRecordRepositoryInterface;

class MedicalRecordEloquentORM extends BaseRepository implements MedicalRecordRepositoryInterface
{
    protected array $searchable = [
        'medical_history',
        'allergies',
        'medications',
    ];

    protected array $sortable = [
        'created_at',
        'updated_at',
    ];

    protected array $relations = [
        'customer',
        'user',
    ];

    public function __construct()
    {
        parent::__construct(new MedicalRecord());
    }
}
