<?php

namespace App\Http\Controllers;

use App\DTO\Service\CreateServiceDTO;
use App\DTO\Service\UpdateServiceDTO;
use App\Http\Controllers\Traits\Swagger\SwaggerServiceDocs;
use App\Http\Requests\Service\CreateServiceRequest;
use App\Http\Requests\Service\UpdateServiceRequest;
use App\Http\Resources\Service\ServiceResource;
use App\Services\ClinicService\ClinicService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ServiceController extends Controller
{
    use SwaggerServiceDocs;

    public function __construct(
        protected ClinicService $service
    ) {}

    public function index(Request $request): \Inertia\Response|JsonResponse
    {
        $page = (int) $request->get('page', 1);
        $search = $request->get('search', null);
        $perPage = (int) $request->get('per_page', 10);
        $orderBy = $request->get('order', 'name:asc');

        $services = $this->service->getAllPaginate($search, $page, $perPage, $orderBy);

        if ($request->wantsJson()) {
            return response()->json([
                'data' => ServiceResource::collection($services->items()),
                'meta' => [
                    'current_page' => $services->currentPage(),
                    'last_page' => $services->lastPage(),
                    'per_page' => $services->perPage(),
                    'total' => $services->total(),
                ],
            ]);
        }

        return Inertia::render('services/index', [
            'services' => $services,
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
        $service = $this->service->getById($id);

        if ($request->wantsJson()) {
            return (new ServiceResource($service))
                ->response()
                ->setStatusCode(200);
        }

        return Inertia::render('services/show', [
            'service' => $service,
        ]);
    }

    public function store(CreateServiceRequest $request): JsonResponse|RedirectResponse
    {
        $service = $this->service->store(CreateServiceDTO::makeFromRequest($request));

        if ($request->wantsJson()) {
            return (new ServiceResource($service))
                ->response()
                ->setStatusCode(201);
        }

        return redirect()->route('services.index')->with('success', 'Serviço criado com sucesso.');
    }

    public function update(int $id, UpdateServiceRequest $request): JsonResponse|RedirectResponse
    {
        $service = $this->service->update($id, UpdateServiceDTO::makeFromRequest($request));

        if ($request->wantsJson()) {
            return (new ServiceResource($service))
                ->response()
                ->setStatusCode(200);
        }

        return redirect()->route('services.index')->with('success', 'Serviço atualizado com sucesso.');
    }

    public function destroy(int $id, Request $request): JsonResponse|RedirectResponse
    {
        $this->service->destroy($id);

        if ($request->wantsJson()) {
            return response()->json(null, 204);
        }

        return redirect()->route('services.index')->with('success', 'Serviço excluído com sucesso.');
    }
}
