<?php

namespace App\Http\Controllers;

use App\DTO\Financial\CreateFinancialAccountDTO;
use App\DTO\Financial\UpdateFinancialAccountDTO;
use App\Http\Controllers\Traits\Swagger\SwaggerFinancialAccountDocs;
use App\Http\Resources\Financial\FinancialAccountResource;
use App\Services\Financial\FinancialService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class FinancialAccountController extends Controller
{
    use SwaggerFinancialAccountDocs;
    public function __construct(
        protected FinancialService $financialService
    ) {}

    /**
     * Lista de contas financeiras
     */
    public function index(Request $request): \Inertia\Response|JsonResponse
    {
        $page = (int) $request->get('page', 1);
        $search = $request->get('search', null);
        $perPage = (int) $request->get('per_page', 15);
        $orderBy = $request->get('order', 'name:asc');

        $accounts = $this->financialService->getAccountsPaginated($search, $page, $perPage, $orderBy);

        if ($request->wantsJson()) {
            return response()->json([
                'data' => FinancialAccountResource::collection($accounts->items()),
                'meta' => [
                    'current_page' => $accounts->currentPage(),
                    'last_page' => $accounts->lastPage(),
                    'per_page' => $accounts->perPage(),
                    'total' => $accounts->total(),
                ],
            ]);
        }

        $balanceSummary = $this->financialService->getBalanceSummary();

        return Inertia::render('financial/accounts/index', [
            'accounts' => $accounts,
            'balanceSummary' => $balanceSummary,
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
        $account = $this->financialService->getAccountById($id);

        if ($request->wantsJson()) {
            return (new FinancialAccountResource($account))
                ->response()
                ->setStatusCode(200);
        }

        return Inertia::render('financial/accounts/show', [
            'account' => $account,
        ]);
    }

    /**
     * Criar nova conta
     */
    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:CHECKING,SAVINGS,CASH,CREDIT_CARD',
            'bank_name' => 'nullable|string|max:255',
            'account_number' => 'nullable|string|max:100',
            'initial_balance' => 'required|numeric',
            'is_active' => 'boolean',
            'description' => 'nullable|string',
        ]);

        $account = $this->financialService->createAccount(
            CreateFinancialAccountDTO::makeFromRequest($request)
        );

        if ($request->wantsJson()) {
            return (new FinancialAccountResource($account))
                ->response()
                ->setStatusCode(201);
        }

        return redirect()->route('financial.accounts.index')
            ->with('success', 'Conta criada com sucesso.');
    }

    /**
     * Atualizar conta
     */
    public function update(int $id, Request $request): JsonResponse|RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:CHECKING,SAVINGS,CASH,CREDIT_CARD',
            'bank_name' => 'nullable|string|max:255',
            'account_number' => 'nullable|string|max:100',
            'initial_balance' => 'required|numeric',
            'is_active' => 'boolean',
            'description' => 'nullable|string',
        ]);

        $account = $this->financialService->updateAccount(
            $id,
            UpdateFinancialAccountDTO::makeFromRequest($request)
        );

        if ($request->wantsJson()) {
            return (new FinancialAccountResource($account))
                ->response()
                ->setStatusCode(200);
        }

        return redirect()->route('financial.accounts.index')
            ->with('success', 'Conta atualizada com sucesso.');
    }

    /**
     * Excluir conta
     */
    public function destroy(int $id, Request $request): JsonResponse|RedirectResponse
    {
        $this->financialService->deleteAccount($id);

        if ($request->wantsJson()) {
            return response()->json(null, 204);
        }

        return redirect()->route('financial.accounts.index')
            ->with('success', 'Conta excluída com sucesso.');
    }
}
