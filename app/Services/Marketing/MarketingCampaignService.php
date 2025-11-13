<?php

namespace App\Services\Marketing;

use App\Models\Customer;
use App\Models\MarketingCampaign;
use App\Models\MarketingCampaignRecipient;
use App\Repositories\Marketing\MarketingCampaignRepositoryInterface;
use App\Traits\ThrowsExceptions;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MarketingCampaignService
{
    use ThrowsExceptions;
    public function __construct(
        private MarketingCampaignRepositoryInterface $repository
    ) {}

    public function getAllCampaigns(): Collection
    {
        return $this->repository->getAll();
    }

    public function getCampaignById(int $id): ?MarketingCampaign
    {
        return $this->repository->findById($id);
    }

    public function paginateCampaigns(?string $search, int $page, int $perPage, ?string $order = null): LengthAwarePaginator
    {
        return $this->repository->paginate($search, $page, $perPage, $order);
    }

    public function createCampaign(array $data): MarketingCampaign
    {
        DB::beginTransaction();

        try {
            $campaign = $this->repository->store((object) $data);

            // Se está agendada, prepara os destinatários
            if ($campaign->status === 'scheduled') {
                $this->prepareRecipients($campaign);
            }

            DB::commit();

            return $campaign;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function updateCampaign(int $id, array $data): MarketingCampaign
    {
        DB::beginTransaction();

        try {
            $campaign = $this->repository->update($id, (object) $data);

            // Se status mudou para agendada, prepara os destinatários
            if (isset($data['status']) && $data['status'] === 'scheduled') {
                $this->prepareRecipients($campaign);
            }

            DB::commit();

            return $campaign;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function deleteCampaign(int $id): void
    {
        $campaign = $this->repository->findById($id);

        if (!$campaign) {
            $this->throwNotFound('Campanha não encontrada');
        }

        if (!$campaign->canBeEdited()) {
            $this->throwDomain('Não é possível excluir uma campanha que já foi enviada ou está sendo enviada');
        }

        $this->repository->deleteById($id);
    }

    public function scheduleCampaign(int $id, string $scheduledAt): MarketingCampaign
    {
        DB::beginTransaction();

        try {
            $campaign = $this->repository->findById($id);

            if (!$campaign) {
                $this->throwNotFound('Campanha não encontrada');
            }

            if (!$campaign->canBeSent()) {
                $this->throwDomain('Esta campanha não pode ser agendada no momento');
            }

            $campaign->scheduled_at = $scheduledAt;
            $campaign->status = 'scheduled';
            $campaign->save();

            $this->prepareRecipients($campaign);

            DB::commit();

            return $campaign;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function cancelCampaign(int $id): MarketingCampaign
    {
        $campaign = $this->repository->findById($id);

        if (!$campaign) {
            $this->throwNotFound('Campanha não encontrada');
        }

        if (!$campaign->canBeCancelled()) {
            $this->throwDomain('Esta campanha não pode ser cancelada no momento');
        }

        $this->repository->updateStatus($id, 'cancelled');
        $campaign->refresh();

        return $campaign;
    }

    public function sendNow(int $id): MarketingCampaign
    {
        DB::beginTransaction();

        try {
            $campaign = $this->repository->findById($id);

            if (!$campaign) {
                $this->throwNotFound('Campanha não encontrada');
            }

            if (!$campaign->canBeSent()) {
                $this->throwDomain('Esta campanha não pode ser enviada no momento');
            }

            $campaign->scheduled_at = now();
            $campaign->status = 'scheduled';
            $campaign->save();

            $this->prepareRecipients($campaign);

            DB::commit();

            return $campaign;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    protected function prepareRecipients(MarketingCampaign $campaign): void
    {
        // Remove destinatários existentes
        MarketingCampaignRecipient::where('id_campaign', $campaign->id)->delete();

        $customers = $this->getTargetCustomers($campaign);

        $recipients = [];
        foreach ($customers as $customer) {
            $recipients[] = [
                'id_campaign' => $campaign->id,
                'id_customer' => $customer->id,
                'status' => 'pending',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        if (!empty($recipients)) {
            MarketingCampaignRecipient::insert($recipients);
        }

        // Atualiza estatísticas da campanha
        $this->repository->updateStatistics($campaign->id, count($recipients), 0, 0);
    }

    protected function getTargetCustomers(MarketingCampaign $campaign): Collection
    {
        $query = Customer::query();

        switch ($campaign->target_audience) {
            case 'all':
                // Todos os clientes
                break;

            case 'with_appointments':
                // Apenas clientes que TÊM agendamentos FUTUROS (data >= hoje)
                $query->whereHas('appointments', function ($q) {
                    $q->where('date', '>=', now()->format('Y-m-d'));
                });
                break;

            case 'without_appointments':
                // Clientes que NÃO TÊM agendamentos FUTUROS
                $query->whereDoesntHave('appointments', function ($q) {
                    $q->where('date', '>=', now()->format('Y-m-d'));
                });
                break;

            case 'custom':
                // Aplica filtros personalizados do JSON target_filters
                if ($campaign->target_filters) {
                    // Adicionar lógica para filtros personalizados aqui
                }
                break;
        }

        // Apenas clientes com números de telefone
        $query->whereNotNull('phone')->where('phone', '!=', '');

        return $query->get();
    }

    /**
     * Busca campanhas pendentes (para usuários autenticados - respeita escopo de empresa)
     */
    public function getPendingCampaigns(): Collection
    {
        return $this->repository->getPendingCampaigns();
    }

    /**
     * Busca campanhas pendentes sem escopos
     * Usado APENAS por comandos agendados executando sem autenticação
     * Este método busca campanhas de TODAS as empresas
     */
    public function getPendingCampaignsForScheduler(): Collection
    {
        return $this->repository->getPendingCampaignsWithoutScopes();
    }

    public function updateCampaignStatistics(int $id, int $sentCount, int $failedCount): void
    {
        // Usa withoutGlobalScopes no método do repositório
        // O método updateStatistics já trata isso internamente
        $campaign = MarketingCampaign::withoutGlobalScopes()->find($id);

        if ($campaign) {
            $this->repository->updateStatistics(
                $id,
                $campaign->total_recipients,
                $sentCount,
                $failedCount
            );
        }
    }

    public function markCampaignAsSent(int $id): void
    {
        $this->repository->markAsSent($id);
    }

    public function updateCampaignStatus(int $id, string $status): void
    {
        $this->repository->updateStatus($id, $status);
    }
}
