<?php

namespace App\Http\Controllers\Traits\Swagger;

/**
 * @OA\Tag(
 *     name="Telemedicine",
 *     description="Gerenciamento de sessões de telemedicina (Jitsi/JaaS)"
 * )
 */
trait SwaggerTelemedicineDocs
{
    /**
     * @OA\Get(
     *     path="/api/telemedicine/config",
     *     summary="Obter configurações de telemedicina",
     *     description="Retorna configurações do Jitsi/JaaS para inicializar videoconferência no frontend",
     *     operationId="getTelemedicineConfig",
     *     tags={"Telemedicine"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Configurações obtidas com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="provider", type="string", enum={"jaas", "jitsi", "custom"}, example="jaas", description="Provedor de videoconferência"),
     *                 @OA\Property(property="server_url", type="string", example="https://8x8.vc", description="URL do servidor Jitsi"),
     *                 @OA\Property(property="app_id", type="string", example="vpaas-magic-cookie-xxx", description="App ID do JaaS (apenas se provider=jaas)"),
     *                 @OA\Property(property="jitsi_config", type="object", description="Configurações customizadas do Jitsi"),
     *                 @OA\Property(property="interface_config", type="object", description="Configurações da interface do Jitsi")
     *             ),
     *             @OA\Property(property="message", type="string", example="Configurações obtidas com sucesso.")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Não autenticado"),
     *     @OA\Response(response=500, description="Erro ao obter configurações")
     * )
     */
    public function getConfig() {}

    /**
     * @OA\Get(
     *     path="/api/telemedicine/credits",
     *     summary="Obter créditos de telemedicina disponíveis",
     *     description="Retorna informações sobre os créditos de telemedicina da empresa autenticada (disponíveis, limite do plano, adicionais comprados)",
     *     operationId="getTelemedicineCredits",
     *     tags={"Telemedicine"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Informações de créditos obtidas com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="credits_available", type="integer", example=35, description="Créditos disponíveis no momento"),
     *                 @OA\Property(property="has_credits", type="boolean", example=true, description="Se tem créditos disponíveis"),
     *                 @OA\Property(property="plan_limit", type="integer", example=50, description="Limite mensal do plano contratado"),
     *                 @OA\Property(property="additional_credits", type="integer", example=10, description="Créditos adicionais comprados"),
     *                 @OA\Property(property="plan_name", type="string", example="Pro", description="Nome do plano (Essencial/Pro/Premium)"),
     *                 @OA\Property(property="last_purchase", type="string", format="date-time", example="2025-01-15T10:30:00Z", description="Data da última compra de créditos adicionais"),
     *                 @OA\Property(property="status", type="string", enum={"available", "depleted"}, example="available", description="Status dos créditos")
     *             ),
     *             @OA\Property(property="message", type="string", example="Informações de créditos obtidas com sucesso.")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Não autenticado"),
     *     @OA\Response(response=404, description="Empresa não encontrada"),
     *     @OA\Response(response=500, description="Erro ao obter créditos")
     * )
     */
    public function getCredits() {}

    /**
     * @OA\Post(
     *     path="/api/telemedicine/sessions",
     *     summary="Criar nova sessão de telemedicina",
     *     description="Cria uma sessão de telemedicina vinculada a um agendamento. Se já existir sessão ativa/aguardando, retorna a existente.",
     *     operationId="createTelemedicineSession",
     *     tags={"Telemedicine"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"appointment_id"},
     *             @OA\Property(property="appointment_id", type="integer", example=123, description="ID do agendamento"),
     *             @OA\Property(property="server_url", type="string", example="https://8x8.vc", description="URL customizada do servidor (opcional)")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Sessão criada com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="session_id", type="integer", example=1),
     *                 @OA\Property(property="room_name", type="string", example="consultation-123-abc456"),
     *                 @OA\Property(property="server_url", type="string", example="https://8x8.vc"),
     *                 @OA\Property(property="join_url", type="string", example="https://8x8.vc/vpaas-magic-cookie-xxx/consultation-123-abc"),
     *                 @OA\Property(property="status", type="string", enum={"WAITING", "ACTIVE", "COMPLETED", "CANCELLED"}, example="WAITING"),
     *                 @OA\Property(property="appointment", type="object",
     *                     @OA\Property(property="id", type="integer"),
     *                     @OA\Property(property="date", type="string", format="date"),
     *                     @OA\Property(property="start_time", type="string"),
     *                     @OA\Property(property="doctor", type="object"),
     *                     @OA\Property(property="customer", type="object")
     *                 )
     *             ),
     *             @OA\Property(property="message", type="string", example="Sessão de telemedicina criada com sucesso.")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Agendamento não encontrado"),
     *     @OA\Response(response=401, description="Não autenticado"),
     *     @OA\Response(response=500, description="Erro ao criar sessão")
     * )
     */
    public function createSession() {}

