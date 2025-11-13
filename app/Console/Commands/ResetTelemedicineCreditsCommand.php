<?php

namespace App\Console\Commands;

use App\Models\Company;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ResetTelemedicineCreditsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'telemedicine:reset-credits';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reseta os créditos de telemedicina de todas as empresas mensalmente (adiciona créditos do plano + mantém adicionais)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Iniciando reset de créditos de telemedicina...');

        // Buscar todas as empresas com assinatura ativa
        $companies = Company::whereHas('subscriptions', function ($query) {
            $query->where('stripe_status', 'active');
        })->get();

        $resetCount = 0;

        foreach ($companies as $company) {
            $creditsBefore = $company->telemedicine_credits;
            $planCredits = $company->getTelemedicineCreditsLimit();
            $additionalCredits = $company->telemedicine_additional_credits ?? 0;

            // Reseta créditos (plano + adicionais)
            $company->resetTelemedicineCredits();
            $resetCount++;

            $this->line("✓ Empresa #{$company->id} ({$company->name}): {$creditsBefore} → {$company->telemedicine_credits} créditos (Plano: {$planCredits} + Adicionais: {$additionalCredits})");

            Log::info('Créditos de telemedicina resetados via comando', [
                'company_id' => $company->id,
                'company_name' => $company->name,
                'credits_before' => $creditsBefore,
                'credits_after' => $company->telemedicine_credits,
                'plan_credits' => $planCredits,
                'additional_credits' => $additionalCredits,
                'reset_at' => now(),
            ]);
        }

        $this->newLine();
        $this->info("Reset concluído!");
        $this->info("Total de empresas processadas: " . $companies->count());
        $this->info("Créditos resetados: {$resetCount}");

        Log::info('Comando de reset de créditos de telemedicina concluído', [
            'total_companies' => $companies->count(),
            'reset_count' => $resetCount,
        ]);

        return Command::SUCCESS;
    }
}
