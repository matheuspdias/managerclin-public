<?php

namespace App\Services\User;

use App\DTO\User\CreateUserDTO;
use App\DTO\User\UpdateUserDTO;
use App\Models\User;
use App\Repositories\User\UserRepositoryInterface;
use App\Services\Schedule\UserScheduleService;
use App\Traits\ThrowsExceptions;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use stdClass;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;


class UserService
{
    use ThrowsExceptions;
    public function __construct(
        protected UserRepositoryInterface $repository,
        protected UserScheduleService $userScheduleService
    ) {}

    public function getRanking(array $period): array
    {
        return $this->repository->getRanking($period);
    }

    public function getTotalUsersCount(array $period): array
    {
        $totalUsers =  $this->repository->getTotalUsersCount();
        $todayRegistered = $this->repository->getTotalRegisteredUsersCount($period);

        return [
            'total' => $totalUsers,
            'total_registered_today' => $todayRegistered,
        ];
    }

    public function getAllPaginate(string|null $search = null, int $page, int $perPage, string $order): LengthAwarePaginator
    {
        return $this->repository->paginate($search, $page, $perPage, $order);
    }

    public function getAll(): Collection
    {
        return $this->repository->getAll();
    }

    public function getProfessionals(): Collection
    {
        return $this->repository->getProfessionals();
    }

    public function store(CreateUserDTO $dto): User
    {
        // Verificar limite de usuários (exceto plano Premium que tem usuários ilimitados)
        $company = Auth::user()->company;
        $userLimitInfo = $company->getUserLimitInfo();

        if ($userLimitInfo['has_subscription'] && !($userLimitInfo['is_premium'] ?? false) && $userLimitInfo['remaining_users'] <= 0) {
            $this->throwDomain('Limite de usuários atingido. Você possui '.$userLimitInfo['current_users'].' usuários cadastrados e seu plano permite '.$userLimitInfo['max_allowed_users'].' usuários.');
        }

        if ($dto->email) {
            $userEmailExists = $this->repository->findByCriteria(['email' => $dto->email]);
            if ($userEmailExists) {
                $this->throwDomain('E-mail já cadastrado.');
            }
        }

        if ($dto->phone) {
            $userPhoneExists = $this->repository->findByCriteria(['phone' => $dto->phone]);
            if ($userPhoneExists) {
                $this->throwDomain('Telefone já cadastrado.');
            }
        }

        //  Gera senha temporária aleatória apenas para persistência
        $temporaryPassword = Str::random(32); // long e seguro
        $dto->password = bcrypt($temporaryPassword); // sobrescreve no DTO
        $dto->id_company = Auth::user()->id_company; // Define a empresa do usuário autenticado

        // Cria o usuário no banco com senha segura
        $user = $this->repository->store($dto);

        // cria os horarios padrão do admin
        $this->userScheduleService->createDefaultUserSchedule($user);

        if ($user->email) {
            // Envia e-mail com link para criar nova senha
            Password::sendResetLink(['email' => $user->email]);
        }
        return $user;
    }

    public function update(int $id, UpdateUserDTO $dto): User
    {
        $user = $this->repository->findById($id);
        if (!$user) {
            $this->throwNotFound('Usuário não encontrado.');
        }

        if ($dto->email) {
            $userEmailExists = $this->repository->findByCriteria(['email' => $dto->email]);
            if ($userEmailExists && $userEmailExists->id !== $id) {
                $this->throwDomain('E-mail já cadastrado.');
            }
        }

        if ($dto->phone) {
            $userPhoneExists = $this->repository->findByCriteria(['phone' => $dto->phone]);
            if ($userPhoneExists && $userPhoneExists->id !== $id) {
                $this->throwDomain('Telefone já cadastrado.');
            }
        }

        return $this->repository->update($id, $dto);
    }

    public function destroy(int $id): void
    {
        $user = $this->repository->findById($id);
        if (!$user) {
            $this->throwNotFound('Usuário não encontrado.');
        }

        if ($user->id === Auth::id()) {
            $this->throwForbidden('Você não pode excluir a si mesmo.');
        }

        $this->repository->deleteById($id);
    }

    public function createDefaultUserAdmin(stdClass $userData): User
    {
        $user = $this->repository->createAdmin($userData);
        if (!$user) {
            $this->throwDomain('Erro ao criar usuário administrador.');
        }
        return $user;
    }
}