    /**
     * @OA\Get(
     *     path="/api/telemedicine/sessions/appointment/{appointmentId}",
     *     summary="Buscar sessão por agendamento",
     *     description="Retorna a última sessão de telemedicina vinculada a um agendamento",
     *     operationId="getTelemedicineSessionByAppointment",
     *     tags={"Telemedicine"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="appointmentId",
     *         in="path",
     *         description="ID do agendamento",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Sessão encontrada com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="session_id", type="integer"),
     *                 @OA\Property(property="room_name", type="string"),
     *                 @OA\Property(property="server_url", type="string"),
     *                 @OA\Property(property="join_url", type="string"),
     *                 @OA\Property(property="status", type="string"),
     *                 @OA\Property(property="started_at", type="string", format="date-time", nullable=true),
     *                 @OA\Property(property="ended_at", type="string", format="date-time", nullable=true),
     *                 @OA\Property(property="duration_minutes", type="integer"),
     *                 @OA\Property(property="notes", type="string", nullable=true),
     *                 @OA\Property(property="appointment", type="object")
     *             ),
     *             @OA\Property(property="message", type="string", example="Sessão encontrada com sucesso.")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Sessão não encontrada"),
     *     @OA\Response(response=401, description="Não autenticado")
     * )
     */
    public function getSessionByAppointment() {}

    /**
     * @OA\Get(
     *     path="/api/telemedicine/sessions/active",
     *     summary="Listar sessões ativas",
     *     description="Retorna lista paginada de sessões de telemedicina ativas",
     *     operationId="getActiveTelemedicineSessions",
     *     tags={"Telemedicine"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="doctor_id",
     *         in="query",
     *         description="Filtrar por ID do médico",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
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
     *         @OA\Schema(type="integer", default=15, maximum=100)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Sessões ativas listadas com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array", @OA\Items(
     *                 @OA\Property(property="session_id", type="integer"),
     *                 @OA\Property(property="room_name", type="string"),
     *                 @OA\Property(property="join_url", type="string"),
     *                 @OA\Property(property="status", type="string"),
     *                 @OA\Property(property="started_at", type="string", format="date-time"),
     *                 @OA\Property(property="current_duration", type="integer", description="Duração atual em minutos"),
     *                 @OA\Property(property="appointment", type="object")
     *             )),
     *             @OA\Property(property="pagination", type="object",
     *                 @OA\Property(property="current_page", type="integer"),
     *                 @OA\Property(property="per_page", type="integer"),
     *                 @OA\Property(property="total", type="integer"),
     *                 @OA\Property(property="last_page", type="integer")
     *             ),
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Não autenticado")
     * )
     */
    public function getActiveSessions() {}

