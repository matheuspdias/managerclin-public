<?php

namespace App\Http\Controllers\Traits\Swagger;

/**
 * @OA\Tag(
 *     name="Financial Transactions",
 *     description="Gerenciamento de transações financeiras"
 * )
 */
trait SwaggerFinancialTransactionDocs
{
    /**
     * @OA\Get(
     *     path="/api/financial/transactions",
     *     summary="Listar todas as transações",
     *     description="Retorna lista paginada de transações financeiras com filtros",
     *     operationId="getFinancialTransactions",
     *     tags={"Financial Transactions"},
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
     *         description="Buscar por descrição",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="type",
     *         in="query",
     *         description="Filtrar por tipo",
     *         required=false,
     *         @OA\Schema(type="string", enum={"INCOME", "EXPENSE", "TRANSFER"})
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filtrar por status",
     *         required=false,
     *         @OA\Schema(type="string", enum={"PENDING", "PAID", "OVERDUE", "CANCELLED"})
     *     ),
     *     @OA\Parameter(
     *         name="account_id",
     *         in="query",
     *         description="Filtrar por conta",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="category_id",
     *         in="query",
     *         description="Filtrar por categoria",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="start_date",
     *         in="query",
     *         description="Data inicial do período",
     *         required=false,
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="end_date",
     *         in="query",
     *         description="Data final do período",
     *         required=false,
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lista de transações retornada com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/FinancialTransaction")),
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
     *     path="/api/financial/transactions/{id}",
     *     summary="Exibir transação específica",
     *     description="Retorna detalhes de uma transação financeira",
     *     operationId="getFinancialTransactionById",
     *     tags={"Financial Transactions"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID da transação",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Transação encontrada",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", ref="#/components/schemas/FinancialTransaction")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Transação não encontrada"),
     *     @OA\Response(response=401, description="Não autenticado")
     * )
     */
    public function show() {}

    /**
     * @OA\Post(
     *     path="/api/financial/transactions",
     *     summary="Criar nova transação",
     *     description="Cria uma nova transação financeira",
     *     operationId="createFinancialTransaction",
     *     tags={"Financial Transactions"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"type", "amount", "description", "transaction_date", "id_financial_account", "id_financial_category"},
     *             @OA\Property(property="type", type="string", enum={"INCOME", "EXPENSE", "TRANSFER"}, example="INCOME"),
     *             @OA\Property(property="amount", type="number", format="float", minimum=0.01, example=150.00),
     *             @OA\Property(property="description", type="string", maxLength=255, example="Pagamento de consulta"),
     *             @OA\Property(property="transaction_date", type="string", format="date", example="2025-01-15"),
     *             @OA\Property(property="id_financial_account", type="integer", example=1, description="ID da conta financeira"),
     *             @OA\Property(property="id_financial_category", type="integer", example=1, description="ID da categoria financeira"),
     *             @OA\Property(property="due_date", type="string", format="date", example="2025-01-30"),
     *             @OA\Property(property="status", type="string", enum={"PENDING", "PAID", "OVERDUE", "CANCELLED"}, example="PAID"),
     *             @OA\Property(property="payment_method", type="string", enum={"CASH", "CARD", "TRANSFER", "PIX", "CHECK"}, example="PIX"),
     *             @OA\Property(property="document_number", type="string", maxLength=100, example="NF-001"),
     *             @OA\Property(property="notes", type="string", example="Observações sobre a transação"),
     *             @OA\Property(property="id_customer", type="integer", example=1, description="ID do cliente relacionado"),
     *             @OA\Property(property="id_appointment", type="integer", example=1, description="ID do agendamento relacionado"),
     *             @OA\Property(property="id_transfer_account", type="integer", example=2, description="ID da conta de destino (apenas para transferências)")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Transação criada com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", ref="#/components/schemas/FinancialTransaction")
     *         )
     *     ),
     *     @OA\Response(response=422, description="Erro de validação"),
     *     @OA\Response(response=401, description="Não autenticado")
     * )
     */
    public function store() {}

    /**
     * @OA\Put(
     *     path="/api/financial/transactions/{id}",
     *     summary="Atualizar transação",
     *     description="Atualiza dados de uma transação financeira existente",
     *     operationId="updateFinancialTransaction",
     *     tags={"Financial Transactions"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID da transação",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"type", "amount", "description", "transaction_date", "id_financial_account", "id_financial_category"},
     *             @OA\Property(property="type", type="string", enum={"INCOME", "EXPENSE", "TRANSFER"}),
     *             @OA\Property(property="amount", type="number", format="float", minimum=0.01),
     *             @OA\Property(property="description", type="string", maxLength=255),
     *             @OA\Property(property="transaction_date", type="string", format="date"),
     *             @OA\Property(property="id_financial_account", type="integer"),
     *             @OA\Property(property="id_financial_category", type="integer"),
     *             @OA\Property(property="due_date", type="string", format="date"),
     *             @OA\Property(property="status", type="string", enum={"PENDING", "PAID", "OVERDUE", "CANCELLED"}),
     *             @OA\Property(property="payment_method", type="string", enum={"CASH", "CARD", "TRANSFER", "PIX", "CHECK"}),
     *             @OA\Property(property="document_number", type="string", maxLength=100),
     *             @OA\Property(property="notes", type="string"),
     *             @OA\Property(property="id_customer", type="integer"),
     *             @OA\Property(property="id_appointment", type="integer"),
     *             @OA\Property(property="id_transfer_account", type="integer")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Transação atualizada com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", ref="#/components/schemas/FinancialTransaction")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Transação não encontrada"),
     *     @OA\Response(response=422, description="Erro de validação"),
     *     @OA\Response(response=401, description="Não autenticado")
     * )
     */
    public function update() {}

    /**
     * @OA\Delete(
     *     path="/api/financial/transactions/{id}",
     *     summary="Deletar transação",
     *     description="Remove uma transação financeira do sistema (soft delete)",
     *     operationId="deleteFinancialTransaction",
     *     tags={"Financial Transactions"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID da transação",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Transação deletada com sucesso"
     *     ),
     *     @OA\Response(response=404, description="Transação não encontrada"),
     *     @OA\Response(response=401, description="Não autenticado")
     * )
     */
    public function destroy() {}
}
