<?php

namespace App\Http\Controllers\Traits\Swagger;

/**
 * @OA\Tag(
 *     name="Customers",
 *     description="Gerenciamento de clientes/pacientes"
 * )
 */
trait SwaggerCustomerDocs
{
    /**
     * @OA\Get(
     *     path="/api/customers",
     *     summary="Listar todos os clientes",
     *     description="Retorna lista paginada de clientes",
     *     operationId="getCustomers",
     *     tags={"Customers"},
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
     *         @OA\Schema(type="integer", default=10)
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Buscar por nome, email ou telefone",
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
     *         description="Lista de clientes retornada com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Customer")),
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
     *     path="/api/customers/{id}",
     *     summary="Exibir cliente específico",
     *     description="Retorna detalhes de um cliente",
     *     operationId="getCustomerById",
     *     tags={"Customers"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID do cliente",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Cliente encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", ref="#/components/schemas/Customer")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Cliente não encontrado"),
     *     @OA\Response(response=401, description="Não autenticado")
     * )
     */
    public function show() {}

    /**
     * @OA\Post(
     *     path="/api/customers",
     *     summary="Criar novo cliente",
     *     description="Cria um novo cliente no sistema",
     *     operationId="createCustomer",
     *     tags={"Customers"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string", maxLength=255, example="João Silva"),
     *             @OA\Property(property="email", type="string", format="email", maxLength=255, example="joao@example.com"),
     *             @OA\Property(property="phone", type="string", maxLength=20, example="11999999999"),
     *             @OA\Property(property="birthdate", type="string", format="date", example="1990-01-15"),
     *             @OA\Property(property="cpf", type="string", maxLength=14, example="123.456.789-00"),
     *             @OA\Property(property="notes", type="string", maxLength=500, example="Observações sobre o cliente")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Cliente criado com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", ref="#/components/schemas/Customer")
     *         )
     *     ),
     *     @OA\Response(response=422, description="Erro de validação"),
     *     @OA\Response(response=401, description="Não autenticado")
     * )
     */
    public function store() {}

    /**
     * @OA\Put(
     *     path="/api/customers/{id}",
     *     summary="Atualizar cliente",
     *     description="Atualiza dados de um cliente existente",
     *     operationId="updateCustomer",
     *     tags={"Customers"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID do cliente",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", maxLength=255),
     *             @OA\Property(property="email", type="string", format="email", maxLength=255),
     *             @OA\Property(property="phone", type="string", maxLength=20),
     *             @OA\Property(property="birthdate", type="string", format="date"),
     *             @OA\Property(property="cpf", type="string", maxLength=14),
     *             @OA\Property(property="notes", type="string", maxLength=500)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Cliente atualizado com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", ref="#/components/schemas/Customer")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Cliente não encontrado"),
     *     @OA\Response(response=422, description="Erro de validação"),
     *     @OA\Response(response=401, description="Não autenticado")
     * )
     */
    public function update() {}

    /**
     * @OA\Delete(
     *     path="/api/customers/{id}",
     *     summary="Deletar cliente",
     *     description="Remove um cliente do sistema (soft delete)",
     *     operationId="deleteCustomer",
     *     tags={"Customers"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID do cliente",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Cliente deletado com sucesso"
     *     ),
     *     @OA\Response(response=404, description="Cliente não encontrado"),
     *     @OA\Response(response=401, description="Não autenticado")
     * )
     */
    public function destroy() {}
}
