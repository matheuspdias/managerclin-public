# 🚀 Guia de Instalação - ManagerClin

Este guia contém instruções detalhadas para instalar e configurar o ManagerClin em ambiente de desenvolvimento e produção.

## 📋 Pré-requisitos

### Ambiente de Desenvolvimento

- **Docker** 20.10+ e **Docker Compose** 2.0+
- **Git** 2.30+
- **Node.js** 18+ (se rodar localmente sem Docker)
- **PHP** 8.3+ (se rodar localmente sem Docker)
- **Composer** 2.0+ (se rodar localmente sem Docker)

### Ambiente de Produção

- Servidor Linux (Ubuntu 20.04+ recomendado)
- Docker e Docker Compose instalados
- Domínio configurado
- Certificado SSL (recomendado: Let's Encrypt)

---

## 🛠️ Instalação para Desenvolvimento

### 1. Clone o Repositório

```bash
git clone https://github.com/yourusername/managerclin.git
cd managerclin
```

### 2. Configure as Variáveis de Ambiente

```bash
cp .env.example .env
```

Edite o arquivo `.env` com suas configurações:

```env
APP_NAME=ManagerClin
APP_ENV=local
APP_KEY=base64:...
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=managerclin
DB_USERNAME=root
DB_PASSWORD=secret

REDIS_HOST=redis
REDIS_PASSWORD=null
REDIS_PORT=6379

# Evolution API (WhatsApp)
EVOLUTION_API_KEY=your-evolution-api-key
WHATSAPP_API_URL=https://your-evolution-api.com

# Stripe (Pagamentos)
STRIPE_KEY=pk_test_...
STRIPE_SECRET=sk_test_...
STRIPE_WEBHOOK_SECRET=whsec_...

# Email
MAIL_MAILER=smtp
MAIL_HOST=mailhog
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
```

### 3. Inicie os Containers Docker

```bash
docker-compose up -d
```

Containers criados:
- `app` - Aplicação Laravel
- `mysql` - Banco de dados
- `redis` - Cache e filas
- `node` - Build do frontend
- `mailhog` - Servidor de email para testes (http://localhost:8025)

### 4. Instale as Dependências

#### PHP (Backend)
```bash
docker-compose exec app composer install
```

#### Node.js (Frontend)
```bash
docker-compose exec node npm install
```

### 5. Gere a Application Key

```bash
docker-compose exec app php artisan key:generate
```

### 6. Execute as Migrations e Seeders

```bash
# Criar as tabelas
docker-compose exec app php artisan migrate

# Popular com dados de teste
docker-compose exec app php artisan db:seed
```

### 7. Configure o Storage

```bash
docker-compose exec app php artisan storage:link
```

### 8. Inicie os Serviços de Desenvolvimento

Com os containers rodando (`docker-compose up -d`), você precisa iniciar os workers e o Vite.

Execute os seguintes comandos em terminais separados:

```bash
# Terminal 1 - Queue Worker (processa jobs: emails, notificações, etc)
docker-compose exec app php artisan queue:work

# Terminal 2 - Scheduler (executa tarefas agendadas: lembretes WhatsApp, etc)
docker-compose exec app php artisan schedule:work

# Terminal 3 - Vite (Frontend com Hot Module Replacement)
docker-compose exec node npm run dev
```

**O que cada comando faz:**
- **`queue:work`** - Processa jobs da fila (envio de emails, notificações assíncronas)
- **`schedule:work`** - Executa tarefas agendadas a cada minuto (lembretes de consultas, relatórios automáticos)
- **`npm run dev`** - Build do frontend com hot reload

**Nota:** O servidor web já está rodando via Docker (Nginx/Apache), então não é necessário rodar `php artisan serve`.

### 9. Acesse a Aplicação

- **Frontend**: http://localhost:8000
- **MailHog** (emails): http://localhost:8025
- **MySQL**: localhost:3306

#### Credenciais Padrão (após seed)
```
Email: admin@managerclin.com.br
Senha: password
```

---

## 🚀 Deploy em Produção

### Automático via GitHub Actions

#### 1. Configure os Secrets no GitHub

Vá em Settings → Secrets → Actions e adicione:

```
SERVER_HOST=seu-servidor.com
SERVER_USER=deploy
SERVER_SSH_KEY=<sua-chave-privada-ssh>
DISCORD_WEBHOOK_URL=https://discord.com/api/webhooks/...
```

#### 2. Crie e Faça Push de uma Tag

```bash
git tag v1.0.0
git push origin v1.0.0
```

O deploy será automaticamente executado via GitHub Actions.

### Manual

#### 1. Preparar o Servidor

```bash
# Instalar Docker
curl -fsSL https://get.docker.com -o get-docker.sh
sh get-docker.sh

# Instalar Docker Compose
sudo curl -L "https://github.com/docker/compose/releases/latest/download/docker-compose-$(uname -s)-$(uname -m)" -o /usr/local/bin/docker-compose
sudo chmod +x /usr/local/bin/docker-compose
```

#### 2. Configurar o Projeto

```bash
# Clone o repositório
git clone https://github.com/yourusername/managerclin.git
cd managerclin

# Configure o ambiente
cp .env.example .env.production
nano .env.production
```

Configure as variáveis de produção:

```env
APP_NAME=ManagerClin
APP_ENV=production
APP_DEBUG=false
APP_URL=https://seu-dominio.com.br

DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=managerclin_prod
DB_USERNAME=managerclin_user
DB_PASSWORD=<senha-segura>

REDIS_HOST=redis
REDIS_PASSWORD=<senha-segura>
REDIS_PORT=6379

# Evolution API
EVOLUTION_API_KEY=<sua-chave-producao>
WHATSAPP_API_URL=https://sua-api-evolution.com

# Stripe
STRIPE_KEY=pk_live_...
STRIPE_SECRET=sk_live_...
STRIPE_WEBHOOK_SECRET=whsec_...

# Email (SendGrid/Mailgun)
MAIL_MAILER=smtp
MAIL_HOST=smtp.sendgrid.net
MAIL_PORT=587
MAIL_USERNAME=apikey
MAIL_PASSWORD=<sua-api-key>
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@seu-dominio.com.br
MAIL_FROM_NAME="${APP_NAME}"
```

#### 3. Build e Deploy

```bash
# Build das imagens
docker-compose -f docker-compose.prod.yml build

# Iniciar os containers
docker-compose -f docker-compose.prod.yml up -d

# Instalar dependências
docker-compose -f docker-compose.prod.yml exec laravel_app composer install --optimize-autoloader --no-dev

# Executar migrations
docker-compose -f docker-compose.prod.yml exec laravel_app php artisan migrate --force

# Otimizações de produção
docker-compose -f docker-compose.prod.yml exec laravel_app php artisan config:cache
docker-compose -f docker-compose.prod.yml exec laravel_app php artisan route:cache
docker-compose -f docker-compose.prod.yml exec laravel_app php artisan view:cache
docker-compose -f docker-compose.prod.yml exec laravel_app php artisan event:cache

# Storage link
docker-compose -f docker-compose.prod.yml exec laravel_app php artisan storage:link
```

#### 4. Configurar o Scheduler (Cron)

O scheduler do Laravel precisa ser executado a cada minuto:

```bash
# Editar crontab
crontab -e

# Adicionar linha
* * * * * cd /caminho/para/managerclin && docker-compose -f docker-compose.prod.yml exec -T laravel_app php artisan schedule:run >> /var/log/laravel-scheduler.log 2>&1
```

#### 5. Configurar SSL (Let's Encrypt)

```bash
# Instalar Certbot
sudo apt-get update
sudo apt-get install certbot python3-certbot-nginx

# Obter certificado
sudo certbot --nginx -d seu-dominio.com.br -d www.seu-dominio.com.br

# Renovação automática (já configurada pelo Certbot)
sudo certbot renew --dry-run
```

---

## 📱 Configuração do WhatsApp

### 1. Evolution API

Você precisa de uma instância do Evolution API rodando. Opções:

- Self-hosted: https://github.com/EvolutionAPI/evolution-api
- Cloud: Contratar um provedor

### 2. Configurar no ManagerClin

1. Acesse: **Configurações → Integrações → WhatsApp**
2. Preencha:
   - **API URL**: URL da sua Evolution API
   - **API Key**: Sua chave de autenticação
   - **Instance Name**: Nome da sua instância

3. Conecte o WhatsApp:
   - Clique em "Conectar WhatsApp"
   - Escaneie o QR Code com seu WhatsApp

### 3. Testar Notificações

```bash
# Via tinker
docker-compose exec app php artisan tinker

# Testar envio
$service = app(\App\Services\Whatsapp\WhatsappService::class);
$service->sendMessage('5511999999999', 'Teste de mensagem');
```

---

## 💳 Configuração do Stripe

### 1. Obter as Chaves

1. Acesse: https://dashboard.stripe.com/apikeys
2. Copie as chaves (Publishable key e Secret key)
3. Configure no `.env`:

```env
STRIPE_KEY=pk_live_...
STRIPE_SECRET=sk_live_...
```

### 2. Configurar Webhook

1. Vá em: https://dashboard.stripe.com/webhooks
2. Adicione endpoint: `https://seu-dominio.com.br/stripe/webhook`
3. Selecione eventos:
   - `customer.subscription.created`
   - `customer.subscription.updated`
   - `customer.subscription.deleted`
   - `invoice.payment_succeeded`
   - `invoice.payment_failed`
4. Copie o Webhook Secret:

```env
STRIPE_WEBHOOK_SECRET=whsec_...
```

### 3. Criar Produtos e Preços

```bash
# Via tinker
docker-compose exec app php artisan tinker

# Criar produto
$stripe = new \Stripe\StripeClient(env('STRIPE_SECRET'));
$product = $stripe->products->create([
    'name' => 'Plano Pro',
    'description' => 'Plano profissional com WhatsApp e IA'
]);

# Criar preço
$price = $stripe->prices->create([
    'product' => $product->id,
    'unit_amount' => 9700, // R$ 97,00 em centavos
    'currency' => 'brl',
    'recurring' => ['interval' => 'month']
]);
```

---

## 🔧 Comandos Úteis

### Desenvolvimento

```bash
# Limpar caches
docker-compose exec app php artisan optimize:clear

# Rodar migrations
docker-compose exec app php artisan migrate

# Rodar migrations fresh (apaga tudo)
docker-compose exec app php artisan migrate:fresh --seed

# Rodar testes
composer test

# Lint e format
npm run lint
npm run format

# Type checking
npm run types

# Build frontend
npm run build
```

### Produção

```bash
# Ver logs
docker-compose -f docker-compose.prod.yml logs -f laravel_app

# Status dos containers
docker-compose -f docker-compose.prod.yml ps

# Restart de um serviço
docker-compose -f docker-compose.prod.yml restart laravel_app

# Otimizar aplicação
docker-compose -f docker-compose.prod.yml exec laravel_app php artisan optimize

# Limpar caches
docker-compose -f docker-compose.prod.yml exec laravel_app php artisan optimize:clear
```

### Monitoramento

```bash
# Laravel logs
docker-compose -f docker-compose.prod.yml exec laravel_app tail -f storage/logs/laravel.log

# Logs do scheduler
tail -f /var/log/laravel-scheduler.log

# Logs do worker
docker-compose -f docker-compose.prod.yml logs -f worker

# Logs WhatsApp
docker-compose -f docker-compose.prod.yml exec laravel_app tail -f storage/logs/laravel.log | grep WhatsApp
```

---

## 🛠️ Solução de Problemas

### Containers não sobem

```bash
# Rebuild forçado
docker-compose down -v
docker-compose build --no-cache
docker-compose up -d --force-recreate
```

### Erro de permissão

```bash
# Ajustar permissões
sudo chown -R $USER:$USER .
sudo chmod -R 755 storage bootstrap/cache
```

### WhatsApp não funciona

```bash
# Verificar configuração
docker-compose exec app php artisan tinker
>>> app(\App\Services\Whatsapp\WhatsappService::class)->getConfig(1);

# Verificar logs
docker-compose exec app tail -f storage/logs/laravel.log | grep WhatsApp

# Testar envio manual
>>> $service = app(\App\Services\Whatsapp\WhatsappService::class);
>>> $service->sendMessage('5511999999999', 'Teste');
```

### Performance Issues

```bash
# Otimizar para produção
docker-compose exec app php artisan optimize

# Limpar todos os caches
docker-compose exec app php artisan optimize:clear

# Restart do Redis
docker-compose restart redis

# Restart do worker
docker-compose restart worker
```

### Banco de dados corrompido

```bash
# Backup (sempre!)
docker-compose exec mysql mysqldump -u root -p managerclin > backup.sql

# Recrear database
docker-compose exec app php artisan migrate:fresh

# Restaurar backup
docker-compose exec -T mysql mysql -u root -p managerclin < backup.sql
```

### Limpeza de espaço em disco

```bash
# Limpar Docker
docker system prune -af --volumes

# Limpar logs Laravel
docker-compose exec app bash -c "echo '' > storage/logs/laravel.log"

# Limpar cache do APT (servidor)
sudo apt-get clean
sudo rm -rf /var/lib/apt/lists/*
```

---

## 📊 Verificações Pós-Deploy

### Checklist

- [ ] Aplicação está acessível via HTTPS
- [ ] SSL válido e renovação automática configurada
- [ ] Banco de dados com dados corretos
- [ ] Redis funcionando (cache)
- [ ] Queue worker rodando
- [ ] Scheduler configurado (cron)
- [ ] WhatsApp conectado e enviando mensagens
- [ ] Stripe webhook funcionando
- [ ] Emails sendo enviados corretamente
- [ ] Logs sendo gerados corretamente
- [ ] Backups automáticos configurados

### Testes Manuais

```bash
# 1. Testar cache
docker-compose exec app php artisan tinker
>>> Cache::put('test', 'working', 60);
>>> Cache::get('test'); // deve retornar 'working'

# 2. Testar queue
>>> dispatch(new \App\Jobs\TestJob());
# Verificar logs: docker-compose logs -f worker

# 3. Testar email
>>> Mail::raw('Teste', function($msg) { $msg->to('seu@email.com')->subject('Teste'); });

# 4. Testar WhatsApp
>>> app(\App\Services\Whatsapp\WhatsappService::class)->sendMessage('5511999999999', 'Teste deploy');
```

---

## 📖 Documentação Adicional

- [CLAUDE.md](CLAUDE.md) - Guia para desenvolvimento com IA
- [DEPLOY.md](DEPLOY.md) - Detalhes do processo de deploy
- [WHATSAPP_SCHEDULER.md](WHATSAPP_SCHEDULER.md) - Configuração de notificações WhatsApp

---

## 📞 Suporte

Se encontrar problemas durante a instalação:

1. Verifique os logs: `docker-compose logs -f`
2. Consulte a seção de [Solução de Problemas](#-solução-de-problemas)
3. Abra uma issue: [GitHub Issues](https://github.com/yourusername/managerclin/issues)
4. Entre em contato: suporte@managerclin.com.br

---

**⚠️ Importante**: Sempre faça backup antes de atualizações em produção!
