<?php

namespace App\Services\ClinicService;

use App\DTO\Service\CreateServiceDTO;
use App\DTO\Service\UpdateServiceDTO;
use App\Models\Service;
use App\Repositories\Service\ServiceRepositoryInterface;
use App\Traits\ThrowsExceptions;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class ClinicService
{
    use ThrowsExceptions;
    public function __construct(
        protected ServiceRepositoryInterface $repository
    ) {}

    public function getAllPaginate(string|null $search = null, int $page, int $perPage, string $order): LengthAwarePaginator
    {
        return $this->repository->paginate($search, $page, $perPage, $order);
    }

    public function getAll(): Collection
    {
        return $this->repository->getAll();
    }

    public function getById(int $id): Service
    {
        $service = $this->repository->findById($id);
        if (!$service) {
            $this->throwNotFound('Serviço não encontrado');
        }
        return $service;
    }

    public function store(CreateServiceDTO $dto): Service
    {
        return $this->repository->store($dto);
    }

    public function update(int $id, UpdateServiceDTO $dto): Service
    {
        return $this->repository->update($id, $dto);
    }

    public function destroy(int $id): void
    {
        $this->repository->deleteById($id);
    }

    public function createDefaultServices(int $idCompany): array
    {
        $defaultServices = [
            'Consulta',
            'Exame',
            'Tratamento',
        ];

        $createdServices = $this->repository->createMany(
            $idCompany,
            $defaultServices
        );

        return $createdServices;
    }
}
