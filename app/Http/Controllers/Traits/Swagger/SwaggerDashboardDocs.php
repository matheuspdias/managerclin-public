<?php

namespace App\Http\Controllers\Traits\Swagger;

/**
 * @OA\Tag(
 *     name="Dashboard",
 *     description="Dados gerais do painel de controle"
 * )
 */
trait SwaggerDashboardDocs
{
    /**
     * @OA\Get(
     *     path="/api/dashboard",
     *     summary="Obter dados do dashboard",
     *     description="Retorna estatísticas e métricas do dashboard incluindo ranking de profissionais, total de usuários, clientes, agendamentos do dia, gráfico de agendamentos e serviços mais populares",
     *     operationId="getDashboard",
     *     tags={"Dashboard"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="start_date",
     *         in="query",
     *         description="Data inicial do período (formato: Y-m-d). Padrão: primeiro dia do mês atual",
     *         required=false,
     *         @OA\Schema(type="string", format="date", example="2025-10-01")
     *     ),
     *     @OA\Parameter(
     *         name="end_date",
     *         in="query",
     *         description="Data final do período (formato: Y-m-d). Padrão: último dia do mês atual",
     *         required=false,
     *         @OA\Schema(type="string", format="date", example="2025-10-31")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Dados do dashboard retornados com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="ranking", type="array",
     *                     description="Ranking de profissionais por número de atendimentos no período",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="name", type="string", example="Matheus Pereira"),
     *                         @OA\Property(property="image", type="string", nullable=true, example=null, description="URL da imagem do profissional"),
     *                         @OA\Property(property="appointments_count", type="integer", example=45, description="Total de atendimentos"),
     *                         @OA\Property(property="ranking", type="string", example="1º Lugar", description="Posição no ranking")
     *                     )
     *                 ),
     *                 @OA\Property(property="totalUsers", type="object",
     *                     description="Total de usuários cadastrados",
     *                     @OA\Property(property="total", type="integer", example=12, description="Total de usuários"),
     *                     @OA\Property(property="total_registered_today", type="integer", example=1, description="Total de usuários cadastrados hoje")
     *                 ),
     *                 @OA\Property(property="totalCustomers", type="object",
     *                     description="Total de clientes/pacientes cadastrados",
     *                     @OA\Property(property="total", type="integer", example=150, description="Total de clientes"),
     *                     @OA\Property(property="total_registered_today", type="integer", example=3, description="Total de clientes cadastrados hoje")
     *                 ),
     *                 @OA\Property(property="appointments", type="array",
     *                     description="Agendamentos do dia atual",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="title", type="string", example="Consulta - Maria Santos"),
     *                         @OA\Property(property="start", type="string", format="date-time", example="2025-10-19 09:00:00"),
     *                         @OA\Property(property="end", type="string", format="date-time", example="2025-10-19 10:00:00"),
     *                         @OA\Property(property="customer", type="object",
     *                             @OA\Property(property="id", type="integer", example=5),
     *                             @OA\Property(property="name", type="string", example="Maria Santos")
     *                         ),
     *                         @OA\Property(property="service", type="object",
     *                             @OA\Property(property="id", type="integer", example=3),
     *                             @OA\Property(property="name", type="string", example="Consulta Médica")
     *                         )
     *                     )
     *                 ),
     *                 @OA\Property(property="appointmentsChart", type="object",
     *                     description="Estatísticas de agendamentos no período",
     *                     @OA\Property(property="count", type="integer", example=2, description="Total de agendamentos"),
     *                     @OA\Property(property="completedCount", type="integer", example=0, description="Total de agendamentos concluídos"),
     *                     @OA\Property(property="cancelledCount", type="integer", example=0, description="Total de agendamentos cancelados"),
     *                     @OA\Property(property="pendingCount", type="integer", example=2, description="Total de agendamentos pendentes"),
     *                     @OA\Property(property="completedPercent", type="number", format="float", example=0, description="Percentual de agendamentos concluídos")
     *                 ),
     *                 @OA\Property(property="mostPopularServices", type="array",
     *                     description="Serviços mais populares no período",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="name", type="string", example="Consulta Médica"),
     *                         @OA\Property(property="total", type="integer", example=35, description="Total de agendamentos deste serviço")
     *                     )
     *                 ),
     *                 @OA\Property(property="period", type="object",
     *                     description="Período usado nas estatísticas",
     *                     @OA\Property(property="start_date", type="string", format="date", example="2025-10-01"),
     *                     @OA\Property(property="end_date", type="string", format="date", example="2025-10-31")
     *                 ),
     *                 @OA\Property(property="userName", type="string", example="Matheus Pereira", description="Nome do usuário autenticado")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Não autenticado"),
     *     @OA\Response(response=403, description="Empresa inativa")
     * )
     */
    public function index() {}
}
