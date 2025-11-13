<?php

namespace App\Repositories\User;

use App\Models\User;
use App\Repositories\BaseRepository;
use App\Repositories\User\UserRepositoryInterface;
use Illuminate\Support\Facades\Auth;
use stdClass;

/**
 * Model User precisa usar forCurrentCompany(), porque ele não consegue extender o BaseModel (configurações de id_company automatica)
 */
class UserEloquentORM extends BaseRepository implements UserRepositoryInterface
{

    protected array $searchable = [
        'name',
        'email',
        'phone',
    ];

    protected array $sortable = [
        'id',
        'name',
        'email',
        'phone',
        'created_at',
    ];

    public function __construct()
    {
        parent::__construct(new User());
    }
    public function findByEmail(string $email): ?User
    {
        return $this->model::forCurrentCompany()->where('email', $email)->first();
    }

    public function me(): ?stdClass
    {
        $user = $this->model::forCurrentCompany()->find(Auth::id())->with(['role', 'company'])->first();
        return $user ? (object) $user->toArray() : null;
    }

    public function getRanking(array $period): array
    {
        return $this->model::forCurrentCompany()->with(['appointments'])
            ->withCount(['appointments as appointments_count' => function ($query) use ($period) {
                $query->where('status', 'completed')
                    ->whereBetween('date', [$period['start_date'], $period['end_date']]);
            }])
            ->orderByDesc('appointments_count')
            ->limit(5) // top 5 usuários
            ->get()
            ->values() // Garante reindexação (0,1,2...) após ordenação
            ->map(function ($user, $index) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'image' => $user->image_url,
                    'appointments_count' => $user->appointments_count,
                    'ranking' => ($index + 1) . 'º Lugar', // ← Aqui adicionamos o ranking
                ];
            })
            ->toArray();
    }

    public function getTotalUsersCount(): int
    {
        return $this->model::forCurrentCompany()->count();
    }

    public function getTotalRegisteredUsersCount(array $period): int
    {
        // Contagem de usuários registrados no período
        return $this->model::forCurrentCompany()->whereBetween('created_at', [$period['start_date'], $period['end_date']])->count();
    }

    public function updateProfile(int $userId, array $data): ?User
    {
        $user = $this->model::forCurrentCompany()->find($userId);

        if (!$user) {
            return null;
        }

        $user->fill($data)->save();

        return $user;
    }

    public function createAdmin(stdClass $userData): ?User
    {
        return $this->model->create([
            'name' => $userData->name,
            'email' => $userData->email,
            'phone' => $userData->phone,
            'is_owner' => true,
            'password' => $userData->password,
            'id_company' => $userData->id_company,
            'id_role' => $userData->id_role,
        ]);
    }

    public function getProfessionals()
    {
        return $this->model::forCurrentCompany()
            ->with(['role'])
            ->whereHas('role', function ($query) {
                $query->whereIn('type', ['ADMIN', 'DOCTOR']);
            })
            ->get();
    }
}
