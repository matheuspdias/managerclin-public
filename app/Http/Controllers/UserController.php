<?php

namespace App\Http\Controllers;

use App\DTO\User\CreateUserDTO;
use App\DTO\User\UpdateUserDTO;
use App\Http\Controllers\Traits\Swagger\SwaggerUserDocs;
use App\Http\Requests\User\CreateUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Resources\User\UserResource;
use App\Services\Role\RoleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Services\User\UserService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class UserController extends Controller
{
    use SwaggerUserDocs;

    public function __construct(
        protected UserService $service,
        protected RoleService $roleService
    ) {}

    public function index(Request $request): \Inertia\Response|JsonResponse
    {
        $page = (int) $request->get('page', 1);
        $search = $request->get('search', null);
        $perPage = (int) $request->get('per_page', 10);
        $orderBy = $request->get('order', 'name:asc');

        $users = $this->service->getAllPaginate($search, $page, $perPage, $orderBy);

        if ($request->wantsJson()) {
            return response()->json([
                'data' => UserResource::collection($users->items()),
                'meta' => [
                    'current_page' => $users->currentPage(),
                    'last_page' => $users->lastPage(),
                    'per_page' => $users->perPage(),
                    'total' => $users->total(),
                ],
            ]);
        }

        $roles = $this->roleService->getAll();
        $company = Auth::user()->company;
        $userLimitInfo = $company->getUserLimitInfo();

        return Inertia::render('users/index', [
            'users' => $users,
            'roles' => $roles,
            'search' => $search,
            'userLimitInfo' => $userLimitInfo,
            'perPage' => $perPage,
            'orderBy' => $orderBy,
            'filters' => [
                'search' => $search,
                'per_page' => $perPage,
                'order' => $orderBy,
                'page' => $page,
            ],
        ]);
    }

    public function show(int $id, Request $request): \Inertia\Response|JsonResponse
    {
        $user = $this->service->getById($id);

        if ($request->wantsJson()) {
            return (new UserResource($user))
                ->response()
                ->setStatusCode(200);
        }

        return Inertia::render('users/show', [
            'user' => $user,
        ]);
    }

    public function store(CreateUserRequest $request): JsonResponse|RedirectResponse
    {
        // Verificar limite de usuários do plano
        $company = Auth::user()->company;
        $userLimitInfo = $company->getUserLimitInfo();

        if ($userLimitInfo['has_subscription'] && $userLimitInfo['is_over_limit']) {
            if ($request->wantsJson()) {
                return response()->json(['error' => $userLimitInfo['message']], 403);
            }
            return redirect()->route('users.index')->with('error', $userLimitInfo['message']);
        }

        // Verificar se adicionar um novo usuário excederá o limite
        if ($userLimitInfo['has_subscription'] && $userLimitInfo['max_allowed_users'] !== null) {
            if ($userLimitInfo['current_users'] >= $userLimitInfo['max_allowed_users']) {
                $errorMessage = "Limite de usuários atingido! Seu plano {$userLimitInfo['plan_name']} permite até {$userLimitInfo['max_allowed_users']} usuário(s). Faça upgrade do seu plano para adicionar mais usuários.";
                if ($request->wantsJson()) {
                    return response()->json(['error' => $errorMessage], 403);
                }
                return redirect()->route('users.index')->with('error', $errorMessage);
            }
        }

        $user = $this->service->store(CreateUserDTO::makeFromRequest($request));

        if ($request->wantsJson()) {
            return (new UserResource($user))
                ->response()
                ->setStatusCode(201);
        }

        return redirect()->route('users.index')->with('success', 'Usuário criado com sucesso.');
    }

    public function update(int $id, UpdateUserRequest $request): JsonResponse|RedirectResponse
    {
        $user = $this->service->update($id, UpdateUserDTO::makeFromRequest($request));

        if ($request->wantsJson()) {
            return (new UserResource($user))
                ->response()
                ->setStatusCode(200);
        }

        return redirect()->route('users.index')->with('success', 'Usuário atualizado com sucesso.');
    }

    public function destroy(int $id, Request $request): JsonResponse|RedirectResponse
    {
        $this->service->destroy($id);

        if ($request->wantsJson()) {
            return response()->json(null, 204);
        }

        return redirect()->route('users.index')->with('success', 'Usuário excluído com sucesso.');
    }

    /**
     * Obter horários de trabalho de um profissional
     */
    public function getWorkingHours(int $id, Request $request): JsonResponse
    {
        $date = $request->get('date');

        // Se não passar data, retorna horário padrão (segunda-feira como exemplo)
        $dayOfWeek = $date ? date('w', strtotime($date)) : 1;

        $schedule = \App\Models\UserSchedule::where('id_user', $id)
            ->where('day_of_week', $dayOfWeek)
            ->where('is_work', true)
            ->first();

        if (!$schedule) {
            // Retornar horário padrão se não houver configuração
            return response()->json([
                'has_schedule' => false,
                'start_time' => '08:00',
                'end_time' => '18:00',
                'day_of_week' => $dayOfWeek,
            ]);
        }

        return response()->json([
            'has_schedule' => true,
            'start_time' => substr($schedule->start_time, 0, 5), // HH:MM
            'end_time' => substr($schedule->end_time, 0, 5), // HH:MM
            'day_of_week' => $schedule->day_of_week,
            'is_work' => $schedule->is_work,
        ]);
    }
}
