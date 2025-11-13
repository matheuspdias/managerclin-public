<?php

namespace App\Http\Controllers\Traits\Swagger;

/**
 * @OA\Tag(
 *     name="Users",
 *     description="Gerenciamento de usuários"
 * )
 */
trait SwaggerUserDocs
{
    /**
     * @OA\Get(
     *     path="/api/users",
     *     summary="Listar todos os usuários",
     *     description="Retorna lista paginada de usuários",
     *     operationId="getUsers",
     *     tags={"Users"},
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
     *         description="Buscar por nome ou email",
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
     *         description="Lista de usuários retornada com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/User")),
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
     *     path="/api/users/{id}",
     *     summary="Exibir usuário específico",
     *     description="Retorna detalhes de um usuário",
     *     operationId="getUserById",
     *     tags={"Users"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID do usuário",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Usuário encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", ref="#/components/schemas/User")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Usuário não encontrado"),
     *     @OA\Response(response=401, description="Não autenticado")
     * )
     */
    public function show() {}

    /**
     * @OA\Post(
     *     path="/api/users",
     *     summary="Criar novo usuário",
     *     description="Cria um novo usuário no sistema",
     *     operationId="createUser",
     *     tags={"Users"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"id_role", "name", "email"},
     *             @OA\Property(property="id_role", type="integer", example=1, description="ID da função/cargo"),
     *             @OA\Property(property="name", type="string", maxLength=100, example="Dr. João Silva"),
     *             @OA\Property(property="email", type="string", format="email", example="joao@clinica.com"),
     *             @OA\Property(property="phone", type="string", example="11999999999")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Usuário criado com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", ref="#/components/schemas/User")
     *         )
     *     ),
     *     @OA\Response(response=422, description="Erro de validação"),
     *     @OA\Response(response=403, description="Limite de usuários atingido"),
     *     @OA\Response(response=401, description="Não autenticado")
     * )
     */
    public function store() {}

    /**
     * @OA\Put(
     *     path="/api/users/{id}",
     *     summary="Atualizar usuário",
     *     description="Atualiza dados de um usuário existente",
     *     operationId="updateUser",
     *     tags={"Users"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID do usuário",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="id_role", type="integer", description="ID da função/cargo"),
     *             @OA\Property(property="name", type="string", maxLength=100),
     *             @OA\Property(property="email", type="string", format="email"),
     *             @OA\Property(property="phone", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Usuário atualizado com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", ref="#/components/schemas/User")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Usuário não encontrado"),
     *     @OA\Response(response=422, description="Erro de validação"),
     *     @OA\Response(response=401, description="Não autenticado")
     * )
     */
    public function update() {}

    /**
     * @OA\Delete(
     *     path="/api/users/{id}",
     *     summary="Deletar usuário",
     *     description="Remove um usuário do sistema (soft delete)",
     *     operationId="deleteUser",
     *     tags={"Users"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID do usuário",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Usuário deletado com sucesso"
     *     ),
     *     @OA\Response(response=404, description="Usuário não encontrado"),
     *     @OA\Response(response=401, description="Não autenticado")
     * )
     */
    public function destroy() {}
}
