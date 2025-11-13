<?php

namespace App\Http\Controllers\Traits\Swagger;

/**
 * @OA\Tag(
 *     name="Appointments",
 *     description="Gerenciamento de agendamentos"
 * )
 */
trait SwaggerAppointmentDocs
{
    /**
     * @OA\Get(
     *     path="/api/appointments",
     *     summary="Listar todos os agendamentos",
     *     description="Retorna lista paginada de agendamentos com filtros",
     *     operationId="getAppointments",
     *     tags={"Appointments"},
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
     *         description="Buscar por nome do paciente ou profissional",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filtrar por status",
     *         required=false,
     *         @OA\Schema(type="string", enum={"SCHEDULED", "IN_PROGRESS", "COMPLETED", "CANCELLED"})
     *     ),
     *     @OA\Parameter(
     *         name="user_id",
     *         in="query",
     *         description="Filtrar por ID do profissional",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="start_date",
     *         in="query",
     *         description="Data inicial do período",
     *         required=false,
     *         @OA\Schema(type="string", format="date", example="2025-01-01")
     *     ),
     *     @OA\Parameter(
     *         name="end_date",
     *         in="query",
     *         description="Data final do período",
     *         required=false,
     *         @OA\Schema(type="string", format="date", example="2025-01-31")
     *     ),
     *     @OA\Parameter(
     *         name="view",
     *         in="query",
     *         description="Tipo de visualização",
     *         required=false,
     *         @OA\Schema(type="string", enum={"calendar", "list", "timeline"}, default="calendar")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lista de agendamentos retornada com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Appointment")),
     *             @OA\Property(property="meta", type="object",
     *                 @OA\Property(property="current_page", type="integer"),
     *                 @OA\Property(property="last_page", type="integer"),
     *                 @OA\Property(property="per_page", type="integer"),
     *                 @OA\Property(property="total", type="integer")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=422, description="Erro de validação"),
     *     @OA\Response(response=401, description="Não autenticado"),
     *     @OA\Response(response=403, description="Empresa inativa")
     * )
     */
    public function index() {}

    /**
     * @OA\Get(
     *     path="/api/appointments/{id}",
     *     summary="Exibir agendamento específico",
     *     description="Retorna detalhes de um agendamento",
     *     operationId="getAppointmentById",
     *     tags={"Appointments"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID do agendamento",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Agendamento encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", ref="#/components/schemas/Appointment")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Agendamento não encontrado"),
     *     @OA\Response(response=401, description="Não autenticado")
     * )
     */
    public function show() {}

    /**
     * @OA\Post(
     *     path="/api/appointments",
     *     summary="Criar novo agendamento",
     *     description="Cria um novo agendamento no sistema",
     *     operationId="createAppointment",
     *     tags={"Appointments"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"id_user", "id_customer", "id_room", "id_service", "date", "start_time"},
     *             @OA\Property(property="id_user", type="integer", example=1, description="ID do profissional"),
     *             @OA\Property(property="id_customer", type="integer", example=1, description="ID do cliente/paciente"),
     *             @OA\Property(property="id_room", type="integer", example=1, description="ID da sala"),
     *             @OA\Property(property="id_service", type="integer", example=1, description="ID do serviço"),
     *             @OA\Property(property="date", type="string", format="date", example="2025-01-15", description="Data do agendamento"),
     *             @OA\Property(property="start_time", type="string", example="09:00", description="Hora de início"),
     *             @OA\Property(property="end_time", type="string", example="10:00", description="Hora de término"),
     *             @OA\Property(property="price", type="number", format="float", example=150.00, description="Preço do serviço"),
     *             @OA\Property(property="status", type="string", enum={"SCHEDULED", "IN_PROGRESS", "COMPLETED", "CANCELLED"}, example="SCHEDULED"),
     *             @OA\Property(property="notes", type="string", maxLength=500, example="Observações sobre o agendamento")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Agendamento criado com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", ref="#/components/schemas/Appointment")
     *         )
     *     ),
     *     @OA\Response(response=422, description="Erro de validação"),
     *     @OA\Response(response=401, description="Não autenticado")
     * )
     */
    public function store() {}

    /**
     * @OA\Put(
     *     path="/api/appointments/{id}",
     *     summary="Atualizar agendamento",
     *     description="Atualiza dados de um agendamento existente",
     *     operationId="updateAppointment",
     *     tags={"Appointments"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID do agendamento",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="id_user", type="integer", description="ID do profissional"),
     *             @OA\Property(property="id_customer", type="integer", description="ID do cliente/paciente"),
     *             @OA\Property(property="id_room", type="integer", description="ID da sala"),
     *             @OA\Property(property="id_service", type="integer", description="ID do serviço"),
     *             @OA\Property(property="date", type="string", format="date", description="Data do agendamento"),
     *             @OA\Property(property="start_time", type="string", description="Hora de início"),
     *             @OA\Property(property="end_time", type="string", description="Hora de término"),
     *             @OA\Property(property="price", type="number", format="float", description="Preço do serviço"),
     *             @OA\Property(property="status", type="string", enum={"SCHEDULED", "IN_PROGRESS", "COMPLETED", "CANCELLED"}),
     *             @OA\Property(property="notes", type="string", maxLength=500)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Agendamento atualizado com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", ref="#/components/schemas/Appointment")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Agendamento não encontrado"),
     *     @OA\Response(response=422, description="Erro de validação"),
     *     @OA\Response(response=401, description="Não autenticado")
     * )
     */
    public function update() {}

