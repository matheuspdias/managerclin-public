<?php

namespace App\Services\Financial;

use App\Repositories\Financial\FinancialAccountRepositoryInterface;
use App\Repositories\Financial\FinancialCategoryRepositoryInterface;
use App\Repositories\Financial\FinancialTransactionRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class FinancialService
{
    public function __construct(
        protected FinancialAccountRepositoryInterface $accountRepository,
        protected FinancialCategoryRepositoryInterface $categoryRepository,
        protected FinancialTransactionRepositoryInterface $transactionRepository
    ) {}

    // Account methods
    public function getAllAccounts(): Collection
    {
        return $this->accountRepository->getAll();
    }

    public function getActiveAccounts(): Collection
    {
        return $this->accountRepository->getActiveAccounts();
    }

    public function getAccountsPaginated(?string $search, int $page, int $perPage, ?string $order = null): LengthAwarePaginator
    {
        return $this->accountRepository->paginate($search, $page, $perPage, $order);
    }

    public function createAccount(object $dto)
    {
        return $this->accountRepository->store($dto);
    }

    public function updateAccount(int $id, object $dto)
    {
        return $this->accountRepository->update($id, $dto);
    }

    public function deleteAccount(int $id): void
    {
        $this->accountRepository->deleteById($id);
    }

    public function getBalanceSummary(): array
    {
        return $this->accountRepository->getBalanceSummary();
    }

    // Category methods
    public function getAllCategories(): Collection
    {
        return $this->categoryRepository->getAll();
    }

    public function getActiveCategories(): Collection
    {
        return $this->categoryRepository->getActiveCategories();
    }

    public function getCategoriesByType(string $type): Collection
    {
        return $this->categoryRepository->getCategoriesByType($type);
    }

    public function getCategoriesPaginated(?string $search, int $page, int $perPage, ?string $order = null): LengthAwarePaginator
    {
        return $this->categoryRepository->paginate($search, $page, $perPage, $order);
    }

    public function createCategory(object $dto)
    {
        return $this->categoryRepository->store($dto);
    }

    public function updateCategory(int $id, object $dto)
    {
        return $this->categoryRepository->update($id, $dto);
    }

    public function deleteCategory(int $id): void
    {
        $this->categoryRepository->deleteById($id);
    }

    // Transaction methods
    public function getTransactionsWithFilters(array $filters): LengthAwarePaginator
    {
        return $this->transactionRepository->getTransactionsWithFilters($filters);
    }

    public function getTransactionsByDateRange(string $startDate, string $endDate): Collection
    {
        return $this->transactionRepository->getTransactionsByDateRange($startDate, $endDate);
    }

    public function getOverdueTransactions(): Collection
    {
        return $this->transactionRepository->getOverdueTransactions();
    }

    public function getPendingTransactions(): Collection
    {
        return $this->transactionRepository->getPendingTransactions();
    }

    public function createTransaction(object $dto)
    {
        return $this->transactionRepository->store($dto);
    }

    public function updateTransaction(int $id, object $dto)
    {
        return $this->transactionRepository->update($id, $dto);
    }

    public function deleteTransaction(int $id): void
    {
        $this->transactionRepository->deleteById($id);
    }

    public function markTransactionAsPaid(int $transactionId, ?string $paymentMethod = null)
    {
        return $this->transactionRepository->markAsPaid($transactionId, $paymentMethod);
    }

    // Dashboard and reports
    public function getFinancialSummary(?string $startDate = null, ?string $endDate = null): array
    {
        return $this->transactionRepository->getFinancialSummary($startDate, $endDate);
    }

    public function getCashFlowData(string $startDate, string $endDate, string $period = 'month'): array
    {
        return $this->transactionRepository->getCashFlowData($startDate, $endDate, $period);
    }

    public function getDashboardData(): array
    {
        $currentMonth = now()->format('Y-m-01');
        $currentMonthEnd = now()->endOfMonth()->format('Y-m-d');

        return [
            'balance_summary' => $this->getBalanceSummary(),
            'monthly_summary' => $this->getFinancialSummary($currentMonth, $currentMonthEnd),
            'overdue_transactions' => $this->getOverdueTransactions(),
            'pending_transactions' => $this->getPendingTransactions()->take(10),
            'cash_flow' => $this->getCashFlowData(
                now()->subMonths(5)->format('Y-m-01'),
                $currentMonthEnd
            ),
        ];
    }

    public function createTransactionFromAppointment(int $appointmentId, array $data)
    {
        // Logic to create a financial transaction from an appointment
        $data['id_appointment'] = $appointmentId;
        $data['type'] = 'INCOME';
        $data['transaction_date'] = $data['transaction_date'] ?? now()->format('Y-m-d');

        return $this->createTransaction((object) $data);
    }
}