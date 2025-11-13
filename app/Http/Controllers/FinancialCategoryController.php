<?php

namespace App\Http\Controllers;

use App\DTO\Financial\CreateFinancialCategoryDTO;
use App\DTO\Financial\UpdateFinancialCategoryDTO;
use App\Http\Controllers\Traits\Swagger\SwaggerFinancialCategoryDocs;
use App\Http\Resources\Financial\FinancialCategoryResource;
use App\Services\Financial\FinancialService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class FinancialCategoryController extends Controller
{
    use SwaggerFinancialCategoryDocs;
    public function __construct(
        protected FinancialService $financialService
    ) {}

    /**
     * Lista de categorias financeiras
     */
    public function index(Request $request): \Inertia\Response|JsonResponse
    {
        $page = (int) $request->get('page', 1);
        $search = $request->get('search', null);
        $perPage = (int) $request->get('per_page', 15);
        $orderBy = $request->get('order', 'name:asc');

        $categories = $this->financialService->getCategoriesPaginated($search, $page, $perPage, $orderBy);

        if ($request->wantsJson()) {
            return response()->json([
                'data' => FinancialCategoryResource::collection($categories->items()),
                'meta' => [
                    'current_page' => $categories->currentPage(),
                    'last_page' => $categories->lastPage(),
                    'per_page' => $categories->perPage(),
                    'total' => $categories->total(),
                ],
            ]);
        }

        return Inertia::render('financial/categories/index', [
            'categories' => $categories,
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
        $category = $this->financialService->getCategoryById($id);

        if ($request->wantsJson()) {
            return (new FinancialCategoryResource($category))
                ->response()
                ->setStatusCode(200);
        }

        return Inertia::render('financial/categories/show', [
            'category' => $category,
        ]);
    }

    /**
     * Criar nova categoria
     */
    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:INCOME,EXPENSE',
            'color' => 'required|string|max:7|regex:/^#[0-9A-Fa-f]{6}$/',
            'icon' => 'nullable|string|max:50',
            'is_active' => 'boolean',
            'description' => 'nullable|string',
        ]);

        $category = $this->financialService->createCategory(
            CreateFinancialCategoryDTO::makeFromRequest($request)
        );

        if ($request->wantsJson()) {
            return (new FinancialCategoryResource($category))
                ->response()
                ->setStatusCode(201);
        }

        return redirect()->route('financial.categories.index')
            ->with('success', 'Categoria criada com sucesso.');
    }

    /**
     * Atualizar categoria
     */
    public function update(int $id, Request $request): JsonResponse|RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:INCOME,EXPENSE',
            'color' => 'required|string|max:7|regex:/^#[0-9A-Fa-f]{6}$/',
            'icon' => 'nullable|string|max:50',
            'is_active' => 'boolean',
            'description' => 'nullable|string',
        ]);

        $category = $this->financialService->updateCategory(
            $id,
            UpdateFinancialCategoryDTO::makeFromRequest($request)
        );

        if ($request->wantsJson()) {
            return (new FinancialCategoryResource($category))
                ->response()
                ->setStatusCode(200);
        }

        return redirect()->route('financial.categories.index')
            ->with('success', 'Categoria atualizada com sucesso.');
    }

    /**
     * Excluir categoria
     */
    public function destroy(int $id, Request $request): JsonResponse|RedirectResponse
    {
        $this->financialService->deleteCategory($id);

        if ($request->wantsJson()) {
            return response()->json(null, 204);
        }

        return redirect()->route('financial.categories.index')
            ->with('success', 'Categoria excluída com sucesso.');
    }
}
