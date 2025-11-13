<?php

namespace App\Console\Commands;

use App\Jobs\SendWhatsappAppointmentNotification;
use App\Models\Company;
use App\Services\Appointment\AppointmentService;
use App\Services\Whatsapp\WhatsappService;
use App\Services\Whatsapp\WhatsappMessageTemplateService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class WhatsappSendAppointmentsNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:whatsapp-send-appointments-notifications';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envia noticações de whatsapp para os clientes que tem agendamento no dia';

    /**
     * constructor.
     */

    public function __construct(
        protected AppointmentService $appointmentService,
        protected WhatsappService $whatsappService,
        protected WhatsappMessageTemplateService $templateService
    ) {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Buscando agendamentos para notificações...');

        $companies = Company::with('subscriptions')->get();
        $totalJobs = 0;

        foreach ($companies as $company) {
            // Verificar se a empresa pode enviar notificações WhatsApp
            if (!$this->canSendWhatsappNotifications($company)) {
                $this->warn("Empresa {$company->name} não tem plano válido para envio de WhatsApp.");
                continue;
            }

            $config = $this->whatsappService->getConfig($company->id);
            if (!$config) {
                $this->error("Configuração do WhatsApp não encontrada para a empresa {$company->name}.");
                continue;
            }

            $today = now()->format('Y-m-d');
            $tomorrow = now()->addDay()->format('Y-m-d');

            // === Notificações 1 dia antes ===
            $tomorrowAppointments = $this->appointmentService->getAppointmentsToNotify($tomorrow, $company->id, 'day_before');

            foreach ($tomorrowAppointments as $appointment) {
                // Usar mensagem personalizada ou padrão
                $messageTemplate = $company->whatsapp_message_day_before
                    ?? $this->templateService->getDefaultDayBeforeMessage();

                $message = $this->templateService->replaceVariables($messageTemplate, $appointment);

                $this->dispatchNotificationJob($appointment->id, $message, 'day_before', $config);
                $totalJobs++;
            }

            // === Notificações 3 horas antes ou menos ===
            $todayAppointments = $this->appointmentService->getAppointmentsToNotify($today, $company->id, 'same_day');

            foreach ($todayAppointments as $appointment) {
                $start = now()->setTimeFromTimeString($appointment->start_time);
                $diffInMinutes = now()->diffInMinutes($start, false);

                // Notifica se faltar 3 horas ou menos (180 minutos ou menos) e ainda não passou
                if ($diffInMinutes > 0 && $diffInMinutes <= 180) {
                    // Usar mensagem personalizada ou padrão
                    $messageTemplate = $company->whatsapp_message_3hours_before
                        ?? $this->templateService->getDefault3HoursBeforeMessage();

                    $message = $this->templateService->replaceVariables($messageTemplate, $appointment);

                    $this->dispatchNotificationJob($appointment->id, $message, 'same_day', $config);
                    $totalJobs++;
                }
            }

            $this->info("Processados agendamentos para empresa: {$company->name}");
        }

        $this->info("Total de {$totalJobs} notificações adicionadas à fila.");
        Log::info('WhatsApp notifications command executed', ['jobs_dispatched' => $totalJobs]);
    }

    /**
     * Verifica se a empresa pode enviar notificações WhatsApp baseado no plano.
     */
    protected function canSendWhatsappNotifications(Company $company): bool
    {
        // Se está em trial (trial_ends_at não é null e ainda não expirou)
        if ($company->trial_ends_at?->isFuture()) {
            $this->info("Empresa {$company->name} está em período de trial até {$company->trial_ends_at->format('d/m/Y')}");
            return true;
        }

        // Buscar subscription ativa
        $activeSubscription = $company->subscriptions()
            ->where('stripe_status', 'active')
            ->first();

        // Se não tem subscription ativa, só pode se estiver em trial
        if (!$activeSubscription) {
            return false;
        }

        // Verificar se tem plano Pro ou Premium usando os price IDs do config
        $allowedPriceIds = [
            config('services.stripe.prices.plans.pro'),
            config('services.stripe.prices.plans.premium'),
        ];

        if (in_array($activeSubscription->stripe_price, $allowedPriceIds)) {
            $this->info("Empresa {$company->name} tem plano válido: {$activeSubscription->stripe_price}");
            return true;
        }

        $this->warn("Empresa {$company->name} tem plano '{$activeSubscription->stripe_price}' que não permite WhatsApp");
        return false;
    }

    /**
     * Despacha job para fila de notificações WhatsApp.
     */
    protected function dispatchNotificationJob(int $appointmentId, string $message, string $type, array $config): void
    {
        SendWhatsappAppointmentNotification::dispatch($appointmentId, $message, $type, $config)
            ->delay(now()->addSeconds(rand(1, 10))); // Pequeno delay aleatório para evitar spam

        $this->info("Job de notificação {$type} adicionado à fila para agendamento #{$appointmentId}");
    }
}
