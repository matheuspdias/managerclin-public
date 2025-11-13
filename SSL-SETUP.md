# Configuração SSL Let's Encrypt

Este projeto está configurado para usar certificados SSL Let's Encrypt automaticamente.

## Pré-requisitos

1. **Domínio configurado**: Seu domínio deve estar apontando para o servidor
2. **Portas abertas**: Portas 80 e 443 devem estar abertas no firewall
3. **Docker e Docker Compose** instalados

## Configuração

### 1. Configurar variáveis de ambiente

Copie o arquivo de exemplo e configure suas variáveis:

```bash
cp .env.production.example .env
```

Edite o arquivo `.env` e configure:

```bash
DOMAIN=seudominio.com
EMAIL=seuemail@exemplo.com
```

### 2. Deploy com SSL

Execute o script de deploy:

```bash
./docker/deploy-ssl.sh
```

Este script irá:
- Fazer build das imagens Docker
- Configurar certificados SSL Let's Encrypt
- Subir os serviços com HTTPS

### 3. Verificar funcionamento

Após o deploy, acesse:
- `https://seudominio.com` - Site principal (HTTPS)
- `http://seudominio.com` - Será redirecionado para HTTPS

## Estrutura dos arquivos SSL

```
docker/
├── certbot/
│   ├── conf/           # Certificados Let's Encrypt
│   ├── www/            # Challenge files
│   └── init-letsencrypt.sh  # Script de inicialização
├── nginx/
│   └── conf.d/
│       └── prod.conf   # Configuração Nginx com SSL
└── deploy-ssl.sh       # Script de deploy
```

## Renovação automática

Os certificados são renovados automaticamente pelo container `certbot` que:
- Verifica renovação a cada 12 horas
- Renova certificados próximos ao vencimento (30 dias)

## Comandos úteis

### Verificar status dos containers
```bash
docker-compose -f docker-compose.prod.yml ps
```

### Ver logs
```bash
docker-compose -f docker-compose.prod.yml logs -f
```

### Renovar certificados manualmente
```bash
docker-compose -f docker-compose.prod.yml exec certbot certbot renew
```

### Reiniciar nginx após renovação
```bash
docker-compose -f docker-compose.prod.yml exec laravel_app nginx -s reload
```

## Troubleshooting

### Erro: "Challenge failed"
- Verifique se o domínio está apontando para o servidor
- Verifique se as portas 80 e 443 estão abertas
- Aguarde a propagação DNS (pode levar até 48h)

### Erro: "Rate limit exceeded"
- Let's Encrypt tem limite de 5 tentativas por semana
- Use modo staging para testes: edite `init-letsencrypt.sh` e defina `staging=1`

### Certificado não carrega
- Verifique se os arquivos estão em `docker/certbot/conf/live/[dominio]/`
- Verifique logs do nginx: `docker logs laravel_app`

## Configurações de segurança

O nginx está configurado com:
- **Redirect HTTP → HTTPS**: Todo tráfego HTTP é redirecionado
- **HSTS**: Força HTTPS por 1 ano
- **Security Headers**: Proteção contra XSS, clickjacking, etc.
- **CSP**: Content Security Policy básica
- **TLS 1.2+**: Protocolos seguros apenas

## Monitoramento

Para monitorar a validade dos certificados:

```bash
# Verificar data de expiração
docker-compose -f docker-compose.prod.yml exec certbot certbot certificates

# Testar renovação
docker-compose -f docker-compose.prod.yml exec certbot certbot renew --dry-run
```