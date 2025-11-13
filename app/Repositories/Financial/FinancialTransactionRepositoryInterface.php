<?php

namespace App\Repositories\Financial;

use App\Models\FinancialTransaction;
use App\Repositories\BaseRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

/**
 * @extends BaseRepositoryInterface<FinancialTransaction>
 */
interface FinancialTransactionRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Get transactions with filters
     */
    public function getTransactionsWithFilters(array $filters): LengthAwarePaginator;

    /**
     * Get transactions by date range
     */
    public function getTransactionsByDateRange(string $startDate, string $endDate): Collection;

    /**
     * Get transactions by account
     */
    public function getTransactionsByAccount(int $accountId): Collection;

    /**
     * Get transactions by category
     */
    public function getTransactionsByCategory(int $categoryId): Collection;

    /**
     * Get overdue transactions
     */
    public function getOverdueTransactions(): Collection;

    /**
     * Get pending transactions
     */
    public function getPendingTransactions(): Collection;

    /**
     * Get transactions by status
     */
    public function getTransactionsByStatus(string $status): Collection;

    /**
     * Get financial summary
     */
    public function getFinancialSummary(?string $startDate = null, ?string $endDate = null): array;

    /**
     * Get cash flow data
     */
    public function getCashFlowData(string $startDate, string $endDate, string $period = 'month'): array;

    /**
     * Mark transaction as paid
     */
    public function markAsPaid(int $transactionId, ?string $paymentMethod = null): FinancialTransaction;

    /**
     * Get transactions from appointments
     */
    public function getTransactionsFromAppointments(): Collection;
}