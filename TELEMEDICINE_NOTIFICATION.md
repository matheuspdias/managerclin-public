# Notificação WhatsApp para Teleconsulta

Este documento explica como usar o endpoint de notificação WhatsApp para teleconsultas.

## 📋 Funcionalidade

O endpoint `POST /api/telemedicine/sessions/{sessionId}/notify` envia uma mensagem via WhatsApp para o paciente com o link de entrada na teleconsulta.

## 🚀 Como Usar

### 1. Criar Sessão de Teleconsulta

Primeiro, crie uma sessão de teleconsulta:

```typescript
const response = await api.post('/telemedicine/sessions', {
  appointment_id: 123
});

const session = response.data.data;
// session.session_id = 1
// session.join_url = 'https://8x8.vc/vpaas-magic-cookie-xxx/consultation-123-abc'
```

### 2. Notificar Paciente

Depois de criar a sessão, notifique o paciente via WhatsApp:

```typescript
const notifyResponse = await api.post(`/telemedicine/sessions/${session.session_id}/notify`);

console.log(notifyResponse.data);
// {
//   success: true,
//   data: {
//     session_id: 1,
//     patient_name: 'João Silva',
//     patient_phone: '5511999887766',
//     join_url: 'https://8x8.vc/vpaas-magic-cookie-xxx/consultation-123-abc',
//     message_sent: true
//   },
//   message: 'Notificação enviada com sucesso para o paciente.'
// }
```

## 📱 Mensagem Enviada

A mensagem enviada ao paciente tem o seguinte formato:

```
Olá João Silva! 👋

Sua teleconsulta com Dr. Maria Santos está pronta!

🔗 Clique no link para entrar:
https://8x8.vc/vpaas-magic-cookie-xxx/consultation-123-abc

⏰ A consulta já começou, entre agora!

📱 Certifique-se de permitir acesso à câmera e microfone.

Qualquer dúvida, entre em contato com a clínica.
```

## 🔧 Requisitos

### 1. WhatsApp Configurado

A empresa deve ter o WhatsApp configurado e habilitado. Verifique a configuração em:

```sql
SELECT * FROM whatsapp_configs WHERE id_company = ?
```

Campos necessários:
- `enabled` = true
- `api_url` (URL da API WhatsApp)
- `api_key` (Chave de autenticação)
- `instance_id` (ID da instância)

### 2. Paciente com Telefone

O paciente (customer) deve ter um número de telefone cadastrado:

```sql
SELECT phone FROM customers WHERE id = ?
```

O sistema **automaticamente normaliza** o telefone para o formato WhatsApp brasileiro:
- Remove caracteres não numéricos
- Adiciona código do país (55)
- Adiciona o 9 se necessário
- Formato final: `5511999887766`

## 📊 Respostas da API

### ✅ Sucesso (200)

```json
{
  "success": true,
  "data": {
    "session_id": 1,
    "patient_name": "João Silva",
    "patient_phone": "5511999887766",
    "join_url": "https://8x8.vc/vpaas-magic-cookie-xxx/consultation-123-abc",
    "message_sent": true
  },
  "message": "Notificação enviada com sucesso para o paciente."
}
```

### ❌ Sessão não encontrada (404)

```json
{
  "success": false,
  "message": "Sessão de telemedicina não encontrada."
}
```

### ❌ Paciente sem telefone (400)

```json
{
  "success": false,
  "message": "Paciente não possui telefone cadastrado."
}
```

### ❌ WhatsApp não configurado (400)

```json
{
  "success": false,
  "message": "WhatsApp não está configurado ou habilitado para esta empresa."
}
```

### ❌ Erro interno (500)

```json
{
  "success": false,
  "message": "Erro ao enviar notificação: [detalhes do erro]"
}
```

## 🔄 Fluxo Completo de Teleconsulta

