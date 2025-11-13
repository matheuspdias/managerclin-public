<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\Swagger\SwaggerPasswordDocs;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;

class PasswordController extends Controller
{
    use SwaggerPasswordDocs;

    /**
     * Show the user's password settings page.
     */
    public function edit(Request $request): Response|JsonResponse
    {
        if ($request->wantsJson()) {
            // Para API: retorna apenas confirmação que o endpoint existe
            return response()->json([
                'message' => 'Endpoint de atualização de senha disponível',
            ], 200);
        }

        // Para Web: renderiza página Inertia
        return Inertia::render('settings/password');
    }

    /**
     * Update the user's password.
     */
    public function update(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', Password::defaults(), 'confirmed'],
        ], [
            'current_password.required' => 'A senha atual é obrigatória.',
            'current_password.current_password' => 'A senha atual está incorreta.',
            'password.required' => 'A nova senha é obrigatória.',
            'password.confirmed' => 'A confirmação da senha não confere.',
        ]);

        $request->user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        if ($request->wantsJson()) {
            // Para API: retorna JSON com sucesso
            return response()->json([
                'message' => 'Senha atualizada com sucesso',
            ], 200);
        }

        // Para Web: redireciona com mensagem de sucesso
        return back()->with('status', 'password-updated');
    }
}
