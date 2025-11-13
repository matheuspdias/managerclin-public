<?php

namespace App\Repositories\User;

use App\Models\User;
use App\Repositories\BaseRepositoryInterface;
use stdClass;

interface UserRepositoryInterface extends BaseRepositoryInterface
{
    public function findByEmail(string $email): ?User;
    public function me(): ?stdClass;
    public function getRanking(array $period): array;
    public function getTotalUsersCount(): int;
    public function getTotalRegisteredUsersCount(array $period): int;
    public function updateProfile(int $id, array $data): ?User;
    public function createAdmin(stdClass $userData): ?User;
    public function getProfessionals();
}
