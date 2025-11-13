<?php

namespace App\Http\Controllers;

use App\Services\AICredits\AICreditsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

class AICreditsController extends Controller
{
    public function __construct(
        protected AICreditsService $aiCreditsService
    ) {}

    public function index(): Response
    {
        $user = Auth::user();
        $company = $user->company;

        if (!$company) {
            return Inertia::render('ai-credits/index', [
                'error' => 'Company not found.',
            ]);
        }

        try {
            $data = $this->aiCreditsService->getCreditsData($company);

            // Adicionar informações sobre método de pagamento
            $data['company'] = [
                'id' => $company->id,
                'name' => $company->name,
                'has_default_payment_method' => $company->hasDefaultPaymentMethod(),
                'default_payment_method' => $this->getDefaultPaymentMethodData($company),
            ];

            return Inertia::render('ai-credits/index', $data);
        } catch (\Exception $e) {
            return Inertia::render('ai-credits/index', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function createPaymentIntent(Request $request)
    {
        Log::info('AI Credits: createPaymentIntent called', [
            'user_id' => Auth::id(),
            'price_id' => $request->get('price_id'),
            'headers' => $request->headers->all()
        ]);

        $user = Auth::user();
        $company = $user->company;

        if (!$company) {
            Log::error('AI Credits: Company not found for user', ['user_id' => $user->id]);
            return response()->json(['error' => 'Company not found.'], 404);
        }

        $request->validate([
            'price_id' => 'required|string',
        ]);

        try {
            $result = $this->aiCreditsService->createPaymentIntent($company, $request->price_id);
            Log::info('AI Credits: Checkout session created successfully', [
                'company_id' => $company->id,
                'price_id' => $request->price_id
            ]);
            return response()->json($result);
        } catch (\Exception $e) {
            Log::error('AI Credits: Error creating checkout session', [
                'error' => $e->getMessage(),
                'company_id' => $company->id,
                'price_id' => $request->price_id
            ]);
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function consumeCredits(Request $request)
    {
        $user = Auth::user();
        $company = $user->company;

        if (!$company) {
            return response()->json(['error' => 'Company not found.'], 404);
        }

        $request->validate([
            'credits' => 'required|integer|min:1',
        ]);

        try {
            $success = $this->aiCreditsService->consumeCredits($company, $request->credits);

            if (!$success) {
                return response()->json([
                    'error' => 'Créditos insuficientes'
                ], 400);
            }

            $freshCompany = $company->fresh();
            return response()->json([
                'message' => 'Créditos consumidos com sucesso',
                'remaining_credits' => $freshCompany->ai_credits + $freshCompany->ai_additional_credits,
                'regular_credits' => $freshCompany->ai_credits,
                'additional_credits' => $freshCompany->ai_additional_credits
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function purchaseWithSavedCard(Request $request)
    {
        $user = Auth::user();
        $company = $user->company;

        if (!$company) {
            return response()->json(['error' => 'Company not found.'], 404);
        }

        if (!$company->hasDefaultPaymentMethod()) {
            return response()->json(['error' => 'No default payment method found.'], 400);
        }

        $request->validate([
            'price_id' => 'required|string',
        ]);

        try {
            $result = $this->aiCreditsService->purchaseWithSavedCard($company, $request->price_id);

            Log::info('AI Credits: Purchase with saved card completed', [
                'company_id' => $company->id,
                'price_id' => $request->price_id
            ]);

            return response()->json($result);
        } catch (\Exception $e) {
            Log::error('AI Credits: Error purchasing with saved card', [
                'error' => $e->getMessage(),
                'company_id' => $company->id,
                'price_id' => $request->price_id
            ]);
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function success(Request $request)
    {
        $user = Auth::user();
        $company = $user->company;

        if (!$company) {
            return Inertia::render('ai-credits/index', [
                'error' => 'Company not found.',
            ]);
        }

        $data = $this->aiCreditsService->getCreditsData($company);

        $data['company'] = [
            'id' => $company->id,
            'name' => $company->name,
            'has_default_payment_method' => $company->hasDefaultPaymentMethod(),
            'default_payment_method' => $this->getDefaultPaymentMethodData($company),
        ];

        $data['success_message'] = 'Compra realizada com sucesso! Os créditos serão adicionados em instantes.';

        return Inertia::render('ai-credits/index', $data);
    }

    private function getDefaultPaymentMethodData($company): ?array
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
}
