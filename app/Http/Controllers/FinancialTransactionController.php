<?php

namespace App\Http\Controllers;

use App\DTO\Financial\CreateFinancialTransactionDTO;
use App\DTO\Financial\UpdateFinancialTransactionDTO;
use App\Http\Controllers\Traits\Swagger\SwaggerFinancialTransactionDocs;
use App\Http\Resources\Financial\FinancialTransactionResource;
use App\Services\Financial\FinancialService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class FinancialTransactionController extends Controller
{
    use SwaggerFinancialTransactionDocs;
    public function __construct(
        protected FinancialService $financialService
    ) {}

    /**
     * Lista de transações com filtros
     */
    public function index(Request $request): \Inertia\Response|JsonResponse
    {
        $filters = [
            'search' => $request->get('search'),
            'type' => $request->get('type'),
            'status' => $request->get('status'),
            'account_id' => $request->get('account_id'),
            'category_id' => $request->get('category_id'),
            'start_date' => $request->get('start_date'),
            'end_date' => $request->get('end_date'),
            'page' => $request->get('page', 1),
            'per_page' => $request->get('per_page', 15),
        ];

        $transactions = $this->financialService->getTransactionsWithFilters($filters);

        if ($request->wantsJson()) {
            return response()->json([
                'data' => FinancialTransactionResource::collection($transactions->items()),
                'meta' => [
                    'current_page' => $transactions->currentPage(),
                    'last_page' => $transactions->lastPage(),
                    'per_page' => $transactions->perPage(),
                    'total' => $transactions->total(),
                ],
            ]);
        }

        $accounts = $this->financialService->getActiveAccounts();
        $categories = $this->financialService->getActiveCategories();

        return Inertia::render('financial/transactions/index', [
            'transactions' => $transactions,
            'accounts' => $accounts,
            'categories' => $categories,
            'filters' => $filters,
        ]);
    }

    public function show(int $id, Request $request): \Inertia\Response|JsonResponse
    {
        $transaction = $this->financialService->getTransactionById($id);

        if ($request->wantsJson()) {
            return (new FinancialTransactionResource($transaction))
                ->response()
                ->setStatusCode(200);
        }

        return Inertia::render('financial/transactions/show', [
            'transaction' => $transaction,
        ]);
    }

    /**
     * Criar nova transação
     */
    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $request->validate([
            'type' => 'required|in:INCOME,EXPENSE,TRANSFER',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'required|string|max:255',
            'transaction_date' => 'required|date',
            'id_financial_account' => 'required|exists:financial_accounts,id',
            'id_financial_category' => 'required|exists:financial_categories,id',
            'due_date' => 'nullable|date',
            'status' => 'in:PENDING,PAID,OVERDUE,CANCELLED',
            'payment_method' => 'nullable|string|in:CASH,CARD,TRANSFER,PIX,CHECK',
            'document_number' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
            'id_customer' => 'nullable|exists:customers,id',
            'id_appointment' => 'nullable|exists:appointments,id',
            'id_transfer_account' => 'nullable|exists:financial_accounts,id',
        ]);

        $transaction = $this->financialService->createTransaction(
            CreateFinancialTransactionDTO::makeFromRequest($request)
        );

        if ($request->wantsJson()) {
            return (new FinancialTransactionResource($transaction))
                ->response()
                ->setStatusCode(201);
        }

        return redirect()->route('financial.transactions.index')
            ->with('success', 'Transação criada com sucesso.');
    }

    /**
     * Atualizar transação
     */
    public function update(int $id, Request $request): JsonResponse|RedirectResponse
    {
        $request->validate([
            'type' => 'required|in:INCOME,EXPENSE,TRANSFER',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'required|string|max:255',
            'transaction_date' => 'required|date',
            'id_financial_account' => 'required|exists:financial_accounts,id',
            'id_financial_category' => 'required|exists:financial_categories,id',
            'due_date' => 'nullable|date',
            'status' => 'in:PENDING,PAID,OVERDUE,CANCELLED',
            'payment_method' => 'nullable|string|in:CASH,CARD,TRANSFER,PIX,CHECK',
            'document_number' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
            'id_customer' => 'nullable|exists:customers,id',
            'id_appointment' => 'nullable|exists:appointments,id',
            'id_transfer_account' => 'nullable|exists:financial_accounts,id',
        ]);

        $transaction = $this->financialService->updateTransaction(
            $id,
            UpdateFinancialTransactionDTO::makeFromRequest($request)
        );

        if ($request->wantsJson()) {
            return (new FinancialTransactionResource($transaction))
                ->response()
                ->setStatusCode(200);
        }

        return redirect()->route('financial.transactions.index')
            ->with('success', 'Transação atualizada com sucesso.');
    }

    /**
     * Excluir transação
     */
    public function destroy(int $id, Request $request): JsonResponse|RedirectResponse
    {
        $this->financialService->deleteTransaction($id);

        if ($request->wantsJson()) {
            return response()->json(null, 204);
        }

        return redirect()->route('financial.transactions.index')
            ->with('success', 'Transação excluída com sucesso.');
    }

    /**
     * Marcar transação como paga
     */
    public function markAsPaid(int $id, Request $request): RedirectResponse
    {
        $request->validate([
            'payment_method' => 'nullable|string|in:CASH,CARD,TRANSFER,PIX,CHECK',
        ]);

        $this->financialService->markTransactionAsPaid(
            $id,
            $request->input('payment_method')
        );

        return redirect()->back()
            ->with('success', 'Transação marcada como paga.');
    }

    /**
     * Lista de transações em atraso
     */
    public function overdue(): \Inertia\Response
    {
        $overdueTransactions = $this->financialService->getOverdueTransactions();

        return Inertia::render('financial/transactions/overdue', [
            'transactions' => $overdueTransactions,
        ]);
    }

    /**
     * Lista de transações pendentes
     */
    public function pending(): \Inertia\Response
    {
        $pendingTransactions = $this->financialService->getPendingTransactions();

        return Inertia::render('financial/transactions/pending', [
            'transactions' => $pendingTransactions,
        ]);
    }
}
