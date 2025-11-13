<?php

namespace App\Services\Inventory;

use App\Repositories\Inventory\InventoryCategoryRepositoryInterface;
use App\Repositories\Inventory\InventoryProductRepositoryInterface;
use App\Models\InventoryMovement;
use App\Models\InventoryProduct;
use App\Traits\ThrowsExceptions;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class InventoryService
{
    use ThrowsExceptions;
    public function __construct(
        protected InventoryCategoryRepositoryInterface $categoryRepository,
        protected InventoryProductRepositoryInterface $productRepository
    ) {}

    // Category methods
    public function getAllCategories(): Collection
    {
        return $this->categoryRepository->getAll();
    }

    public function getActiveCategories(): Collection
    {
        return $this->categoryRepository->getActiveCategories();
    }

    public function getCategoriesWithProductCount(): Collection
    {
        return $this->categoryRepository->getCategoriesWithProductCount();
    }

    public function createCategory(array $data)
    {
        return $this->categoryRepository->store((object) $data);
    }

    public function updateCategory(int $id, array $data)
    {
        return $this->categoryRepository->update($id, (object) $data);
    }

    public function deleteCategory(int $id): void
    {
        $this->categoryRepository->deleteById($id);
    }

    // Product methods
    public function getProductsWithFilters(?string $search = null, ?int $categoryId = null, ?int $supplierId = null, int $page = 1, int $perPage = 15): LengthAwarePaginator
    {
        return $this->productRepository->getProductsWithRelations($search, $categoryId, $supplierId, $page, $perPage);
    }

    public function createProduct(array $data)
    {
        return $this->productRepository->store((object) $data);
    }

    public function updateProduct(int $id, array $data)
    {
        return $this->productRepository->update($id, (object) $data);
    }

    public function deleteProduct(int $id): void
    {
        $this->productRepository->deleteById($id);
    }

    public function getProductById(int $id)
    {
        return $this->productRepository->findById($id);
    }

    // Stock movement methods
    public function addStockMovement(
        int $productId,
        string $type,
        float $quantity,
        string $reason,
        ?float $unitCost = null,
        ?string $documentNumber = null,
        ?string $notes = null,
        ?string $batchNumber = null,
        ?Carbon $expiryDate = null,
        ?Carbon $movementDate = null
    ) {
        return DB::transaction(function () use (
            $productId, $type, $quantity, $reason, $unitCost,
            $documentNumber, $notes, $batchNumber, $expiryDate, $movementDate
        ) {
            $product = $this->productRepository->findById($productId);
            if (!$product) {
                $this->throwNotFound('Produto não encontrado');
            }

            $stockBefore = $product->current_stock;

            // Calculate new stock based on movement type
            $stockAfter = match($type) {
                InventoryMovement::TYPE_IN,
                InventoryMovement::TYPE_RETURN => $stockBefore + $quantity,
                InventoryMovement::TYPE_OUT => $stockBefore - $quantity,
                InventoryMovement::TYPE_ADJUSTMENT => $quantity,
                InventoryMovement::TYPE_TRANSFER => $stockBefore - $quantity,
                default => $this->throwDomain('Tipo de movimentação inválido')
            };

            // Validate stock doesn't go negative for OUT movements
            if (in_array($type, [InventoryMovement::TYPE_OUT, InventoryMovement::TYPE_TRANSFER]) && $stockAfter < 0) {
                $this->throwDomain('Estoque insuficiente para esta operação');
            }

            // Create movement record
            $movement = InventoryMovement::create([
                'id_company' => $product->id_company,
                'id_product' => $productId,
                'id_user' => Auth::id(),
                'type' => $type,
                'quantity' => $quantity,
                'unit_cost' => $unitCost,
                'total_cost' => $unitCost ? $unitCost * $quantity : null,
                'stock_before' => $stockBefore,
                'stock_after' => $stockAfter,
                'reason' => $reason,
                'notes' => $notes,
                'document_number' => $documentNumber,
                'movement_date' => $movementDate ?? now(),
                'batch_number' => $batchNumber,
                'expiry_date' => $expiryDate,
            ]);

            // Update product stock
            $this->productRepository->updateStock($productId, $stockAfter);

            // Update product cost price if provided
            if ($unitCost && in_array($type, [InventoryMovement::TYPE_IN, InventoryMovement::TYPE_RETURN])) {
                $this->productRepository->update($productId, (object) ['cost_price' => $unitCost]);
            }

            return $movement;
        });
    }

    public function getProductMovements(int $productId, int $page = 1, int $perPage = 15): LengthAwarePaginator
    {
        return InventoryMovement::with(['user', 'product'])
            ->where('id_product', $productId)
            ->orderBy('movement_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);
    }

    // Alert methods
    public function getLowStockProducts(): Collection
    {
        return $this->productRepository->getLowStockProducts();
    }

    public function getExpiredProducts(): Collection
    {
        return $this->productRepository->getExpiredProducts();
    }

    public function getExpiringSoonProducts(int $days = 30): Collection
    {
        return $this->productRepository->getExpiringSoonProducts($days);
    }

    // Dashboard methods
    public function getDashboardData(): array
    {
        $totalProducts = $this->productRepository->getActiveProductsQuery()->count();
        $totalCategories = $this->categoryRepository->getActiveCategoriesQuery()->count();
        $lowStockCount = $this->productRepository->getLowStockProducts()->count();
        $expiredCount = $this->productRepository->getExpiredProducts()->count();
        $expiringSoonCount = $this->productRepository->getExpiringSoonProducts()->count();

        $totalStockValue = $this->productRepository->getActiveProductsQuery()
            ->whereNotNull('cost_price')
            ->get()
            ->sum(function ($product) {
                return $product->current_stock * $product->cost_price;
            });

        return [
            'total_products' => $totalProducts,
            'total_categories' => $totalCategories,
            'low_stock_count' => $lowStockCount,
            'expired_count' => $expiredCount,
            'expiring_soon_count' => $expiringSoonCount,
            'total_stock_value' => $totalStockValue,
            'low_stock_products' => $this->getLowStockProducts()->take(5),
            'expiring_soon_products' => $this->getExpiringSoonProducts()->take(5),
        ];
    }

    // Reports
    public function getMovementsByDateRange(string $startDate, string $endDate, ?int $productId = null): Collection
    {
        $query = InventoryMovement::with(['product.category', 'user'])
            ->whereBetween('movement_date', [$startDate, $endDate])
            ->orderBy('movement_date', 'desc');

        if ($productId) {
            $query->where('id_product', $productId);
        }

        return $query->get();
    }

    public function getStockReport(): Collection
    {
        return $this->productRepository->getActiveProductsQuery()
            ->with(['category', 'supplier'])
            ->orderBy('name')
            ->get();
    }
}