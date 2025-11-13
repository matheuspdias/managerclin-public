#!/bin/bash

# Script para testar o sistema de notificações WhatsApp no Docker
echo "🚀 Testando Sistema de Notificações WhatsApp (Docker)"
echo "===================================================="

# Verificar se Docker Compose está disponível
if ! command -v docker &> /dev/null; then
    echo "❌ Docker não encontrado"
    exit 1
fi

# Verificar se o comando existe no container
echo "📋 Verificando comando no container..."
docker compose exec app bash -c "php artisan list | grep whatsapp-send-appointments-notifications" > /dev/null 2>&1
if [ $? -eq 0 ]; then
    echo "✅ Comando encontrado"
else
    echo "❌ Comando não encontrado"
    exit 1
fi

# Testar permissões de empresas
echo "🔍 Testando permissões de empresas..."
docker compose exec app bash -c "php artisan app:test-whatsapp-permissions"

echo ""
echo "⚡ Executando comando de notificações..."
docker compose exec app bash -c "php artisan app:whatsapp-send-appointments-notifications"

# Processar jobs da fila
echo ""
echo "📦 Processando jobs da fila..."
docker compose exec app bash -c "php artisan queue:work --once --queue=whatsapp"

# Verificar logs
echo ""
echo "📋 Verificando logs recentes..."
docker compose exec app bash -c "tail -10 storage/logs/laravel.log | grep -i whatsapp" || echo "Nenhum log de WhatsApp encontrado"

# Verificar configuração de agendamento
echo ""
echo "📅 Verificando agendamento..."
docker compose exec app bash -c "php artisan schedule:list | grep whatsapp" && echo "✅ Agendamento configurado" || echo "⚠️  Agendamento não encontrado"

echo ""
echo "✅ Teste completo finalizado!"
echo ""
echo "🔧 Comandos úteis para Docker:"
echo "# Monitorar logs em tempo real:"
echo "docker compose exec app bash -c \"tail -f storage/logs/laravel.log | grep WhatsApp\""
echo ""
echo "# Processar fila continuamente:"
echo "docker compose exec app bash -c \"php artisan queue:work --queue=whatsapp\""
echo ""
echo "# Executar comando manualmente:"
echo "docker compose exec app bash -c \"php artisan app:whatsapp-send-appointments-notifications\""
echo ""
echo "# Ver todos os agendamentos:"
echo "docker compose exec app bash -c \"php artisan schedule:list\""