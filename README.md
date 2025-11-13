# 🏥 Clínica Agenda - Sistema de Agendamento Médico

Sistema completo de agendamento para clínicas médicas com notificações WhatsApp automáticas, construído com Laravel 12, React 19 e Inertia.js.

## ✨ Funcionalidades

- 📅 **Agendamento de Consultas** - Sistema completo de scheduling
- 👥 **Multi-tenant** - Isolamento por empresa/clínica
- 📱 **Notificações WhatsApp** - Lembretes automáticos via Evolution API
- 💳 **Integração Stripe** - Pagamentos e assinaturas
- 🔐 **Autenticação Multi-role** - Diferentes níveis de acesso
- 📋 **Prontuários Digitais** - Registro médico completo
- 📄 **Atestados Médicos** - Geração de PDFs com QR Code
- 🤖 **Chat IA** - Assistente integrado
- 🌙 **Dark Mode** - Interface adaptável

## 🛠️ Tech Stack

### Backend

- **Laravel 12** com PHP 8.3+
- **MySQL** - Banco de dados principal
- **Redis** - Cache e filas
- **Evolution API** - WhatsApp integration

### Frontend

- **React 19** com TypeScript
- **Inertia.js** - Full-stack framework
- **Tailwind CSS v4** - Styling
- **Radix UI** - Component library
- **Vite** - Build tool

### DevOps

- **Docker** - Containerização
- **GitHub Actions** - CI/CD
- **Nginx** - Web server
- **Supervisor** - Process management

## 🚀 Quick Start (Desenvolvimento)

### Pré-requisitos

- Docker e Docker Compose
- Git

### 1. Clone e Setup

```bash
git clone https://github.com/matheuspdias/inertiajs-clinica-agenda.git
cd inertiajs-clinica-agenda
cp .env.example .env
```

### 2. Iniciar Containers

```bash
docker-compose up -d
```

### 3. Instalar Dependências

```bash
# PHP dependencies
docker-compose exec app composer install

# Node dependencies
docker-compose exec node npm install
```

### 4. Database Setup

```bash
# Run migrations
docker-compose exec app php artisan migrate

# Seed database
docker-compose exec app php artisan db:seed
```

### 5. Desenvolvimento

```bash
# Start development stack (Laravel + Queue + Vite)
composer dev

# Or individual services
docker-compose exec app php artisan serve
docker-compose exec app php artisan queue:work
docker-compose exec node npm run dev
```

## 📱 WhatsApp Notifications

O sistema possui notificações automáticas configuradas para rodar a cada 5 minutos:

### Funcionalidades

- ✅ Lembrete 1 dia antes da consulta
- ✅ Lembrete 3 horas antes da consulta
- ✅ Normalização automática de telefones brasileiros
- ✅ Suporte apenas para planos Pro/Premium e trial

### Configuração

As notificações são automaticamente configuradas no deploy. Veja [WHATSAPP_SCHEDULER.md](WHATSAPP_SCHEDULER.md) para detalhes.

## 🚀 Deploy em Produção

### Automático via GitHub Actions

```bash
# Criar tag e fazer push
git tag 1.0.0
git push origin 1.0.0
```

O workflow automaticamente:

- ✅ Faz build das imagens Docker
- ✅ Deploy no servidor
- ✅ **Configura WhatsApp Scheduler**
- ✅ Verifica funcionamento
- ✅ Notifica via Discord

Veja [DEPLOY.md](DEPLOY.md) para detalhes completos.

## 🔧 Comandos de Desenvolvimento

### Laravel

```bash
# Migrations
docker-compose exec app php artisan migrate
docker-compose exec app php artisan migrate:fresh --seed

# Cache
docker-compose exec app php artisan config:cache
docker-compose exec app php artisan route:cache
docker-compose exec app php artisan view:cache

# Queue
docker-compose exec app php artisan queue:work
docker-compose exec app php artisan queue:restart
```

