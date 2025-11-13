<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinancialCategory extends BaseModel
{
    protected $fillable = [
        'name',
        'type',
        'color',
        'icon',
        'is_active',
        'description',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Relationships
    public function transactions(): HasMany
    {
        return $this->hasMany(FinancialTransaction::class, 'id_financial_category');
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

    public function scopeIncome($query)
    {
        return $query->where('type', 'INCOME');
    }

    public function scopeExpense($query)
    {
        return $query->where('type', 'EXPENSE');
    }

    // Helper methods
    public function getTotalTransactionsAttribute()
    {
        return $this->transactions()->where('status', 'PAID')->sum('amount');
    }

    public function getFormattedTotalAttribute()
    {
        return 'R$ ' . number_format($this->total_transactions, 2, ',', '.');
    }
}
