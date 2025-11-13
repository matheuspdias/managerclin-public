<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class WhatsappMediaNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Cria uma nova instância da notificação.
     */
    protected string $caption;
    protected string $mediaType;
    protected string $mediaUrl;
    protected ?string $fileName;
    protected array $config;
    protected ?string $normalizedPhone;

    public function __construct(
        string $caption,
        string $mediaType,
        string $mediaUrl,
        array $config = [],
        ?string $fileName = null,
        ?string $normalizedPhone = null
    ) {
        $this->caption = $caption;
        $this->mediaType = $mediaType;
        $this->mediaUrl = $mediaUrl;
        $this->fileName = $fileName;
        $this->config = $config;
        $this->normalizedPhone = $normalizedPhone;
    }

    /**
     * Retorna os canais de entrega da notificação.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return [
            WhatsappMediaChannel::class,
        ];
    }

    public function toWhatsappMedia(object $notifiable): array
    {
        return [
            'caption' => $this->caption,
            'mediaType' => $this->mediaType,
            'mediaUrl' => $this->mediaUrl,
            'fileName' => $this->fileName,
            'phone' => $this->normalizedPhone ?? $notifiable->phone,
            'config' => $this->config,
        ];
    }

    /**
     * Retorna a representação em array da notificação.
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
