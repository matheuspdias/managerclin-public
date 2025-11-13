<?php

namespace App\Http\Controllers\Traits\Swagger;

/**
 * @OA\Tag(
 *     name="Financial Categories",
 *     description="Gerenciamento de categorias financeiras"
 * )
 */
trait SwaggerFinancialCategoryDocs
{
    /**
     * @OA\Get(
     *     path="/api/financial/categories",
     *     summary="Listar todas as categorias financeiras",
     *     description="Retorna lista paginada de categorias financeiras",
     *     operationId="getFinancialCategories",
     *     tags={"Financial Categories"},
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
     *         description="Buscar por nome da categoria",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="order",
     *         in="query",
     *         description="Ordenação (campo:direção)",
     *         required=false,
     *         @OA\Schema(type="string", default="name:asc")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lista de categorias retornada com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/FinancialCategory")),
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
    public function index() {}

    /**
     * @OA\Get(
     *     path="/api/financial/categories/{id}",
     *     summary="Exibir categoria específica",
     *     description="Retorna detalhes de uma categoria financeira",
     *     operationId="getFinancialCategoryById",
     *     tags={"Financial Categories"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID da categoria",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Categoria encontrada",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", ref="#/components/schemas/FinancialCategory")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Categoria não encontrada"),
     *     @OA\Response(response=401, description="Não autenticado")
     * )
     */
    public function show() {}

    /**
     * @OA\Post(
     *     path="/api/financial/categories",
     *     summary="Criar nova categoria",
     *     description="Cria uma nova categoria financeira",
     *     operationId="createFinancialCategory",
     *     tags={"Financial Categories"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "type", "color"},
     *             @OA\Property(property="name", type="string", maxLength=255, example="Consultas"),
     *             @OA\Property(property="type", type="string", enum={"INCOME", "EXPENSE"}, example="INCOME"),
     *             @OA\Property(property="color", type="string", maxLength=7, pattern="^#[0-9A-Fa-f]{6}$", example="#4CAF50"),
     *             @OA\Property(property="icon", type="string", maxLength=50, example="medical"),
     *             @OA\Property(property="is_active", type="boolean", example=true),
     *             @OA\Property(property="description", type="string", example="Receitas de consultas médicas")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Categoria criada com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", ref="#/components/schemas/FinancialCategory")
     *         )
     *     ),
     *     @OA\Response(response=422, description="Erro de validação"),
     *     @OA\Response(response=401, description="Não autenticado")
     * )
     */
    public function store() {}

    /**
     * @OA\Put(
     *     path="/api/financial/categories/{id}",
     *     summary="Atualizar categoria",
     *     description="Atualiza dados de uma categoria financeira existente",
     *     operationId="updateFinancialCategory",
     *     tags={"Financial Categories"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID da categoria",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "type", "color"},
     *             @OA\Property(property="name", type="string", maxLength=255),
     *             @OA\Property(property="type", type="string", enum={"INCOME", "EXPENSE"}),
     *             @OA\Property(property="color", type="string", maxLength=7, pattern="^#[0-9A-Fa-f]{6}$"),
     *             @OA\Property(property="icon", type="string", maxLength=50),
     *             @OA\Property(property="is_active", type="boolean"),
     *             @OA\Property(property="description", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Categoria atualizada com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", ref="#/components/schemas/FinancialCategory")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Categoria não encontrada"),
     *     @OA\Response(response=422, description="Erro de validação"),
     *     @OA\Response(response=401, description="Não autenticado")
     * )
     */
    public function update() {}

    /**
     * @OA\Delete(
     *     path="/api/financial/categories/{id}",
     *     summary="Deletar categoria",
     *     description="Remove uma categoria financeira do sistema (soft delete)",
     *     operationId="deleteFinancialCategory",
     *     tags={"Financial Categories"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID da categoria",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Categoria deletada com sucesso"
     *     ),
     *     @OA\Response(response=404, description="Categoria não encontrada"),
     *     @OA\Response(response=401, description="Não autenticado")
     * )
     */
    public function destroy() {}
}
