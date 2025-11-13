<?php

namespace App\Http\Controllers\Traits\Swagger;

/**
 * @OA\Tag(
 *     name="Marketing Campaigns",
 *     description="Gerenciamento de campanhas de marketing"
 * )
 */
trait SwaggerMarketingCampaignDocs
{
    /**
     * @OA\Get(
     *     path="/api/marketing/campaigns",
     *     summary="Listar todas as campanhas",
     *     description="Retorna lista paginada de campanhas de marketing",
     *     operationId="getMarketingCampaigns",
     *     tags={"Marketing Campaigns"},
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
     *         description="Buscar por nome da campanha",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="order",
     *         in="query",
     *         description="Ordenação",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lista de campanhas retornada com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/MarketingCampaign")),
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
     *     path="/api/marketing/campaigns/{id}",
     *     summary="Exibir campanha específica",
     *     description="Retorna detalhes de uma campanha de marketing",
     *     operationId="getMarketingCampaignById",
     *     tags={"Marketing Campaigns"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID da campanha",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Campanha encontrada",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", ref="#/components/schemas/MarketingCampaign")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Campanha não encontrada"),
     *     @OA\Response(response=401, description="Não autenticado")
     * )
     */
    public function show() {}

    /**
     * @OA\Post(
     *     path="/api/marketing/campaigns",
     *     summary="Criar nova campanha",
     *     description="Cria uma nova campanha de marketing",
     *     operationId="createMarketingCampaign",
     *     tags={"Marketing Campaigns"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "message", "target_audience"},
     *             @OA\Property(property="name", type="string", maxLength=255, example="Campanha Janeiro 2025"),
     *             @OA\Property(property="message", type="string", example="Olá! Temos novidades especiais para você este mês."),
     *             @OA\Property(property="target_audience", type="string", enum={"all", "with_appointments", "without_appointments", "custom"}, example="all"),
     *             @OA\Property(property="target_filters", type="object", description="Filtros customizados para público-alvo"),
     *             @OA\Property(property="scheduled_at", type="string", format="date-time", example="2025-01-20 10:00:00", description="Data e hora de envio agendado"),
     *             @OA\Property(property="media_type", type="string", enum={"image", "video", "document", "audio"}, example="image"),
     *             @OA\Property(property="media_url", type="string", example="https://example.com/image.jpg"),
     *             @OA\Property(property="media_filename", type="string", example="promocao.jpg")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Campanha criada com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", ref="#/components/schemas/MarketingCampaign")
     *         )
     *     ),
     *     @OA\Response(response=422, description="Erro de validação"),
     *     @OA\Response(response=401, description="Não autenticado")
     * )
     */
    public function store() {}

    /**
     * @OA\Put(
     *     path="/api/marketing/campaigns/{id}",
     *     summary="Atualizar campanha",
     *     description="Atualiza dados de uma campanha existente",
     *     operationId="updateMarketingCampaign",
     *     tags={"Marketing Campaigns"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID da campanha",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", maxLength=255),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="target_audience", type="string", enum={"all", "with_appointments", "without_appointments", "custom"}),
     *             @OA\Property(property="target_filters", type="object"),
     *             @OA\Property(property="scheduled_at", type="string", format="date-time"),
     *             @OA\Property(property="media_type", type="string", enum={"image", "video", "document", "audio"}),
     *             @OA\Property(property="media_url", type="string"),
     *             @OA\Property(property="media_filename", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Campanha atualizada com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", ref="#/components/schemas/MarketingCampaign")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Campanha não encontrada"),
     *     @OA\Response(response=422, description="Erro de validação"),
     *     @OA\Response(response=401, description="Não autenticado")
     * )
     */
    public function update() {}

    /**
     * @OA\Delete(
     *     path="/api/marketing/campaigns/{id}",
     *     summary="Deletar campanha",
     *     description="Remove uma campanha do sistema",
     *     operationId="deleteMarketingCampaign",
     *     tags={"Marketing Campaigns"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID da campanha",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Campanha deletada com sucesso"
     *     ),
     *     @OA\Response(response=404, description="Campanha não encontrada"),
     *     @OA\Response(response=401, description="Não autenticado")
     * )
     */
    public function destroy() {}
}
