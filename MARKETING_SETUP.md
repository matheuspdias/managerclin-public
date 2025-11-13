# 📱 Sistema de Marketing por WhatsApp - Guia de Configuração

## ⏰ Agendamento de Comandos

### Comando Configurado

O comando `app:process-marketing-campaigns` foi configurado para executar automaticamente a cada **5 minutos**.

```php
Schedule::command('app:process-marketing-campaigns')
    ->everyFiveMinutes()
    ->withoutOverlapping(15)
    ->runInBackground()
```

### Por que 5 minutos?

**Razões para o intervalo de 5 minutos:**

1. **Pontualidade**: Garante que campanhas agendadas sejam enviadas com no máximo 5 minutos de atraso
2. **Eficiência**: Não sobrecarrega o sistema (comparado a 1 minuto)
3. **Experiência do Usuário**: O usuário pode agendar para horários específicos e ter confiança no envio pontual
4. **Balanceamento**: Equilíbrio entre precisão e uso de recursos

### Alternativas de Intervalo

Você pode ajustar o intervalo conforme necessário em `routes/console.php`:

```php
// Opções disponíveis:
->everyMinute()          // A cada minuto (mais preciso, mais recursos)
->everyTwoMinutes()      // A cada 2 minutos
->everyFiveMinutes()     // A cada 5 minutos ✅ RECOMENDADO
->everyTenMinutes()      // A cada 10 minutos
->everyFifteenMinutes()  // A cada 15 minutos
->everyThirtyMinutes()   // A cada 30 minutos
->hourly()               // A cada hora (apenas para campanhas menos urgentes)
```

## 🚀 Ativação do Scheduler

### Método 1: Cron do Sistema (Produção)

Adicione esta linha ao crontab do servidor:

```bash
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

**Como configurar:**

```bash
# Abrir crontab
crontab -e

# Adicionar a linha (substitua o caminho pelo seu projeto)
* * * * * cd /home/matheus/development/projetos/inertiajs-clinica-agenda && php artisan schedule:run >> /dev/null 2>&1
```

### Método 2: Docker Compose (Desenvolvimento)

Se estiver usando Docker, adicione um serviço scheduler ao `docker-compose.yml`:

```yaml
scheduler:
  build:
    context: .
    dockerfile: Dockerfile
  container_name: clinica_scheduler
  working_dir: /var/www
  volumes:
    - ./:/var/www
  command: php artisan schedule:work
  depends_on:
    - app
    - db
  networks:
    - clinica-network
```

### Método 3: Supervisor (Produção Recomendado)

Crie um arquivo de configuração do Supervisor:

```ini
[program:clinica-scheduler]
process_name=%(program_name)s
command=php /home/matheus/development/projetos/inertiajs-clinica-agenda/artisan schedule:work
autostart=true
autorestart=true
user=www-data
redirect_stderr=true
stdout_logfile=/var/log/clinica-scheduler.log
```

## 📊 Monitoramento

### Logs do Sistema

Os comandos geram logs automáticos:

```bash
# Ver logs de sucesso
tail -f storage/logs/laravel.log | grep "Marketing campaigns"

# Logs específicos
grep "Marketing campaigns" storage/logs/laravel.log
```

### Verificar Próximas Execuções

```bash
# Listar comandos agendados
php artisan schedule:list

