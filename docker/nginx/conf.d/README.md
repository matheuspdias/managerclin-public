# Configuração Nginx

## Arquivos

### ✅ `prod.conf` - ÚNICO ARQUIVO DE CONFIGURAÇÃO
Configuração completa de produção com:
- **HTTP (porta 80)**: Redireciona para HTTPS + proteção de arquivos sensíveis
- **HTTPS (porta 443)**: Servidor principal com SSL CloudFlare Origin
- Proteção allowlist (bloqueia tudo, permite só o necessário)
- Suporte a variável `${DOMAIN}` substituída no deploy

**Este arquivo é copiado como `/etc/nginx/conf.d/default.conf` no container.**

## Fluxo de Deploy

1. **Build**: `Dockerfile.prod` copia `prod.conf` → `/etc/nginx/conf.d/default.conf`
2. **Deploy**: `deploy.yml` substitui `${DOMAIN}` pelo domínio real no arquivo
3. **Container**: Nginx carrega `/etc/nginx/conf.d/default.conf` (que é o prod.conf)

## Segurança

Ambos os blocos (HTTP e HTTPS) usam **allowlist**:

✅ **PERMITE**:
- `/.well-known/acme-challenge/` (CloudFlare/Certbot)
- Assets públicos (`.js`, `.css`, imagens, etc)
- Rotas Laravel via PHP-FPM

❌ **BLOQUEIA**:
- Arquivos ocultos (`.env`, `.git`, `.gitignore`)
- Diretórios de código (`vendor`, `app`, `config`, `database`, etc)
- Extensões perigosas (`.sql`, `.sh`, `.key`, `.pem`, `.log`)
- Arquivos específicos (`composer.json`, `artisan`, `package.json`)

## Variáveis

- `${DOMAIN}`: Substituída durante deploy pelo domínio do `.env`
- Certificados SSL: Mapeados via volume do `docker-compose.prod.yml`
