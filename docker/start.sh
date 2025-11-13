#!/bin/sh
set -e

echo "=== INICIANDO START.SH ==="
echo "Diretório atual: $(pwd)"
echo "Usuário atual: $(whoami)"
echo "Arquivo .env existe: $(test -f .env && echo 'SIM' || echo 'NÃO')"

# Aguardar DB estar disponível
echo "=== AGUARDANDO BANCO DE DADOS ==="
until php artisan tinker --execute="DB::connection()->getPdo();" 2>/dev/null; do
    echo "Aguardando DB..."
    sleep 2
done

# Executar migrações
echo "=== EXECUTANDO MIGRAÇÕES ==="
php artisan migrate --force

# Executar seeder de roles em produção
echo "=== EXECUTANDO SEEDER DE ROLES ==="
php artisan db:seed --class=RoleSeeder --force

# Criar link simbólico do storage
echo "=== CRIANDO LINK SIMBÓLICO DO STORAGE ==="
php artisan storage:link

# Gerar documentação do Swagger
echo "=== GERANDO DOCUMENTAÇÃO SWAGGER ==="
php artisan l5-swagger:generate

# Limpar e otimizar cache
echo "=== OTIMIZANDO APLICAÇÃO ==="
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Garantir que as permissões estão corretas
echo "=== AJUSTANDO PERMISSÕES ==="
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/public/storage
chmod -R 755 /var/www/html/storage
chmod -R 755 /var/www/html/bootstrap/cache
chmod -R 755 /var/www/html/public/storage

# Iniciar PHP-FPM em background
echo "=== INICIANDO PHP-FPM ==="
php-fpm -D

# Verificar configuração do Nginx
echo "=== VERIFICANDO CONFIGURAÇÃO DO NGINX ==="
nginx -t

# Iniciar Nginx em foreground
echo "=== INICIANDO NGINX ==="
nginx -g 'daemon off;'