```typescript
// 1. Criar sessão
const session = await api.post('/telemedicine/sessions', {
  appointment_id: appointmentId
});

// 2. Notificar paciente via WhatsApp
const notification = await api.post(
  `/telemedicine/sessions/${session.data.data.session_id}/notify`
);

// 3. Atualizar status para ACTIVE quando entrar
await api.patch(`/telemedicine/sessions/${session.data.data.session_id}`, {
  status: 'ACTIVE'
});

// 4. Finalizar sessão quando sair
await api.post(`/telemedicine/sessions/${session.data.data.session_id}/end`, {
  end_reason: 'Consulta concluída',
  notes: 'Paciente foi atendido com sucesso'
});
```

## 🎯 Exemplo Prático (React Native)

```typescript
import { api } from './services/api';
import { Alert } from 'react-native';

const TelemedicineService = {
  // Criar sessão e notificar paciente
  startTelemedicine: async (appointmentId: number) => {
    try {
      // 1. Criar sessão
      const sessionResponse = await api.post('/telemedicine/sessions', {
        appointment_id: appointmentId
      });

      const session = sessionResponse.data.data;

      // 2. Notificar paciente
      const notifyResponse = await api.post(
        `/telemedicine/sessions/${session.session_id}/notify`
      );

      if (notifyResponse.data.success) {
        Alert.alert(
          'Sucesso',
          `Paciente ${notifyResponse.data.data.patient_name} foi notificado via WhatsApp!`
        );
      }

      return session;

    } catch (error) {
      console.error('Erro ao iniciar teleconsulta:', error);
      Alert.alert('Erro', 'Não foi possível iniciar a teleconsulta.');
      throw error;
    }
  },

  // Atualizar status da sessão
  updateStatus: async (sessionId: number, status: string) => {
    try {
      await api.patch(`/telemedicine/sessions/${sessionId}`, {
        status: status
      });
    } catch (error) {
      console.error('Erro ao atualizar status:', error);
    }
  },

  // Finalizar sessão
  endSession: async (sessionId: number, notes?: string) => {
    try {
      await api.post(`/telemedicine/sessions/${sessionId}/end`, {
        end_reason: 'Consulta finalizada',
        notes: notes
      });
    } catch (error) {
      console.error('Erro ao finalizar sessão:', error);
    }
  }
};

export default TelemedicineService;
```

## 🔐 Segurança

- ✅ Endpoint protegido por `auth:sanctum` e `company.active`
- ✅ Validação de relacionamentos (session → appointment → customer)
- ✅ Logs de auditoria para todas as notificações enviadas
- ✅ Job em fila para não bloquear a resposta da API
- ✅ Tentativas automáticas em caso de falha (3 tentativas)

## 📈 Monitoramento

Os logs são registrados em:

```php
Log::info('Notificação de teleconsulta enviada', [
    'session_id' => 1,
    'appointment_id' => 123,
    'patient_id' => 456,
    'patient_name' => 'João Silva',
    'patient_phone' => '5511999887766',
]);
```

Você pode acompanhar os jobs na fila:

```bash
php artisan queue:work
```

## 🆘 Troubleshooting

### Mensagem não foi enviada

1. Verifique se o WhatsApp está configurado:
   ```sql
   SELECT * FROM whatsapp_configs WHERE id_company = ?
   ```

2. Verifique se o job foi processado:
   ```bash
   php artisan queue:failed
   ```

3. Verifique os logs:
   ```bash
   tail -f storage/logs/laravel.log
   ```

### Telefone inválido

O sistema normaliza automaticamente, mas se ainda houver problemas:
- Verifique se o telefone tem pelo menos 8 dígitos
- Formato esperado: `11999887766` ou `5511999887766`
- O sistema adiciona o código do país (55) se não tiver

## 📚 Recursos Relacionados

- [Documentação JaaS](TELEMEDICINE_JAAS.md)
- [Endpoints de Telemedicina](routes/api.php)
- [Job de WhatsApp](app/Jobs/SendWhatsappAppointmentNotification.php)
