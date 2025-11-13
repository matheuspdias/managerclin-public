<?php

namespace App\Repositories\Financial;

use App\Models\FinancialCategory;
use App\Repositories\BaseRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

/**
 * @extends BaseRepositoryInterface<FinancialCategory>
 */
interface FinancialCategoryRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Get categories by type (INCOME/EXPENSE)
     */
    public function getCategoriesByType(string $type): Collection;

    /**
     * Get active categories
     */
    public function getActiveCategories(): Collection;

    /**
     * Get categories with transaction totals
     */
    public function getCategoriesWithTotals(?string $startDate = null, ?string $endDate = null): Collection;
}