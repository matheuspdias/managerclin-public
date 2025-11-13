<?php

namespace App\Http\Controllers;

use App\DTO\Customer\CreateCustomerDTO;
use App\DTO\Customer\UpdateCustomerDTO;
use App\Http\Controllers\Traits\Swagger\SwaggerCustomerDocs;
use App\Http\Requests\Customer\CreateCustomerRequest;
use App\Http\Requests\Customer\UpdateCustomerRequest;
use App\Http\Resources\Customer\CustomerResource;
use Illuminate\Http\Request;
use App\Services\Customer\CustomerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Inertia\Inertia;

class CustomerController extends Controller
{
    use SwaggerCustomerDocs;

    public function __construct(
        protected CustomerService $service,
    ) {}

    public function index(Request $request): \Inertia\Response|JsonResponse
    {
        $page = (int) $request->get('page', 1);
        $search = $request->get('search', null);
        $perPage = (int) $request->get('per_page', 10);
        $orderBy = $request->get('order', 'name:asc');

        $customers = $this->service->getAllPaginate($search, $page, $perPage, $orderBy);

        if ($request->wantsJson()) {
            // Para API: retorna JSON com paginação
            return response()->json([
                'data' => CustomerResource::collection($customers->items()),
                'meta' => [
                    'current_page' => $customers->currentPage(),
                    'last_page' => $customers->lastPage(),
                    'per_page' => $customers->perPage(),
                    'total' => $customers->total(),
                ],
            ]);
        }

        // Para Web: renderiza página Inertia
        return Inertia::render('patients/index', [
            'patients' => $customers,
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
        $customer = $this->service->getById($id);

        if ($request->wantsJson()) {
            // Para API: retorna JSON com os dados do cliente
            return (new CustomerResource($customer))
                ->response()
                ->setStatusCode(200);
        }

        // Para Web: renderiza página Inertia (você pode criar uma view de detalhes se precisar)
        return Inertia::render('patients/show', [
            'patient' => $customer,
        ]);
    }

    public function store(CreateCustomerRequest $request): JsonResponse|RedirectResponse
    {
        $customer = $this->service->store(CreateCustomerDTO::makeFromRequest($request));

        if ($request->wantsJson()) {
            // Para API: retorna JSON com o cliente criado
            return (new CustomerResource($customer))
                ->response()
                ->setStatusCode(201);
        }

        // Para Web: redireciona após criação
        return redirect()->route('patients.index')
            ->with('success', 'Customer created successfully');
    }

    public function update(int $id, UpdateCustomerRequest $request): JsonResponse|RedirectResponse
    {
        $customer = $this->service
            ->update($id, UpdateCustomerDTO::makeFromRequest($request));

        if ($request->wantsJson()) {
            // Para API: retorna JSON com o cliente atualizado
            return (new CustomerResource($customer))
                ->response()
                ->setStatusCode(200);
        }
        // Para Web: redireciona após atualização
        return redirect()->route('patients.index')
            ->with('success', 'Customer updated successfully');
    }

    public function destroy(int $id, Request $request): JsonResponse|RedirectResponse
    {
        $this->service->destroy($id);

        if ($request->wantsJson()) {
            // Para API: retorna 204 No Content
            return response()->json(null, 204);
        }

        // Para Web: redireciona após exclusão
        return redirect()->route('patients.index')
            ->with('success', 'Customer deleted successfully');
    }
}
