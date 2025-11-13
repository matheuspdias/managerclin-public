# 🔒 Correção de Segurança (Compatível com CloudFlare)

## ❌ Por que o bloqueio via IP causou erro 521?

Quando tentamos bloquear acesso direto via IP com `server { ... server_name _; }`, bloqueamos também o CloudFlare, porque:

1. **CloudFlare se conecta ao servidor pelo IP** (não pelo domínio)
2. CloudFlare envia `Host: seu-dominio.com` no header
3. Nginx roteia baseado no `server_name`, então CloudFlare vai para o servidor correto
4. **MAS** se bloqueamos com `return 444`, o CloudFlare não consegue conectar = **Erro 521**

## ✅ Solução Correta: Proteger Arquivos, Não Bloquear IP

A abordagem correta é:
- ✅ **Permitir** CloudFlare se conectar normalmente
- ✅ **Bloquear** apenas arquivos sensíveis em TODAS as rotas
- ✅ **Confiar** no CloudFlare para filtrar tráfego malicioso

### Configuração Atual (Segura + Funcional)

O arquivo `docker/nginx/conf.d/prod.conf` agora tem:

```nginx
# Server HTTP (porta 80)
server {
    listen 80;
    server_name seu-dominio.com;

    # Bloquear arquivos sensíveis
    location ~ /\.(env|git) {
        deny all;
        return 404;
    }

    # Bloquear arquivos de config
    location ~* ^/(\.env.*|bootstrap/cache|config/.*\.php|...) {
        deny all;
        return 404;
    }

    # Redirecionar para HTTPS
    location / {
        return 301 https://$server_name$request_uri;
    }
}

# Server HTTPS (porta 443)
server {
    listen 443 ssl;
    server_name seu-dominio.com;

    # Bloquear arquivos sensíveis (PRIMEIRA PRIORIDADE)
    location ~ /\. {
        deny all;
        return 404;
    }

    location ~* ^/(\.env.*|\.git.*|bootstrap/cache|...) {
        deny all;
        return 404;
    }

    # Resto da configuração normal...
}
```

## 🛡️ Camadas de Proteção

### 1. CloudFlare (Primeira Linha)
- WAF (Web Application Firewall)
- Bot Protection
- DDoS Protection
- Rate Limiting

### 2. Nginx (Segunda Linha)
- Bloqueia arquivos sensíveis
- Bloqueia diretórios sensíveis
- Headers de segurança

### 3. Laravel (Terceira Linha)
- `.env` fora do `public/`
- `public/` como document root
- Validação de entrada

## 🧪 Testes de Segurança

### Teste 1: Arquivos Sensíveis (deve retornar 404)
```bash
curl https://seu-dominio.com/.env
curl https://seu-dominio.com/.git/config
curl https://seu-dominio.com/composer.json
curl https://seu-dominio.com/bootstrap/cache/config.php
```

### Teste 2: Acesso Normal (deve funcionar)
```bash
curl https://seu-dominio.com/
curl https://seu-dominio.com/login
curl https://seu-dominio.com/build/assets/app.js
```

### Teste 3: CloudFlare (não deve dar 521)
```bash
# Acessar pelo domínio (via CloudFlare)
curl https://seu-dominio.com/
# Esperado: HTML da aplicação (não erro 521)
```

## 🔐 Proteção Adicional: CloudFlare WAF Rules

Como usamos CloudFlare, adicione WAF rules customizadas:

### Rule 1: Bloquear Paths Sensíveis
```
(http.request.uri.path contains ".env") or
(http.request.uri.path contains ".git") or
(http.request.uri.path contains "composer.json") or
(http.request.uri.path contains "bootstrap/cache") or
(http.request.uri.path contains "config/")

Action: Block
```

### Rule 2: Bloquear User Agents Suspeitos
```
(http.user_agent contains "scanner") or
(http.user_agent contains "sqlmap") or
(http.user_agent contains "nikto")

Action: Challenge (CAPTCHA)
```

### Rule 3: Rate Limiting em APIs
```
(http.request.uri.path starts_with "/api/") and
(cf.threat_score gt 10)

Action: Rate Limit (10 req/min)
```

## 📊 Monitoramento

### CloudFlare Analytics
Monitore em: https://dash.cloudflare.com/

- Requests bloqueados por WAF
- Países de origem do tráfego
- Bots detectados

### Nginx Logs
```bash
# Ver tentativas de acesso a .env
docker compose -f docker-compose.prod.yml logs nginx | grep -E "(\.env|\.git)"

# Ver requisições bloqueadas
docker compose -f docker-compose.prod.yml logs nginx | grep "deny"
```

## ✅ Verificação Final

Depois de aplicar as correções:

- [ ] Site acessível via domínio (sem erro 521)
- [ ] `/.env` retorna 404
- [ ] `/composer.json` retorna 404
- [ ] `/bootstrap/cache/config.php` retorna 404
- [ ] Página principal carrega normalmente
- [ ] Assets (JS/CSS) carregam normalmente
- [ ] CloudFlare Analytics mostrando tráfego normal

## 🚨 Se ainda der erro 521

1. **Verifique CloudFlare SSL Mode:**
   - Deve estar em **Full (Strict)**
   - Não pode ser "Flexible"

2. **Verifique Certificado:**
   ```bash
   docker compose -f docker-compose.prod.yml exec nginx \
     openssl x509 -in /etc/ssl/cloudflare/fullchain.pem -text -noout | grep Issuer
   ```
   - Deve mostrar "Cloudflare"

3. **Teste direto no servidor (bypassing CloudFlare):**
   ```bash
   # No servidor
   curl -k https://localhost/
   ```
   - Deve retornar HTML (se funcionar, problema é no CloudFlare)

4. **Restart Nginx:**
   ```bash
   docker compose -f docker-compose.prod.yml restart nginx
   ```

## 🎯 Resumo

| Cenário | Status |
|---------|--------|
| CloudFlare → Nginx | ✅ Funciona |
| `https://dominio/.env` | ✅ 404 (bloqueado) |
| `https://dominio/` | ✅ Funciona |
| Erro 521 | ✅ Resolvido |
| Arquivos sensíveis protegidos | ✅ Sim |
