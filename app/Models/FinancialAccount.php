<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinancialAccount extends BaseModel
{
    protected $fillable = [
        'name',
        'type',
        'bank_name',
        'account_number',
        'initial_balance',
        'current_balance',
        'is_active',
        'description',
    ];

    protected $casts = [
        'initial_balance' => 'decimal:2',
        'current_balance' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    // Relationships
    public function transactions(): HasMany
    {
        return $this->hasMany(FinancialTransaction::class, 'id_financial_account');
    }

    public function transfersOut(): HasMany
    {
        return $this->hasMany(FinancialTransaction::class, 'id_financial_account')
            ->where('type', 'TRANSFER');
    }

    public function transfersIn(): HasMany
    {
        return $this->hasMany(FinancialTransaction::class, 'id_transfer_account')
            ->where('type', 'TRANSFER');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'id_company');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    // Helper methods
    public function updateBalance()
    {
        $totalIncome = $this->transactions()
            ->where('type', 'INCOME')
            ->where('status', 'PAID')
            ->sum('amount');

        $totalExpense = $this->transactions()
            ->where('type', 'EXPENSE')
            ->where('status', 'PAID')
            ->sum('amount');

        $transfersOut = $this->transfersOut()
            ->where('status', 'PAID')
            ->sum('amount');

        $transfersIn = $this->transfersIn()
            ->where('status', 'PAID')
            ->sum('amount');

        $this->current_balance = $this->initial_balance + $totalIncome - $totalExpense - $transfersOut + $transfersIn;
        $this->save();

        return $this->current_balance;
    }

    public function getFormattedBalanceAttribute()
    {
        return 'R$ ' . number_format($this->current_balance, 2, ',', '.');
    }
}
