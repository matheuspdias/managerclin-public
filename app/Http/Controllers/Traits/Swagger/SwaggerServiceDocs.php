<?php

namespace App\Http\Controllers\Traits\Swagger;

/**
 * @OA\Tag(
 *     name="Services",
 *     description="Gerenciamento de serviços"
 * )
 */
trait SwaggerServiceDocs
{
    /**
     * @OA\Get(
     *     path="/api/services",
     *     summary="Listar todos os serviços",
     *     description="Retorna lista paginada de serviços",
     *     operationId="getServices",
     *     tags={"Services"},
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
     *         description="Buscar por nome ou descrição",
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
     *         description="Lista de serviços retornada com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Service")),
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
     *     path="/api/services/{id}",
     *     summary="Exibir serviço específico",
     *     description="Retorna detalhes de um serviço",
     *     operationId="getServiceById",
     *     tags={"Services"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID do serviço",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Serviço encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", ref="#/components/schemas/Service")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Serviço não encontrado"),
     *     @OA\Response(response=401, description="Não autenticado")
     * )
     */
    public function show() {}

    /**
     * @OA\Post(
     *     path="/api/services",
     *     summary="Criar novo serviço",
     *     description="Cria um novo serviço no sistema",
     *     operationId="createService",
     *     tags={"Services"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "price"},
     *             @OA\Property(property="name", type="string", maxLength=255, example="Consulta Médica"),
     *             @OA\Property(property="description", type="string", maxLength=500, example="Consulta médica geral com duração de 30 minutos"),
     *             @OA\Property(property="price", type="number", format="float", minimum=0, example=150.00)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Serviço criado com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", ref="#/components/schemas/Service")
     *         )
     *     ),
     *     @OA\Response(response=422, description="Erro de validação"),
     *     @OA\Response(response=401, description="Não autenticado")
     * )
     */
    public function store() {}

    /**
     * @OA\Put(
     *     path="/api/services/{id}",
     *     summary="Atualizar serviço",
     *     description="Atualiza dados de um serviço existente",
     *     operationId="updateService",
     *     tags={"Services"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID do serviço",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", maxLength=255),
     *             @OA\Property(property="description", type="string", maxLength=500),
     *             @OA\Property(property="price", type="number", format="float", minimum=0)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Serviço atualizado com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", ref="#/components/schemas/Service")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Serviço não encontrado"),
     *     @OA\Response(response=422, description="Erro de validação"),
     *     @OA\Response(response=401, description="Não autenticado")
     * )
     */
    public function update() {}

    /**
     * @OA\Delete(
     *     path="/api/services/{id}",
     *     summary="Deletar serviço",
     *     description="Remove um serviço do sistema (soft delete)",
     *     operationId="deleteService",
     *     tags={"Services"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID do serviço",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Serviço deletado com sucesso"
     *     ),
     *     @OA\Response(response=404, description="Serviço não encontrado"),
     *     @OA\Response(response=401, description="Não autenticado")
     * )
     */
    public function destroy() {}
}
