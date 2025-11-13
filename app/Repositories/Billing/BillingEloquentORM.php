<?php

namespace App\Repositories\Billing;

use App\Models\Company;
use App\Repositories\BaseRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Laravel\Cashier\Exceptions\IncompletePayment;

class BillingEloquentORM extends BaseRepository implements BillingRepositoryInterface
{
    public function __construct()
    {
        parent::__construct(new Company());
    }

    public function getCompanySubscription(Company $company): ?object
    {
        return $company->subscription('default');
    }

    public function getCompanyInvoices(Company $company): array
    {
        try {
            $stripeInvoices = $company->invoicesIncludingPending();
            return collect($stripeInvoices)->map(function ($invoice) {
                return [
                    'id' => $invoice->id,
                    'number' => $invoice->number,
                    'date' => $invoice->created ? Carbon::createFromTimestamp($invoice->created)->toISOString() : null,
                    'due_date' => $invoice->due_date ? Carbon::createFromTimestamp($invoice->due_date)->toISOString() : null,
                    'amount_due' => $invoice->amount_due / 100,
                    'amount_paid' => $invoice->amount_paid / 100,
                    'status' => $invoice->status,
                    'paid' => $invoice->paid,
                    'attempted' => $invoice->attempted,
                    'invoice_pdf' => $invoice->invoice_pdf,
                    'hosted_invoice_url' => $invoice->hosted_invoice_url,
                    'lines' => collect($invoice->lines)->map(function ($line) {
                        return [
                            'description' => $line->description,
                            'amount' => $line->amount / 100,
                            'quantity' => $line->quantity,
                            'period' => $line->period ? [
                                'start' => $line->period->start ? Carbon::createFromTimestamp($line->period->start)->toISOString() : null,
                                'end' => $line->period->end ? Carbon::createFromTimestamp($line->period->end)->toISOString() : null,
                            ] : null,
                        ];
                    })->toArray(),
                ];
            })->toArray();
        } catch (\Exception $e) {
            Log::error('Erro ao buscar invoices do Stripe: ' . $e->getMessage());
            return [];
        }
    }

    public function getCompanyPaymentMethod(Company $company): ?array
    {
        if (!$company->hasDefaultPaymentMethod()) {
            return null;
        }

        $paymentMethod = $company->defaultPaymentMethod();
        return [
            'id' => $paymentMethod->id,
            'type' => $paymentMethod->type,
            'card' => [
                'brand' => $paymentMethod->card->brand,
                'last4' => $paymentMethod->card->last4,
                'exp_month' => $paymentMethod->card->exp_month,
                'exp_year' => $paymentMethod->card->exp_year,
            ]
        ];
    }

    public function getCompanyUsersCount(Company $company): int
    {
        try {
            return $company->users()->count();
        } catch (\Exception $e) {
            Log::error('Erro ao contar usuários da empresa: ' . $e->getMessage(), [
                'company_id' => $company->id,
            ]);
            return 0;
        }
    }

    public function getAvailablePlans(): array
    {
        return [
            [
                'name' => 'Essencial',
                'cicle' => 'Mensal',
                'description' => 'Reduza a papelada e organize suas consultas com nosso plano Essencial, perfeito para clínicas que estão começando.',
                'id' => 'essencial',
                'price' => 'R$ 79,90/Mensal',
                'base_price' => 79.90,
                'features' => [
                    'Dashboard Completo',
                    'Controle de pacientes e agendamentos',
                    'Prontuario digital',
                    'Controle de serviços e salas',
                    'Gerador de atestados e receitas',
                    'Preferência de horário de atendimento',
                    '100 Créditos de IA mensais',
                    'Usuários adicionais R$ 39,90',
                ],
                'included_users' => 1,
            ],
            [
                'name' => 'Pro',
                'cicle' => 'Mensal',
                'description' => 'elimine as faltas com lembretes automáticos de consultas via WhatsApp e gerencie suas finanças com facilidade.',
                'id' => 'pro',
                'price' => 'R$ 149,00/Mensal',
                'base_price' => 149.00,
                'features' => [
                    'Todos os recursos do plano Essencial',
                    'Lembrete de consultas via WhatsApp',
                    'Gerenciamento financeiro',
                    'Dashboard financeiro',
                    'Controle de estoque completo',
                    '400 Créditos de IA mensais',
                    'Usuários adicionais R$ 39,90',
                ],
                'included_users' => 1,
            ],
            [
                'name' => 'Premium',
                'cicle' => 'Mensal',
                'description' => 'Para clínicas que buscam inovação, nosso plano Premium oferece 2000 creditos de IA para otimizar o atendimento ao paciente e também para gerenciamento financeiro e controle de estoque.',
                'id' => 'premium',
                'price' => 'R$ 249,00/Mensal',
                'base_price' => 249.00,
                'features' => [
                    'Todos os recursos do plano Pro',
                    '2000 Créditos de IA mensais',
                    'Usuários ilimitados',
                ],
                'included_users' => 1,
            ]
        ];
    }

    public function updateCompanySubscription(Company $company, string $planId, ?string $paymentMethod = null): void
    {
        $subscription = $company->subscription('default');

        if ($subscription) {
            $subscription->swap($planId);
        } else {
            $newSub = $company->newSubscription('default', $planId);

            if ($paymentMethod) {
                $newSub->create($paymentMethod);
                $company->updateDefaultPaymentMethod($paymentMethod);
                $company->update(['trial_ends_at' => null]);
            } else {
                $newSub->create();
            }
        }
    }

    public function updateAdditionalUsers(Company $company, int $quantity): void
    {
        $subscription = $company->subscription('default');

        if (!$subscription) {
            throw new \Exception('Não foi possível localizar a assinatura ativa. Tente novamente em alguns segundos.');
        }

        if (!$subscription->active()) {
            throw new \Exception('A assinatura não está ativa. Verifique o status do pagamento.');
        }

        $additionalUsersPrice = config('services.stripe.prices.addons.additional_users');

        if (!$additionalUsersPrice) {
            throw new \Exception('Configuração de preço para usuários adicionais não encontrada.');
        }

        $existingItem = $subscription->items->first(function ($item) use ($additionalUsersPrice) {
            return $item->stripe_price === $additionalUsersPrice;
        });

        if ($quantity > 0) {
            if ($existingItem) {
                $subscription->updateQuantity($quantity, $existingItem->stripe_price);
            } else {
                $subscription->addPrice($additionalUsersPrice, $quantity);
            }
        } elseif ($existingItem) {
            $subscription->removePrice($existingItem->stripe_price);
        }
    }

    public function updateCompanyPaymentMethod(Company $company, string $paymentMethodId): void
    {
        $company->updateDefaultPaymentMethod($paymentMethodId);
    }
}
