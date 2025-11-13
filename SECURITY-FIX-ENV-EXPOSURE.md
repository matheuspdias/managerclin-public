# 🔒 Correção: Exposição do .env via IP

## ❌ Problema Identificado

**CRÍTICO:** Arquivos sensíveis (.env, bootstrap/cache/config.php) estavam acessíveis quando se acessava o servidor pelo **IP direto** ao invés do domínio.

### Teste de Vulnerabilidade

```bash
# ❌ VULNERÁVEL (antes da correção):
curl http://SEU_IP/.env
# Retornava o conteúdo do .env com STRIPE_SECRET, DB_PASSWORD, etc

# ✅ PROTEGIDO (depois da correção):
curl http://SEU_IP/.env
# Retorna 444 (conexão fechada) ou 404
```

## 🛡️ Correção Aplicada

### 1. Bloqueio Total de Acesso via IP

Adicionado servidor default que **bloqueia qualquer acesso via IP**:

```nginx
server {
    listen 80 default_server;
    listen 443 ssl default_server;
    server_name _;

    location / {
        deny all;
        return 444; # Fecha conexão sem resposta
    }
}
```

**Resultado:** Acessar via IP (HTTP ou HTTPS) = Bloqueado ✅

### 2. Proteção Reforçada no HTTP (porta 80)

Mesmo antes de redirecionar para HTTPS, bloqueia arquivos sensíveis:

```nginx
server {
    listen 80;
    server_name seu-dominio.com;

    # Bloquear arquivos sensíveis
    location ~ /\.(env|git) {
        deny all;
        return 404;
    }

    # Bloquear diretórios sensíveis
    location ~* ^/(\.env.*|bootstrap/cache|config/.*\.php|...) {
        deny all;
        return 404;
    }

    # Depois redireciona para HTTPS
    location / {
        return 301 https://$server_name$request_uri;
    }
}
```

### 3. Proteção Abrangente no HTTPS (porta 443)

Lista completa de arquivos e diretórios bloqueados:

```nginx
# Arquivos ocultos (.env, .git, etc)
location ~ /\. {
    deny all;
    return 404;
}

# Arquivos de configuração
location ~* ^/(\.env.*|\.git.*|storage/logs/.*|bootstrap/cache/.*|
config/.*\.php|composer\.(json|lock)|package.*\.json|
artisan|phpunit\.xml.*|docker-compose.*|Dockerfile.*|
\.github/.*|database/.*\.sql|...)$ {
    deny all;
    return 404;
}

# Diretórios sensíveis
location ~ ^/(vendor|node_modules|tests|database|
storage/framework|storage/logs|bootstrap/cache)/ {
    deny all;
    return 404;
}
```

## 📋 Arquivos Protegidos

### Arquivos Críticos (segredos)
- ✅ `.env`, `.env.production`, `.env.example`
- ✅ `bootstrap/cache/config.php`
- ✅ `config/*.php`
- ✅ `composer.json`, `composer.lock`
- ✅ `package.json`, `package-lock.json`

### Arquivos de Código-Fonte
- ✅ `.git/` (todo o repositório)
- ✅ `.github/` (workflows)
- ✅ `vendor/` (dependências PHP)
- ✅ `node_modules/` (dependências JS)
- ✅ `tests/` (testes)

### Arquivos de Dados
- ✅ `database/*.sql` (dumps)
- ✅ `storage/logs/` (logs)
- ✅ `storage/framework/` (cache, sessões)

### Arquivos de Configuração Docker
- ✅ `docker-compose*.yml`
- ✅ `Dockerfile*`
- ✅ `.dockerignore`

## 🧪 Como Testar a Proteção

### Teste 1: Acesso via IP (deve falhar)
```bash
# Tentar acessar pelo IP
curl http://SEU_IP/.env
curl https://SEU_IP/.env

# Esperado: Conexão recusada ou 444
```

### Teste 2: Acesso via Domínio a arquivos sensíveis (deve falhar)
```bash
# Tentar acessar .env
curl https://seu-dominio.com/.env
# Esperado: 404

# Tentar acessar config cache
curl https://seu-dominio.com/bootstrap/cache/config.php
# Esperado: 404

# Tentar acessar composer.json
curl https://seu-dominio.com/composer.json
# Esperado: 404
```

### Teste 3: Acesso normal deve funcionar
```bash
# Página principal
curl https://seu-dominio.com/
# Esperado: HTML da aplicação

# Assets públicos
curl https://seu-dominio.com/build/assets/app.js
# Esperado: Conteúdo do arquivo JS
```

## 🚀 Deploy da Correção

```bash
# 1. Commitar mudanças
git add docker/nginx/conf.d/prod.conf
git commit -m "fix: bloquear exposição de .env via IP"

# 2. Criar tag de deploy
git tag 1.x.x
git push origin 1.x.x

# 3. Aguardar deploy automático

# 4. Testar proteção
curl http://SEU_IP/.env  # Deve falhar
curl https://seu-dominio.com/.env  # Deve retornar 404
```

## 📊 Logs de Segurança

Os acessos bloqueados **NÃO são logados** para evitar poluir logs:

```nginx
location ~ /\. {
    deny all;
    access_log off;  # ← Não loga
    log_not_found off;  # ← Não loga "não encontrado"
    return 404;
}
```

Isso evita que scanners automatizados encham seus logs.

## ✅ Checklist de Segurança

Depois do deploy, verifique:

- [ ] `curl http://IP/.env` → Falha
- [ ] `curl https://IP/.env` → Falha
- [ ] `curl https://dominio/.env` → 404
- [ ] `curl https://dominio/composer.json` → 404
- [ ] `curl https://dominio/bootstrap/cache/config.php` → 404
- [ ] `curl https://dominio/` → Funciona (página principal)
- [ ] `curl https://dominio/build/assets/app.js` → Funciona (assets)

## 🔐 Melhores Práticas Adicionais

### 1. Firewall
Configure UFW/iptables para permitir apenas CloudFlare IPs:

```bash
# Bloquear acesso direto ao IP
# Apenas CloudFlare deve conseguir acessar
ufw allow from 173.245.48.0/20
ufw allow from 103.21.244.0/22
# ... outros ranges do CloudFlare
```

### 2. CloudFlare Settings
- SSL: Full (Strict) ✅
- WAF: Enabled
- Bot Fight Mode: Enabled
- Always Use HTTPS: On

### 3. Rate Limiting
Nginx já tem proteção básica, mas considere adicionar:

```nginx
limit_req_zone $binary_remote_addr zone=mylimit:10m rate=10r/s;
limit_req zone=mylimit burst=20 nodelay;
```

## 📚 Referências

- [OWASP - Configuration Management](https://owasp.org/www-project-top-ten/)
- [Nginx Security](https://www.nginx.com/blog/mitigating-ddos-attacks-with-nginx-and-nginx-plus/)
- [Laravel Security Best Practices](https://laravel.com/docs/deployment#optimization)
