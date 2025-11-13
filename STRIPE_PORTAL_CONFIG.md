# Configuração do Stripe Billing Portal

Este documento descreve como configurar o Stripe Customer Portal para permitir que os usuários gerenciem suas assinaturas.

## 1. Configuração Manual no Dashboard

### Passo 1: Acesse o Customer Portal
1. Acesse [Stripe Dashboard](https://dashboard.stripe.com/)
2. Clique em **Settings** (canto superior direito)
3. Navegue até **Billing** → **Customer portal**

### Passo 2: Habilite as Funcionalidades

#### ✅ Subscription Update (Trocar Planos)
- Marque "Allow customers to switch plans"
- Selecione "Customers can switch to different products"
- Configure política de proration (recomendado: "Always invoice immediately")

#### ✅ Update Quantities (Adicionar/Remover Usuários)
- Marque "Allow customers to update quantities"
- Isso permite ajustar a quantidade de usuários adicionais

#### ✅ Payment Method
- Marque "Allow customers to update payment methods"

#### ✅ Cancellation
- Marque "Allow customers to cancel subscriptions"
- Configure política de cancelamento (imediato ou fim do período)

#### ✅ Invoice History
- Marque "Allow customers to view invoice history"

### Passo 3: Configure os Produtos

Para cada produto (Essencial, Pro, Premium):

1. Vá em **Products** no Stripe Dashboard
2. Clique no produto
3. Na seção **Customer portal settings**:
   - ✅ Marque "Customers can switch to this product"
   - Configure se permite upgrade e downgrade

### Passo 4: Configure Usuários Adicionais

O produto "Usuários Adicionais" deve estar configurado como:
- **Pricing model**: Standard pricing
- **Billing period**: Monthly
- **Usage type**: Licensed
- **Allow quantity updates**: YES

## 2. Configuração via Código (Opcional)

Se precisar de mais controle, você pode criar uma configuração personalizada:

```php
// app/Services/Billing/BillingService.php

use Stripe\StripeClient;

public function createCustomPortalConfiguration(): string
{
    $stripe = new StripeClient(config('cashier.secret'));

    $configuration = $stripe->billingPortal->configurations->create([
        'business_profile' => [
            'headline' => 'Gerencie sua assinatura',
        ],
        'features' => [
            'customer_update' => [
                'allowed_updates' => ['email', 'address'],
                'enabled' => true,
            ],
            'invoice_history' => [
                'enabled' => true,
            ],
            'payment_method_update' => [
                'enabled' => true,
            ],
            'subscription_cancel' => [
                'enabled' => true,
                'mode' => 'at_period_end', // ou 'immediately'
                'cancellation_reason' => [
                    'enabled' => true,
                    'options' => [
                        'too_expensive',
                        'missing_features',
                        'switched_service',
                        'unused',
                        'other',
                    ],
                ],
            ],
            'subscription_update' => [
                'enabled' => true,
                'default_allowed_updates' => ['price', 'quantity'],
                'proration_behavior' => 'always_invoice',
                'products' => [
                    [
                        'product' => 'prod_xxxxx', // ID do produto Essencial
                        'prices' => [
                            config('services.stripe.prices.plans.essencial'),
                        ],
                    ],
                    [
                        'product' => 'prod_yyyyy', // ID do produto Pro
                        'prices' => [
                            config('services.stripe.prices.plans.pro'),
                        ],
                    ],
                    [
                        'product' => 'prod_zzzzz', // ID do produto Premium
                        'prices' => [
                            config('services.stripe.prices.plans.premium'),
                        ],
                    ],
                ],
            ],
        ],
    ]);

    return $configuration->id;
}

// Usar a configuração ao gerar o portal URL
public function getBillingPortalUrl(Company $company): string
{
    $configId = 'bpc_xxxxx'; // ID da sua configuração

    return $company->billingPortalUrl(route('billing.index'), [
        'configuration' => $configId,
    ]);
}
```

## 3. Verificação

Após configurar, teste:

1. Acesse a página de billing do sistema
2. Clique em "Gerenciar no Stripe"
3. Você deve ver:
   - ✅ Opção de "Update plan" ou "Switch plan"
   - ✅ Opção de ajustar quantidade de usuários
   - ✅ Histórico de faturas
   - ✅ Atualizar método de pagamento
   - ✅ (Opcional) Cancelar assinatura

## 4. Troubleshooting

### Problema: Não aparece opção de trocar plano
**Solução**:
- Verifique se "Allow customers to switch plans" está habilitado
- Certifique-se que os produtos estão marcados como "switchable"

### Problema: Não aparece opção de adicionar usuários
**Solução**:
- Verifique se "Allow customers to update quantities" está habilitado
- Certifique-se que o price de usuários adicionais permite quantity updates

### Problema: Portal não abre
**Solução**:
- Verifique se `STRIPE_SECRET` está configurado no `.env`
- Verifique se a company tem `stripe_id` no banco de dados
- Verifique os logs do Laravel para erros

## 5. Configurações de Ambiente

Certifique-se de ter no `.env`:

```env
STRIPE_KEY=pk_test_xxxxx
STRIPE_SECRET=sk_test_xxxxx
STRIPE_WEBHOOK_SECRET=whsec_xxxxx

# Prices IDs
STRIPE_PLAN_ESSENCIAL=price_xxxxx
STRIPE_PLAN_PRO=price_xxxxx
STRIPE_PLAN_PREMIUM=price_xxxxx
STRIPE_PRICE_ADDITIONAL_USERS=price_xxxxx

# AI Credits
STRIPE_AI_CREDITS_BASIC=price_xxxxx
STRIPE_AI_CREDITS_PROFESSIONAL=price_xxxxx
STRIPE_AI_CREDITS_ENTERPRISE=price_xxxxx
```

## 6. Referências

- [Stripe Customer Portal Docs](https://stripe.com/docs/billing/subscriptions/integrating-customer-portal)
- [Laravel Cashier Billing Portal](https://laravel.com/docs/billing#billing-portal)
