<?php

namespace App\Jobs;

use App\Models\Appointment;
use App\Models\Customer;
use App\Notifications\WhatsappNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendWhatsappAppointmentNotification implements ShouldQueue
{
    use Queueable, InteractsWithQueue, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The maximum number of seconds the job can run.
     */
    public int $timeout = 30;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public int $appointmentId,
        public string $message,
        public string $notificationType,
        public array $whatsappConfig
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $appointment = Appointment::find($this->appointmentId);

        if (!$appointment) {
            Log::warning("Appointment not found: {$this->appointmentId}");
            return;
        }

        $customer = Customer::withoutGlobalScopes()->find($appointment->id_customer);

        if (!$customer || !$customer->phone) {
            Log::warning("Customer not found or no phone: {$appointment->id_customer}");
            return;
        }

        // Normalize and format phone number
        $originalPhone = $customer->phone;
        $customer->phone = $this->normalizePhoneNumber($customer->phone);

        Log::info("Phone normalization", [
            'appointment_id' => $this->appointmentId,
            'customer_name' => $customer->name,
            'original_phone' => $originalPhone,
            'normalized_phone' => $customer->phone
        ]);

        try {
            // Send WhatsApp notification
            Log::info("Attempting to send WhatsApp notification", [
                'appointment_id' => $this->appointmentId,
                'customer_name' => $customer->name,
                'phone' => $customer->phone,
                'message' => $this->message
            ]);

            // Create notification with normalized phone
            $notification = new WhatsappNotification($this->message, $this->whatsappConfig, $customer->phone);
            $customer->notify($notification);

            // Update appointment notification status
            if ($this->notificationType === 'day_before') {
                $appointment->notified_day_before_at = now();
            } elseif ($this->notificationType === 'same_day') {
                $appointment->notified_same_day_at = now();
            }

            $appointment->save();

            Log::info("WhatsApp notification sent successfully", [
                'appointment_id' => $this->appointmentId,
                'customer_name' => $customer->name,
                'phone' => $customer->phone,
                'type' => $this->notificationType
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to send WhatsApp notification", [
                'appointment_id' => $this->appointmentId,
                'customer_name' => $customer->name,
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("WhatsApp notification job failed permanently", [
            'appointment_id' => $this->appointmentId,
            'notification_type' => $this->notificationType,
            'error' => $exception->getMessage()
        ]);
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
