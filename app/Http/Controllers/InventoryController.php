<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Traits\Swagger\SwaggerInventoryDocs;
use App\Http\Resources\Inventory\InventoryProductResource;
use App\Services\Inventory\InventoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class InventoryController extends Controller
{
    use SwaggerInventoryDocs;
    public function __construct(
        protected InventoryService $inventoryService
    ) {}

    /**
     * Dashboard principal do estoque
     */
    public function index(Request $request): \Inertia\Response
    {
        $dashboardData = $this->inventoryService->getDashboardData();

        return Inertia::render('inventory/index', [
            'dashboardData' => $dashboardData,
        ]);
    }

    /**
     * Lista de produtos
     */
    public function products(Request $request): \Inertia\Response|JsonResponse
    {
        $search = $request->get('search');
        $categoryId = $request->get('category_id');
        $supplierId = $request->get('supplier_id');
        $page = $request->get('page', 1);
        $perPage = $request->get('per_page', 15);

        $products = $this->inventoryService->getProductsWithFilters(
            $search,
            $categoryId,
            $supplierId,
            $page,
            $perPage
        );

        if ($request->wantsJson()) {
            return response()->json([
                'data' => InventoryProductResource::collection($products->items()),
                'meta' => [
                    'current_page' => $products->currentPage(),
                    'last_page' => $products->lastPage(),
                    'per_page' => $products->perPage(),
                    'total' => $products->total(),
                ],
            ]);
        }

        $categories = $this->inventoryService->getActiveCategories();

        return Inertia::render('inventory/products/index', [
            'products' => $products,
            'categories' => $categories,
            'filters' => [
                'search' => $search,
                'category_id' => $categoryId,
                'supplier_id' => $supplierId,
            ],
        ]);
    }

    /**
     * Criar produto
     */
    public function storeProduct(Request $request): JsonResponse|RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'id_category' => 'required|exists:inventory_categories,id',
            'id_supplier' => 'nullable|exists:inventory_suppliers,id',
            'code' => 'nullable|string|max:255',
            'barcode' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'unit' => 'required|string|max:10',
            'minimum_stock' => 'required|numeric|min:0',
            'maximum_stock' => 'nullable|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0',
            'expiry_date' => 'nullable|date',
            'batch_number' => 'nullable|string|max:255',
            'storage_location' => 'nullable|string',
            'requires_prescription' => 'boolean',
            'controlled_substance' => 'boolean',
        ]);

        $data = $request->all();
        $data['id_company'] = auth()->user()->id_company;
        $data['current_stock'] = 0; // Inicia com estoque zero

        $product = $this->inventoryService->createProduct($data);

        if ($request->wantsJson()) {
            return (new InventoryProductResource($product))
                ->response()
                ->setStatusCode(201);
        }

        return redirect()->back()->with('success', 'Produto criado com sucesso!');
    }

    /**
     * Atualizar produto
     */
    public function updateProduct(Request $request, int $id): JsonResponse|RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'id_category' => 'required|exists:inventory_categories,id',
            'id_supplier' => 'nullable|exists:inventory_suppliers,id',
            'code' => 'nullable|string|max:255',
            'barcode' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'unit' => 'required|string|max:10',
            'minimum_stock' => 'required|numeric|min:0',
            'maximum_stock' => 'nullable|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0',
            'expiry_date' => 'nullable|date',
            'batch_number' => 'nullable|string|max:255',
            'storage_location' => 'nullable|string',
            'requires_prescription' => 'boolean',
            'controlled_substance' => 'boolean',
        ]);

        $product = $this->inventoryService->updateProduct($id, $request->all());

        if ($request->wantsJson()) {
            return (new InventoryProductResource($product))
                ->response()
                ->setStatusCode(200);
        }

        return redirect()->back()->with('success', 'Produto atualizado com sucesso!');
    }

    /**
     * Deletar produto
     */
    public function destroyProduct(int $id, Request $request): JsonResponse|RedirectResponse
    {
        $this->inventoryService->deleteProduct($id);

        if ($request->wantsJson()) {
            return response()->json(null, 204);
        }

        return redirect()->back()->with('success', 'Produto removido com sucesso!');
    }

    /**
     * Movimentações de estoque
     */
    public function movements(Request $request): \Inertia\Response
    {
        $productId = $request->get('product_id');
        $startDate = $request->get('start_date', now()->subMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));

        $movements = $this->inventoryService->getMovementsByDateRange($startDate, $endDate, $productId);
        $products = $this->inventoryService->getProductsWithFilters()->items();

        return Inertia::render('inventory/movements/index', [
            'movements' => $movements,
            'products' => $products,
            'filters' => [
                'product_id' => $productId,
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
        ]);
    }

    /**
     * Criar movimentação de estoque
     */
    public function storeMovement(Request $request)
    {
        $request->validate([
            'id_product' => 'required|exists:inventory_products,id',
            'type' => 'required|in:IN,OUT,ADJUSTMENT,TRANSFER,RETURN',
            'quantity' => 'required|numeric|min:0.01',
            'reason' => 'required|string|max:255',
            'unit_cost' => 'nullable|numeric|min:0',
            'document_number' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'batch_number' => 'nullable|string|max:255',
            'expiry_date' => 'nullable|date',
            'movement_date' => 'required|date',
        ]);

        $this->inventoryService->addStockMovement(
            $request->id_product,
            $request->type,
            $request->quantity,
            $request->reason,
            $request->unit_cost,
            $request->document_number,
            $request->notes,
            $request->batch_number,
            $request->expiry_date ? \Carbon\Carbon::parse($request->expiry_date) : null,
            \Carbon\Carbon::parse($request->movement_date)
        );

        return redirect()->back()->with('success', 'Movimentação registrada com sucesso!');
    }

    /**
     * Categorias
     */
    public function categories(Request $request): \Inertia\Response
    {
        $categories = $this->inventoryService->getCategoriesWithProductCount();

        return Inertia::render('inventory/categories/index', [
            'categories' => $categories,
        ]);
    }

    /**
     * Criar categoria
     */
    public function storeCategory(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'color' => 'required|string|size:7|regex:/^#[a-fA-F0-9]{6}$/',
        ]);

        $data = $request->all();
        $data['id_company'] = auth()->user()->id_company;

        $this->inventoryService->createCategory($data);

        return redirect()->back()->with('success', 'Categoria criada com sucesso!');
    }

    /**
     * Atualizar categoria
     */
    public function updateCategory(Request $request, int $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'color' => 'required|string|size:7|regex:/^#[a-fA-F0-9]{6}$/',
        ]);

        $this->inventoryService->updateCategory($id, $request->all());

        return redirect()->back()->with('success', 'Categoria atualizada com sucesso!');
    }

    /**
     * Deletar categoria
     */
    public function destroyCategory(int $id)
    {
        $this->inventoryService->deleteCategory($id);

        return redirect()->back()->with('success', 'Categoria removida com sucesso!');
    }

    /**
     * Alertas - produtos com estoque baixo ou vencidos
     */
    public function alerts(Request $request): \Inertia\Response
    {
        $lowStockProducts = $this->inventoryService->getLowStockProducts();
        $expiredProducts = $this->inventoryService->getExpiredProducts();
        $expiringSoonProducts = $this->inventoryService->getExpiringSoonProducts();

        return Inertia::render('inventory/alerts/index', [
            'lowStockProducts' => $lowStockProducts,
            'expiredProducts' => $expiredProducts,
            'expiringSoonProducts' => $expiringSoonProducts,
        ]);
    }

    /**
     * Relatórios
     */
    public function reports(Request $request): \Inertia\Response
    {
        $stockReport = $this->inventoryService->getStockReport();

        return Inertia::render('inventory/reports/index', [
            'stockReport' => $stockReport,
        ]);
    }
}