<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Inspiring;
use Illuminate\Http\Request;
use Inertia\Middleware;
use Tighten\Ziggy\Ziggy;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        [$message, $author] = str(Inspiring::quotes()->random())->explode('-');
        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'quote' => ['message' => trim($message), 'author' => trim($author)],
            'auth' => [
                'user' => $request->user() ? [
                    'id' => $request->user()->id,
                    'name' => $request->user()->name,
                    'email' => $request->user()->email,
                    'phone' => $request->user()->phone,
                    'crm' => $request->user()->crm,
                    'id_role' => $request->user()->id_role,
                    'image' => $request->user()->image_url,
                    'is_admin' => $request->user()->isAdmin(),
                    'role' => $request->user()->load('role')->role ? [
                        'id' => $request->user()->role->id,
                        'name' => $request->user()->role->name,
                        'type' => $request->user()->role->type,
                    ] : null,
                ] : null,
                'company' => $request->user()?->company ? [
                    'trial_ends_at' => $request->user()->company->trial_ends_at,
                    'is_on_trial' => $request->user()->company->isOnTrial(),
                    'plan' => $this->getCompanyPlan($request->user()->company),
                ] : null,

            ],
            'ziggy' => fn(): array => [
                ...(new Ziggy)->toArray(),
                'location' => $request->url(),
            ],
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
            'flash' => [
                'success' => $request->session()->get('success'),
                'error' => $request->session()->get('error'),
            ],
        ];
    }

    /**
     * Get the company's subscription plan
     */
    private function getCompanyPlan($company): ?string
    {
        if (!$company) {
            return null;
        }

        $subscription = $company->subscription('default');

        if (!$subscription || !$subscription->active()) {
            return null;
        }

        // Check which plan the company is subscribed to
        $essencialPriceId = config('services.stripe.prices.plans.essencial');
        $proPriceId = config('services.stripe.prices.plans.pro');
        $premiumPriceId = config('services.stripe.prices.plans.premium');

        $planItem = $subscription->items->first(fn($item) => in_array($item->stripe_price, [$essencialPriceId, $proPriceId, $premiumPriceId]));

        if (!$planItem) {
            return null;
        }

        // Return the plan name based on the price ID
        if ($planItem->stripe_price === $essencialPriceId) {
            return 'essencial';
        } elseif ($planItem->stripe_price === $proPriceId) {
            return 'pro';
        } elseif ($planItem->stripe_price === $premiumPriceId) {
            return 'premium';
        }

        return null;
    }
}
