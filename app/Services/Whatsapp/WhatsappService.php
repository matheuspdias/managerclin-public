<?php

namespace App\Services\Whatsapp;

use App\Models\Company;
use App\Models\WhatsAppConfig;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsappService
{
    public static function sendMessage(string $phone, string $message, array $config): array
    {
        return Http::withHeaders([
            'apiKey' => env('EVOLUTION_API_KEY'),
        ])->post(
            env('WHATSAPP_API_URL') . '/message/sendText/' . $config['instance_name'],
            [
                'number' => $phone,
                'text' => $message,
            ]
        )->json();
    }

    public static function sendMedia(
        string $phone,
        string $caption,
        string $mediaType,
        string $mediaUrl,
        array $config,
        ?string $fileName = null
    ): array {
        // Formato Evolution API v2
        $payload = [
            'number' => $phone,
            'mediatype' => $mediaType,
            'media' => $mediaUrl,
            'delay' => 1200,
        ];

        // Determina o mimetype baseado no mediaType
        $mimetypes = [
            'image' => 'image/png',
            'video' => 'video/mp4',
            'document' => 'application/pdf',
            'audio' => 'audio/mpeg',
        ];
        $payload['mimetype'] = $mimetypes[$mediaType] ?? 'image/png';

        // Adiciona caption se não for áudio (áudio não suporta caption)
        if ($mediaType !== 'audio' && !empty($caption)) {
            $payload['caption'] = $caption;
        }

        // Adiciona fileName
        if (!empty($fileName)) {
            $payload['fileName'] = $fileName;
        }

        $url = env('WHATSAPP_API_URL') . '/message/sendMedia/' . $config['instance_name'];

        Log::info("Enviando mídia para Evolution API", [
            'url' => $url,
            'payload' => $payload,
            'phone' => $phone
        ]);

        $response = Http::withHeaders([
            'apiKey' => env('EVOLUTION_API_KEY'),
        ])->post($url, $payload)->json();

        Log::info("Resposta da Evolution API", [
            'response' => $response
        ]);

        return $response;
    }


    public function getConfig(int $companyId): ?array
    {
        $company = Company::with('whatsappConfig')->find($companyId);

        if (!$company || !$company->whatsappConfig) {
            return null;
        }

        return [
            'instance_name' => $company->whatsappConfig->instance_name,
            'token' => $company->whatsappConfig->token,
            'is_active' => $company->whatsappConfig->is_active,
            'id_company' => $company->id,
        ];
    }

    public function createInstanceConfig(Company $company): void
    {
        //criar nome trocando espaços por -
        $instanceName = preg_replace('/\s+/', '-', $company->name) . '-' . $company->id . env('APP_ENV');

        $response = Http::withHeaders([
            'apiKey' => env('EVOLUTION_API_KEY'),
        ])->asJson()->post(
            env('WHATSAPP_API_URL') . '/instance/create',
            [
                'instanceName' => $instanceName,
                'integration' => 'WHATSAPP-BAILEYS'
            ]
        )->json();

        if (isset($response['instance']['instanceName'])) {
            $company->whatsappConfig()->create([
                'instance_name' => $response['instance']['instanceName'],
                'instance_id' => $response['instance']['instanceId'],
                'token' => $response['hash'],
                'is_active' => true,
            ]);
        } else {
            Log::error('Erro ao criar instância do WhatsApp: ' . json_encode($response));
        }
    }
}
