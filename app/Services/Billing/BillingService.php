<?php

namespace App\Services\Billing;

use App\DTO\Billing\UpdateBillingDTO;
use App\Models\Company;
use App\Models\User;
use App\Repositories\Billing\BillingRepositoryInterface;
use App\Services\AICredits\AICreditsService;
use App\Traits\ThrowsExceptions;
use Illuminate\Support\Facades\Log;
use Laravel\Cashier\Exceptions\IncompletePayment;

class BillingService
{
    use ThrowsExceptions;

    public function __construct(
        protected BillingRepositoryInterface $repository,
        protected AICreditsService $aiCreditsService
    ) {}

    public function getBillingData(Company $company): array
    {
        if (!$company) {
            $this->throwNotFound('Company not found.');
        }

        $subscription = $this->repository->getCompanySubscription($company);
        $defaultPaymentMethod = $this->repository->getCompanyPaymentMethod($company);
        $hasDefaultPaymentMethod = $company->hasDefaultPaymentMethod();

        $invoices = $this->repository->getCompanyInvoices($company);

        // Identificar plano atual
        $currentPlan = null;
        if ($subscription && $subscription->active()) {
            $planConfigs = config('services.stripe.prices.plans', []);

            foreach ($subscription->items as $item) {
                foreach ($planConfigs as $planKey => $stripePriceId) {
                    if ($item->stripe_price === $stripePriceId) {
                        $availablePlans = $this->repository->getAvailablePlans();
                        $currentPlan = collect($availablePlans)->firstWhere('id', $planKey);
                        break 2;
                    }
                }
            }
        }

        $usersCount = $this->repository->getCompanyUsersCount($company);

        // Calcular total mensal (apenas o preço base do plano)
        $totalMonthly = 0;
        if ($currentPlan) {
            $totalMonthly = $currentPlan['base_price'];
        }

        // Get billing portal URL without custom configuration
        $billingPortalUrl = null;
        if ($company->hasStripeId()) {
            try {
                $billingPortalUrl = $company->billingPortalUrl(route('billing.index'));
            } catch (\Exception $e) {
                Log::error('Failed to create billing portal URL', ['error' => $e->getMessage()]);
            }
        }

        return [
            'company' => [
                'id' => $company->id,
                'name' => $company->name,
                'has_default_payment_method' => $hasDefaultPaymentMethod,
                'default_payment_method' => $defaultPaymentMethod,
                'current_users_count' => $usersCount,
            ],
            'subscription' => $subscription ? [
                'id' => $subscription->id,
                'status' => $subscription->status,
                'active' => $subscription->active(),
                'ends_at' => $subscription->ends_at?->toISOString(),
                'items' => $subscription->items->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'price' => [
                            'id' => $item->stripe_price,
                            'nickname' => $item->stripe_product,
                        ],
                        'quantity' => $item->quantity,
                    ];
                })
            ] : null,
            'currentPlan' => $currentPlan,
            'totalMonthly' => number_format($totalMonthly, 2, '.', ''),
            'invoices' => $invoices,
            'billingPortalUrl' => $billingPortalUrl,
        ];
    }

    public function updateSubscription(Company $company, UpdateBillingDTO $dto, User $user): array
    {
        if (!$company) {
            $this->throwNotFound('Company not found.');
        }


        try {
            // Log da tentativa de atualização
            Log::info('Billing update attempt', [
                'company_id' => $company->id,
                'user_id' => $user->id,
                'plan_id' => $dto->planId,
                'has_payment_method' => !empty($dto->paymentMethodId),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            $subscriptionWasCreated = false;

            // Atualizar plano principal
            if ($dto->planId) {
                // Converter chave do plano para price ID do Stripe
                $stripePriceId = config("services.stripe.prices.plans.{$dto->planId}");

                if (!$stripePriceId) {
                    throw new \InvalidArgumentException("Plano '{$dto->planId}' não encontrado na configuração.");
                }

                $hadSubscription = $company->subscription('default') !== null;
                $this->repository->updateCompanySubscription($company, $stripePriceId, $dto->paymentMethodId);
                $subscriptionWasCreated = !$hadSubscription;

                // Aplicar créditos de IA baseado no plano escolhido
                $this->applyPlanCredits($company, $dto->planId);

                Log::info('Subscription plan updated', [
                    'company_id' => $company->id,
                    'user_id' => $user->id,
                    'plan_key' => $dto->planId,
                    'stripe_price_id' => $stripePriceId,
                    'subscription_created' => $subscriptionWasCreated,
                ]);
            }

            // Atualizar método de pagamento se fornecido
            if ($dto->paymentMethodId && $company->hasDefaultPaymentMethod()) {
                $this->repository->updateCompanyPaymentMethod($company, $dto->paymentMethodId);
                Log::info('Payment method updated', [
                    'company_id' => $company->id,
                    'user_id' => $user->id,
                ]);
            }

            Log::info('Billing update successful', [
                'company_id' => $company->id,
                'user_id' => $user->id,
            ]);

            return [
                'message' => 'Assinatura atualizada com sucesso!',
                'redirect_url' => route('billing.index')
            ];
        } catch (IncompletePayment $exception) {
            Log::warning('Incomplete payment during billing update', [
                'company_id' => $company->id,
                'user_id' => $user->id,
                'payment_id' => $exception->payment->id,
            ]);

            return [
                'error' => 'Pagamento incompleto.',
                'payment_url' => route('cashier.payment', [$exception->payment->id, 'redirect' => route('billing.index')])
            ];
        } catch (\Exception $e) {
            Log::error('Billing update failed', [
                'company_id' => $company->id,
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'error' => 'Erro ao atualizar assinatura: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Define os créditos de IA incluídos em cada plano
     */
    private function getPlanCredits(): array
    {
        return [
            'essencial' => 100,
            'pro' => 400,
            'premium' => 2000,
        ];
    }

    /**
     * Aplica os créditos de IA baseado no plano escolhido
     */
    private function applyPlanCredits(Company $company, string $planId): void
    {
        $planCredits = $this->getPlanCredits();

        if (isset($planCredits[$planId])) {
            $credits = $planCredits[$planId];

            // Setar os créditos do plano (substitui o valor atual)
            $company->update([
                'ai_credits' => $credits,
            ]);

            Log::info('AI credits applied for plan', [
                'company_id' => $company->id,
                'plan_id' => $planId,
                'credits_applied' => $credits,
            ]);
        }
    }

}
