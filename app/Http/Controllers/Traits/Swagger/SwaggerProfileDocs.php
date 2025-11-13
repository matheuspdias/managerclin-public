<?php

namespace App\Http\Controllers\Traits\Swagger;

/**
 * @OA\Tag(
 *     name="Profile",
 *     description="Gerenciamento do perfil do usuário autenticado"
 * )
 */
trait SwaggerProfileDocs
{
    /**
     * @OA\Get(
     *     path="/api/profile",
     *     summary="Exibir perfil do usuário autenticado",
     *     description="Retorna os dados completos do perfil do usuário autenticado, incluindo nome, email, telefone, CRM, foto de perfil, role e dados da empresa.",
     *     operationId="getProfile",
     *     tags={"Profile"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Perfil retornado com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", ref="#/components/schemas/User",
     *                 description="Objeto completo do usuário com relacionamentos (role, company)"
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
    public function edit() {}

    /**
     * @OA\Post(
     *     path="/api/profile",
     *     summary="Atualizar perfil completo do usuário",
     *     description="Atualiza os dados do perfil do usuário autenticado, incluindo: nome, email, telefone, CRM e foto de perfil.
     *
     * **Funcionalidades:**
     * - ✅ Atualizar nome, email, telefone e CRM
     * - ✅ Upload de nova foto de perfil (remove automaticamente a foto anterior)
     * - ✅ Alteração de email reseta a verificação (email_verified_at será null)
     * - ✅ Suporta multipart/form-data para upload de imagem
     * - ✅ Validação de email único no sistema
     *
     * **Formatos aceitos:**
     * - multipart/form-data (quando enviando foto)
     * - application/json (quando NÃO enviando foto)
     *
     * **Importante:**
     * - A foto antiga é automaticamente deletada do storage ao enviar uma nova
     * - Se alterar o email, o usuário precisará verificá-lo novamente
     * - Todos os campos são opcionais, exceto name e email",
     *     operationId="updateProfile",
     *     tags={"Profile"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Dados do perfil a serem atualizados. Use multipart/form-data se enviar foto, ou application/json caso contrário.",
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"name", "email"},
     *                 @OA\Property(
     *                     property="name",
     *                     type="string",
     *                     maxLength=255,
     *                     example="Dr. João Silva",
     *                     description="Nome completo do usuário (obrigatório)"
     *                 ),
     *                 @OA\Property(
     *                     property="email",
     *                     type="string",
     *                     format="email",
     *                     maxLength=255,
     *                     example="joao.silva@clinica.com.br",
     *                     description="Email do usuário - deve ser único no sistema (obrigatório). ATENÇÃO: Alterar o email reseta a verificação."
     *                 ),
     *                 @OA\Property(
     *                     property="phone",
     *                     type="string",
     *                     maxLength=20,
     *                     example="(11) 98765-4321",
     *                     description="Telefone de contato do usuário (opcional)"
     *                 ),
     *                 @OA\Property(
     *                     property="crm",
     *                     type="string",
     *                     maxLength=50,
     *                     example="CRM/SP 123456",
     *                     description="Registro profissional CRM do médico (opcional)"
     *                 ),
     *                 @OA\Property(
     *                     property="image",
     *                     type="string",
     *                     format="binary",
     *                     description="Arquivo de imagem de perfil. Formatos: JPG, PNG, GIF. Tamanho máximo: 2MB. A foto anterior será automaticamente deletada. (opcional)"
     *                 )
     *             )
     *         ),
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 required={"name", "email"},
     *                 @OA\Property(
     *                     property="name",
     *                     type="string",
     *                     maxLength=255,
     *                     example="Dr. João Silva",
     *                     description="Nome completo do usuário"
     *                 ),
     *                 @OA\Property(
     *                     property="email",
     *                     type="string",
     *                     format="email",
     *                     maxLength=255,
     *                     example="joao.silva@clinica.com.br",
     *                     description="Email do usuário - deve ser único. Alterar reseta verificação."
     *                 ),
     *                 @OA\Property(
     *                     property="phone",
     *                     type="string",
     *                     maxLength=20,
     *                     example="(11) 98765-4321",
     *                     description="Telefone de contato"
     *                 ),
     *                 @OA\Property(
     *                     property="crm",
     *                     type="string",
     *                     maxLength=50,
     *                     example="CRM/SP 123456",
     *                     description="Registro CRM do médico"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Perfil atualizado com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Perfil atualizado com sucesso"),
     *             @OA\Property(
     *                 property="data",
     *                 ref="#/components/schemas/User",
     *                 description="Dados atualizados do usuário com relacionamentos"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erro de validação dos dados enviados",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Os dados fornecidos são inválidos."),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 description="Lista de erros de validação por campo",
     *                 @OA\Property(
     *                     property="email",
     *                     type="array",
     *                     @OA\Items(type="string", example="O e-mail já está em uso por outro usuário.")
     *                 ),
     *                 @OA\Property(
     *                     property="name",
     *                     type="array",
     *                     @OA\Items(type="string", example="O campo nome é obrigatório.")
     *                 ),
     *                 @OA\Property(
     *                     property="image",
     *                     type="array",
     *                     @OA\Items(type="string", example="A imagem não pode ter mais de 2MB.")
     *                 ),
     *                 @OA\Property(
     *                     property="phone",
     *                     type="array",
     *                     @OA\Items(type="string", example="O campo telefone não pode ter mais de 20 caracteres.")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Não autenticado - Token inválido ou expirado"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Empresa inativa - Acesso negado"
     *     )
     * )
     */
    public function update() {}

