<?php

namespace App\Repositories\Inventory;

use App\Repositories\BaseRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface InventoryProductRepositoryInterface extends BaseRepositoryInterface
{
    public function getProductsWithRelations(?string $search = null, ?int $categoryId = null, ?int $supplierId = null, int $page = 1, int $perPage = 15): LengthAwarePaginator;
    public function getLowStockProducts(): Collection;
    public function getExpiredProducts(): Collection;
    public function getExpiringSoonProducts(int $days = 30): Collection;
    public function getProductsByCategory(int $categoryId): Collection;
    public function getProductsBySupplier(int $supplierId): Collection;
    public function updateStock(int $productId, float $newStock): bool;
    public function searchByCodeOrBarcode(string $code): Collection;
    public function getActiveProductsQuery();
}