<?php

namespace App\Http\Controllers\Traits\Swagger;

/**
 * @OA\Tag(
 *     name="Financial Accounts",
 *     description="Gerenciamento de contas financeiras"
 * )
 */
trait SwaggerFinancialAccountDocs
{
    /**
     * @OA\Get(
     *     path="/api/financial/accounts",
     *     summary="Listar todas as contas financeiras",
     *     description="Retorna lista paginada de contas financeiras",
     *     operationId="getFinancialAccounts",
     *     tags={"Financial Accounts"},
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
     *         description="Buscar por nome da conta",
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
     *         description="Lista de contas retornada com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/FinancialAccount")),
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
     *     path="/api/financial/accounts/{id}",
     *     summary="Exibir conta específica",
     *     description="Retorna detalhes de uma conta financeira",
     *     operationId="getFinancialAccountById",
     *     tags={"Financial Accounts"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID da conta",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Conta encontrada",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", ref="#/components/schemas/FinancialAccount")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Conta não encontrada"),
     *     @OA\Response(response=401, description="Não autenticado")
     * )
     */
    public function show() {}

    /**
     * @OA\Post(
     *     path="/api/financial/accounts",
     *     summary="Criar nova conta",
     *     description="Cria uma nova conta financeira",
     *     operationId="createFinancialAccount",
     *     tags={"Financial Accounts"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "type", "initial_balance"},
     *             @OA\Property(property="name", type="string", maxLength=255, example="Conta Corrente Banco do Brasil"),
     *             @OA\Property(property="type", type="string", enum={"CHECKING", "SAVINGS", "CASH", "CREDIT_CARD"}, example="CHECKING"),
     *             @OA\Property(property="bank_name", type="string", maxLength=255, example="Banco do Brasil"),
     *             @OA\Property(property="account_number", type="string", maxLength=100, example="12345-6"),
     *             @OA\Property(property="initial_balance", type="number", format="float", example=5000.00),
     *             @OA\Property(property="is_active", type="boolean", example=true),
     *             @OA\Property(property="description", type="string", example="Conta principal da clínica")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Conta criada com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", ref="#/components/schemas/FinancialAccount")
     *         )
     *     ),
     *     @OA\Response(response=422, description="Erro de validação"),
     *     @OA\Response(response=401, description="Não autenticado")
     * )
     */
    public function store() {}

    /**
     * @OA\Put(
     *     path="/api/financial/accounts/{id}",
     *     summary="Atualizar conta",
     *     description="Atualiza dados de uma conta financeira existente",
     *     operationId="updateFinancialAccount",
     *     tags={"Financial Accounts"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID da conta",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "type", "initial_balance"},
     *             @OA\Property(property="name", type="string", maxLength=255),
     *             @OA\Property(property="type", type="string", enum={"CHECKING", "SAVINGS", "CASH", "CREDIT_CARD"}),
     *             @OA\Property(property="bank_name", type="string", maxLength=255),
     *             @OA\Property(property="account_number", type="string", maxLength=100),
     *             @OA\Property(property="initial_balance", type="number", format="float"),
     *             @OA\Property(property="is_active", type="boolean"),
     *             @OA\Property(property="description", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Conta atualizada com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", ref="#/components/schemas/FinancialAccount")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Conta não encontrada"),
     *     @OA\Response(response=422, description="Erro de validação"),
     *     @OA\Response(response=401, description="Não autenticado")
     * )
     */
    public function update() {}

    /**
     * @OA\Delete(
     *     path="/api/financial/accounts/{id}",
     *     summary="Deletar conta",
     *     description="Remove uma conta financeira do sistema (soft delete)",
     *     operationId="deleteFinancialAccount",
     *     tags={"Financial Accounts"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID da conta",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Conta deletada com sucesso"
     *     ),
     *     @OA\Response(response=404, description="Conta não encontrada"),
     *     @OA\Response(response=401, description="Não autenticado")
     * )
     */
    public function destroy() {}
}
