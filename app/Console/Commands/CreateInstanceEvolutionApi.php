<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CreateInstanceEvolutionApi extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:create-instance-evolution-api {idCompany}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Atualiza a instância da API do Evolution para uma empresa específica';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $idCompany = (int)$this->argument('idCompany');
        if (!$idCompany) {
            $this->error('ID da empresa é obrigatório e deve ser um número inteiro.');
            return;
        }

        $company = app()->make(\App\Models\Company::class)->find($idCompany);

        if (!$company) {
            $this->error('Empresa não encontrada.');
            return;
        }

        //verifica se já existe configuração
        if ($company->whatsappConfig) {
            $this->info('Empresa já possui configuração de WhatsApp.');
            return;
        }

        $whatsappService = app()->make(\App\Services\Whatsapp\WhatsappService::class);
        $whatsappService->createInstanceConfig($company);
        $this->info('Instância criada com sucesso para a empresa: ' . $company->name);
    }
}
