<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Services\AICredits\AICreditsService;
use App\Services\Billing\BillingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Laravel\Cashier\Http\Controllers\WebhookController as CashierController;

class WebhookController extends CashierController
{
    protected ?AICreditsService $aiCreditsService = null;
    protected ?BillingService $billingService = null;

    /**
     * Get AI Credits Service instance
     */
    protected function getAICreditsService(): AICreditsService
    {
        if (!$this->aiCreditsService) {
            $this->aiCreditsService = app(AICreditsService::class);
        }
        return $this->aiCreditsService;
    }

    /**
     * Get Billing Service instance
     */
    protected function getBillingService(): BillingService
    {
        if (!$this->billingService) {
            $this->billingService = app(BillingService::class);
        }
        return $this->billingService;
    }

    /**
     * Override handleWebhook to add logging
     */
    public function handleWebhook(\Illuminate\Http\Request $request)
    {
        $payload = json_decode($request->getContent(), true);

        Log::info('Webhook received', [
            'type' => $payload['type'] ?? 'unknown',
            'id' => $payload['id'] ?? null,
            'data_preview' => isset($payload['data']['object']['id']) ? $payload['data']['object']['id'] : 'no id',
        ]);

        return parent::handleWebhook($request);
    }

    /**
     * Verify webhook signature (override para dev)
     */
    protected function verifyWebhookSignature($request)
    {
        // Em desenvolvimento local com Stripe CLI, não precisa verificar
        if (config('app.env') === 'local') {
            Log::debug('Skipping webhook signature verification (local env)');
            return;
        }

        return parent::verifyWebhookSignature($request);
    }

    /**
     * Handle checkout session completed
     */
    protected function handleCheckoutSessionCompleted(array $payload)
    {
        $session = $payload['data']['object'];
        $metadata = $session['metadata'] ?? [];

        Log::info('Checkout session completed', [
            'session_id' => $session['id'] ?? null,
            'metadata' => $metadata,
            'metadata_type' => gettype($metadata),
            'customer' => $session['customer'] ?? null,
            'payment_method' => $session['payment_method'] ?? null,
            'setup_intent' => $session['setup_intent'] ?? null,
        ]);

        // Salvar método de pagamento como padrão
        if (isset($session['customer'])) {
            $company = Company::where('stripe_id', $session['customer'])->first();

            if ($company) {
                $paymentMethodId = null;

                // Tentar obter o payment method do session
                if (isset($session['payment_method'])) {
                    $paymentMethodId = $session['payment_method'];
                } elseif (isset($session['setup_intent'])) {
                    // Se não houver payment_method direto, buscar via setup_intent
                    try {
                        $stripe = new \Stripe\StripeClient(config('cashier.secret'));
                        $setupIntent = $stripe->setupIntents->retrieve($session['setup_intent']);
                        $paymentMethodId = $setupIntent->payment_method;
                    } catch (\Exception $e) {
                        Log::warning("Failed to retrieve payment method from setup_intent", [
                            'setup_intent' => $session['setup_intent'],
                            'error' => $e->getMessage(),
                        ]);
                    }
                }

                if ($paymentMethodId) {
                    try {
                        $company->updateDefaultPaymentMethod($paymentMethodId);
                        Log::info("Saved default payment method for company {$company->id}", [
                            'payment_method' => $paymentMethodId,
                            'session_id' => $session['id'],
                            'had_previous_method' => $company->hasDefaultPaymentMethod(),
                        ]);
                    } catch (\Exception $e) {
                        Log::error("Failed to save payment method for company {$company->id}", [
                            'error' => $e->getMessage(),
                            'payment_method' => $paymentMethodId,
                        ]);
                    }
                } else {
                    Log::warning("No payment method found in checkout session", [
                        'company_id' => $company->id,
                        'session_id' => $session['id'],
                    ]);
                }
            }
        }

        // Check if this is an AI credits purchase
        $priceId = $metadata['price_id'] ?? null;
        $credits = $metadata['credits'] ?? null;
        $companyId = $metadata['company_id'] ?? null;

        if ($priceId && $credits && $companyId) {
            $company = Company::find($companyId);

            if ($company) {
                $creditsInt = (int) $credits;
                $this->getAICreditsService()->addCredits($company, $creditsInt);

                Log::info("Added {$creditsInt} AI credits to company {$company->id} via checkout session", [
                    'session_id' => $session['id'],
                    'price_id' => $priceId,
                    'metadata' => $metadata,
                ]);
            } else {
                Log::warning("Company not found for AI credits purchase", [
                    'company_id' => $companyId,
                    'metadata' => $metadata,
                ]);
            }
        } else {
            Log::info('Checkout session completed but not an AI credits purchase', [
                'has_price_id' => !empty($priceId),
                'has_credits' => !empty($credits),
                'has_company_id' => !empty($companyId),
                'metadata' => $metadata,
            ]);
        }
    }

