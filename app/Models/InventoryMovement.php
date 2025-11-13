<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryMovement extends Model
{
    protected $fillable = [
        'id_company',
        'id_product',
        'id_user',
        'type',
        'quantity',
        'unit_cost',
        'total_cost',
        'stock_before',
        'stock_after',
        'reason',
        'notes',
        'document_number',
        'movement_date',
        'batch_number',
        'expiry_date',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_cost' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'stock_before' => 'decimal:2',
        'stock_after' => 'decimal:2',
        'movement_date' => 'date',
        'expiry_date' => 'date',
    ];

    const TYPE_IN = 'IN';           // Entrada
    const TYPE_OUT = 'OUT';         // Saída
    const TYPE_ADJUSTMENT = 'ADJUSTMENT'; // Ajuste
    const TYPE_TRANSFER = 'TRANSFER';     // Transferência
    const TYPE_RETURN = 'RETURN';         // Devolução

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'id_company');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(InventoryProduct::class, 'id_product');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    public function scopeByCompany($query, int $companyId)
    {
        return $query->where('id_company', $companyId);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeByProduct($query, int $productId)
    {
        return $query->where('id_product', $productId);
    }

    public function scopeByDateRange($query, string $startDate, string $endDate)
    {
        return $query->whereBetween('movement_date', [$startDate, $endDate]);
    }

    public function getTypeDescriptionAttribute(): string
    {
        return match($this->type) {
            self::TYPE_IN => 'Entrada',
            self::TYPE_OUT => 'Saída',
            self::TYPE_ADJUSTMENT => 'Ajuste',
            self::TYPE_TRANSFER => 'Transferência',
            self::TYPE_RETURN => 'Devolução',
            default => 'Desconhecido',
        };
    }

    public function getFormattedQuantityAttribute(): string
    {
        return number_format($this->quantity, 2, ',', '.') . ' ' . $this->product->unit;
    }

    public function getFormattedUnitCostAttribute(): string
    {
        return $this->unit_cost ? 'R$ ' . number_format($this->unit_cost, 2, ',', '.') : '-';
    }

    public function getFormattedTotalCostAttribute(): string
    {
        return $this->total_cost ? 'R$ ' . number_format($this->total_cost, 2, ',', '.') : '-';
    }

    public static function getTypeOptions(): array
    {
        return [
            self::TYPE_IN => 'Entrada',
            self::TYPE_OUT => 'Saída',
            self::TYPE_ADJUSTMENT => 'Ajuste',
            self::TYPE_TRANSFER => 'Transferência',
            self::TYPE_RETURN => 'Devolução',
        ];
    }
}