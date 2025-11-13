<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckResourcePermission
{
    /**
     * Recursos que exigem plano Pro ou Premium
     */
    private const PRO_PREMIUM_REQUIRED_RESOURCES = ['financial', 'inventory'];

    /**
     * Recursos que exigem plano Premium (não disponível no Pro)
     */
    private const PREMIUM_ONLY_RESOURCES = ['marketing'];

    public function handle(Request $request, Closure $next, string $resource): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        if (!$user->role) {
            return inertia('Errors/403', [
                'message' => 'Usuário sem role definida.'
            ])->toResponse($request)->setStatusCode(403);
        }

        if (!$user->role->canAccess($resource)) {
            return inertia('Errors/403', [
                'message' => 'Você não tem permissão para acessar este recurso.'
            ])->toResponse($request)->setStatusCode(403);
        }

        $company = $user->company;

        if (!$company) {
            return inertia('Errors/403', [
                'message' => 'Empresa não encontrada.'
            ])->toResponse($request)->setStatusCode(403);
        }

        // Se está em trial, permite acesso a tudo
        if ($company->isOnTrial()) {
            return $next($request);
        }

        // Verificar se o recurso requer plano Premium exclusivo
        if (in_array($resource, self::PREMIUM_ONLY_RESOURCES)) {
            $subscription = $company->subscription('default');

            if (!$subscription || !$subscription->active()) {
                return inertia('Errors/403', [
                    'message' => 'Este recurso requer um plano ativo. Acesse a página de Faturamento para assinar um plano.',
                    'upgrade_required' => true
                ])->toResponse($request)->setStatusCode(403);
            }

            // Verificar se o plano é Premium
            $premiumPriceId = config('services.stripe.prices.plans.premium');

            $hasPremiumPlan = $subscription->items->contains(function ($item) use ($premiumPriceId) {
                return $item->stripe_price === $premiumPriceId;
            });

            if (!$hasPremiumPlan) {
                return inertia('Errors/403', [
                    'message' => 'Este recurso está disponível apenas no plano Premium. Faça upgrade do seu plano para ter acesso.',
                    'upgrade_required' => true
                ])->toResponse($request)->setStatusCode(403);
            }
        }

        // Verificar se o recurso requer plano Pro ou Premium
        if (in_array($resource, self::PRO_PREMIUM_REQUIRED_RESOURCES)) {
            $subscription = $company->subscription('default');

            if (!$subscription || !$subscription->active()) {
                return inertia('Errors/403', [
                    'message' => 'Este recurso requer um plano ativo. Acesse a página de Faturamento para assinar um plano.',
                    'upgrade_required' => true
                ])->toResponse($request)->setStatusCode(403);
            }

            // Verificar se o plano é Pro ou Premium
            $proPriceId = config('services.stripe.prices.plans.pro');
            $premiumPriceId = config('services.stripe.prices.plans.premium');

            $hasProOrPremiumPlan = $subscription->items->contains(function ($item) use ($proPriceId, $premiumPriceId) {
                return in_array($item->stripe_price, [$proPriceId, $premiumPriceId]);
            });

            if (!$hasProOrPremiumPlan) {
                return inertia('Errors/403', [
                    'message' => 'Este recurso está disponível apenas nos planos Pro e Premium. Faça upgrade do seu plano para ter acesso.',
                    'upgrade_required' => true
                ])->toResponse($request)->setStatusCode(403);
            }
        }

        return $next($request);
    }
}