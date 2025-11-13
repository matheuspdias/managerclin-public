<?php

namespace App\Console\Commands;

use App\Jobs\SendMarketingCampaignMessage;
use App\Models\MarketingCampaign;
use App\Models\MarketingCampaignRecipient;
use App\Services\Marketing\MarketingCampaignService;
use App\Services\Whatsapp\WhatsappService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ProcessMarketingCampaigns extends Command
{
    /**
     * Nome e assinatura do comando de console.
     *
     * @var string
     */
    protected $signature = 'app:process-marketing-campaigns';

    /**
     * Descrição do comando de console.
     *
     * @var string
     */
    protected $description = 'Processa campanhas de marketing agendadas e envia mensagens de WhatsApp';

    /**
     * Construtor.
     */
    public function __construct(
        protected MarketingCampaignService $campaignService,
        protected WhatsappService $whatsappService
    ) {
        parent::__construct();
    }

    /**
     * Executa o comando de console.
     */
    public function handle()
    {
        $this->info('Processando campanhas de marketing agendadas...');

        // Busca campanhas pendentes de TODAS as empresas (sem escopos globais)
        $campaigns = $this->campaignService->getPendingCampaignsForScheduler();

        if ($campaigns->isEmpty()) {
            $this->info('Nenhuma campanha para processar.');
            return;
        }

        $totalJobs = 0;

        foreach ($campaigns as $campaign) {
            $this->info("Processando campanha: {$campaign->name} (ID: {$campaign->id}) - Empresa: {$campaign->company->name}");

            // Busca configuração de WhatsApp da empresa
            $config = $this->whatsappService->getConfig($campaign->id_company);

            if (!$config) {
                $this->error("Configuração de WhatsApp não encontrada para empresa ID: {$campaign->id_company}");
                $this->campaignService->updateCampaignStatus($campaign->id, 'failed');
                continue;
            }

            // Verifica se a empresa pode enviar campanhas de WhatsApp (plano Pro ou Premium)
            if (!$this->canSendWhatsappMarketing($campaign->company)) {
                $this->warn("Empresa {$campaign->company->name} não possui um plano válido para marketing via WhatsApp.");
                $this->campaignService->updateCampaignStatus($campaign->id, 'failed');
                continue;
            }

            // Atualiza status da campanha para "enviando"
            $this->campaignService->updateCampaignStatus($campaign->id, 'sending');

            // Busca destinatários pendentes
            $recipients = MarketingCampaignRecipient::where('id_campaign', $campaign->id)
                ->where('status', 'pending')
                ->get();


            $this->info("Encontrados {$recipients->count()} destinatários para a campanha {$campaign->name}");

            foreach ($recipients as $recipient) {
                // Despacha job com pequeno delay aleatório para evitar spam
                SendMarketingCampaignMessage::dispatch(
                    $recipient->id,
                    $campaign->message,
                    $config,
                    $campaign->media_type,
                    $campaign->media_url,
                    $campaign->media_filename,
                    $campaign->local_media_path
                )->delay(now()->addSeconds(rand(1, 10)));

                $totalJobs++;
            }

            $this->info("Campanha {$campaign->name} processada. {$recipients->count()} mensagens na fila.");
            $this->info("Os jobs verificarão a conclusão da campanha após cada mensagem enviada.");
        }

        $this->info("Total de {$totalJobs} mensagens de marketing adicionadas à fila.");
        Log::info('Comando de campanhas de marketing executado', ['jobs_dispatched' => $totalJobs]);
    }

    /**
     * Verifica se a empresa pode enviar marketing via WhatsApp (plano Pro ou Premium)
     */
    protected function canSendWhatsappMarketing($company): bool
    {
        // Se estiver em período de teste, permite marketing
        if ($company->trial_ends_at?->isFuture()) {
            $this->info("Empresa {$company->name} está em período de teste até {$company->trial_ends_at->format('d/m/Y')}");
            return true;
        }

        // Verifica se há assinatura ativa
        $activeSubscription = $company->subscriptions()
            ->where('stripe_status', 'active')
            ->first();

        if (!$activeSubscription) {
            return false;
        }

        // Apenas planos Pro e Premium podem enviar campanhas de marketing
        $allowedPriceIds = [
            config('services.stripe.prices.plans.pro'),
            config('services.stripe.prices.plans.premium'),
        ];

        // Verifica itens da assinatura pelos IDs de preço dos planos (Cashier armazena preços em subscription_items)
        $hasPlanItem = $activeSubscription->items->contains(fn($item) => in_array($item->stripe_price, $allowedPriceIds));

        if ($hasPlanItem) {
            $planItem = $activeSubscription->items->first(fn($item) => in_array($item->stripe_price, $allowedPriceIds));
            $this->info("Empresa {$company->name} possui plano válido: {$planItem->stripe_price}");
            return true;
        }

        $this->warn("Empresa {$company->name} não possui plano Pro ou Premium");
        return false;
    }
}
