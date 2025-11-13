<?php

namespace App\Services\MedicalRecord;

use App\DTO\MedicalRecord\CreateMedicalRecordDTO;
use App\DTO\MedicalRecord\UpdateMedicalRecordDTO;
use App\Models\MedicalRecord;
use App\Repositories\MedicalRecord\MedicalRecordRepositoryInterface;
use App\Traits\ThrowsExceptions;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class MedicalRecordService
{
    use ThrowsExceptions;
    public function __construct(
        protected MedicalRecordRepositoryInterface $repository
    ) {}

    public function getAllPaginate(string|null $search = null, int $page, int $perPage, string $order): LengthAwarePaginator
    {
        return $this->repository->paginate($search, $page, $perPage, $order);
    }

    public function getAll(): Collection
    {
        return $this->repository->getAll();
    }

    public function getById(int $id): MedicalRecord
    {
        $medicalRecord = $this->repository->findById($id);
        if (!$medicalRecord) {
            $this->throwNotFound('Prontuário não encontrado');
        }
        return $medicalRecord;
    }

    public function store(CreateMedicalRecordDTO $dto): MedicalRecord
    {
        return $this->repository->store($dto);
    }

    public function update(int $id, UpdateMedicalRecordDTO $dto): MedicalRecord
    {
        return $this->repository->update($id, $dto);
    }

    public function destroy(int $id): void
    {
        $this->repository->deleteById($id);
    }
}
