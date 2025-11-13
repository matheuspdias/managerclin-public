# Configuração JaaS (Jitsi as a Service) - 8x8

Este documento explica como integrar o JaaS da 8x8 com o módulo de telemedicina do ManagerClin.

## 📋 Configuração no `.env`

Adicione as seguintes variáveis no seu arquivo `.env`:

```env
# Provedor de telemedicina (jaas, jitsi ou custom)
TELEMEDICINE_PROVIDER=jaas

# URL do servidor JaaS
TELEMEDICINE_SERVER_URL=https://8x8.vc

# App ID fornecido pelo JaaS (formato: vpaas-magic-cookie-xxx)
# Obtenha seu App ID em: https://jaas.8x8.vc/
TELEMEDICINE_APP_ID=vpaas-magic-cookie-your-app-id-here
```

## 🔗 Formato da URL JaaS

Quando você usa o JaaS, a URL da sala tem o seguinte formato:

```
https://8x8.vc/{APP_ID}/{ROOM_NAME}
```

Exemplo:
```
https://8x8.vc/vpaas-magic-cookie-seu-app-id/consultation-123-abc456
```

O sistema **automaticamente** constrói a URL correta usando o accessor `join_url` do model `TelemedicineSession`.

## 🚀 Como Integrar no Frontend (React Native / React)

### 1. Obter Configurações do JaaS

Primeiro, busque as configurações do backend:

```typescript
import { api } from './services/api';

const getTelemedicineConfig = async () => {
  const response = await api.get('/telemedicine/config');
  return response.data.data;

  // Retorna:
  // {
  //   provider: 'jaas',
  //   server_url: 'https://8x8.vc',
  //   app_id: 'vpaas-magic-cookie-xxx',
  //   jitsi_config: { ... },
  //   interface_config: { ... }
  // }
};
```

### 2. Criar Sessão de Telemedicina

```typescript
const createSession = async (appointmentId: number) => {
  const response = await api.post('/telemedicine/sessions', {
    appointment_id: appointmentId
  });

  return response.data.data;

  // Retorna:
  // {
  //   session_id: 1,
  //   room_name: 'consultation-123-abc456',
  //   server_url: 'https://8x8.vc',
  //   join_url: 'https://8x8.vc/vpaas-magic-cookie-xxx/consultation-123-abc456',
  //   status: 'WAITING',
  //   appointment: { ... }
  // }
};
```

### 3. Integrar com Jitsi Meet SDK (React Native)

#### Instalação

```bash
npm install @jitsi/react-native-sdk
```

#### Implementação Básica

```typescript
import { JitsiMeeting } from '@jitsi/react-native-sdk';
import { useState, useEffect } from 'react';

const TelemedicineScreen = ({ appointmentId }) => {
  const [config, setConfig] = useState(null);
  const [session, setSession] = useState(null);

  useEffect(() => {
    // Carregar configurações
    const loadConfig = async () => {
      const telemedicineConfig = await getTelemedicineConfig();
      setConfig(telemedicineConfig);

      // Criar sessão
      const newSession = await createSession(appointmentId);
      setSession(newSession);
    };

    loadConfig();
  }, [appointmentId]);

  if (!config || !session) {
    return <Loading />;
  }

  return (
    <JitsiMeeting
      domain={config.server_url.replace('https://', '')} // '8x8.vc'
      roomName={`${config.app_id}/${session.room_name}`} // 'vpaas-magic-cookie-xxx/consultation-123'
      serverURL={config.server_url} // 'https://8x8.vc'

      // Configurações customizadas
      configOverwrite={{
        ...config.jitsi_config,
        subject: `Consulta - Agendamento #${appointmentId}`,
      }}

      // Interface customizada
      interfaceConfigOverwrite={config.interface_config}

      // Callbacks de eventos
      onConferenceJoined={() => {
        console.log('Entrou na conferência');
        // Atualizar status para ACTIVE
        api.patch(`/telemedicine/sessions/${session.session_id}`, {
          status: 'ACTIVE'
        });
      }}

      onConferenceTerminated={() => {
        console.log('Conferência finalizada');
        // Finalizar sessão
        api.post(`/telemedicine/sessions/${session.session_id}/end`, {
          end_reason: 'Conferência encerrada pelo usuário'
        });
      }}

      onReadyToClose={() => {
        console.log('Pronto para fechar');
        // Navegar de volta
      }}
    />
  );
};
```

### 4. Integrar com Jitsi Meet SDK (Web/React)

#### Instalação

```bash
npm install @jitsi/react-sdk
```

#### Implementação

```typescript
import { JitsiMeeting } from '@jitsi/react-sdk';

