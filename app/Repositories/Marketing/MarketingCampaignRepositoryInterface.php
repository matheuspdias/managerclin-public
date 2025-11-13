<?php

namespace App\Repositories\Marketing;

use App\Models\MarketingCampaign;
use App\Repositories\BaseRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

/**
 * @extends BaseRepositoryInterface<MarketingCampaign>
 */
interface MarketingCampaignRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Busca campanhas pendentes de envio (com escopo de empresa se autenticado)
     */
    public function getPendingCampaigns(): Collection;

    /**
     * Busca campanhas pendentes de envio (de todas as empresas)
     * Usado por comando agendado sem autenticação
     */
    public function getPendingCampaignsWithoutScopes(): Collection;

    /**
     * Atualiza status da campanha (sem escopos globais para comandos)
     */
    public function updateStatus(int $id, string $status): void;

    /**
     * Atualiza estatísticas da campanha
     */
    public function updateStatistics(int $id, int $totalRecipients, int $sentCount, int $failedCount): void;

    /**
     * Marca campanha como enviada
     */
    public function markAsSent(int $id): void;
}
