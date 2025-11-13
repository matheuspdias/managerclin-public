<?php

namespace App\Console\Commands\Telemedicine;

use App\Jobs\Telemedicine\MonitorActiveSessionsJob;
use Illuminate\Console\Command;

class MonitorSessionsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'telemedicine:monitor-sessions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Monitora sessões ativas de telemedicina e consome créditos baseado no tempo de uso';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🔍 Iniciando monitoramento de sessões de telemedicina...');

        try {
            // Despachar o job de forma síncrona para execução imediata
            MonitorActiveSessionsJob::dispatchSync();

            $this->info('✅ Monitoramento concluído com sucesso!');
            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('❌ Erro ao monitorar sessões: ' . $e->getMessage());
            $this->error($e->getTraceAsString());
            return Command::FAILURE;
        }
    }
}
