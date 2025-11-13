<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

class InventorySupplier extends BaseModel
{
    protected $fillable = [
        'id_company',
        'name',
        'cnpj',
        'phone',
        'email',
        'address',
        'contact_person',
        'notes',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function products(): HasMany
    {
        return $this->hasMany(InventoryProduct::class, 'id_supplier');
    }

    public function activeProducts(): HasMany
    {
        return $this->products()->where('active', true);
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function getFormattedCnpjAttribute(): ?string
    {
        if (!$this->cnpj) {
            return null;
        }

        return preg_replace('/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/', '$1.$2.$3/$4-$5', $this->cnpj);
    }

    public function getFormattedPhoneAttribute(): ?string
    {
        if (!$this->phone) {
            return null;
        }

        // Remove all non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', $this->phone);

        // Format based on length
        if (strlen($phone) === 11) {
            return preg_replace('/(\d{2})(\d{5})(\d{4})/', '($1) $2-$3', $phone);
        } elseif (strlen($phone) === 10) {
            return preg_replace('/(\d{2})(\d{4})(\d{4})/', '($1) $2-$3', $phone);
        }

        return $this->phone;
    }
}