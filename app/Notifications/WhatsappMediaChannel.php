<?php

namespace App\Notifications;

use App\Services\Whatsapp\WhatsappService;
use Illuminate\Notifications\Notification;

class WhatsappMediaChannel
{
    public function send($notifiable, Notification $notification): void
    {
        // Laravel vai chamar automaticamente toWhatsappMedia do notification
        $data = $notification->toWhatsappMedia($notifiable);

        $caption = $data['caption'] ?? '';
        $mediaType = $data['mediaType'] ?? 'image';
        $mediaUrl = $data['mediaUrl'] ?? '';
        $fileName = $data['fileName'] ?? null;
        $phone = $data['phone'] ?? '';
        $config = $data['config'] ?? [];

        WhatsappService::sendMedia(
            $phone,
            $caption,
            $mediaType,
            $mediaUrl,
            $config,
            $fileName
        );
    }
}
