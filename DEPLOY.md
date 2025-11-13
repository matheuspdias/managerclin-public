# 🚀 Deploy em Produção

Este guia explica como o deploy automático funciona via GitHub Actions com configuração do WhatsApp Scheduler.

## 📋 Pré-requisitos

- Docker e Docker Compose instalados no servidor
- GitHub Actions configurado com SSH key
- Servidor com acesso root configurado

## 🔧 Deploy Automático via GitHub Actions

### Como Funciona

O deploy é **totalmente automatizado** via GitHub Actions quando você cria uma tag:

```bash
# Criar tag e fazer push
git tag 1.0.0
git push origin 1.0.0
```

**O workflow automaticamente:**
- ✅ Faz checkout do código no servidor
- ✅ Build das imagens Docker
- ✅ Sobe containers atualizados
- ✅ **Configura cron automaticamente**
- ✅ Verifica se aplicação está funcionando
- ✅ Notifica via Discord

### Deploy Manual (Emergência)

Se precisar fazer deploy manual no servidor:

```bash
cd /home/deploy/clinica-app
git pull origin main
docker compose -f docker-compose.prod.yml up -d --build
```

## 📊 Monitoramento

### Verificar Status dos Containers
```bash
docker compose -f docker-compose.prod.yml ps
```

### Logs da Aplicação
```bash
# Aplicação principal
docker compose -f docker-compose.prod.yml logs -f laravel_app

# Worker de filas
docker compose -f docker-compose.prod.yml logs -f worker
```

### Logs do Laravel Scheduler
```bash
# Ver logs do cron
tail -f /var/log/laravel-scheduler.log

# Verificar crontab
crontab -l
```

### Logs do WhatsApp
```bash
# Dentro do container da aplicação
docker compose -f docker-compose.prod.yml exec laravel_app tail -f storage/logs/laravel.log | grep WhatsApp
```

## 🔍 Verificação do Sistema WhatsApp

### Testar Scheduler Manualmente
```bash
# Executar comando manualmente
docker compose -f docker-compose.prod.yml exec laravel_app php artisan app:whatsapp-send-appointments-notifications

# Verificar jobs na fila
docker compose -f docker-compose.prod.yml exec laravel_app php artisan queue:monitor
```

### Debug de Agendamento Específico
```bash
# Debugar agendamento específico
docker compose -f docker-compose.prod.yml exec laravel_app php artisan app:debug-specific-appointment {ID}

# Forçar notificação manualmente
docker compose -f docker-compose.prod.yml exec laravel_app php artisan app:force-whatsapp-notification {ID}
```

## 🛠️ Solução de Problemas

### Cron não está funcionando
```bash
# Verificar se cron está instalado
sudo systemctl status cron

# Verificar logs do cron
sudo tail -f /var/log/syslog | grep CRON

# Verificar se cron job existe
crontab -l | grep schedule:run
```

### Worker não processa jobs
```bash
# Verificar se worker está rodando
docker compose -f docker-compose.prod.yml ps worker

# Restart do worker
docker compose -f docker-compose.prod.yml restart worker

# Logs do worker
docker compose -f docker-compose.prod.yml logs -f worker
```

### Containers não sobem
```bash
# Verificar logs de erro
docker compose -f docker-compose.prod.yml logs

# Rebuild forçado
docker compose -f docker-compose.prod.yml build --no-cache
docker compose -f docker-compose.prod.yml up -d
```

## 📈 Configurações Avançadas

### Ajustar Intervalo do Scheduler
Edite o arquivo `routes/console.php`:
```php
Schedule::command('app:whatsapp-send-appointments-notifications')
    ->everyTenMinutes() // Alterar conforme necessário
```

### Configurar Workers Adicionais
Se precisar de mais performance, edite `docker-compose.prod.yml`:
```yaml
worker:
  deploy:
    replicas: 2  # Mais workers
```

---

**✅ Após cada deploy via tag, o sistema estará:**
- Executando scheduler a cada minuto via cron
- Enviando notificações WhatsApp automaticamente a cada 5 minutos
- Processando filas continuamente
- Monitorando e logando todas as atividades