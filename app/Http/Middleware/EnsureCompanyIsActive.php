<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureCompanyIsActive
{
    public function handle(Request $request, Closure $next)
    {
        // Permitir acesso à rota de billing sem verificação
        if ($request->routeIs('billing.*')) {
            return $next($request);
        }

        $company = $request->user()->company;

        if (! $company->isActive()) {
            return redirect()->route('billing.index');
        }

        return $next($request);
    }
}
