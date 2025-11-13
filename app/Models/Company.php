<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Cashier\Billable;
use Laravel\Cashier\Subscription as CashierSubscription;

class Company extends Model
{
    use SoftDeletes, Billable;

    protected $table = 'companies';

    protected $fillable = [
        'name',
        'signature_status',
        'signature_start_at',
        'signature_end_at',
        'trial_ends_at',
        'ai_credits',
        'ai_additional_credits',
        'ai_credits_last_purchase',
        'telemedicine_credits',
        'telemedicine_additional_credits',
        'telemedicine_credits_last_purchase',
        'whatsapp_message_day_before',
        'whatsapp_message_3hours_before',
    ];

    protected $dates = [
        'signature_start_at',
        'signature_end_at',
        'trial_ends_at',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $casts = [
        'trial_ends_at' => 'datetime',
        'telemedicine_credits_last_purchase' => 'datetime',
    ];

    /**
     * Verifica se está em trial.
     */
    public function isOnTrial(): bool
    {
        return $this->trial_ends_at instanceof Carbon && $this->trial_ends_at->isFuture();
    }

    /**
     * Verifica se o trial expirou.
     */
    public function hasExpiredTrial(): bool
    {
        return ! $this->isOnTrial();
    }

    /**
     * Retorna os dias restantes de trial.
     */
    public function remainingTrialDays(): int
    {
        if ($this->isOnTrial()) {
            return now()->diffInDays($this->trial_ends_at, false);
        }

        return 0;
    }

    /**
     * Verifica se a empresa está ativa (trial ou assinatura válida).
     */

    public function isActive(): bool
    {
        // Se ainda estiver em trial, está ativo
        if ($this->isOnTrial()) {
            return true;
        }

        // Se tiver assinatura válida no Cashier, está ativo
        if ($this->subscribed('default')) {
            return true;
        }

        // Fallback: verificar signature_status (útil durante o período de sincronização do webhook)
        if ($this->signature_status === \App\Enums\SignatureStatusEnum::ACTIVE) {
            return true;
        }

        // Caso contrário, não está ativo
        return false;
    }

    /**
     * Retorna informações sobre o limite de usuários baseado no plano.
     * Novos limites:
     * - Essencial: até 2 usuários
     * - Pro: até 5 usuários
     * - Premium: até 10 usuários
     */
    public function getUserLimitInfo(): array
    {
        $subscription = $this->subscription('default');
        $currentUsers = $this->users()->count();

        if (!$subscription) {
            return [
                'current_users' => $currentUsers,
                'max_allowed_users' => null,
                'has_subscription' => false,
                'is_over_limit' => false,
                'remaining_users' => null,
                'plan_name' => null,
                'message' => 'ℹ️ Você não possui um plano ativo. Algumas funcionalidades podem ser limitadas.'
            ];
        }

        // Determinar o plano atual e seus limites
        $essencialPriceId = config('services.stripe.prices.plans.essencial');
        $proPriceId = config('services.stripe.prices.plans.pro');
        $premiumPriceId = config('services.stripe.prices.plans.premium');

        $planLimits = [
            $essencialPriceId => ['name' => 'Essencial', 'max_users' => 2],
            $proPriceId => ['name' => 'Pro', 'max_users' => 5],
            $premiumPriceId => ['name' => 'Premium', 'max_users' => 10],
        ];

        $currentPlanLimit = null;
        foreach ($subscription->items as $item) {
            if (isset($planLimits[$item->stripe_price])) {
                $currentPlanLimit = $planLimits[$item->stripe_price];
                break;
            }
        }

        if (!$currentPlanLimit) {
            return [
                'current_users' => $currentUsers,
                'max_allowed_users' => null,
                'has_subscription' => true,
                'is_over_limit' => false,
                'remaining_users' => null,
                'plan_name' => 'Desconhecido',
                'message' => 'ℹ️ Plano não identificado.'
            ];
        }

        $maxAllowed = $currentPlanLimit['max_users'];
        $isOverLimit = $currentUsers > $maxAllowed;
        $remainingUsers = max(0, $maxAllowed - $currentUsers);

        return [
            'current_users' => $currentUsers,
            'max_allowed_users' => $maxAllowed,
            'has_subscription' => true,
            'is_over_limit' => $isOverLimit,
            'remaining_users' => $remainingUsers,
            'plan_name' => $currentPlanLimit['name'],
            'message' => $this->getUserLimitMessage($currentUsers, $maxAllowed, $currentPlanLimit['name'])
        ];
    }

    /**
     * Gera mensagem sobre o limite de usuários
     */
    protected function getUserLimitMessage(int $currentUsers, int $maxAllowed, string $planName): string
    {
        if ($currentUsers > $maxAllowed) {
            return "⚠️ Limite excedido! Seu plano {$planName} permite até {$maxAllowed} usuário(s), mas você tem {$currentUsers} cadastrados.";
        } else if ($currentUsers === $maxAllowed) {
            return "✓ Você está no limite máximo de usuários do plano {$planName} ({$maxAllowed} usuários).";
        }

        return "✓ {$currentUsers}/{$maxAllowed} usuários utilizados no plano {$planName}";
    }

    /*
    |--------------------------------------------------------------------------
    | Métodos de Créditos de Telemedicina
    |--------------------------------------------------------------------------
    */

    /**
     * Retorna o limite de créditos de telemedicina baseado no plano.
     * - Essencial: 20 créditos/mês
     * - Pro: 50 créditos/mês
     * - Premium: 100 créditos/mês
     */
    public function getTelemedicineCreditsLimit(): int
    {
        $subscription = $this->subscription('default');

        if (!$subscription) {
            return 0;
        }

        $essencialPriceId = config('services.stripe.prices.plans.essencial');
        $proPriceId = config('services.stripe.prices.plans.pro');
        $premiumPriceId = config('services.stripe.prices.plans.premium');

        $planLimits = [
            $essencialPriceId => 20,
            $proPriceId => 50,
            $premiumPriceId => 100,
        ];

        foreach ($subscription->items as $item) {
            if (isset($planLimits[$item->stripe_price])) {
                return $planLimits[$item->stripe_price];
            }
        }

        return 0;
    }

    /**
     * Verifica se a empresa tem créditos de telemedicina disponíveis.
     */
    public function hasTelemedicineCredits(): bool
    {
        return $this->telemedicine_credits > 0;
    }

    /**
     * Consome 1 crédito de telemedicina (subtrai do total).
     * Retorna true se consumiu com sucesso, false se não tinha créditos.
     */
    public function consumeTelemedicineCredit(): bool
    {
        if (!$this->hasTelemedicineCredits()) {
            return false;
        }

        $this->decrement('telemedicine_credits');

        return true;
    }

    /**
     * Adiciona créditos de telemedicina adicionais (compra).
     */
    public function addTelemedicineCredits(int $credits): void
    {
        $this->increment('telemedicine_credits', $credits);
        $this->increment('telemedicine_additional_credits', $credits);
        $this->update(['telemedicine_credits_last_purchase' => now()]);
    }

    /**
     * Reseta os créditos de telemedicina mensalmente.
     * Mantém os créditos adicionais e adiciona os créditos do plano.
     */
    public function resetTelemedicineCredits(): void
    {
        $planCredits = $this->getTelemedicineCreditsLimit();
        $additionalCredits = $this->telemedicine_additional_credits ?? 0;

        $this->update([
            'telemedicine_credits' => $planCredits + $additionalCredits,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Relacionamentos internos
    |--------------------------------------------------------------------------
    */
    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'id_company');
    }


    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class, 'id_company');
    }

    public function rooms(): HasMany
    {
        return $this->hasMany(Room::class, 'id_company');
    }

    public function services(): HasMany
    {
        return $this->hasMany(Service::class, 'id_company');
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class, 'id_company');
    }

    public function whatsappConfig(): HasOne
    {
        return $this->hasOne(WhatsAppConfig::class, 'id_company');
    }

    public function integration_status()
    {
        return $this->hasMany(IntegrationStatus::class, 'id_company');
    }


    /*
    |--------------------------------------------------------------------------
    | Relacionamentos com o Cashier
    |--------------------------------------------------------------------------
    */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(CashierSubscription::class, 'company_id');
    }
}
