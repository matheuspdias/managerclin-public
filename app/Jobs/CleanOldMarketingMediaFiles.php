<?php

namespace App\Jobs;

use App\Models\MarketingCampaign;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class CleanOldMarketingMediaFiles implements ShouldQueue
{
    use Queueable;

    /**
     * Número de dias para manter os arquivos após o envio
     */
    protected int $daysToKeep = 30;

    /**
     * Cria uma nova instância do job.
     */
    public function __construct(int $daysToKeep = 30)
    {
        $this->daysToKeep = $daysToKeep;
    }

    /**
     * Executa o job de limpeza de arquivos antigos.
     */
    public function handle(): void
    {
        Log::info("Iniciando limpeza de arquivos de mídia de campanhas antigas");

        // Busca campanhas enviadas há mais de X dias que possuem arquivo local
        $cutoffDate = now()->subDays($this->daysToKeep);

        $campaigns = MarketingCampaign::withoutGlobalScopes()
            ->whereNotNull('local_media_path')
            ->where('status', 'sent')
            ->where('sent_at', '<=', $cutoffDate)
            ->get();

        $deletedCount = 0;
        $errorCount = 0;

        foreach ($campaigns as $campaign) {
            try {
                // Verifica se o arquivo existe antes de tentar deletar
                if (Storage::disk('public')->exists($campaign->local_media_path)) {
                    // Deleta o arquivo
                    Storage::disk('public')->delete($campaign->local_media_path);

                    // Limpa os campos de mídia local na campanha
                    $campaign->local_media_path = null;
                    $campaign->save();

                    $deletedCount++;

                    Log::info("Arquivo de mídia deletado", [
                        'campaign_id' => $campaign->id,
                        'campaign_name' => $campaign->name,
                        'file_path' => $campaign->local_media_path,
                        'sent_at' => $campaign->sent_at->format('Y-m-d H:i:s'),
                    ]);
                }
            } catch (\Exception $e) {
                $errorCount++;
                Log::error("Erro ao deletar arquivo de mídia", [
                    'campaign_id' => $campaign->id,
                    'file_path' => $campaign->local_media_path,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        Log::info("Limpeza de arquivos de mídia concluída", [
            'cutoff_date' => $cutoffDate->format('Y-m-d H:i:s'),
            'days_to_keep' => $this->daysToKeep,
            'campaigns_found' => $campaigns->count(),
            'files_deleted' => $deletedCount,
            'errors' => $errorCount,
        ]);
    }
}
