<?php

namespace App\Http\Controllers\Traits\Swagger;

/**
 * @OA\Tag(
 *     name="Password",
 *     description="Gerenciamento de senha do usuário autenticado"
 * )
 */
trait SwaggerPasswordDocs
{
    /**
     * @OA\Put(
     *     path="/api/password",
     *     summary="Atualizar senha do usuário",
     *     description="Atualiza a senha do usuário autenticado. Requer a senha atual para validação de segurança.
     *
     * **Funcionalidades:**
     * - ✅ Validação da senha atual antes de permitir alteração
     * - ✅ Validação de força da nova senha (mínimo 8 caracteres)
     * - ✅ Confirmação da nova senha (password_confirmation)
     * - ✅ Segurança: Hash BCrypt para armazenamento
     *
     * **Regras de senha:**
     * - Mínimo de 8 caracteres
     * - Deve confirmar a nova senha (campo password_confirmation)
     * - A senha atual deve estar correta
     *
     * **Importante:**
     * - Após trocar a senha, o usuário não será deslogado
     * - Todos os tokens de API permanecem válidos
     * - A senha antiga não pode mais ser utilizada",
     *     operationId="updatePassword",
     *     tags={"Password"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Dados para atualização de senha",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 required={"current_password", "password", "password_confirmation"},
     *                 @OA\Property(
     *                     property="current_password",
     *                     type="string",
     *                     format="password",
     *                     example="senha_atual_123",
     *                     description="Senha atual do usuário para validação (obrigatório)"
     *                 ),
     *                 @OA\Property(
     *                     property="password",
     *                     type="string",
     *                     format="password",
     *                     minLength=8,
     *                     example="nova_senha_segura_456",
     *                     description="Nova senha desejada - mínimo 8 caracteres (obrigatório)"
     *                 ),
     *                 @OA\Property(
     *                     property="password_confirmation",
     *                     type="string",
     *                     format="password",
     *                     example="nova_senha_segura_456",
     *                     description="Confirmação da nova senha - deve ser idêntica ao campo 'password' (obrigatório)"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Senha atualizada com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Senha atualizada com sucesso"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erro de validação",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Os dados fornecidos são inválidos."),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 description="Detalhes dos erros de validação",
     *                 @OA\Property(
     *                     property="current_password",
     *                     type="array",
     *                     @OA\Items(
     *                         type="string",
     *                         example="A senha atual está incorreta.",
     *                         enum={"A senha atual é obrigatória.", "A senha atual está incorreta."}
     *                     )
     *                 ),
     *                 @OA\Property(
     *                     property="password",
     *                     type="array",
     *                     @OA\Items(
     *                         type="string",
     *                         example="A nova senha é obrigatória.",
     *                         enum={"A nova senha é obrigatória.", "A senha deve ter no mínimo 8 caracteres."}
     *                     )
     *                 ),
     *                 @OA\Property(
     *                     property="password_confirmation",
     *                     type="array",
     *                     @OA\Items(
     *                         type="string",
     *                         example="A confirmação da senha não confere."
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Não autenticado - Token inválido ou expirado",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Empresa inativa - Acesso negado",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Empresa inativa. Entre em contato com o suporte.")
     *         )
     *     )
     * )
     */
    public function update() {}
}
