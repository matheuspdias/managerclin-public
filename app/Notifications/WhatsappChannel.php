<?php

namespace App\Notifications;

use App\Services\Whatsapp\WhatsappService;
use Illuminate\Notifications\Notification;

class WhatsappChannel
{
    public function send($notifiable, Notification $notification): void
    {
        // aqui o Laravel já vai chamar
        $data = $notification->toWhatsapp($notifiable);

        $message = $data['message'] ?? '';
        $phone   = $data['phone'] ?? '';
        $config  = $data['config'] ?? [];

        WhatsappService::sendMessage(
            $phone,
            $message,
            $config
        );
    }
}
