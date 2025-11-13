# Configuração de Segurança do Stripe

Este documento descreve as configurações obrigatórias no Stripe Dashboard para reduzir bloqueios por alto risco e habilitar 3D Secure (SCA - Strong Customer Authentication).

## ⚠️ Problema Identificado

Pagamentos estavam sendo bloqueados por "alto risco" devido a:

1. **Falta de 3D Secure** - Sem autenticação forte do cliente
2. **Informações incompletas** - Sem endereço, telefone, etc.
3. **Radar bloqueando** - Regras de fraude muito agressivas

## ✅ Alterações Feitas no Código

Atualizamos os seguintes arquivos para incluir configurações de segurança:

1. **`app/Http/Controllers/BillingController.php`**

    - Método `checkout()`: Habilitado 3D Secure, coleta de endereço, telefone e metadados

2. **`app/Repositories/AICredits/AICreditsEloquentORM.php`**
    - Método `createPaymentIntent()`: Mesmas configurações de segurança
    - Método `purchaseWithSavedCard()`: Adicionado 3D Secure para cartões salvos

### Principais Configurações Adicionadas:

```php
'billing_address_collection' => 'required',
'phone_number_collection' => ['enabled' => true],
'payment_method_options' => [
    'card' => [
        'request_three_d_secure' => 'automatic',
    ],
],
'customer_update' => [
    'address' => 'auto',
    'name' => 'auto',
],
'locale' => 'pt-BR',
'statement_descriptor' => 'ManagerClin',
```

## 🎯 Configurações Obrigatórias no Stripe Dashboard

### 1. Habilitar Radar (Prevenção de Fraude)

**Caminho:** Stripe Dashboard → Fraud & Risk → Radar

**Ações:**

- ✅ Ativar Radar (se não estiver ativo)
- ⚙️ Revisar regras automáticas
- 🔧 Ajustar nível de risco se necessário:
    - Recomendado: **Medium** para início
    - Evitar: "Highest" (muito agressivo)

### 2. Configurar SCA (Strong Customer Authentication)

**Caminho:** Settings → Payment methods → Cards

**Ações:**

- ✅ Habilitar "Request 3D Secure authentication when required by regulation"
- ✅ Marcar "Always request 3D Secure on the first payment"
- ✅ Para Brasil: "Request 3D Secure for payments to Brazil"

### 3. Billing Details Collection

**Caminho:** Settings → Checkout → Billing details

**Ações:**

- ✅ Configurar para **"Always collect"** ou **"If required"**
- ✅ Incluir obrigatoriamente:
    - Name ✓
    - Email ✓
    - Address ✓
    - Phone ✓

### 4. Tax ID Collection (Recomendado para Brasil)

**Caminho:** Settings → Tax → Tax ID collection

**Ações:**

- ✅ Adicionar "BR_CPF" (CPF brasileiro)
- ✅ Adicionar "BR_CNPJ" (CNPJ brasileiro)
- ⚙️ Configurar como "Optional" ou "Required" conforme necessidade

### 5. Dispute & Business Settings (⚠️ CRÍTICO)

**Caminho:** Settings → Business settings → Customer information

**Ações:**

- ✅ **Statement descriptor:** **"MANAGERCLIN"** (máx 22 caracteres, sem espaços especiais)
  - ⚠️ **IMPORTANTE:** Este é o nome que aparece no extrato do cartão do cliente
  - Para **assinaturas** (subscriptions), deve ser configurado aqui no Dashboard
  - Para **pagamentos únicos** (one-time), é configurado via código
  - Evite caracteres especiais, use apenas letras e números
  - Recomendado: Nome curto e reconhecível da sua empresa
- ✅ Adicionar support phone: Seu telefone de suporte
- ✅ Adicionar support email: Seu email de suporte
- ℹ️ Isso ajuda clientes a reconhecer a cobrança e **reduz drasticamente chargebacks**

### 6. Webhooks (Verificar configuração)

**Caminho:** Developers → Webhooks

**Verificar eventos necessários:**

- ✅ `checkout.session.completed`
- ✅ `payment_intent.succeeded`
- ✅ `charge.succeeded`
- ✅ `invoice.payment_succeeded`
- ✅ `customer.subscription.updated`
- ✅ `customer.subscription.created`
- ✅ `customer.updated`

**Endpoint:** `https://seu-dominio.com/stripe/webhook`

### 7. Payment Method Configuration

**Caminho:** Settings → Payment methods

**Ações:**

- ✅ Verificar se "Cards" está habilitado
- ✅ Verificar configurações regionais para Brasil
- ⚙️ Considerar habilitar outros métodos (Pix, Boleto) no futuro

## 🧪 Testando em Ambiente de Teste

### Cartões de Teste para 3D Secure:

1. **Requer 3DS e autentica com sucesso:**

    ```
    4000 0027 6000 3184
    ```

2. **Requer 3DS mas falha na autenticação:**

    ```
    4000 0000 0000 0341
    ```

3. **3DS opcional (baixo risco):**
    ```
    4242 4242 4242 4242
    ```

### Como Testar:

1. Use modo TEST no Stripe
2. Faça uma compra de teste com os cartões acima
3. Verifique se o popup de 3D Secure aparece
4. Complete a autenticação
5. Verifique logs no Stripe Dashboard

## 📊 Monitoramento Pós-Implementação

### Métricas para Acompanhar:

1. **Taxa de Bloqueio por Fraude**

    - Antes: Alta (problema atual)
    - Meta: < 1%

2. **Taxa de Aprovação**

    - Meta: > 95%

3. **Chargebacks**

    - Meta: < 0.5%

4. **3D Secure Challenge Rate**
    - Esperado: 20-40% (depende do Radar)

### Onde Verificar:

- **Radar Dashboard:** Fraud & Risk → Overview
- **Payment Analytics:** Payments → Analytics
- **Disputes:** Disputes → Overview

## 🚨 Troubleshooting

### Problema: Ainda ocorrendo bloqueios

**Soluções:**

1. Verificar se o Radar está em modo muito agressivo
2. Adicionar endereço do cliente na "Allow List" temporariamente
3. Verificar se o statement descriptor está claro
4. Confirmar que o webhook está respondendo 200 OK

### Problema: 3D Secure não está sendo solicitado

**Soluções:**

1. Verificar configuração "request_three_d_secure" no código
2. Confirmar que o cartão suporta 3DS
3. Testar com cartões de teste específicos

### Problema: Cliente não consegue completar 3DS

**Soluções:**

1. Verificar se o popup não está sendo bloqueado pelo navegador
2. Testar em modo anônimo/privado
3. Verificar console do navegador para erros JavaScript

## 📞 Suporte

Se os problemas persistirem após essas configurações:

1. **Stripe Support:** https://support.stripe.com/
2. **Documentação 3DS:** https://stripe.com/docs/payments/3d-secure
3. **Radar Docs:** https://stripe.com/docs/radar

## ✅ Checklist de Implementação

- [x] Código atualizado com configurações de segurança
- [ ] Radar habilitado e configurado
- [ ] SCA/3D Secure habilitado
- [ ] Billing details configurado para coletar tudo
- [ ] Statement descriptor definido
- [ ] Testes realizados em ambiente de teste
- [ ] Deploy em produção
- [ ] Monitoramento de métricas por 7 dias
- [ ] Ajustes finos se necessário

---

**Última atualização:** 2025-10-07
**Responsável:** Equipe de Desenvolvimento
