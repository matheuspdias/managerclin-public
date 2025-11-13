<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('app:whatsapp-send-appointments-notifications')
    ->everyThirtyMinutes()
    ->withoutOverlapping(10) // Evita sobreposição por 10 minutos
    ->runInBackground() // Roda em background para não bloquear outros comandos
    ->onSuccess(function () {
        Log::info('WhatsApp notifications scheduled command completed successfully');
    })
    ->onFailure(function () {
        Log::error('WhatsApp notifications scheduled command failed');
    })
    ->description('Envia notificações de WhatsApp para agendamentos do dia e do dia seguinte');

// Processa campanhas de marketing agendadas
// Executa a cada 5 minutos para garantir envio pontual das campanhas
Schedule::command('app:process-marketing-campaigns')
    ->everyFiveMinutes()
    ->withoutOverlapping(15) // Evita sobreposição por até 15 minutos
    ->runInBackground() // Roda em background para não bloquear outros comandos
    ->onSuccess(function () {
        Log::info('Marketing campaigns scheduled command completed successfully');
    })
    ->onFailure(function () {
        Log::error('Marketing campaigns scheduled command failed');
    })
    ->description('Processa e envia campanhas de marketing agendadas via WhatsApp');

// Limpa arquivos de mídia de campanhas antigas (mantém por 30 dias após envio)
// Executa diariamente às 3h da manhã
Schedule::job(new \App\Jobs\CleanOldMarketingMediaFiles())
    ->dailyAt('03:00')
    ->onSuccess(function () {
        Log::info('Marketing media cleanup job completed successfully');
    })
    ->onFailure(function () {
        Log::error('Marketing media cleanup job failed');
    })
    ->description('Remove arquivos de mídia de campanhas enviadas há mais de 30 dias');

// Monitora sessões ativas de telemedicina e consome créditos
// Executa a cada 5 minutos para verificar consumo de créditos por tempo
Schedule::command('telemedicine:monitor-sessions')
    ->everyFiveMinutes()
    ->withoutOverlapping(10) // Evita sobreposição por 10 minutos
    ->runInBackground() // Roda em background para não bloquear outros comandos
    ->onSuccess(function () {
        Log::info('Telemedicine session monitoring completed successfully');
    })
    ->onFailure(function () {
        Log::error('Telemedicine session monitoring failed');
    })
    ->description('Monitora sessões ativas e consome créditos baseado no tempo de uso');

// Reseta créditos de telemedicina mensalmente
// Executa todo dia 1º do mês às 00:00
Schedule::command('telemedicine:reset-credits')
    ->monthlyOn(1, '00:00')
    ->withoutOverlapping(30) // Evita sobreposição por 30 minutos
    ->runInBackground() // Roda em background
    ->onSuccess(function () {
        Log::info('Telemedicine credits reset scheduled command completed successfully');
    })
    ->onFailure(function () {
        Log::error('Telemedicine credits reset scheduled command failed');
    })
    ->description('Reseta créditos de telemedicina mensalmente (plano + adicionais)');
