<?php

namespace App\Repositories\Marketing;

use App\Models\MarketingCampaign;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Collection;

/**
 * @extends BaseRepository<MarketingCampaign>
 */
class MarketingCampaignEloquentORM extends BaseRepository implements MarketingCampaignRepositoryInterface
{
    protected array $searchable = ['name', 'message'];
    protected array $sortable = ['name', 'scheduled_at', 'created_at', 'status'];
    protected array $relations = ['createdBy', 'recipients'];

    public function __construct(MarketingCampaign $model)
    {
        parent::__construct($model);
    }

    /**
     * Busca campanhas pendentes (respeita escopo de empresa se autenticado)
     */
    public function getPendingCampaigns(): Collection
    {
        return $this->getQuery()
            ->pendingToSend()
            ->with('recipients')
            ->get();
    }

    /**
     * Busca campanhas pendentes sem escopos globais
     * Usado por comandos agendados que executam sem autenticação
     * Isso garante que campanhas de TODAS as empresas sejam processadas
     */
    public function getPendingCampaignsWithoutScopes(): Collection
    {
        return MarketingCampaign::withoutGlobalScopes()
            ->where('status', 'scheduled')
            ->whereNotNull('scheduled_at')
            ->where('scheduled_at', '<=', now())
            ->with(['recipients', 'company'])
            ->get();
    }

    /**
     * Atualiza status da campanha sem escopos globais
     * Usado por comandos que precisam atualizar campanhas de qualquer empresa
     */
    public function updateStatus(int $id, string $status): void
    {
        $campaign = MarketingCampaign::withoutGlobalScopes()->find($id);

        if (!$campaign) {
            $this->throwNotFound("Campanha com ID {$id} não encontrada.");
        }

        $campaign->status = $status;
        $campaign->save();
    }

    /**
     * Atualiza estatísticas da campanha sem escopos globais
     */
    public function updateStatistics(int $id, int $totalRecipients, int $sentCount, int $failedCount): void
    {
        $campaign = MarketingCampaign::withoutGlobalScopes()->find($id);

        if (!$campaign) {
            $this->throwNotFound("Campanha com ID {$id} não encontrada.");
        }

        $campaign->total_recipients = $totalRecipients;
        $campaign->sent_count = $sentCount;
        $campaign->failed_count = $failedCount;
        $campaign->save();
    }

    /**
     * Marca campanha como enviada sem escopos globais
     */
    public function markAsSent(int $id): void
    {
        $campaign = MarketingCampaign::withoutGlobalScopes()->find($id);

        if (!$campaign) {
            $this->throwNotFound("Campanha com ID {$id} não encontrada.");
        }

        $campaign->status = 'sent';
        $campaign->sent_at = now();
        $campaign->save();
    }
}
