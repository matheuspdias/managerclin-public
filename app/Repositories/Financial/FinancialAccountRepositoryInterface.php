<?php

namespace App\Repositories\Financial;

use App\Models\FinancialAccount;
use App\Repositories\BaseRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

/**
 * @extends BaseRepositoryInterface<FinancialAccount>
 */
interface FinancialAccountRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Get all active accounts
     */
    public function getActiveAccounts(): Collection;

    /**
     * Get accounts by type
     */
    public function getAccountsByType(string $type): Collection;

    /**
     * Get account balance summary
     */
    public function getBalanceSummary(): array;

    /**
     * Update account balance
     */
    public function updateAccountBalance(int $accountId): float;
}