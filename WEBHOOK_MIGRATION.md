# Migração do Webhook para Cashier Padrão

## O que mudou?

Refatoramos o sistema de webhooks para usar o **WebhookController padrão do Laravel Cashier**, que já faz toda a sincronização automática de subscriptions.

### Antes ❌
- Webhook customizado em `/webhook/stripe`
- Sincronização manual de subscriptions (duplicando lógica do Cashier)
- Race conditions entre webhook e redirect
- Plano não aparecia na tela após pagamento

### Depois ✅
- Webhook padrão do Cashier em `/stripe/webhook`
- Sincronização automática pelo Cashier
- `WebhookController` customizado **estende** `CashierController`
- Apenas lógica de negócio customizada (AI credits, notificações, etc)

## Passos para Deploy em Produção

### 1. Atualizar webhook no Stripe Dashboard

Acesse: https://dashboard.stripe.com/webhooks

**Opção A: Editar webhook existente**
1. Clique no webhook atual (`/webhook/stripe`)
2. Edite o endpoint URL de:
   ```
   https://managerclin.com.br/webhook/stripe
   ```
   Para:
   ```
   https://managerclin.com.br/stripe/webhook
   ```

**Opção B: Criar novo webhook**
1. Clique em "Add endpoint"
2. URL: `https://managerclin.com.br/stripe/webhook`
3. Eventos a escutar:
   - `customer.subscription.created`
   - `customer.subscription.updated`
   - `customer.subscription.deleted`
   - `invoice.payment_action_required`
   - `invoice.payment_succeeded`
   - `customer.updated`
   - `checkout.session.completed`
   - `charge.succeeded`
   - `payment_intent.succeeded`

4. Copie o **Signing secret** (começa com `whsec_...`)

### 2. Atualizar variável de ambiente

No arquivo `.env` de produção:

```env
STRIPE_WEBHOOK_SECRET=whsec_seu_novo_secret_aqui
```

Se estava usando outra variável, remova-a.

### 3. Deploy do código

```bash
git add .
git commit -m "Refactor: Use Cashier webhook controller for automatic subscription sync"
git push
```

### 4. Testar em produção

1. Fazer um novo checkout de teste
2. Verificar logs: `storage/logs/laravel.log`
3. Confirmar que:
   - Webhook chega em `/stripe/webhook`
   - Subscription é sincronizada automaticamente
   - Plano aparece na tela de billing
   - Créditos de IA são aplicados

### 5. Remover webhook antigo (opcional)

Após confirmar que tudo funciona, você pode desativar/deletar o webhook antigo no Stripe Dashboard.

## Como funciona agora?

### Fluxo de Pagamento

```
1. Usuário completa checkout no Stripe
   ↓
2. Stripe envia webhook para /stripe/webhook
   ↓
3. CashierController processa e SINCRONIZA subscription automaticamente
   ↓
4. WebhookController customizado sobrescreve métodos para:
   - Aplicar créditos de IA
   - Atualizar signature_status
   - Salvar payment method
   ↓
5. Usuário é redirecionado para /billing
   ↓
6. Subscription JÁ ESTÁ SINCRONIZADA ✅
   ↓
7. Plano aparece na tela corretamente
```

### Código Customizado

O `WebhookController` agora:

```php
class WebhookController extends CashierController
{
    // Sobrescreve métodos do Cashier para adicionar lógica customizada
    protected function handleCustomerSubscriptionCreated(array $payload)
    {
        parent::handleCustomerSubscriptionCreated($payload); // Cashier sincroniza

        // Lógica customizada aqui (AI credits, etc)
    }
}
```

## Vantagens

✅ Sincronização automática pelo Cashier (mais confiável)
✅ Menos código custom para manter
✅ Sem race conditions
✅ Plano aparece imediatamente após pagamento
✅ Compatível com todos os recursos do Cashier
✅ Melhor tratamento de edge cases (reembolsos, trials, etc)

## Troubleshooting

### Webhook não está sendo recebido
- Verifique se a URL está correta no Stripe Dashboard
- Confirme que o `STRIPE_WEBHOOK_SECRET` está correto
- Veja logs em Stripe Dashboard > Webhooks > Requests

### Subscription não sincroniza
- Verifique os logs: `tail -f storage/logs/laravel.log`
- Confirme que o modelo `Company` usa trait `Billable`
- Verifique se a tabela `subscriptions` existe

### Plano não aparece na tela
- Aguarde alguns segundos após o redirect (webhook pode ter delay)
- Limpe o cache: `php artisan cache:clear`
- Verifique se `signature_status` foi atualizado no banco

## Suporte

Para mais informações sobre webhooks do Cashier:
https://laravel.com/docs/11.x/billing#handling-stripe-webhooks
