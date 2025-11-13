<?php

namespace App\Http\Controllers;

use App\DTO\Billing\UpdateBillingDTO;
use App\Http\Requests\Billing\UpdateBillingRequest;
use App\Services\Billing\BillingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

class BillingController extends Controller
{
    public function __construct(
        protected BillingService $billingService
    ) {}
    public function billing(): Response
    {
        $user = Auth::user();

        if (!Gate::allows('viewBilling', $user)) {
            abort(403, 'Acesso negado. Apenas o proprietário da empresa pode acessar as configurações de cobrança.');
        }

        $company = $user->company;

        if (!$company) {
            return Inertia::render('billing/billing', [
                'error' => 'Company not found.',
            ]);
        }

        try {
            $billingData = $this->billingService->getBillingData($company);
            return Inertia::render('billing/billing', $billingData);
        } catch (\Exception $e) {
            Log::error('Error in billing controller', ['error' => $e->getMessage()]);
            return Inertia::render('billing/billing', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function checkout(Request $request)
    {
        $user = Auth::user();

        if (!Gate::allows('updateBilling', $user)) {
            return response()->json(['error' => 'Acesso negado. Apenas o proprietário da empresa pode alterar as configurações de cobrança.'], 403);
        }

        $company = $user->company;

        if (!$company) {
            return response()->json(['error' => 'Company not found.'], 404);
        }

        $request->validate([
            'plan' => 'required|string|in:essencial,pro,premium',
        ]);

        try {
            // Obter o Stripe Price ID baseado no plano
            $stripePriceId = config("services.stripe.prices.plans.{$request->plan}");

            if (!$stripePriceId) {
                throw new \Exception('Plano inválido.');
            }

            // Criar checkout session do Stripe com configurações de segurança completas
            $checkoutOptions = [
                'success_url' => route('billing.index') . '?success=true',
                'cancel_url' => route('billing.index'),

                // CRÍTICO: Coletar endereço de cobrança (ajuda na verificação AVS e reduz fraude)
                'billing_address_collection' => 'required',

                // Coletar telefone (adiciona camada extra de verificação)
                'phone_number_collection' => [
                    'enabled' => true,
                ],

                // Sempre coletar método de pagamento
                'payment_method_collection' => 'always',

                // CRÍTICO: Habilitar 3D Secure automático (SCA - Strong Customer Authentication)
                'payment_method_options' => [
                    'card' => [
                        'request_three_d_secure' => 'automatic', // Solicita 3DS quando necessário
                    ],
                ],

                // Atualizar informações do customer automaticamente
                'customer_update' => [
                    'address' => 'auto',
                    'name' => 'auto',
                ],

                // Locale em português
                'locale' => 'pt-BR',

                // Adicionar metadados úteis para análise de fraude
                'metadata' => [
                    'company_id' => $company->id,
                    'plan' => $request->plan,
                    'user_email' => $user->email,
                    'user_ip' => $request->ip(),
                ],

                // Texto customizado para tranquilizar o cliente
                'custom_text' => [
                    'submit' => [
                        'message' => 'Seus dados estão protegidos com criptografia de ponta a ponta',
                    ],
                ],

                // Para subscriptions, usar subscription_data ao invés de payment_intent_data
                'subscription_data' => [
                    'metadata' => [
                        'company_id' => $company->id,
                        'plan' => $request->plan,
                    ],
                ],
            ];

            $checkout = $company
                ->newSubscription('default', $stripePriceId)
                ->checkout($checkoutOptions);

            // Retornar URL para redirect no frontend
            return response()->json([
                'url' => $checkout->url
            ]);
        } catch (\Exception $e) {
            Log::error('Error creating checkout session', [
                'error' => $e->getMessage(),
                'company_id' => $company->id,
                'plan' => $request->plan,
            ]);

            return response()->json([
                'error' => 'Erro ao criar sessão de checkout: ' . $e->getMessage()
            ], 500);
        }
    }

    public function changePlan(Request $request)
    {
        $user = Auth::user();

        if (!Gate::allows('updateBilling', $user)) {
            return response()->json(['error' => 'Acesso negado. Apenas o proprietário da empresa pode alterar as configurações de cobrança.'], 403);
        }

        $company = $user->company;

        if (!$company) {
            return response()->json(['error' => 'Company not found.'], 404);
        }

        $request->validate([
            'plan' => 'required|string|in:essencial,pro,premium',
        ]);

        try {
            // Obter o Stripe Price ID baseado no plano
            $stripePriceId = config("services.stripe.prices.plans.{$request->plan}");

            if (!$stripePriceId) {
                throw new \Exception('Plano inválido.');
            }

            $subscription = $company->subscription('default');

            if (!$subscription) {
                return response()->json(['error' => 'Nenhuma assinatura ativa encontrada.'], 404);
            }

            // Trocar o plano (swap simples sem lógica de usuários adicionais)
            $subscription->swap($stripePriceId);

            // Aplicar créditos de IA do novo plano
            $planCredits = [
                'essencial' => 100,
                'pro' => 400,
                'premium' => 2000,
            ];

            if (isset($planCredits[$request->plan])) {
                $company->update([
                    'ai_credits' => $planCredits[$request->plan],
                    'signature_status' => \App\Enums\SignatureStatusEnum::ACTIVE,
                    'trial_ends_at' => null,
                ]);
            }

            Log::info('Plan changed successfully', [
                'company_id' => $company->id,
                'user_id' => $user->id,
                'new_plan' => $stripePriceId,
                'plan_key' => $request->plan,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Plano alterado com sucesso!',
            ]);
        } catch (\Exception $e) {
            Log::error('Error changing plan', [
                'error' => $e->getMessage(),
                'company_id' => $company->id,
                'plan' => $request->plan,
            ]);

            return response()->json([
                'error' => 'Erro ao trocar plano: ' . $e->getMessage()
            ], 500);
        }
    }

}
