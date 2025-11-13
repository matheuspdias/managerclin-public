<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Provedor de Telemedicina
    |--------------------------------------------------------------------------
    |
    | Define qual provedor será usado para videoconferências:
    | - 'jaas': Jitsi as a Service (JaaS) da 8x8 (requer App ID)
    | - 'jitsi': Jitsi Meet público gratuito (meet.jit.si)
    | - 'custom': Servidor Jitsi auto-hospedado
    |
    */

    'provider' => env('TELEMEDICINE_PROVIDER', 'jaas'),

    /*
    |--------------------------------------------------------------------------
    | Servidor Jitsi
    |--------------------------------------------------------------------------
    |
    | URL do servidor Jitsi Meet que será usado para as videoconferências.
    | - JaaS (8x8): https://8x8.vc
    | - Jitsi público: https://meet.jit.si
    | - Custom: URL do seu servidor próprio
    |
    */

    'server_url' => env('TELEMEDICINE_SERVER_URL', 'https://8x8.vc'),

    /*
    |--------------------------------------------------------------------------
    | JaaS App ID
    |--------------------------------------------------------------------------
    |
    | App ID fornecido pelo Jitsi as a Service (JaaS) da 8x8.
    | Necessário apenas quando provider = 'jaas'.
    | Formato: vpaas-magic-cookie-xxxxxxxx
    |
    */

    'jaas_app_id' => env('TELEMEDICINE_APP_ID', null),

    /*
    |--------------------------------------------------------------------------
    | Habilitar Gravação
    |--------------------------------------------------------------------------
    |
    | Define se as sessões de telemedicina podem ser gravadas.
    | IMPORTANTE: Verifique as leis de privacidade e proteção de dados
    | do seu país antes de habilitar esta funcionalidade.
    |
    */

    'enable_recording' => env('TELEMEDICINE_ENABLE_RECORDING', false),

    /*
    |--------------------------------------------------------------------------
    | Duração Máxima
    |--------------------------------------------------------------------------
    |
    | Duração máxima permitida para uma sessão de telemedicina em minutos.
    | Após este período, o sistema pode enviar alertas ou finalizar
    | automaticamente a sessão.
    |
    */

    'max_duration_minutes' => env('TELEMEDICINE_MAX_DURATION', 120),

    /*
    |--------------------------------------------------------------------------
    | Configurações de Notificação
    |--------------------------------------------------------------------------
    |
    | Define se notificações devem ser enviadas aos participantes
    | sobre eventos da sessão (início, término, etc).
    |
    */

    'notifications' => [
        'enabled' => env('TELEMEDICINE_NOTIFICATIONS_ENABLED', true),
        'send_on_session_start' => true,
        'send_on_session_end' => true,
        'remind_before_minutes' => 15, // Enviar lembrete 15 minutos antes
    ],

    /*
    |--------------------------------------------------------------------------
    | Configurações de Segurança
    |--------------------------------------------------------------------------
    |
    | Configurações relacionadas à segurança das sessões de telemedicina.
    |
    */

    'security' => [
        'require_password' => env('TELEMEDICINE_REQUIRE_PASSWORD', false),
        'waiting_room_enabled' => env('TELEMEDICINE_WAITING_ROOM', true),
        'lobby_enabled' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Configurações de Interface Jitsi
    |--------------------------------------------------------------------------
    |
    | Configurações customizadas para a interface do Jitsi Meet.
    | Estas configurações serão passadas ao inicializar o Jitsi.
    |
    */

    'jitsi_config' => [
        'startWithAudioMuted' => false,
        'startWithVideoMuted' => false,
        'enableWelcomePage' => false,
        'prejoinPageEnabled' => true,
        'disableDeepLinking' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Configurações de Interface
    |--------------------------------------------------------------------------
    |
    | Personalização da interface do Jitsi Meet.
    |
    */

    'interface_config' => [
        'SHOW_JITSI_WATERMARK' => false,
        'SHOW_WATERMARK_FOR_GUESTS' => false,
        'DEFAULT_BACKGROUND' => '#474747',
        'DISABLE_VIDEO_BACKGROUND' => false,
        'TOOLBAR_BUTTONS' => [
            'microphone',
            'camera',
            'closedcaptions',
            'desktop',
            'fullscreen',
            'fodeviceselection',
            'hangup',
            'profile',
            'chat',
            'recording',
            'livestreaming',
            'etherpad',
            'sharedvideo',
            'settings',
            'raisehand',
            'videoquality',
            'filmstrip',
            'invite',
            'feedback',
            'stats',
            'shortcuts',
            'tileview',
            'videobackgroundblur',
            'download',
            'help',
            'mute-everyone',
        ],
    ],
];