    /**
     * Handle charge succeeded
     */
    protected function handleChargeSucceeded(array $payload)
    {
        $charge = $payload['data']['object'];
        $metadata = $charge['metadata'] ?? [];

        Log::info('Charge succeeded', [
            'charge_id' => $charge['id'] ?? null,
            'metadata' => $metadata,
            'customer' => $charge['customer'] ?? null,
        ]);

        // Check if this is an AI credits purchase (from purchaseWithSavedCard)
        if (isset($metadata['type']) && $metadata['type'] === 'ai_credits_purchase') {
            $credits = $metadata['credits'] ?? null;
            $companyId = $metadata['company_id'] ?? null;

            if ($credits && $companyId) {
                $company = Company::find($companyId);

                if ($company) {
                    $creditsInt = (int) $credits;
                    $this->aiCreditsService->addCredits($company, $creditsInt);

                    Log::info("Added {$creditsInt} AI credits to company {$company->id} via saved card charge", [
                        'charge_id' => $charge['id'],
                        'price_id' => $metadata['price_id'] ?? 'unknown',
                    ]);
                } else {
                    Log::warning("Company not found for AI credits purchase", [
                        'company_id' => $companyId,
                        'metadata' => $metadata,
                    ]);
                }
            }
        }
    }

    /**
     * Handle payment intent succeeded
     */
    protected function handlePaymentIntentSucceeded(array $payload)
    {
        $paymentIntent = $payload['data']['object'];
        $metadata = $paymentIntent['metadata'] ?? [];

        Log::info('Payment intent succeeded', [
            'payment_intent_id' => $paymentIntent['id'] ?? null,
            'metadata' => $metadata,
        ]);

        // NÃO processar créditos aqui para evitar duplicação
        // Os créditos são processados no checkout.session.completed ou charge.succeeded
        // Este evento é apenas para logging e debug
    }

    /**
     * Handle invoice payment succeeded
     * Nota: O Cashier não tem este método, então criamos do zero
     */
    protected function handleInvoicePaymentSucceeded(array $payload)
    {
        // Adicionar lógica customizada: aplicar créditos de IA
        $invoice = (object) $payload['data']['object'];
        $customerId = $invoice->customer;

        if (!$customerId) {
            Log::warning('Invoice payment succeeded but no customer ID found');
            return;
        }

        // Encontrar a company pelo stripe_id
        $company = Company::where('stripe_id', $customerId)->first();

        if (!$company) {
            Log::warning("Company not found for Stripe customer: {$customerId}");
            return;
        }

        // Verificar se é o primeiro pagamento de uma nova assinatura
        // ou uma renovação mensal que precisa recarregar créditos
        // Adicionar delay para dar tempo do Cashier sincronizar
        $subscription = $company->subscription('default');

        if (!$subscription) {
            Log::info("Subscription not yet synced for company: {$company->id}, will be handled by subscription.updated event");
            return;
        }

        // Identificar o plano atual
        $mainPriceItem = $subscription->items->first(function ($item) {
            $additionalUsersPrice = config('services.stripe.prices.addons.additional_users');
            return $item->stripe_price !== $additionalUsersPrice;
        });

        if (!$mainPriceItem) {
            Log::warning("No main plan item found for subscription: {$subscription->id}");
            return;
        }

        // Mapear stripe_price para plan_id
        $planConfigs = config('services.stripe.prices.plans', []);
        $planId = null;

        foreach ($planConfigs as $planKey => $stripePriceId) {
            if ($mainPriceItem->stripe_price === $stripePriceId) {
                $planId = $planKey;
                break;
            }
        }

        if (!$planId) {
            Log::warning("Plan not found for stripe price: {$mainPriceItem->stripe_price}");
            return;
        }

        // Aplicar créditos baseado no plano
        $planAiCredits = [
            'essencial' => 100,
            'pro' => 400,
            'premium' => 2000,
        ];

        $planTelemedicineCredits = [
            'essencial' => 20,
            'pro' => 50,
            'premium' => 100,
        ];

        if (isset($planAiCredits[$planId]) && isset($planTelemedicineCredits[$planId])) {
            $aiCredits = $planAiCredits[$planId];
            $telemedicineCredits = $planTelemedicineCredits[$planId];
            $additionalTelemedicineCredits = $company->telemedicine_additional_credits ?? 0;

            // Recarregar os créditos do plano (substitui os créditos atuais)
            // E atualizar o status da assinatura para ACTIVE
            $company->update([
                'ai_credits' => $aiCredits,
                'telemedicine_credits' => $telemedicineCredits + $additionalTelemedicineCredits,
                'signature_status' => \App\Enums\SignatureStatusEnum::ACTIVE,
                'trial_ends_at' => null,
            ]);

            Log::info("AI and Telemedicine credits recharged via webhook", [
                'company_id' => $company->id,
                'plan_id' => $planId,
                'ai_credits_applied' => $aiCredits,
                'telemedicine_credits_applied' => $telemedicineCredits,
                'telemedicine_additional_credits' => $additionalTelemedicineCredits,
                'signature_status' => 'ACTIVE',
                'invoice_id' => $invoice->id,
            ]);
        }
    }

