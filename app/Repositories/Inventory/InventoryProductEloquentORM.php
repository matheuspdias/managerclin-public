<?php

namespace App\Repositories\Inventory;

use App\Models\InventoryProduct;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class InventoryProductEloquentORM extends BaseRepository implements InventoryProductRepositoryInterface
{
    protected array $searchable = [
        'name',
        'code',
        'barcode',
        'description',
        'batch_number',
    ];

    public function __construct(InventoryProduct $model)
    {
        parent::__construct($model);
    }

    public function getProductsWithRelations(?string $search = null, ?int $categoryId = null, ?int $supplierId = null, int $page = 1, int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->getQuery()
            ->with(['category', 'supplier'])
            ->orderBy('name');

        if ($search) {
            $query->where(function ($q) use ($search) {
                foreach ($this->searchable as $field) {
                    $q->orWhere($field, 'like', "%{$search}%");
                }
            });
        }

        if ($categoryId) {
            $query->where('id_category', $categoryId);
        }

        if ($supplierId) {
            $query->where('id_supplier', $supplierId);
        }

        return $query->paginate($perPage, ['*'], 'page', $page);
    }

    public function getLowStockProducts(): Collection
    {
        return $this->getQuery()
            ->with(['category', 'supplier'])
            ->lowStock()
            ->active()
            ->orderBy('name')
            ->get();
    }

    public function getExpiredProducts(): Collection
    {
        return $this->getQuery()
            ->with(['category', 'supplier'])
            ->expired()
            ->active()
            ->orderBy('expiry_date')
            ->get();
    }

    public function getExpiringSoonProducts(int $days = 30): Collection
    {
        return $this->getQuery()
            ->with(['category', 'supplier'])
            ->expiringSoon($days)
            ->active()
            ->orderBy('expiry_date')
            ->get();
    }

    public function getProductsByCategory(int $categoryId): Collection
    {
        return $this->getQuery()
            ->with(['category', 'supplier'])
            ->where('id_category', $categoryId)
            ->active()
            ->orderBy('name')
            ->get();
    }

    public function getProductsBySupplier(int $supplierId): Collection
    {
        return $this->getQuery()
            ->with(['category', 'supplier'])
            ->where('id_supplier', $supplierId)
            ->active()
            ->orderBy('name')
            ->get();
    }

    public function updateStock(int $productId, float $newStock): bool
    {
        return $this->getQuery()
            ->where('id', $productId)
            ->update(['current_stock' => $newStock]);
    }

    public function searchByCodeOrBarcode(string $code): Collection
    {
        return $this->getQuery()
            ->with(['category', 'supplier'])
            ->where(function ($query) use ($code) {
                $query->where('code', $code)
                      ->orWhere('barcode', $code);
            })
            ->active()
            ->get();
    }

    public function getActiveProductsQuery()
    {
        return $this->getQuery()->active();
    }
}