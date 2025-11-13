# 🔒 Checklist de Segurança - Antes de Tornar Público

## ✅ Checklist Obrigatório

### 1. Limpar .env.example
- [ ] Substituir `APP_KEY` por placeholder vazio ou gerar novo exemplo
- [ ] Substituir `WHATSAPP_API_TOKEN` por placeholder
- [ ] Substituir `TELEMEDICINE_APP_ID` por placeholder
- [ ] Substituir IDs Stripe por placeholders genéricos
- [ ] Remover ou sanitizar `DOCKERHUB_USERNAME`

### 2. Atualizar deploy.yml
- [ ] Substituir URLs específicas do repositório por placeholders
- [ ] Remover links diretos para releases

### 3. Verificar histórico Git
- [ ] Verificar se `.env` real nunca foi commitado
- [ ] Usar `git log --all --full-history --diff-filter=A -- .env` para verificar
- [ ] Se encontrar, usar BFG Repo-Cleaner ou git filter-branch

### 4. Adicionar arquivos de segurança
- [ ] Criar `.env.example` limpo (este arquivo)
- [ ] Verificar `.gitignore` contém `.env`
- [ ] Adicionar `SECURITY.md` com política de vulnerabilidades
- [ ] Adicionar `LICENSE` apropriada

### 5. Revisar documentação
- [ ] DEPLOY.md - remover informações específicas de servidor
- [ ] README.md - verificar se não há credenciais
- [ ] Outros arquivos .md - verificar chaves/tokens

## 📝 Arquivo .env.example Limpo (Sugerido)

```env
# Aplicação
APP_NAME=ManagerClin
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8000

# Banco de Dados
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=clinica
DB_USERNAME=root
DB_PASSWORD=root

# Email
MAIL_MAILER=mailgun
MAILGUN_DOMAIN=
MAILGUN_SECRET=
MAILGUN_ENDPOINT=api.mailgun.net
MAIL_FROM_ADDRESS=
MAIL_FROM_NAME="${APP_NAME}"

# IA
OPENAI_API_KEY=
DEEPSEEK_API_KEY=

# WhatsApp Evolution API
WHATSAPP_API_URL=http://host.docker.internal:8081
WHATSAPP_API_TOKEN=
WHATSAPP_INSTANCE_ID=
EVOLUTION_API_KEY=

# Stripe (Use apenas chaves de teste)
STRIPE_SECRET_KEY=sk_test_
STRIPE_PUBLISHABLE_KEY=pk_test_
STRIPE_WEBHOOK_SECRET=whsec_
VITE_STRIPE_KEY=pk_test_

# Stripe Price IDs (Você precisa criar seus próprios no Stripe Dashboard)
STRIPE_PLAN_ESSENCIAL=price_
STRIPE_PLAN_PRO=price_
STRIPE_PLAN_PREMIUM=price_
STRIPE_PRICE_ADDITIONAL_USERS=price_
STRIPE_AI_CREDITS_BASIC=price_
STRIPE_AI_CREDITS_PROFESSIONAL=price_
STRIPE_AI_CREDITS_ENTERPRISE=price_

# Cashier
CASHIER_CURRENCY=BRL

# Docker Hub (opcional)
DOCKERHUB_USERNAME=

# Telemedicina - JaaS/8x8
TELEMEDICINE_PROVIDER=jaas
TELEMEDICINE_SERVER_URL=https://8x8.vc
TELEMEDICINE_APP_ID=
```

## 🛡️ Comandos de Verificação

### Verificar se .env está no histórico Git:
```bash
git log --all --full-history --diff-filter=A -- .env
```

### Buscar possíveis chaves em todo o repositório:
```bash
# Buscar padrões de API keys
grep -r "sk_live\|pk_live\|whsec_[^x]" --exclude-dir={vendor,node_modules,.git} .

# Buscar tokens
grep -r "token.*[A-Za-z0-9]{32,}" --exclude-dir={vendor,node_modules,.git} .

# Buscar senhas hardcoded
grep -ri "password.*=" --exclude-dir={vendor,node_modules,.git} . | grep -v ".example"
```

### Limpar histórico se necessário (CUIDADO!):
```bash
# Usar BFG Repo-Cleaner (recomendado)
bfg --delete-files .env

# OU git filter-branch (mais complexo)
git filter-branch --force --index-filter \
  "git rm --cached --ignore-unmatch .env" \
  --prune-empty --tag-name-filter cat -- --all
```

## 📋 Antes do Push Final

1. [ ] Executar todos os comandos de verificação acima
2. [ ] Criar novo repositório público limpo (clone fresh)
3. [ ] Copiar código limpo para novo repo
4. [ ] NÃO fazer push do histórico antigo
5. [ ] Configurar GitHub Secrets para CI/CD
6. [ ] Testar instalação limpa com .env.example

## ⚠️ IMPORTANTE

**NUNCA** faça force push para main/master com histórico limpo se já tiver colaboradores!
**SEMPRE** crie um novo repositório público se houver qualquer dúvida sobre histórico comprometido.

## 🔐 Recomendações Adicionais

1. **Habilitar GitHub Secret Scanning** no repositório
2. **Adicionar dependabot** para atualizações de segurança
3. **Configurar branch protection rules** na main
4. **Revisar permissões** de colaboradores
5. **Documentar** processo de setup seguro no README

---

✅ **Após completar este checklist, seu repositório estará seguro para ser público!**