    /**
     * Handle subscription updated (override Cashier method to add custom logic)
     */
    protected function handleCustomerSubscriptionUpdated(array $payload)
    {
        try {
            // Deixar o Cashier fazer a sincronização automática
            parent::handleCustomerSubscriptionUpdated($payload);
        } catch (\Illuminate\Database\UniqueConstraintViolationException $e) {
            // Ignorar erro de chave duplicada - acontece quando Stripe envia eventos duplicados
            Log::warning('Duplicate subscription event received, ignoring', [
                'subscription_id' => $payload['data']['object']['id'] ?? 'unknown',
                'error' => $e->getMessage(),
            ]);
        }

        // Adicionar lógica customizada: sincronizar dados adicionais
        $subscription = $payload['data']['object'];
        $customerId = $subscription['customer'];

        $company = Company::where('stripe_id', $customerId)->first();
        if ($company) {
            // Salvar payment method como padrão se a subscription tiver um
            if (isset($subscription['default_payment_method']) && $subscription['default_payment_method']) {
                if (!$company->hasDefaultPaymentMethod()) {
                    try {
                        $company->updateDefaultPaymentMethod($subscription['default_payment_method']);
                        Log::info("Saved default payment method from subscription.updated for company {$company->id}", [
                            'payment_method' => $subscription['default_payment_method'],
                            'subscription_id' => $subscription['id'],
                            'subscription_status' => $subscription['status'],
                        ]);
                    } catch (\Exception $e) {
                        Log::error("Failed to save payment method from subscription.updated for company {$company->id}", [
                            'error' => $e->getMessage(),
                            'payment_method' => $subscription['default_payment_method'],
                        ]);
                    }
                }
            }

            $this->syncSubscriptionData($company, (object) $subscription);
        }
    }

    /**
     * Handle subscription created (override Cashier method to add custom logic)
     */
    protected function handleCustomerSubscriptionCreated(array $payload)
    {
        try {
            // Deixar o Cashier fazer a sincronização automática
            parent::handleCustomerSubscriptionCreated($payload);
        } catch (\Illuminate\Database\UniqueConstraintViolationException $e) {
            // Ignorar erro de chave duplicada - acontece quando Stripe envia eventos duplicados
            Log::warning('Duplicate subscription event received, ignoring', [
                'subscription_id' => $payload['data']['object']['id'] ?? 'unknown',
                'error' => $e->getMessage(),
            ]);
        }

        // Adicionar lógica customizada
        $subscription = $payload['data']['object'];
        $customerId = $subscription['customer'];

        $company = Company::where('stripe_id', $customerId)->first();
        if ($company) {
            Log::info("New subscription created for company: {$company->id}", [
                'subscription_id' => $subscription['id'],
                'status' => $subscription['status'],
                'default_payment_method' => $subscription['default_payment_method'] ?? null,
            ]);

            // Tentar salvar método de pagamento como padrão se ainda não tiver
            if (isset($subscription['default_payment_method']) && !$company->hasDefaultPaymentMethod()) {
                try {
                    $company->updateDefaultPaymentMethod($subscription['default_payment_method']);
                    Log::info("Saved default payment method from subscription for company {$company->id}", [
                        'payment_method' => $subscription['default_payment_method'],
                        'subscription_id' => $subscription['id'],
                    ]);
                } catch (\Exception $e) {
                    Log::error("Failed to save payment method from subscription for company {$company->id}", [
                        'error' => $e->getMessage(),
                        'payment_method' => $subscription['default_payment_method'],
                    ]);
                }
            }

            $this->syncSubscriptionData($company, (object) $subscription);
        }
    }