### Frontend

```bash
# Development
docker-compose exec node npm run dev

# Build
docker-compose exec node npm run build

# Lint & Format
npm run lint
npm run format
npm run types
```

### Testing

```bash
# PHP Tests
composer test

# Type checking
npm run types
```

## 📊 Monitoramento (Produção)

### Status dos Containers

```bash
docker compose -f docker-compose.prod.yml ps
```

### Logs

```bash
# Aplicação
docker compose -f docker-compose.prod.yml logs -f laravel_app

# Laravel Logs
docker compose -f docker-compose.prod.yml exec laravel_app tail -f storage/logs/laravel.log

# Worker
docker compose -f docker-compose.prod.yml logs -f worker

# WhatsApp Scheduler
tail -f /var/log/laravel-scheduler.log
```

### WhatsApp Debug

```bash
# Logs WhatsApp
docker compose -f docker-compose.prod.yml exec laravel_app tail -f storage/logs/laravel.log | grep WhatsApp
```

## 🔑 Variáveis de Ambiente

### Essenciais

```env
APP_ENV=production
APP_KEY=base64:...
APP_URL=https://yourdomain.com

DB_CONNECTION=mysql
DB_HOST=mysql
DB_DATABASE=clinica_agenda
DB_USERNAME=root
DB_PASSWORD=secret

EVOLUTION_API_KEY=your-evolution-api-key
WHATSAPP_API_URL=https://your-evolution-api.com

STRIPE_KEY=chave_publica
STRIPE_SECRET=chave_secret
```

## 🛠️ Solução de Problemas

### Containers não sobem

```bash
# Rebuild forçado
docker-compose build --no-cache
docker-compose up -d --force-recreate
```

### WhatsApp não funciona

```bash
# Verificar instância
docker-compose exec app php artisan tinker
>>> app(\App\Services\Whatsapp\WhatsappService::class)->getConfig(1);

# Verificar cron
crontab -l | grep schedule:run
```

### Performance Issues

```bash
# Limpar cache
docker-compose exec app php artisan optimize:clear

# Otimizar para produção
docker-compose exec app php artisan optimize
```

### Limpeza de Espaço

```bash
# Docker cleanup
docker container prune -f
docker image prune -af
docker volume prune -f
sudo apt-get clean
sudo rm -rf /var/lib/apt/lists/*
rm -rf /tmp/*

# Laravel logs
docker-compose exec app bash -c "echo '' > storage/logs/laravel.log"
```

## 📁 Estrutura do Projeto

```
├── app/
│   ├── Console/Commands/     # Comandos Artisan
│   ├── Http/Controllers/     # Controllers REST
│   ├── Jobs/                 # Background jobs
│   ├── Models/               # Eloquent models
│   ├── Notifications/        # Notifications
│   └── Services/             # Business logic
├── resources/
│   ├── js/
│   │   ├── components/       # React components
│   │   ├── pages/           # Inertia pages
│   │   ├── hooks/           # Custom hooks
│   │   └── layouts/         # Layout components
│   └── views/               # Blade templates
├── routes/
│   ├── web.php              # Web routes
│   └── console.php          # Scheduled tasks
├── docker/                  # Docker configs
├── .github/workflows/       # GitHub Actions
└── docs/                    # Documentation
```

## 📄 Licença

Este projeto é privado e proprietário.

## 👥 Contribuição

Para contribuir:

1. Fork o projeto
2. Crie uma branch (`git checkout -b feature/AmazingFeature`)
3. Commit suas mudanças (`git commit -m 'Add some AmazingFeature'`)
4. Push para a branch (`git push origin feature/AmazingFeature`)
5. Abra um Pull Request

## 📞 Suporte

Para suporte, entre em contato através dos issues do GitHub ou pelo Discord configurado no projeto.

---

**🏥 Sistema desenvolvido para modernizar a gestão de clínicas médicas com tecnologia de ponta.**
