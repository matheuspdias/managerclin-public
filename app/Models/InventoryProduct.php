<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class InventoryProduct extends BaseModel
{
    protected $fillable = [
        'id_company',
        'id_category',
        'id_supplier',
        'name',
        'code',
        'barcode',
        'description',
        'unit',
        'current_stock',
        'minimum_stock',
        'maximum_stock',
        'cost_price',
        'sale_price',
        'expiry_date',
        'batch_number',
        'storage_location',
        'requires_prescription',
        'controlled_substance',
        'active',
    ];

    protected $casts = [
        'current_stock' => 'decimal:2',
        'minimum_stock' => 'decimal:2',
        'maximum_stock' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'expiry_date' => 'date',
        'requires_prescription' => 'boolean',
        'controlled_substance' => 'boolean',
        'active' => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(InventoryCategory::class, 'id_category');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(InventorySupplier::class, 'id_supplier');
    }

    public function movements(): HasMany
    {
        return $this->hasMany(InventoryMovement::class, 'id_product');
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function scopeLowStock($query)
    {
        return $query->whereRaw('current_stock <= minimum_stock');
    }

    public function scopeExpiringSoon($query, int $days = 30)
    {
        return $query->whereNotNull('expiry_date')
            ->where('expiry_date', '<=', Carbon::now()->addDays($days));
    }

    public function scopeExpired($query)
    {
        return $query->whereNotNull('expiry_date')
            ->where('expiry_date', '<', Carbon::now());
    }

    public function getIsLowStockAttribute(): bool
    {
        return $this->current_stock <= $this->minimum_stock;
    }

    public function getIsExpiredAttribute(): bool
    {
        return $this->expiry_date && $this->expiry_date < Carbon::now();
    }

    public function getIsExpiringSoonAttribute(): bool
    {
        return $this->expiry_date &&
               $this->expiry_date <= Carbon::now()->addDays(30) &&
               !$this->is_expired;
    }

    public function getStockStatusAttribute(): string
    {
        if ($this->is_expired) {
            return 'expired';
        }

        if ($this->is_expiring_soon) {
            return 'expiring_soon';
        }

        if ($this->is_low_stock) {
            return 'low_stock';
        }

        if ($this->maximum_stock && $this->current_stock >= $this->maximum_stock) {
            return 'overstock';
        }

        return 'normal';
    }

    public function getFormattedCostPriceAttribute(): string
    {
        return $this->cost_price ? 'R$ ' . number_format($this->cost_price, 2, ',', '.') : '-';
    }

    public function getFormattedSalePriceAttribute(): string
    {
        return $this->sale_price ? 'R$ ' . number_format($this->sale_price, 2, ',', '.') : '-';
    }

    public function getFormattedCurrentStockAttribute(): string
    {
        return number_format($this->current_stock, 2, ',', '.') . ' ' . $this->unit;
    }
}