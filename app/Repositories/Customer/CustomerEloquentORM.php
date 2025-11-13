<?php

namespace App\Repositories\Customer;

use App\Models\Customer;
use App\Repositories\BaseRepository;
use App\Repositories\Customer\CustomerRepositoryInterface;

class CustomerEloquentORM extends BaseRepository implements CustomerRepositoryInterface
{
    protected array $searchable = [
        'name',
        'email',
        'phone',
        'cpf',
        'notes',
    ];

    protected array $sortable = [
        'id',
        'name',
        'email',
        'phone',
        'birthdate',
        'cpf',
        'created_at',
    ];

    protected array $relations = [
        'appointments',
        'medicalRecords',
    ];

    public function __construct()
    {
        parent::__construct(new Customer());
    }

    public function getTotalCustomersCount(): int
    {
        return $this->model->count();
    }

    public function getRegisteredCustomersInPeriodCount(array $period): int
    {
        return $this->model->whereDate('created_at', '>=', $period['start_date'])
            ->whereDate('created_at', '<=', $period['end_date'])
            ->count();
    }

    public function createDefaultCustomer(int $idCompany): Customer
    {
        return $this->model->withoutGlobalScopes()->create([
            'name' => 'Paciente de exemplo',
            'email' => 'paciente@exemplo.com',
            'phone' => '11999999999',
            'birthdate' => '2000-01-01',
            'cpf' => '12345678909',
            'notes' => 'Paciente criado automaticamente para testes.',
            'id_company' => $idCompany,
        ]);
    }
}
