<?php

namespace App\Repositories\AICredits;

use App\Models\Company;
use Illuminate\Support\Facades\Log;

class AICreditsEloquentORM implements AICreditsRepositoryInterface
{
    public function __construct()
    {
        //
    }

    public function getCompanyCredits(Company $company): array
    {
        return [
            'current_credits' => $company->ai_credits,
            'additional_credits' => $company->ai_additional_credits,
            'total_credits' => $company->ai_credits + $company->ai_additional_credits,
            'last_purchase' => $company->ai_credits_last_purchase,
        ];
    }

    public function addCredits(Company $company, int $credits): void
    {
        $company->update([
            'ai_additional_credits' => $company->ai_additional_credits + $credits,
            'ai_credits_last_purchase' => now(),
        ]);
    }

    public function consumeCredits(Company $company, int $credits): bool
    {
        $totalCredits = $company->ai_credits + $company->ai_additional_credits;

        if ($totalCredits < $credits) {
            return false;
        }

        $remainingToConsume = $credits;
        $newAiCredits = $company->ai_credits;
        $newAdditionalCredits = $company->ai_additional_credits;

        // Primeiro consome os créditos regulares
        if ($remainingToConsume > 0 && $newAiCredits > 0) {
            $consumeFromRegular = min($remainingToConsume, $newAiCredits);
            $newAiCredits -= $consumeFromRegular;
            $remainingToConsume -= $consumeFromRegular;
        }

        // Se ainda precisar consumir mais, usa os créditos adicionais
        if ($remainingToConsume > 0 && $newAdditionalCredits > 0) {
            $consumeFromAdditional = min($remainingToConsume, $newAdditionalCredits);
            $newAdditionalCredits -= $consumeFromAdditional;
            $remainingToConsume -= $consumeFromAdditional;
        }

        $company->update([
            'ai_credits' => $newAiCredits,
            'ai_additional_credits' => $newAdditionalCredits,
        ]);

        return true;
    }

    public function getAvailablePackages(): array
    {
        return [
            [
                'price_id' => config('services.stripe.prices.ai_credits.basic'),
                'name' => 'Pacote Básico',
                'credits' => 100,
                'price' => 14.90,
                'price_formatted' => 'R$ 14,90',
                'description' => 'Ideal para uso ocasional',
                'popular' => false,
            ],
            [
                'price_id' => config('services.stripe.prices.ai_credits.professional'),
                'name' => 'Pacote Profissional',
                'credits' => 500,
                'price' => 59.90,
                'price_formatted' => 'R$ 59,90',
                'description' => 'Perfeito para uso regular',
                'popular' => true,
            ],
            [
                'price_id' => config('services.stripe.prices.ai_credits.enterprise'),
                'name' => 'Pacote Empresarial',
                'credits' => 2000,
                'price' => 147.00,
                'price_formatted' => 'R$ 147,00',
                'description' => 'Para uso intensivo',
                'popular' => false,
            ],
        ];
    }

    public function createPaymentIntent(Company $company, string $priceId): array
    {
        $packages = $this->getAvailablePackages();
        $package = collect($packages)->firstWhere('price_id', $priceId);

        if (!$package) {
            throw new \Exception('Pacote não encontrado');
        }

        try {
            // Usar Cashier para criar checkout session para one-time payment com configurações de segurança
            $checkoutOptions = [
                'success_url' => route('ai-credits.success') . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => route('ai-credits.index'),

                // CRÍTICO: Coletar endereço de cobrança (ajuda na verificação AVS e reduz fraude)
                'billing_address_collection' => 'required',

                // Coletar telefone (adiciona camada extra de verificação)
                'phone_number_collection' => [
                    'enabled' => true,
                ],

                // CRÍTICO: Habilitar 3D Secure automático (SCA - Strong Customer Authentication)
                'payment_method_options' => [
                    'card' => [
                        'request_three_d_secure' => 'automatic',
                    ],
                ],

                // Atualizar informações do customer automaticamente
                'customer_update' => [
                    'address' => 'auto',
                    'name' => 'auto',
                ],

                // Locale em português
                'locale' => 'pt-BR',

                // Metadados úteis para análise de fraude
                'metadata' => [
                    'price_id' => $priceId,
                    'credits' => $package['credits'],
                    'company_id' => $company->id,
                    'package_name' => $package['name'],
                ],

                // Statement descriptor (aparece na fatura do cartão)
                'payment_intent_data' => [
                    'statement_descriptor' => 'ManagerClin',
                    'statement_descriptor_suffix' => 'AI CREDITS',
                    'metadata' => [
                        'price_id' => $priceId,
                        'credits' => $package['credits'],
                        'company_id' => $company->id,
                    ],
                ],

                // Texto customizado para tranquilizar o cliente
                'custom_text' => [
                    'submit' => [
                        'message' => 'Seus dados estão protegidos com criptografia de ponta a ponta',
                    ],
                ],
            ];

            // Se não tem método de pagamento salvo, configurar para salvar após o pagamento
            if (!$company->hasDefaultPaymentMethod()) {
                $checkoutOptions['payment_intent_data']['setup_future_usage'] = 'off_session';
            }

            $checkout = $company->checkout([$priceId => 1], $checkoutOptions);

            return [
                'checkout_url' => $checkout->url,
                'package' => $package,
            ];
        } catch (\Exception $e) {
            Log::error('Erro ao criar checkout session para créditos de IA', [
                'company_id' => $company->id,
                'price_id' => $priceId,
                'error' => $e->getMessage(),
            ]);

            throw new \Exception('Erro ao inicializar pagamento. Tente novamente.');
        }
    }

    public function purchaseWithSavedCard(Company $company, string $priceId): array
    {
        $packages = $this->getAvailablePackages();
        $package = collect($packages)->firstWhere('price_id', $priceId);

        if (!$package) {
            throw new \Exception('Pacote não encontrado');
        }

        if (!$company->hasDefaultPaymentMethod()) {
            throw new \Exception('Nenhum método de pagamento salvo encontrado');
        }

        try {
            // Usar Cashier para cobrar com método de pagamento salvo
            $amount = (int) ($package['price'] * 100); // Convert to cents

            $payment = $company->charge($amount, $company->defaultPaymentMethod()->id, [
                'metadata' => [
                    'price_id' => $priceId,
                    'credits' => $package['credits'],
                    'company_id' => $company->id,
                    'type' => 'ai_credits_purchase',
                ],
                'description' => "Compra de {$package['name']} - {$package['credits']} créditos de IA",
                'statement_descriptor' => 'ManagerClin',
                'statement_descriptor_suffix' => 'AI CREDITS',
                // Habilitar 3D Secure quando necessário mesmo com cartão salvo
                'payment_method_options' => [
                    'card' => [
                        'request_three_d_secure' => 'automatic',
                    ],
                ],
            ]);

            // Se o pagamento foi bem-sucedido, retornar sucesso (créditos serão adicionados via webhook)
            if ($payment->status === 'succeeded') {
                return [
                    'success' => true,
                    'message' => 'Compra realizada com sucesso! Os créditos serão adicionados em instantes.',
                    'credits_to_add' => $package['credits'],
                    'package' => $package,
                ];
            }

            return [
                'success' => false,
                'error' => 'Pagamento não pôde ser processado',
                'payment_status' => $payment->status,
            ];
        } catch (\Exception $e) {
            Log::error('Erro ao comprar com cartão salvo', [
                'company_id' => $company->id,
                'price_id' => $priceId,
                'error' => $e->getMessage(),
            ]);

            throw new \Exception('Erro ao processar pagamento. Tente novamente ou use outro método de pagamento.');
        }
    }
}
