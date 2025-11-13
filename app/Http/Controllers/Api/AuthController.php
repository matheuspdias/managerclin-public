<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\Swagger\SwaggerAuthDocs;
use App\Http\Resources\User\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    use SwaggerAuthDocs;
    /**
     * Login do usuário via API
     * Retorna token de autenticação Bearer para uso nos endpoints protegidos
     *
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'device_name' => 'string|max:255',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['As credenciais fornecidas estão incorretas.'],
            ]);
        }

        // Verifica se a empresa está ativa
        if (!$user->company || !$user->company->isActive()) {
            return response()->json([
                'message' => 'Empresa inativa. Entre em contato com o suporte.',
            ], 403);
        }

        // Cria token com abilities (permissões)
        $deviceName = $request->device_name ?? 'mobile-app';
        $token = $user->createToken($deviceName)->plainTextToken;

        return response()->json([
            'message' => 'Login realizado com sucesso',
            'user' => new UserResource($user->load('role', 'company')),
            'token' => $token,
            'token_type' => 'Bearer',
        ], 200);
    }

    /**
     * Logout do usuário via API
     * Revoga o token atual do usuário
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function logout(Request $request): JsonResponse
    {
        // Revoga o token atual
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logout realizado com sucesso',
        ], 200);
    }

    /**
     * Revoga todos os tokens do usuário
     * Útil para fazer logout de todos os dispositivos
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function logoutAll(Request $request): JsonResponse
    {
        // Revoga todos os tokens do usuário
        $request->user()->tokens()->delete();

        return response()->json([
            'message' => 'Logout de todos os dispositivos realizado com sucesso',
        ], 200);
    }

    /**
     * Retorna dados do usuário autenticado
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user()->load('role', 'company');

        return response()->json([
            'user' => new UserResource($user),
        ], 200);
    }
}