    /**
     * @OA\Delete(
     *     path="/api/appointments/{id}",
     *     summary="Deletar agendamento",
     *     description="Remove um agendamento do sistema (soft delete)",
     *     operationId="deleteAppointment",
     *     tags={"Appointments"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID do agendamento",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Agendamento deletado com sucesso"
     *     ),
     *     @OA\Response(response=404, description="Agendamento não encontrado"),
     *     @OA\Response(response=401, description="Não autenticado")
     * )
     */
    public function destroy() {}

    /**
     * @OA\Post(
     *     path="/api/appointments/check-conflicts",
     *     summary="Verificar conflitos de horário",
     *     description="Verifica se existe conflito de horário para um agendamento",
     *     operationId="checkAppointmentConflicts",
     *     tags={"Appointments"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"date", "start_time", "end_time", "user_id", "room_id"},
     *             @OA\Property(property="date", type="string", format="date", example="2025-01-15"),
     *             @OA\Property(property="start_time", type="string", example="09:00"),
     *             @OA\Property(property="end_time", type="string", example="10:00"),
     *             @OA\Property(property="user_id", type="integer", example=1),
     *             @OA\Property(property="room_id", type="integer", example=1),
     *             @OA\Property(property="appointment_id", type="integer", example=1, description="ID do agendamento sendo editado (opcional)")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Verificação realizada com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="has_conflicts", type="boolean"),
     *             @OA\Property(property="conflicts", type="array", @OA\Items(type="object"))
     *         )
     *     ),
     *     @OA\Response(response=422, description="Erro de validação"),
     *     @OA\Response(response=401, description="Não autenticado")
     * )
     */
    public function checkConflicts() {}

    /**
     * @OA\Get(
     *     path="/api/appointments/available-slots",
     *     summary="Obter horários disponíveis",
     *     description="Retorna lista de horários disponíveis para agendamento",
     *     operationId="getAvailableSlots",
     *     tags={"Appointments"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="date",
     *         in="query",
     *         description="Data para verificar disponibilidade",
     *         required=true,
     *         @OA\Schema(type="string", format="date", example="2025-01-15")
     *     ),
     *     @OA\Parameter(
     *         name="user_id",
     *         in="query",
     *         description="ID do profissional",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="duration",
     *         in="query",
     *         description="Duração em minutos",
     *         required=false,
     *         @OA\Schema(type="integer", default=60, minimum=15, maximum=480)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Horários disponíveis retornados com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="available_slots", type="array", @OA\Items(type="string"))
     *         )
     *     ),
     *     @OA\Response(response=422, description="Erro de validação"),
     *     @OA\Response(response=401, description="Não autenticado")
     * )
     */
    public function getAvailableSlots() {}

    /**
     * @OA\Patch(
     *     path="/api/appointments/{id}/status",
     *     summary="Atualizar status do agendamento",
     *     description="Atualiza apenas o status do agendamento sem validar outros campos. Ideal para mudanças rápidas de status (ex: teleconsulta).",
     *     operationId="updateAppointmentStatus",
     *     tags={"Appointments"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID do agendamento",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"status"},
     *             @OA\Property(
     *                 property="status",
     *                 type="string",
     *                 enum={"SCHEDULED", "IN_PROGRESS", "COMPLETED", "CANCELLED"},
     *                 example="IN_PROGRESS",
     *                 description="Novo status do agendamento"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Status atualizado com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=123),
     *                 @OA\Property(property="status", type="string", example="IN_PROGRESS"),
     *                 @OA\Property(property="date", type="string", format="date", example="2025-01-25"),
     *                 @OA\Property(property="start_time", type="string", example="14:00:00"),
     *                 @OA\Property(property="end_time", type="string", example="15:00:00"),
     *                 @OA\Property(property="customer", type="object",
     *                     @OA\Property(property="id", type="integer"),
     *                     @OA\Property(property="name", type="string")
     *                 ),
     *                 @OA\Property(property="user", type="object",
     *                     @OA\Property(property="id", type="integer"),
     *                     @OA\Property(property="name", type="string")
     *                 )
     *             ),
     *             @OA\Property(property="message", type="string", example="Status do agendamento atualizado com sucesso.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erro de validação",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Erro de validação"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Agendamento não encontrado"),
     *     @OA\Response(response=401, description="Não autenticado"),
     *     @OA\Response(response=500, description="Erro ao atualizar status")
     * )
     */
    public function updateStatus() {}
}
