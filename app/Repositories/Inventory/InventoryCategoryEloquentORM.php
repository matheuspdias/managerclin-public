<?php

namespace App\Repositories\Inventory;

use App\Models\InventoryCategory;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Collection;

class InventoryCategoryEloquentORM extends BaseRepository implements InventoryCategoryRepositoryInterface
{
    public function __construct(InventoryCategory $model)
    {
        parent::__construct($model);
    }

    public function getActiveCategories(): Collection
    {
        return $this->getQuery()
            ->active()
            ->orderBy('name')
            ->get();
    }

    public function getCategoriesWithProductCount(): Collection
    {
        return $this->getQuery()
            ->withCount(['activeProducts as products_count'])
            ->orderBy('name')
            ->get();
    }

    public function getActiveCategoriesQuery()
    {
        return $this->getQuery()->active();
    }
}