const TelemedicineWebView = ({ appointmentId }) => {
  const [config, setConfig] = useState(null);
  const [session, setSession] = useState(null);

  useEffect(() => {
    // Carregar configurações e criar sessão (mesmo código acima)
  }, [appointmentId]);

  if (!config || !session) {
    return <Loading />;
  }

  return (
    <JitsiMeeting
      domain={config.server_url.replace('https://', '')}
      roomName={`${config.app_id}/${session.room_name}`}

      configOverwrite={{
        ...config.jitsi_config,
        startWithAudioMuted: false,
        startWithVideoMuted: false,
      }}

      interfaceConfigOverwrite={config.interface_config}

      userInfo={{
        displayName: 'Dr. João Silva', // Nome do usuário atual
        email: 'joao.silva@clinica.com'
      }}

      onApiReady={(externalApi) => {
        // API externa disponível para controlar a reunião
        externalApi.addEventListener('videoConferenceJoined', () => {
          console.log('Entrou na conferência');
          api.patch(`/telemedicine/sessions/${session.session_id}`, {
            status: 'ACTIVE'
          });
        });

        externalApi.addEventListener('videoConferenceLeft', () => {
          console.log('Saiu da conferência');
          api.post(`/telemedicine/sessions/${session.session_id}/end`);
        });
      }}

      getIFrameRef={(iframeRef) => {
        iframeRef.style.height = '100vh';
      }}
    />
  );
};
```

## 🔄 Fluxo Completo

1. **Frontend chama** `GET /api/telemedicine/config` para obter configurações
2. **Frontend chama** `POST /api/telemedicine/sessions` com `appointment_id`
3. **Backend retorna** `join_url` já formatada corretamente:
   - JaaS: `https://8x8.vc/vpaas-magic-cookie-xxx/consultation-123-abc456`
   - Jitsi: `https://meet.jit.si/consultation-123-abc456`
4. **Frontend** usa o SDK do Jitsi para inicializar a videoconferência
5. **Ao entrar**, frontend atualiza status para `ACTIVE` via `PATCH /api/telemedicine/sessions/{id}`
6. **Ao sair**, frontend finaliza sessão via `POST /api/telemedicine/sessions/{id}/end`

## 📊 Endpoints Disponíveis

| Método | Endpoint | Descrição |
|--------|----------|-----------|
| GET | `/api/telemedicine/config` | Obter configurações do JaaS/Jitsi |
| POST | `/api/telemedicine/sessions` | Criar nova sessão |
| GET | `/api/telemedicine/sessions/appointment/{id}` | Buscar sessão por agendamento |
| GET | `/api/telemedicine/sessions/active` | Listar sessões ativas |
| PATCH | `/api/telemedicine/sessions/{id}` | Atualizar status da sessão |
| POST | `/api/telemedicine/sessions/{id}/end` | Finalizar sessão |

## 🔐 Segurança

- Todas as rotas estão protegidas por `auth:sanctum` e `company.active`
- O App ID do JaaS **não é sensível** e pode ser exposto no frontend
- Para segurança adicional, considere implementar JWT tokens do JaaS

## 📱 Diferenças entre Provedores

### JaaS (8x8)
- ✅ **Vantagens**: SLA garantido, suporte empresarial, customização avançada
- 💰 **Custo**: Pago (baseado em minutos de uso)
- 🔗 **URL**: `https://8x8.vc/{app_id}/{room_name}`

### Jitsi Público
- ✅ **Vantagens**: Gratuito, sem necessidade de conta
- ⚠️ **Limitações**: Sem SLA, pode ter instabilidade em horários de pico
- 🔗 **URL**: `https://meet.jit.si/{room_name}`

### Jitsi Custom (Auto-hospedado)
- ✅ **Vantagens**: Controle total, privacidade máxima
- 💻 **Requisitos**: Servidor próprio, conhecimento técnico
- 🔗 **URL**: `https://seu-dominio.com/{room_name}`

## 🆘 Troubleshooting

### Erro: "Failed to join conference"
- Verifique se o `TELEMEDICINE_APP_ID` está correto
- Confirme que o domínio `8x8.vc` está acessível
- Verifique as configurações de firewall

### Erro: "Invalid room name"
- Room names devem ser únicos
- Não use caracteres especiais além de `-` e `_`
- O sistema já gera room names seguros automaticamente

### Vídeo/Áudio não funciona
- Verifique permissões de câmera/microfone no dispositivo
- Teste em um navegador atualizado
- Verifique se há bloqueadores de popup/scripts

## 📚 Recursos Adicionais

- [JaaS Documentation](https://jaas.8x8.vc/#/)
- [Jitsi Meet SDK (React Native)](https://github.com/jitsi/jitsi-meet-react-native-sdk)
- [Jitsi Meet SDK (Web)](https://github.com/jitsi/jitsi-meet-react-sdk)
- [Jitsi Meet API](https://jitsi.github.io/handbook/docs/dev-guide/dev-guide-iframe)