    /**
     * @OA\Post(
     *     path="/api/profile/photo",
     *     summary="Atualizar apenas a foto de perfil",
     *     description="Endpoint dedicado para atualizar exclusivamente a foto de perfil do usuário autenticado, sem alterar outros dados.
     *
     * **Funcionalidades:**
     * - ✅ Upload de nova foto de perfil
     * - ✅ Remove automaticamente a foto anterior do storage
     * - ✅ Não afeta outros dados do perfil (nome, email, etc)
     * - ✅ Ideal para apps mobile ou quando precisa atualizar só a foto
     *
     * **Formato:** multipart/form-data (obrigatório)
     * **Tamanho máximo:** 2MB
     * **Formatos aceitos:** JPG, PNG, GIF
     *
     * **Importante:** A foto antiga é permanentemente deletada do servidor ao enviar uma nova.",
     *     operationId="updateProfilePhoto",
     *     tags={"Profile"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Arquivo de imagem para atualizar a foto de perfil",
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"image"},
     *                 @OA\Property(
     *                     property="image",
     *                     type="string",
     *                     format="binary",
     *                     description="Arquivo de imagem. Formatos: JPG, PNG, GIF. Máximo: 2MB. (obrigatório)"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Foto de perfil atualizada com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Foto de perfil atualizada com sucesso"),
     *             @OA\Property(
     *                 property="data",
     *                 ref="#/components/schemas/User",
     *                 description="Dados atualizados do usuário com a nova foto"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erro de validação do arquivo enviado",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Os dados fornecidos são inválidos."),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 description="Detalhes dos erros de validação",
     *                 @OA\Property(
     *                     property="image",
     *                     type="array",
     *                     @OA\Items(
     *                         type="string",
     *                         example="A imagem é obrigatória.",
     *                         enum={"A imagem é obrigatória.", "O arquivo enviado deve ser uma imagem.", "A imagem não pode ter mais de 2MB."}
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Não autenticado - Token inválido ou expirado"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Empresa inativa - Acesso negado"
     *     )
     * )
     */
    public function updatePhoto() {}

    /**
     * @OA\Delete(
     *     path="/api/profile/photo",
     *     summary="Remover foto de perfil do usuário",
     *     description="Remove completamente a foto de perfil do usuário autenticado, deixando o perfil sem imagem.
     *
     * **Funcionalidades:**
     * - ✅ Remove permanentemente a foto do storage (servidor)
     * - ✅ Remove a referência da foto no banco de dados (image = null)
     * - ✅ Não afeta outros dados do perfil (nome, email, etc)
     * - ✅ Útil quando o usuário quer voltar ao avatar padrão
     *
     * **Importante:**
     * - A foto é deletada permanentemente e não pode ser recuperada
     * - Após remover, o perfil ficará sem foto até que uma nova seja enviada
     * - Esta operação é irreversível
     *
     * **Nenhum parâmetro é necessário**, apenas o token de autenticação no header.",
     *     operationId="deleteProfilePhoto",
     *     tags={"Profile"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Foto de perfil removida com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Foto de perfil removida com sucesso"),
     *             @OA\Property(
     *                 property="data",
     *                 ref="#/components/schemas/User",
     *                 description="Dados atualizados do usuário sem foto (image = null)"
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
    public function deletePhoto() {}

    /**
     * @OA\Get(
     *     path="/api/timezone",
     *     summary="Obter horários de atendimento do usuário",
     *     description="Retorna todos os horários de atendimento configurados para o usuário autenticado, organizados por dia da semana.
     *
     * **Funcionalidades:**
     * - ✅ Lista todos os horários ativos do usuário
     * - ✅ Múltiplos períodos por dia (ex: manhã e tarde)
     * - ✅ Controle de dias de trabalho (is_work)
     * - ✅ Horário de início e término para cada período
     *
     * **Estrutura de dados:**
     * - day_of_week: 0=Domingo, 1=Segunda, ..., 6=Sábado
     * - start_time: Hora de início (formato HH:MM:SS)
     * - end_time: Hora de término (formato HH:MM:SS)
     * - is_work: Se o dia está ativo para atendimento",
     *     operationId="getTimezone",
     *     tags={"Profile"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Horários retornados com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 description="Lista de horários configurados",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1, description="ID do horário"),
     *                     @OA\Property(property="day_of_week", type="integer", example=1, description="Dia da semana (0-6)", enum={0,1,2,3,4,5,6}),
     *                     @OA\Property(property="start_time", type="string", example="08:00:00", description="Hora de início"),
     *                     @OA\Property(property="end_time", type="string", example="12:00:00", description="Hora de término"),
     *                     @OA\Property(property="is_work", type="boolean", example=true, description="Se o período está ativo")
     *                 ),
     *                 example={
     *                     {"id": 1, "day_of_week": 1, "start_time": "08:00:00", "end_time": "12:00:00", "is_work": true},
     *                     {"id": 2, "day_of_week": 1, "start_time": "14:00:00", "end_time": "18:00:00", "is_work": true},
     *                     {"id": 3, "day_of_week": 2, "start_time": "08:00:00", "end_time": "17:00:00", "is_work": true}
     *                 }
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Não autenticado",
     *         @OA\JsonContent(@OA\Property(property="message", type="string", example="Unauthenticated."))
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Empresa inativa",
     *         @OA\JsonContent(@OA\Property(property="message", type="string", example="Empresa inativa. Entre em contato com o suporte."))
     *     )
     * )
     */
    public function timezone() {}

