<?php

namespace App\Http\Controllers\Traits\Swagger;

/**
 * @OA\Tag(
 *     name="Rooms",
 *     description="Gerenciamento de salas"
 * )
 */
trait SwaggerRoomDocs
{
    /**
     * @OA\Get(
     *     path="/api/rooms",
     *     summary="Listar todas as salas",
     *     description="Retorna lista paginada de salas",
     *     operationId="getRooms",
     *     tags={"Rooms"},
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
     *         description="Buscar por nome ou localização",
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
     *         description="Lista de salas retornada com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Room")),
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
     *     path="/api/rooms/{id}",
     *     summary="Exibir sala específica",
     *     description="Retorna detalhes de uma sala",
     *     operationId="getRoomById",
     *     tags={"Rooms"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID da sala",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Sala encontrada",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", ref="#/components/schemas/Room")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Sala não encontrada"),
     *     @OA\Response(response=401, description="Não autenticado")
     * )
     */
    public function show() {}

    /**
     * @OA\Post(
     *     path="/api/rooms",
     *     summary="Criar nova sala",
     *     description="Cria uma nova sala no sistema",
     *     operationId="createRoom",
     *     tags={"Rooms"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string", maxLength=255, example="Consultório 1"),
     *             @OA\Property(property="location", type="string", maxLength=255, example="Primeiro Andar")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Sala criada com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", ref="#/components/schemas/Room")
     *         )
     *     ),
     *     @OA\Response(response=422, description="Erro de validação"),
     *     @OA\Response(response=401, description="Não autenticado")
     * )
     */
    public function store() {}

    /**
     * @OA\Put(
     *     path="/api/rooms/{id}",
     *     summary="Atualizar sala",
     *     description="Atualiza dados de uma sala existente",
     *     operationId="updateRoom",
     *     tags={"Rooms"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID da sala",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", maxLength=255),
     *             @OA\Property(property="location", type="string", maxLength=255)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Sala atualizada com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", ref="#/components/schemas/Room")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Sala não encontrada"),
     *     @OA\Response(response=422, description="Erro de validação"),
     *     @OA\Response(response=401, description="Não autenticado")
     * )
     */
    public function update() {}

    /**
     * @OA\Delete(
     *     path="/api/rooms/{id}",
     *     summary="Deletar sala",
     *     description="Remove uma sala do sistema (soft delete)",
     *     operationId="deleteRoom",
     *     tags={"Rooms"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID da sala",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Sala deletada com sucesso"
     *     ),
     *     @OA\Response(response=404, description="Sala não encontrada"),
     *     @OA\Response(response=401, description="Não autenticado")
     * )
     */
    public function destroy() {}
}