# Testar o comando manualmente
php artisan app:process-marketing-campaigns
```

### Dashboard de Monitoramento (Opcional)

Considere adicionar o [Laravel Horizon](https://laravel.com/docs/horizon) ou [Laravel Telescope](https://laravel.com/docs/telescope) para monitoramento visual.

## 🎯 Fluxo de Funcionamento

### 1. Criação da Campanha
- Usuário cria campanha na interface
- Define público-alvo e mensagem
- Agenda data/hora de envio

### 2. Processamento Automático
- A cada 5 minutos, o scheduler executa o comando
- Comando busca campanhas com `status = 'scheduled'` e `scheduled_at <= now()`
- Valida se empresa tem plano Pro/Premium
- Cria jobs individuais para cada destinatário

### 3. Envio das Mensagens
- Cada job processa um destinatário
- Normaliza número de telefone
- Envia via WhatsApp
- Atualiza status (enviado/falhou)
- Registra erros se houver falha

### 4. Finalização
- Comando atualiza estatísticas da campanha
- Marca campanha como 'sent' quando todos os destinatários foram processados
- Gera logs de sucesso/falha

## ⚙️ Configurações Avançadas

### Ajustar Delay Entre Mensagens

No arquivo `app/Console/Commands/ProcessMarketingCampaigns.php` (linha ~82):

```php
// Delay aleatório atual: 1 a 10 segundos
SendMarketingCampaignMessage::dispatch($recipient->id, $campaign->message, $config)
    ->delay(now()->addSeconds(rand(1, 10)));

// Aumentar para 5 a 15 segundos (menos spam)
SendMarketingCampaignMessage::dispatch($recipient->id, $campaign->message, $config)
    ->delay(now()->addSeconds(rand(5, 15)));
```

### Limitar Processamento por Execução

Para evitar sobrecarga, limite o número de campanhas processadas por vez:

```php
// Em ProcessMarketingCampaigns.php
$campaigns = $this->campaignService->getPendingCampaigns()
    ->take(5); // Processar apenas 5 campanhas por execução
```

## 🔍 Troubleshooting

### Campanhas não estão sendo enviadas

1. **Verificar se o scheduler está rodando:**
   ```bash
   php artisan schedule:list
   ```

2. **Executar comando manualmente:**
   ```bash
   php artisan app:process-marketing-campaigns
   ```

3. **Verificar logs:**
   ```bash
   tail -f storage/logs/laravel.log
   ```

4. **Verificar status da campanha:**
   ```sql
   SELECT id, name, status, scheduled_at FROM marketing_campaigns;
   ```

### Queue não está processando

1. **Verificar worker da queue:**
   ```bash
   php artisan queue:work --tries=3
   ```

2. **Verificar configuração da queue:**
   ```bash
   # Em .env
   QUEUE_CONNECTION=database
   ```

3. **Limpar jobs falhados:**
   ```bash
   php artisan queue:flush
   ```

## 📈 Melhores Práticas

### Horários Recomendados para Campanhas

- ✅ **09h - 12h**: Horário comercial manhã
- ✅ **14h - 18h**: Horário comercial tarde
- ❌ **22h - 08h**: Evitar (horário de descanso)
- ❌ **Domingos e feriados**: Considerar com cuidado

### Tamanho das Campanhas

- **Pequenas** (até 100 destinatários): Pode enviar de uma vez
- **Médias** (100-500): Agendar em horário comercial
- **Grandes** (500+): Considerar dividir em múltiplas campanhas

### Frequência de Envio

- Evite enviar mais de 1 campanha por dia para o mesmo público
- Aguarde pelo menos 3-7 dias entre campanhas similares
- Monitore taxa de resposta e ajuste frequência

## 🔒 Segurança e Compliance

### LGPD e Consentimento

- Certifique-se de ter consentimento dos pacientes para receber mensagens
- Inclua opção de descadastramento nas mensagens
- Mantenha registro do consentimento

### Limite de Taxa (Rate Limiting)

O sistema já inclui delay aleatório entre mensagens. Para maior controle:

```php
// Adicionar throttling no Job
use Illuminate\Support\Facades\RateLimiter;

RateLimiter::attempt(
    'send-marketing-'.$this->recipientId,
    $perMinute = 10,
    function() {
        // Enviar mensagem
    }
);
```

## 📞 Suporte

Para problemas ou dúvidas sobre o sistema de marketing:

1. Verifique os logs em `storage/logs/laravel.log`
2. Execute o comando manualmente para debug
3. Verifique configurações de plano da empresa
4. Confirme que WhatsApp está configurado corretamente

---

**Última atualização:** 2025-10-13
**Versão:** 1.0.0
