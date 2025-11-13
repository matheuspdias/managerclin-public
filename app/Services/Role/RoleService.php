<?php

namespace App\Services\Role;

use App\Models\Role;
use App\Repositories\Role\RoleRepositoryInterface;;

use App\Traits\ThrowsExceptions;
use Illuminate\Database\Eloquent\Collection;

class RoleService
{
    use ThrowsExceptions;

    public function __construct(
        protected RoleRepositoryInterface $repository
    ) {}

    public function getAll(): Collection
    {
        return $this->repository->getAll();
    }

    public function getRoleByType(string $type): Role
    {
        $role = $this->repository->findByCriteria(['type' => $type]);
        if (!$role) {
            $this->throwNotFound('Role not found');
        }
        return $role;
    }
}
