<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class FinancialTransaction extends BaseModel
{
    protected $fillable = [
        'type',
        'amount',
        'description',
        'transaction_date',
        'due_date',
        'status',
        'payment_method',
        'document_number',
        'notes',
        'attachments',
        'id_financial_account',
        'id_financial_category',
        'id_customer',
        'id_appointment',
        'id_transfer_account',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'transaction_date' => 'date',
        'due_date' => 'date',
        'attachments' => 'array',
    ];

    // Relationships
    public function account(): BelongsTo
    {
        return $this->belongsTo(FinancialAccount::class, 'id_financial_account');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(FinancialCategory::class, 'id_financial_category');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'id_customer');
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class, 'id_appointment');
    }

    public function transferAccount(): BelongsTo
    {
        return $this->belongsTo(FinancialAccount::class, 'id_transfer_account');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'id_company');
    }

    // Scopes
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'PAID');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'PENDING');
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', 'OVERDUE')
            ->orWhere(function($q) {
                $q->where('status', 'PENDING')
                  ->where('due_date', '<', now());
            });
    }

    public function scopeIncome($query)
    {
        return $query->where('type', 'INCOME');
    }

    public function scopeExpense($query)
    {
        return $query->where('type', 'EXPENSE');
    }

    public function scopeTransfer($query)
    {
        return $query->where('type', 'TRANSFER');
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('transaction_date', [$startDate, $endDate]);
    }

    // Helper methods
    public function getFormattedAmountAttribute()
    {
        return 'R$ ' . number_format($this->amount, 2, ',', '.');
    }

    public function getIsOverdueAttribute()
    {
        return $this->status === 'PENDING' && $this->due_date && $this->due_date->isPast();
    }

    public function getDaysOverdueAttribute()
    {
        if (!$this->is_overdue) {
            return 0;
        }
        return now()->diffInDays($this->due_date);
    }

    public function getStatusColorAttribute()
    {
        return match($this->status) {
            'PAID' => 'green',
            'PENDING' => $this->is_overdue ? 'red' : 'yellow',
            'OVERDUE' => 'red',
            'CANCELLED' => 'gray',
            default => 'gray'
        };
    }

    public function getStatusLabelAttribute()
    {
        return match($this->status) {
            'PAID' => 'Pago',
            'PENDING' => $this->is_overdue ? 'Em Atraso' : 'Pendente',
            'OVERDUE' => 'Em Atraso',
            'CANCELLED' => 'Cancelado',
            default => 'Desconhecido'
        };
    }

    // Events
    protected static function booted()
    {
        parent::booted();

        static::created(function ($transaction) {
            if ($transaction->status === 'PAID') {
                $transaction->account->updateBalance();
                if ($transaction->transferAccount) {
                    $transaction->transferAccount->updateBalance();
                }
            }
        });

        static::updated(function ($transaction) {
            if ($transaction->isDirty('status') || $transaction->isDirty('amount')) {
                $transaction->account->updateBalance();
                if ($transaction->transferAccount) {
                    $transaction->transferAccount->updateBalance();
                }
            }
        });

        static::deleted(function ($transaction) {
            $transaction->account->updateBalance();
            if ($transaction->transferAccount) {
                $transaction->transferAccount->updateBalance();
            }
        });
    }
}
