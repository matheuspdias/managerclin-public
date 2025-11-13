<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class WhatsappNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    protected string $message;
    protected array $config;
    protected ?string $normalizedPhone;

    public function __construct(string $message, array $config = [], ?string $normalizedPhone = null)
    {
        $this->message = $message;
        $this->config = $config;
        $this->normalizedPhone = $normalizedPhone;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return [
            WhatsappChannel::class,
        ];
    }

    public function toWhatsapp(object $notifiable): array
    {
        return [
            'message' => $this->message,
            'phone' => $this->normalizedPhone ?? $notifiable->phone,
            'config' => $this->config,
        ];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
