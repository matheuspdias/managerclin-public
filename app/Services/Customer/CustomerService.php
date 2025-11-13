<?php

namespace App\Services\Customer;

use App\DTO\Customer\CreateCustomerDTO;
use App\DTO\Customer\UpdateCustomerDTO;
use App\Models\Customer;
use App\Repositories\Customer\CustomerRepositoryInterface;
use App\Traits\ThrowsExceptions;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class CustomerService
{
    use ThrowsExceptions;
    public function __construct(
        protected CustomerRepositoryInterface $repository
    ) {}

    public function getAllPaginate(string|null $search = null, int $page, int $perPage, string $order): LengthAwarePaginator
    {
        return $this->repository->paginate($search, $page, $perPage, $order);
    }

    public function getAll(): Collection
    {
        return $this->repository->getAll();
    }

    public function getById(int $id): Customer
    {
        $customer = $this->repository->findById($id);
        if (!$customer) {
            $this->throwNotFound('Customer not found');
        }
        return $customer;
    }

    public function store(CreateCustomerDTO $dto): Model
    {
        if ($dto->email) {
            $emailExists = $this->repository->findByCriteria(['email' => $dto->email]);
            if ($emailExists) {
                $this->throwDomain('Email already exists');
            }
        }

        if ($dto->phone) {
            $phoneExists = $this->repository->findByCriteria(['phone' => $dto->phone]);
            if ($phoneExists) {
                $this->throwDomain('Esse numero de telefone já está cadastrado');
            }
        }

        return $this->repository->store($dto);
    }

    public function update(int $id, UpdateCustomerDTO $dto): Customer
    {
        $customer = $this->repository->findById($id);
        if (!$customer) {
            $this->throwNotFound('Customer not found');
        }

        if ($dto->email) {
            $userEmailExists = $this->repository->findByCriteria(['email' => $dto->email]);
            if ($userEmailExists && $userEmailExists->id != $id) {
                $this->throwDomain('Email already exists');
            }
        }

        if ($dto->phone) {
            $userPhoneExists = $this->repository->findByCriteria(['phone' => $dto->phone]);
            if ($userPhoneExists && $userPhoneExists->id != $id) {
                $this->throwDomain('Esse numero de telefone já está cadastrado');
            }
        }

        return $this->repository->update($id, $dto);
    }

    public function destroy(int $id): void
    {
        $this->repository->deleteById($id);
    }

    public function getTotalCustomersCount(array $period): array
    {
        $customersCount = $this->repository->getTotalCustomersCount();
        $todayRegisteredCount = $this->repository->getRegisteredCustomersInPeriodCount($period);

        return [
            'total' => $customersCount,
            'total_registered_today' => $todayRegisteredCount,
        ];
    }

    public function createDefaultCustomer(int $idCompany): Customer
    {
        return $this->repository->createDefaultCustomer($idCompany);
    }
}
