<?php

namespace App\Repositories\Inventory;

use App\Repositories\BaseRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

interface InventoryCategoryRepositoryInterface extends BaseRepositoryInterface
{
    public function getActiveCategories(): Collection;
    public function getCategoriesWithProductCount(): Collection;
    public function getActiveCategoriesQuery();
}