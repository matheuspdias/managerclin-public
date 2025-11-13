<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

class InventoryCategory extends BaseModel
{
    protected $fillable = [
        'id_company',
        'name',
        'description',
        'color',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function products(): HasMany
    {
        return $this->hasMany(InventoryProduct::class, 'id_category');
    }

    public function activeProducts(): HasMany
    {
        return $this->products()->where('active', true);
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }
}