    /**
     * @OA\Patch(
     *     path="/api/telemedicine/sessions/{sessionId}",
     *     summary="Atualizar status da sessão",
     *     description="Atualiza o status da sessão. Ao mudar para ACTIVE, registra started_at. Ao mudar para COMPLETED/CANCELLED, registra ended_at e calcula duração.",
     *     operationId="updateTelemedicineSessionStatus",
     *     tags={"Telemedicine"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="sessionId",
     *         in="path",
     *         description="ID da sessão",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"status"},
     *             @OA\Property(property="status", type="string", enum={"ACTIVE", "COMPLETED", "CANCELLED"}, example="ACTIVE", description="Novo status da sessão"),
     *             @OA\Property(property="notes", type="string", maxLength=5000, description="Observações (opcional)")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Status atualizado com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="session_id", type="integer"),
     *                 @OA\Property(property="status", type="string"),
     *                 @OA\Property(property="started_at", type="string", format="date-time", nullable=true),
     *                 @OA\Property(property="ended_at", type="string", format="date-time", nullable=true),
     *                 @OA\Property(property="duration_minutes", type="integer"),
     *                 @OA\Property(property="notes", type="string", nullable=true)
     *             ),
     *             @OA\Property(property="message", type="string", example="Status da sessão atualizado com sucesso.")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Sessão não encontrada"),
     *     @OA\Response(response=422, description="Erro de validação"),
     *     @OA\Response(response=401, description="Não autenticado")
     * )
     */
    public function updateSessionStatus() {}

    /**
     * @OA\Post(
     *     path="/api/telemedicine/sessions/{sessionId}/end",
     *     summary="Finalizar sessão",
     *     description="Finaliza a sessão de telemedicina, marcando como COMPLETED e calculando a duração total",
     *     operationId="endTelemedicineSession",
     *     tags={"Telemedicine"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="sessionId",
     *         in="path",
     *         description="ID da sessão",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=false,
     *         @OA\JsonContent(
     *             @OA\Property(property="end_reason", type="string", maxLength=255, example="Consulta concluída com sucesso", description="Motivo do encerramento (opcional)"),
     *             @OA\Property(property="notes", type="string", maxLength=5000, example="Paciente apresentou melhora", description="Observações finais (opcional)")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Sessão finalizada com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="session_id", type="integer"),
     *                 @OA\Property(property="status", type="string", example="COMPLETED"),
     *                 @OA\Property(property="started_at", type="string", format="date-time"),
     *                 @OA\Property(property="ended_at", type="string", format="date-time"),
     *                 @OA\Property(property="duration_minutes", type="integer", example=25),
     *                 @OA\Property(property="notes", type="string")
     *             ),
     *             @OA\Property(property="message", type="string", example="Sessão finalizada com sucesso.")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Sessão não encontrada"),
     *     @OA\Response(response=422, description="Erro de validação"),
     *     @OA\Response(response=401, description="Não autenticado")
     * )
     */
    public function endSession() {}

    /**
     * @OA\Post(
     *     path="/api/telemedicine/sessions/{sessionId}/notify",
     *     summary="Notificar paciente via WhatsApp",
     *     description="Envia notificação via WhatsApp para o paciente com o link de entrada na teleconsulta. Funciona para empresas trial (config global) e empresas pagas (config própria).",
     *     operationId="notifyTelemedicinePatient",
     *     tags={"Telemedicine"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="sessionId",
     *         in="path",
     *         description="ID da sessão",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Notificação enviada com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="session_id", type="integer", example=1),
     *                 @OA\Property(property="patient_name", type="string", example="João Silva"),
     *                 @OA\Property(property="patient_phone", type="string", example="5511999887766"),
     *                 @OA\Property(property="join_url", type="string", example="https://8x8.vc/vpaas-magic-cookie-xxx/consultation-123"),
     *                 @OA\Property(property="message_sent", type="boolean", example=true)
     *             ),
     *             @OA\Property(property="message", type="string", example="Notificação enviada com sucesso para o paciente.")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Sessão não encontrada"),
     *     @OA\Response(response=400, description="Paciente sem telefone ou WhatsApp não configurado"),
     *     @OA\Response(response=401, description="Não autenticado"),
     *     @OA\Response(response=500, description="Erro ao enviar notificação")
     * )
     */
    public function notifyPatient() {}
}
