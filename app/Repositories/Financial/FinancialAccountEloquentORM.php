<?php

namespace App\Repositories\Financial;

use App\Models\FinancialAccount;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Collection;

class FinancialAccountEloquentORM extends BaseRepository implements FinancialAccountRepositoryInterface
{
    protected array $searchable = [
        'name',
        'bank_name',
        'account_number',
    ];

    protected array $sortable = [
        'id',
        'name',
        'type',
        'current_balance',
        'created_at',
    ];

    public function __construct()
    {
        parent::__construct(new FinancialAccount());
    }

    public function getActiveAccounts(): Collection
    {
        return $this->getQuery()->active()->get();
    }

    public function getAccountsByType(string $type): Collection
    {
        return $this->getQuery()->byType($type)->get();
    }

    public function getBalanceSummary(): array
    {
        $accounts = $this->getQuery()->active()->get();

        $summary = [
            'total_balance' => 0,
            'by_type' => [],
            'accounts' => []
        ];

        foreach ($accounts as $account) {
            $summary['total_balance'] += $account->current_balance;

            if (!isset($summary['by_type'][$account->type])) {
                $summary['by_type'][$account->type] = 0;
            }
            $summary['by_type'][$account->type] += $account->current_balance;

            $summary['accounts'][] = [
                'id' => $account->id,
                'name' => $account->name,
                'type' => $account->type,
                'balance' => $account->current_balance,
                'formatted_balance' => $account->formatted_balance
            ];
        }

        return $summary;
    }

    public function updateAccountBalance(int $accountId): float
    {
        $account = $this->findById($accountId);
        if ($account) {
            return $account->updateBalance();
        }
        return 0;
    }

}