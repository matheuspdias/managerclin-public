<?php

namespace App\Http\Controllers\Traits\Swagger;

/**
 * @OA\Tag(
 *     name="Inventory",
 *     description="Gerenciamento de estoque e produtos"
 * )
 */
trait SwaggerInventoryDocs
{
    /**
     * @OA\Get(
     *     path="/api/inventory/products",
     *     summary="Listar todos os produtos",
     *     description="Retorna lista paginada de produtos do estoque",
     *     operationId="getInventoryProducts",
     *     tags={"Inventory"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Número da página",
     *         required=false,
     *         @OA\Schema(type="integer", default=1)
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Itens por página",
     *         required=false,
     *         @OA\Schema(type="integer", default=15)
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Buscar por nome ou código do produto",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="category_id",
     *         in="query",
     *         description="Filtrar por categoria",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="supplier_id",
     *         in="query",
     *         description="Filtrar por fornecedor",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lista de produtos retornada com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/InventoryProduct")),
     *             @OA\Property(property="meta", type="object",
     *                 @OA\Property(property="current_page", type="integer"),
     *                 @OA\Property(property="last_page", type="integer"),
     *                 @OA\Property(property="per_page", type="integer"),
     *                 @OA\Property(property="total", type="integer")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Não autenticado"),
     *     @OA\Response(response=403, description="Empresa inativa")
     * )
     */
    public function products() {}

    /**
     * @OA\Post(
     *     path="/api/inventory/products",
     *     summary="Criar novo produto",
     *     description="Cria um novo produto no estoque",
     *     operationId="createInventoryProduct",
     *     tags={"Inventory"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "id_category", "unit", "minimum_stock"},
     *             @OA\Property(property="name", type="string", maxLength=255, example="Luva de procedimento"),
     *             @OA\Property(property="id_category", type="integer", example=1, description="ID da categoria do produto"),
     *             @OA\Property(property="id_supplier", type="integer", example=1, description="ID do fornecedor"),
     *             @OA\Property(property="code", type="string", maxLength=255, example="PROD-001"),
     *             @OA\Property(property="barcode", type="string", maxLength=255, example="7891234567890"),
     *             @OA\Property(property="description", type="string", example="Luva de procedimento tamanho M"),
     *             @OA\Property(property="unit", type="string", maxLength=10, example="CX", description="Unidade de medida"),
     *             @OA\Property(property="minimum_stock", type="number", format="float", minimum=0, example=10.00),
     *             @OA\Property(property="maximum_stock", type="number", format="float", minimum=0, example=100.00),
     *             @OA\Property(property="cost_price", type="number", format="float", minimum=0, example=25.50),
     *             @OA\Property(property="sale_price", type="number", format="float", minimum=0, example=35.00),
     *             @OA\Property(property="expiry_date", type="string", format="date", example="2026-12-31"),
     *             @OA\Property(property="batch_number", type="string", maxLength=255, example="LOTE-2025-01"),
     *             @OA\Property(property="storage_location", type="string", example="Armário A - Prateleira 2"),
     *             @OA\Property(property="requires_prescription", type="boolean", example=false),
     *             @OA\Property(property="controlled_substance", type="boolean", example=false)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Produto criado com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", ref="#/components/schemas/InventoryProduct")
     *         )
     *     ),
     *     @OA\Response(response=422, description="Erro de validação"),
     *     @OA\Response(response=401, description="Não autenticado")
     * )
     */
    public function storeProduct() {}

    /**
     * @OA\Put(
     *     path="/api/inventory/products/{id}",
     *     summary="Atualizar produto",
     *     description="Atualiza dados de um produto existente no estoque",
     *     operationId="updateInventoryProduct",
     *     tags={"Inventory"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID do produto",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "id_category", "unit", "minimum_stock"},
     *             @OA\Property(property="name", type="string", maxLength=255),
     *             @OA\Property(property="id_category", type="integer"),
     *             @OA\Property(property="id_supplier", type="integer"),
     *             @OA\Property(property="code", type="string", maxLength=255),
     *             @OA\Property(property="barcode", type="string", maxLength=255),
     *             @OA\Property(property="description", type="string"),
     *             @OA\Property(property="unit", type="string", maxLength=10),
     *             @OA\Property(property="minimum_stock", type="number", format="float", minimum=0),
     *             @OA\Property(property="maximum_stock", type="number", format="float", minimum=0),
     *             @OA\Property(property="cost_price", type="number", format="float", minimum=0),
     *             @OA\Property(property="sale_price", type="number", format="float", minimum=0),
     *             @OA\Property(property="expiry_date", type="string", format="date"),
     *             @OA\Property(property="batch_number", type="string", maxLength=255),
     *             @OA\Property(property="storage_location", type="string"),
     *             @OA\Property(property="requires_prescription", type="boolean"),
     *             @OA\Property(property="controlled_substance", type="boolean")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Produto atualizado com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", ref="#/components/schemas/InventoryProduct")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Produto não encontrado"),
     *     @OA\Response(response=422, description="Erro de validação"),
     *     @OA\Response(response=401, description="Não autenticado")
     * )
     */
    public function updateProduct() {}

    /**
     * @OA\Delete(
     *     path="/api/inventory/products/{id}",
     *     summary="Deletar produto",
     *     description="Remove um produto do estoque (soft delete)",
     *     operationId="deleteInventoryProduct",
     *     tags={"Inventory"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID do produto",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Produto deletado com sucesso"
     *     ),
     *     @OA\Response(response=404, description="Produto não encontrado"),
     *     @OA\Response(response=401, description="Não autenticado")
     * )
     */
    public function destroyProduct() {}
}
