<?php

namespace App\Jobs;

use App\Models\Customer;
use App\Models\MarketingCampaign;
use App\Models\MarketingCampaignRecipient;
use App\Notifications\WhatsappNotification;
use App\Notifications\WhatsappMediaNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendMarketingCampaignMessage implements ShouldQueue
{
    use Queueable, InteractsWithQueue, SerializesModels;

    /**
     * Número de vezes que o job pode ser tentado.
     */
    public int $tries = 3;

    /**
     * Número máximo de segundos que o job pode executar.
     */
    public int $timeout = 30;

    /**
     * Cria uma nova instância do job.
     */
    public function __construct(
        public int $recipientId,
        public string $message,
        public array $whatsappConfig,
        public ?string $mediaType = null,
        public ?string $mediaUrl = null,
        public ?string $mediaFilename = null,
        public ?string $localMediaPath = null
    ) {}

    /**
     * Executa o job.
     */
    public function handle(): void
    {
        $recipient = MarketingCampaignRecipient::find($this->recipientId);

        if (!$recipient) {
            Log::warning("Destinatário de campanha de marketing não encontrado: {$this->recipientId}");
            return;
        }

        $customer = Customer::withoutGlobalScopes()->find($recipient->id_customer);

        if (!$customer || !$customer->phone) {
            Log::warning("Cliente não encontrado ou sem telefone", [
                'recipient_id' => $this->recipientId,
                'customer_id' => $recipient->id_customer
            ]);

            $recipient->status = 'failed';
            $recipient->error_message = 'Cliente não encontrado ou sem telefone';
            $recipient->save();

            return;
        }

        // Normaliza e formata o número de telefone
        $originalPhone = $customer->phone;
        $customer->phone = $this->normalizePhoneNumber($customer->phone);

        Log::info("Normalização de telefone para campanha de marketing", [
            'recipient_id' => $this->recipientId,
            'customer_name' => $customer->name,
            'original_phone' => $originalPhone,
            'normalized_phone' => $customer->phone
        ]);

        try {
            // Envia notificação do WhatsApp
            Log::info("Tentando enviar mensagem de WhatsApp de marketing", [
                'recipient_id' => $this->recipientId,
                'campaign_id' => $recipient->id_campaign,
                'customer_name' => $customer->name,
                'phone' => $customer->phone,
                'has_media' => !empty($this->mediaType),
                'media_type' => $this->mediaType,
                'media_url' => $this->mediaUrl,
                'local_media_path' => $this->localMediaPath,
            ]);

            // Verifica se é uma mensagem com mídia ou apenas texto
            if (!empty($this->mediaType) && (!empty($this->mediaUrl) || !empty($this->localMediaPath))) {
                // Determina a URL da mídia (prioriza arquivo local se disponível)
                $mediaUrl = $this->mediaUrl;

                if (!empty($this->localMediaPath)) {
                    // Gera URL pública do arquivo
                    $publicUrl = asset('storage/' . $this->localMediaPath);

                    // Em ambiente local (localhost), troca por host.docker.internal para acessar de dentro do container
                    // Em produção, usa a URL original (APP_URL do .env)
                    if (app()->environment('local') && str_contains($publicUrl, 'localhost')) {
                        $mediaUrl = str_replace('http://localhost', 'http://host.docker.internal', $publicUrl);
                    } else {
                        // Em produção, usa a URL completa configurada no APP_URL
                        $mediaUrl = $publicUrl;
                    }

                    Log::info("Usando arquivo local para mídia de marketing", [
                        'recipient_id' => $this->recipientId,
                        'local_path' => $this->localMediaPath,
                        'original_url' => $publicUrl,
                        'final_url' => $mediaUrl,
                        'environment' => app()->environment()
                    ]);
                }

                // Envia mensagem com mídia
                $notification = new WhatsappMediaNotification(
                    $this->message, // caption
                    $this->mediaType,
                    $mediaUrl,
                    $this->whatsappConfig,
                    $this->mediaFilename,
                    $customer->phone
                );
            } else {
                // Envia mensagem de texto normal
                $notification = new WhatsappNotification(
                    $this->message,
                    $this->whatsappConfig,
                    $customer->phone
                );
            }

            $customer->notify($notification);

            // Atualiza status do destinatário
            $recipient->status = 'sent';
            $recipient->sent_at = now();
            $recipient->error_message = null;
            $recipient->save();

            Log::info("Mensagem de WhatsApp de marketing enviada com sucesso", [
                'recipient_id' => $this->recipientId,
                'campaign_id' => $recipient->id_campaign,
                'customer_name' => $customer->name,
                'phone' => $customer->phone
            ]);

            // Verifica se a campanha está completa e atualiza estatísticas
            $this->checkCampaignCompletion($recipient->id_campaign);
        } catch (\Exception $e) {
            Log::error("Falha ao enviar mensagem de WhatsApp de marketing", [
                'recipient_id' => $this->recipientId,
                'campaign_id' => $recipient->id_campaign,
                'customer_name' => $customer->name,
                'error' => $e->getMessage()
            ]);

            $recipient->status = 'failed';
            $recipient->error_message = $e->getMessage();
            $recipient->save();

            // Verifica se a campanha está completa mesmo em caso de falha
            $this->checkCampaignCompletion($recipient->id_campaign);

            throw $e;
        }
    }

    /**
     * Trata falha do job.
     */
    public function failed(\Throwable $exception): void
    {
        $recipient = MarketingCampaignRecipient::find($this->recipientId);

        if ($recipient) {
            $recipient->status = 'failed';
            $recipient->error_message = $exception->getMessage();
            $recipient->save();
        }

        Log::error("Job de mensagem de campanha de marketing falhou permanentemente", [
            'recipient_id' => $this->recipientId,
            'error' => $exception->getMessage()
        ]);
    }

    /**
     * Verifica se a campanha está completa e atualiza o status
     */
    protected function checkCampaignCompletion(int $campaignId): void
    {
        // Usa lock de cache para prevenir condições de corrida quando múltiplos jobs finalizam ao mesmo tempo
        $lock = \Illuminate\Support\Facades\Cache::lock("campaign_completion_{$campaignId}", 10);

        try {
            if (!$lock->get()) {
                // Outro job já está verificando, pula
                Log::debug("Verificação de conclusão de campanha ignorada (bloqueada)", ['campaign_id' => $campaignId]);
                return;
            }

            // Verifica se há destinatários pendentes
            $pendingCount = MarketingCampaignRecipient::where('id_campaign', $campaignId)
                ->where('status', 'pending')
                ->count();

            Log::debug("Verificação de conclusão de campanha", [
                'campaign_id' => $campaignId,
                'pending_count' => $pendingCount
            ]);

            // Se ainda há destinatários pendentes, não atualiza a campanha
            if ($pendingCount > 0) {
                return;
            }

            // Todos os destinatários processados, atualiza estatísticas da campanha
            $campaign = MarketingCampaign::withoutGlobalScopes()->find($campaignId);

            if (!$campaign) {
                Log::warning("Campanha não encontrada para conclusão", ['campaign_id' => $campaignId]);
                return;
            }

            if ($campaign->status !== 'sending') {
                Log::debug("Campanha não está em status de envio", [
                    'campaign_id' => $campaignId,
                    'status' => $campaign->status
                ]);
                return;
            }

            // Conta enviados e falhas
            $sentCount = MarketingCampaignRecipient::where('id_campaign', $campaignId)
                ->where('status', 'sent')
                ->count();

            $failedCount = MarketingCampaignRecipient::where('id_campaign', $campaignId)
                ->where('status', 'failed')
                ->count();

            // Atualiza a campanha
            $campaign->sent_count = $sentCount;
            $campaign->failed_count = $failedCount;
            $campaign->status = 'sent';
            $campaign->sent_at = now();
            $campaign->save();

            Log::info("Campanha concluída", [
                'campaign_id' => $campaignId,
                'campaign_name' => $campaign->name,
                'sent' => $sentCount,
                'failed' => $failedCount,
                'total' => $campaign->total_recipients
            ]);
        } finally {
            $lock->release();
        }
    }

    /**
     * Normaliza o número de telefone para o formato WhatsApp (55XXXXXXXXXXX)
     */
    private function normalizePhoneNumber(string $phone): string
    {
        // Remove todos os caracteres não numéricos
        $cleanPhone = preg_replace('/\D/', '', $phone);

        // Se está vazio após limpeza, retorna original
        if (empty($cleanPhone)) {
            return $phone;
        }

        // Se já tem 13 dígitos e começa com 55, está correto
        if (strlen($cleanPhone) === 13 && str_starts_with($cleanPhone, '55')) {
            return $cleanPhone;
        }

        // Se tem 12 dígitos e começa com 55, adiciona um 9 após o código de área
        if (strlen($cleanPhone) === 12 && str_starts_with($cleanPhone, '55')) {
            // Formato: 55XXYYY... -> 55XX9YYY...
            return '55' . substr($cleanPhone, 2, 2) . '9' . substr($cleanPhone, 4);
        }

        // Se tem 11 dígitos (formato nacional), adiciona 55
        if (strlen($cleanPhone) === 11) {
            return '55' . $cleanPhone;
        }

        // Se tem 10 dígitos (formato nacional sem 9), adiciona 55 e 9
        if (strlen($cleanPhone) === 10) {
            // Formato: XXYYYY... -> 55XX9YYYY...
            return '55' . substr($cleanPhone, 0, 2) . '9' . substr($cleanPhone, 2);
        }

        // Se não tem código do país e tem 9 dígitos, assume código de área 11 (São Paulo)
        if (strlen($cleanPhone) === 9) {
            return '5511' . $cleanPhone;
        }

        // Se tem 8 dígitos, adiciona código de área 11 e 9
        if (strlen($cleanPhone) === 8) {
            return '55119' . $cleanPhone;
        }

        // Remove zeros à esquerda se o número for muito longo
        if (strlen($cleanPhone) > 13) {
            $cleanPhone = ltrim($cleanPhone, '0');
        }

        // Para outros casos, adiciona 55 se não começar com 55
        if (!str_starts_with($cleanPhone, '55')) {
            return '55' . $cleanPhone;
        }

        return $cleanPhone;
    }
}
