<?php

namespace App\Http\Controllers\Traits\Swagger;

/**
 * @OA\Tag(
 *     name="Authentication",
 *     description="Autenticação de usuários via API"
 * )
 */
trait SwaggerAuthDocs
{
    /**
     * @OA\Post(
     *     path="/api/auth/login",
     *     summary="Login de usuário",
     *     description="Autentica um usuário e retorna token Bearer para uso nos demais endpoints",
     *     operationId="authLogin",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email", "password"},
     *             @OA\Property(property="email", type="string", format="email", example="admin@example.com", description="Email do usuário"),
     *             @OA\Property(property="password", type="string", format="password", example="password123", description="Senha do usuário"),
     *             @OA\Property(property="device_name", type="string", example="iPhone 13", description="Nome do dispositivo (opcional)")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Login realizado com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Login realizado com sucesso"),
     *             @OA\Property(property="user", ref="#/components/schemas/User"),
     *             @OA\Property(property="token", type="string", example="1|abcdef123456..."),
     *             @OA\Property(property="token_type", type="string", example="Bearer")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Credenciais inválidas",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Os dados fornecidos são inválidos."),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(
     *                     property="email",
     *                     type="array",
     *                     @OA\Items(type="string", example="As credenciais fornecidas estão incorretas.")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Empresa inativa",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Empresa inativa. Entre em contato com o suporte.")
     *         )
     *     )
     * )
     */
    public function login() {}

    /**
     * @OA\Post(
     *     path="/api/auth/logout",
     *     summary="Logout de usuário",
     *     description="Revoga o token atual do usuário autenticado",
     *     operationId="authLogout",
     *     tags={"Authentication"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Logout realizado com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Logout realizado com sucesso")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Não autenticado")
     * )
     */
    public function logout() {}

    /**
     * @OA\Post(
     *     path="/api/auth/logout-all",
     *     summary="Logout de todos os dispositivos",
     *     description="Revoga todos os tokens do usuário autenticado (faz logout de todos os dispositivos)",
     *     operationId="authLogoutAll",
     *     tags={"Authentication"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Logout de todos os dispositivos realizado com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Logout de todos os dispositivos realizado com sucesso")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Não autenticado")
     * )
     */
    public function logoutAll() {}

    /**
     * @OA\Get(
     *     path="/api/auth/me",
     *     summary="Dados do usuário autenticado",
     *     description="Retorna os dados completos do usuário autenticado",
     *     operationId="authMe",
     *     tags={"Authentication"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Dados do usuário retornados com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="user", ref="#/components/schemas/User")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Não autenticado")
     * )
     */
    public function me() {}
}