    /**
     * @OA\Patch(
     *     path="/api/timezone",
     *     summary="Atualizar horários de atendimento",
     *     description="Atualiza os horários de atendimento do usuário autenticado. Permite configurar múltiplos períodos por dia.
     *
     * **Funcionalidades:**
     * - ✅ Configurar horários para cada dia da semana
     * - ✅ Múltiplos períodos por dia (ex: manhã e tarde)
     * - ✅ Ativar/desativar dias de atendimento
     * - ✅ Validação de conflitos de horários
     *
     * **Regras:**
     * - Dias com is_work=true devem ter horários válidos
     * - start_time deve ser menor que end_time
     * - Não pode haver sobreposição de horários no mesmo dia
     * - Horários devem estar no formato HH:MM:SS
     *
     * **Importante:**
     * - Envie apenas os horários que deseja manter (is_work=true)
     * - Horários não enviados serão removidos
     * - A resposta retorna todos os horários atualizados",
     *     operationId="updateTimezone",
     *     tags={"Profile"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Array de horários a serem configurados",
     *         @OA\JsonContent(
     *             required={"schedules"},
     *             @OA\Property(
     *                 property="schedules",
     *                 type="array",
     *                 description="Lista de horários de atendimento",
     *                 @OA\Items(
     *                     type="object",
     *                     required={"day_of_week", "start_time", "end_time", "is_work"},
     *                     @OA\Property(
     *                         property="id",
     *                         type="integer",
     *                         example=1,
     *                         description="ID do horário (opcional, apenas para edição)"
     *                     ),
     *                     @OA\Property(
     *                         property="day_of_week",
     *                         type="integer",
     *                         example=1,
     *                         description="Dia da semana: 0=Domingo, 1=Segunda, 2=Terça, 3=Quarta, 4=Quinta, 5=Sexta, 6=Sábado",
     *                         enum={0,1,2,3,4,5,6}
     *                     ),
     *                     @OA\Property(
     *                         property="start_time",
     *                         type="string",
     *                         example="08:00:00",
     *                         description="Hora de início do atendimento (formato HH:MM:SS)"
     *                     ),
     *                     @OA\Property(
     *                         property="end_time",
     *                         type="string",
     *                         example="12:00:00",
     *                         description="Hora de término do atendimento (formato HH:MM:SS)"
     *                     ),
     *                     @OA\Property(
     *                         property="is_work",
     *                         type="boolean",
     *                         example=true,
     *                         description="Se o período está ativo para atendimento"
     *                     )
     *                 ),
     *                 example={
     *                     {"day_of_week": 1, "start_time": "08:00:00", "end_time": "12:00:00", "is_work": true},
     *                     {"day_of_week": 1, "start_time": "14:00:00", "end_time": "18:00:00", "is_work": true},
     *                     {"day_of_week": 2, "start_time": "08:00:00", "end_time": "17:00:00", "is_work": true},
     *                     {"day_of_week": 3, "start_time": "08:00:00", "end_time": "17:00:00", "is_work": true},
     *                     {"day_of_week": 4, "start_time": "08:00:00", "end_time": "17:00:00", "is_work": true},
     *                     {"day_of_week": 5, "start_time": "08:00:00", "end_time": "17:00:00", "is_work": true}
     *                 }
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Horários atualizados com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Horários atualizados com sucesso"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 description="Lista completa de horários após atualização",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="day_of_week", type="integer", example=1),
     *                     @OA\Property(property="start_time", type="string", example="08:00:00"),
     *                     @OA\Property(property="end_time", type="string", example="12:00:00"),
     *                     @OA\Property(property="is_work", type="boolean", example=true)
     *                 )
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
     *                 @OA\Property(
     *                     property="schedules",
     *                     type="array",
     *                     @OA\Items(
     *                         type="string",
     *                         example="O horário de início deve ser anterior ao horário de término."
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Não autenticado"),
     *     @OA\Response(response=403, description="Empresa inativa")
     * )
     */
    public function updateTimezone() {}
}
