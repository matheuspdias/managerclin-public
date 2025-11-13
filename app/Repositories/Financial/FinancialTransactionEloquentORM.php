<?php

namespace App\Repositories\Financial;

use App\Models\FinancialTransaction;
use App\Repositories\BaseRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Carbon\Carbon;

class FinancialTransactionEloquentORM extends BaseRepository implements FinancialTransactionRepositoryInterface
{
    protected array $searchable = [
        'description',
        'document_number',
        'notes',
    ];

    protected array $sortable = [
        'id',
        'transaction_date',
        'amount',
        'status',
        'created_at',
    ];

    public function __construct()
    {
        parent::__construct(new FinancialTransaction());
    }

    public function getTransactionsWithFilters(array $filters): LengthAwarePaginator
    {
        $query = $this->getQuery()->with(['account', 'category', 'customer']);

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('document_number', 'like', "%{$search}%")
                  ->orWhereHas('customer', function($customerQuery) use ($search) {
                      $customerQuery->where('name', 'like', "%{$search}%");
                  });
            });
        }

        if (!empty($filters['type'])) {
            $query->byType($filters['type']);
        }

        if (!empty($filters['status'])) {
            $query->byStatus($filters['status']);
        }

        if (!empty($filters['account_id'])) {
            $query->where('id_financial_account', $filters['account_id']);
        }

        if (!empty($filters['category_id'])) {
            $query->where('id_financial_category', $filters['category_id']);
        }

        if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
            $query->byDateRange($filters['start_date'], $filters['end_date']);
        }

        $query->orderBy('transaction_date', 'desc')->orderBy('created_at', 'desc');

        return $query->paginate($filters['per_page'] ?? 15, ['*'], 'page', $filters['page'] ?? 1);
    }

    public function getTransactionsByDateRange(string $startDate, string $endDate): Collection
    {
        return $this->getQuery()->byDateRange($startDate, $endDate)
            ->with(['account', 'category', 'customer'])
            ->orderBy('transaction_date', 'desc')
            ->get();
    }

    public function getTransactionsByAccount(int $accountId): Collection
    {
        return $this->getQuery()->where('id_financial_account', $accountId)
            ->with(['category', 'customer'])
            ->orderBy('transaction_date', 'desc')
            ->get();
    }

    public function getTransactionsByCategory(int $categoryId): Collection
    {
        return $this->getQuery()->where('id_financial_category', $categoryId)
            ->with(['account', 'customer'])
            ->orderBy('transaction_date', 'desc')
            ->get();
    }

    public function getOverdueTransactions(): Collection
    {
        return $this->getQuery()->overdue()
            ->with(['account', 'category', 'customer'])
            ->orderBy('due_date', 'asc')
            ->get();
    }

    public function getPendingTransactions(): Collection
    {
        return $this->getQuery()->pending()
            ->with(['account', 'category', 'customer'])
            ->orderBy('due_date', 'asc')
            ->get();
    }

    public function getTransactionsByStatus(string $status): Collection
    {
        return $this->getQuery()->byStatus($status)
            ->with(['account', 'category', 'customer'])
            ->orderBy('transaction_date', 'desc')
            ->get();
    }

    public function getFinancialSummary(?string $startDate = null, ?string $endDate = null): array
    {
        $query = $this->getQuery()->paid();

        if ($startDate && $endDate) {
            $query->byDateRange($startDate, $endDate);
        }

        $transactions = $query->get();

        $income = $transactions->where('type', 'INCOME')->sum('amount');
        $expenses = $transactions->where('type', 'EXPENSE')->sum('amount');
        $balance = $income - $expenses;

        // Calcular dados do período anterior para comparação
        $incomePercent = 0;
        $expensesPercent = 0;

        if ($startDate && $endDate) {
            $startCarbon = Carbon::parse($startDate);
            $endCarbon = Carbon::parse($endDate);

            // Calcular o período anterior com a mesma duração
            $diffDays = $startCarbon->diffInDays($endCarbon) + 1;
            $previousStartDate = $startCarbon->copy()->subDays($diffDays)->format('Y-m-d');
            $previousEndDate = $startCarbon->copy()->subDay()->format('Y-m-d');

            $previousTransactions = $this->getQuery()->paid()
                ->byDateRange($previousStartDate, $previousEndDate)
                ->get();

            $previousIncome = $previousTransactions->where('type', 'INCOME')->sum('amount');
            $previousExpenses = $previousTransactions->where('type', 'EXPENSE')->sum('amount');

            // Calcular percentuais
            if ($previousIncome > 0) {
                $incomePercent = round((($income - $previousIncome) / $previousIncome) * 100, 1);
            }

            if ($previousExpenses > 0) {
                $expensesPercent = round((($expenses - $previousExpenses) / $previousExpenses) * 100, 1);
            }
        }

        return [
            'income' => $income,
            'expenses' => $expenses,
            'balance' => $balance,
            'formatted_income' => 'R$ ' . number_format($income, 2, ',', '.'),
            'formatted_expenses' => 'R$ ' . number_format($expenses, 2, ',', '.'),
            'formatted_balance' => 'R$ ' . number_format($balance, 2, ',', '.'),
            'transactions_count' => $transactions->count(),
            'income_percent_change' => $incomePercent,
            'expenses_percent_change' => $expensesPercent,
        ];
    }

    public function getCashFlowData(string $startDate, string $endDate, string $period = 'month'): array
    {
        $transactions = $this->getQuery()->paid()
            ->byDateRange($startDate, $endDate)
            ->get();

        $cashFlow = [];
        $current = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);

        while ($current <= $end) {
            $periodKey = $period === 'month'
                ? $current->format('Y-m')
                : $current->format('Y-m-d');

            $periodTransactions = $transactions->filter(function($transaction) use ($current, $period) {
                $transactionDate = Carbon::parse($transaction->transaction_date);
                return $period === 'month'
                    ? $transactionDate->format('Y-m') === $current->format('Y-m')
                    : $transactionDate->format('Y-m-d') === $current->format('Y-m-d');
            });

            $income = $periodTransactions->where('type', 'INCOME')->sum('amount');
            $expenses = $periodTransactions->where('type', 'EXPENSE')->sum('amount');

            $cashFlow[] = [
                'period' => $periodKey,
                'period_label' => $period === 'month'
                    ? $current->locale('pt_BR')->format('M Y')
                    : $current->format('d/m'),
                'income' => $income,
                'expenses' => $expenses,
                'balance' => $income - $expenses,
            ];

            $current = $period === 'month'
                ? $current->addMonth()
                : $current->addDay();
        }

        return $cashFlow;
    }

    public function markAsPaid(int $transactionId, ?string $paymentMethod = null): FinancialTransaction
    {
        $transaction = $this->findById($transactionId);

        if ($transaction) {
            $transaction->status = 'PAID';
            if ($paymentMethod) {
                $transaction->payment_method = $paymentMethod;
            }
            $transaction->save();
        }

        return $transaction;
    }

    public function getTransactionsFromAppointments(): Collection
    {
        return $this->getQuery()->whereNotNull('id_appointment')
            ->with(['appointment', 'account', 'category', 'customer'])
            ->orderBy('transaction_date', 'desc')
            ->get();
    }
}