    private function syncSubscriptionData($company, $subscription)
    {
        try {
            // Verificar se a assinatura está ativa
            if ($subscription->status !== 'active') {
                Log::info("Subscription not active, skipping sync", [
                    'company_id' => $company->id,
                    'subscription_status' => $subscription->status,
                ]);
                return;
            }

            $additionalUsersPrice = config('services.stripe.prices.addons.additional_users');
            $planConfigs = config('services.stripe.prices.plans', []);

            $mainPriceItem = null;
            $additionalUsersCount = 0;

            // Analisar items da assinatura
            // Nota: subscription do webhook vem como array, não objeto Eloquent
            $items = is_array($subscription->items) ? $subscription->items : $subscription->items->data;
            foreach ($items['data'] ?? $items as $item) {
                $itemArray = is_array($item) ? $item : (array) $item;
                $priceId = $itemArray['price']['id'] ?? $item->price->id ?? null;

                if ($priceId === $additionalUsersPrice) {
                    // Este é o item de usuários adicionais
                    $additionalUsersCount = $itemArray['quantity'] ?? $item->quantity ?? 0;
                    Log::info("Found additional users item", [
                        'company_id' => $company->id,
                        'quantity' => $additionalUsersCount,
                    ]);
                } else {
                    // Este pode ser o plano principal
                    $mainPriceItem = $itemArray;
                }
            }

            if (!$mainPriceItem) {
                Log::warning("No main plan item found in subscription", [
                    'company_id' => $company->id,
                    'subscription_id' => $subscription->id,
                ]);
                return;
            }

            // Identificar o plano atual
            $mainPricePriceId = $mainPriceItem['price']['id'] ?? null;

            if (!$mainPricePriceId) {
                Log::warning("No price ID found in main price item", [
                    'company_id' => $company->id,
                    'main_price_item' => $mainPriceItem,
                ]);
                return;
            }

            $planId = null;
            foreach ($planConfigs as $planKey => $stripePriceId) {
                if ($mainPricePriceId === $stripePriceId) {
                    $planId = $planKey;
                    break;
                }
            }

            if (!$planId) {
                Log::warning("Plan not found for stripe price", [
                    'company_id' => $company->id,
                    'stripe_price' => $mainPricePriceId,
                ]);
                return;
            }

            Log::info("Syncing subscription data", [
                'company_id' => $company->id,
                'plan_id' => $planId,
                'additional_users' => $additionalUsersCount,
            ]);

            // Aqui você pode atualizar dados específicos da company se necessário
            // Por exemplo, guardar o número de usuários adicionais contratados
            // $company->update(['contracted_additional_users' => $additionalUsersCount]);

            // Aplicar créditos do plano se necessário
            $planAiCredits = [
                'essencial' => 100,
                'pro' => 400,
                'premium' => 2000,
            ];

            $planTelemedicineCredits = [
                'essencial' => 20,
                'pro' => 50,
                'premium' => 100,
            ];

            if (isset($planAiCredits[$planId]) && isset($planTelemedicineCredits[$planId])) {
                $aiCredits = $planAiCredits[$planId];
                $telemedicineCredits = $planTelemedicineCredits[$planId];
                $additionalTelemedicineCredits = $company->telemedicine_additional_credits ?? 0;

                // Só recarrega créditos se não foram recarregados recentemente
                // (evita duplicar créditos em updates de subscription)
                $lastCreditRecharge = $company->updated_at;
                $timeSinceLastUpdate = now()->diffInMinutes($lastCreditRecharge);

                if ($timeSinceLastUpdate > 5) { // Só recarrega se passou mais de 5 minutos
                    $company->update([
                        'ai_credits' => $aiCredits,
                        'telemedicine_credits' => $telemedicineCredits + $additionalTelemedicineCredits,
                        'signature_status' => \App\Enums\SignatureStatusEnum::ACTIVE,
                        'trial_ends_at' => null,
                    ]);

                    Log::info("AI and Telemedicine credits recharged via subscription sync", [
                        'company_id' => $company->id,
                        'plan_id' => $planId,
                        'ai_credits_applied' => $aiCredits,
                        'telemedicine_credits_applied' => $telemedicineCredits,
                        'telemedicine_additional_credits' => $additionalTelemedicineCredits,
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::error("Error syncing subscription data", [
                'company_id' => $company->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }
}
