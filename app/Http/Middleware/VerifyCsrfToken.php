<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;
use Illuminate\Support\Facades\Log;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        'webhook/*',
        'stripe/webhook',
        'ai-credits/create-payment-intent',
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, \Closure $next)
    {
        // Log apenas em desenvolvimento
        if (config('app.debug')) {
            Log::debug('CSRF Token Verification', [
                'url' => $request->url(),
                'method' => $request->method(),
                'has_token' => $request->hasHeader('X-CSRF-TOKEN'),
            ]);
        }

        return parent::handle($request, $next);
    }
}