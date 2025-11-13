# Configuração do Agendamento de Notificações WhatsApp

Este documento explica como configurar o sistema de notificações WhatsApp para rodar automaticamente a cada 5 minutos.

## 📋 Configuração Atual

### Comando Agendado

```php
Schedule::command('app:whatsapp-send-appointments-notifications')
    ->everyFiveMinutes()
    ->withoutOverlapping(10) // Evita sobreposição por 10 minutos
    ->runInBackground() // Roda em background
    ->onSuccess(function () {
        Log::info('WhatsApp notifications scheduled command completed successfully');
    })
    ->onFailure(function () {
        Log::error('WhatsApp notifications scheduled command failed');
    })
    ->description('Envia notificações de WhatsApp para agendamentos do dia e do dia seguinte');
```

## 🚀 Configuração no Servidor

### 1. Configurar Cron Job no Servidor

Adicione esta linha ao crontab do servidor:

```bash
# Editar crontab (como usuário deploy)
crontab -e

# Adicionar esta linha:
* * * * * cd /home/deploy/clinica-app && docker-compose exec -T laravel_app php artisan schedule:run >> /dev/null 2>&1
```

**Esta configuração executa o scheduler Laravel dentro do container a cada minuto.**

### 2. Worker de Fila (Automático)

O worker já está configurado no `docker-compose.prod.yml` e processa automaticamente todas as filas, incluindo WhatsApp:

```yaml
worker:
    container_name: laravel_worker
    command: php artisan queue:work --sleep=3 --tries=3 --timeout=30
    restart: unless-stopped
```

**Não é necessária configuração adicional** - o worker inicia automaticamente com o container.

## ⚙️ Funcionamento do Sistema

### Fluxo de Execução

1. **A cada 5 minutos**: Laravel executa o comando agendado
2. **Verificação de planos**: Apenas empresas em trial ou com plano Pro/Premium
3. **Busca agendamentos**: Para hoje e amanhã
4. **Criação de jobs**: Jobs são adicionados à fila `whatsapp`
5. **Normalização de telefones**: Garante formato correto (55XXXXXXXXXXX)
6. **Processamento**: Workers processam os jobs e enviam WhatsApp

### 📱 Normalização de Telefones

O sistema possui normalização automática para garantir compatibilidade:

**Formatos Suportados:**

- ✅ `11999999999` → `5511999999999` (11 dígitos)
- ✅ `1199999999` → `5511999999999` (10 dígitos + 9)
- ✅ `(11) 99999-9999` → `5511999999999` (formatado)
- ✅ `+55 11 99999-9999` → `5511999999999` (internacional)
- ✅ `5511999999999` → `5511999999999` (já correto)
- ✅ `99999999` → `5511999999999` (8 dígitos + código 11)

**Lógica de Normalização:**

1. Remove caracteres não numéricos
2. Identifica formato atual (8-13 dígitos)
3. Adiciona código do país (55) se necessário
4. Adiciona código de área padrão (11) para números incompletos
5. Adiciona 9 para celulares se necessário

### Tipos de Notificação

- **1 dia antes**: Lembrete enviado no dia anterior ao agendamento
- **3 horas antes**: Lembrete enviado 3 horas antes do agendamento (janela de 170-180 minutos)

### Verificação de Planos

```php
// Empresas que podem enviar:
✅ Em trial (trial_ends_at no futuro)
✅ Plano Pro ativo
✅ Plano Premium ativo

// Empresas que NÃO podem:
❌ Sem trial e sem plano
❌ Planos básicos
❌ Subscriptions inativas
```

## 🔍 Monitoramento

### Verificar Status

```bash
# Ver agendamentos configurados
php artisan schedule:list

# Executar manualmente para teste
php artisan app:whatsapp-send-appointments-notifications

# Ver jobs na fila
php artisan queue:monitor

# Ver logs em tempo real
tail -f storage/logs/laravel.log | grep WhatsApp
```

### Logs Importantes

- ✅ `WhatsApp notifications scheduled command completed successfully`
- ❌ `WhatsApp notifications scheduled command failed`
- 📊 `WhatsApp notifications command executed` (com contagem de jobs)
- 📱 `WhatsApp notification sent successfully`
- ⚠️ `Failed to send WhatsApp notification`

## 🛠️ Solução de Problemas

### Comando não executa

1. Verificar se o cron está configurado
2. Verificar permissões do usuário
3. Verificar logs: `tail -f /var/log/cron.log`

### Jobs não são processados

1. Verificar se o worker está rodando: `ps aux | grep queue:work`
2. Verificar configuração de filas no `.env`
3. Reiniciar workers: `php artisan queue:restart`

### Notificações não são enviadas

1. Verificar configuração do WhatsApp na empresa
2. Verificar se cliente tem telefone válido
3. Verificar logs de erro
4. Verificar se empresa tem plano válido

### Performance

```bash
# Ver estatísticas da fila
php artisan horizon:status  # se usando Horizon

# Ver jobs falhados
php artisan queue:failed

# Reprocessar jobs falhados
php artisan queue:retry all
```

## 📊 Métricas

Para monitorar a eficácia do sistema:

```bash
# Contar jobs processados hoje
grep "$(date +'%Y-%m-%d')" storage/logs/laravel.log | grep "WhatsApp notification sent successfully" | wc -l

# Ver empresas processadas
grep "$(date +'%Y-%m-%d')" storage/logs/laravel.log | grep "Processados agendamentos"

# Ver empresas bloqueadas por plano
grep "$(date +'%Y-%m-%d')" storage/logs/laravel.log | grep "não tem plano válido"
```

## 🔄 Atualizações

Após fazer alterações no código:

1. **Deploy do código**
2. **Reiniciar workers**: `php artisan queue:restart`
3. **Verificar logs**: Para confirmar funcionamento
4. **Teste manual**: `php artisan app:whatsapp-send-appointments-notifications`

## ⚡ Configuração de Desenvolvimento

Para desenvolvimento local:

```bash
# Executar scheduler a cada minuto (só para teste)
php artisan schedule:work

# Processar fila manualmente
php artisan queue:work --queue=whatsapp

# Executar comando manualmente
php artisan app:whatsapp-send-appointments-notifications --verbose
```

---

**⚠️ Importante**: Sempre teste em ambiente de desenvolvimento antes de colocar em produção!
