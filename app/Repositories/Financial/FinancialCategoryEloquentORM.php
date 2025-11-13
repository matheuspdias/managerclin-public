<?php

namespace App\Repositories\Financial;

use App\Models\FinancialCategory;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Collection;

class FinancialCategoryEloquentORM extends BaseRepository implements FinancialCategoryRepositoryInterface
{
    protected array $searchable = [
        'name',
        'description',
    ];

    protected array $sortable = [
        'id',
        'name',
        'type',
        'created_at',
    ];

    public function __construct()
    {
        parent::__construct(new FinancialCategory());
    }

    public function getCategoriesByType(string $type): Collection
    {
        return $this->getQuery()->byType($type)->active()->get();
    }

    public function getActiveCategories(): Collection
    {
        return $this->getQuery()->active()->get();
    }

    public function getCategoriesWithTotals(?string $startDate = null, ?string $endDate = null): Collection
    {
        $query = $this->getQuery()->with(['transactions' => function($q) use ($startDate, $endDate) {
            $q->where('status', 'PAID');
            if ($startDate && $endDate) {
                $q->whereBetween('transaction_date', [$startDate, $endDate]);
            }
        }]);

        return $query->get()->map(function($category) {
            $category->total_amount = $category->transactions->sum('amount');
            $category->formatted_total = 'R$ ' . number_format($category->total_amount, 2, ',', '.');
            return $category;
        });
    }

}