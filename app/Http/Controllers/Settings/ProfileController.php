<?php

namespace App\Http\Controllers\Settings;

use App\DTO\Schedule\UpdateScheduleCollectionDTO;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\Swagger\SwaggerProfileDocs;
use App\Http\Requests\Schedule\UpdateScheduleRequest;
use App\Http\Requests\Settings\ProfileUpdateRequest;
use App\Http\Resources\User\UserResource;
use App\Services\Role\RoleService;
use App\Services\Schedule\UserScheduleService;
use App\Services\Whatsapp\WhatsappMessageTemplateService;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    use SwaggerProfileDocs;

    public function __construct(
        protected RoleService $roleService,
        protected UserScheduleService $userScheduleService,
        protected WhatsappMessageTemplateService $templateService
    ) {}

    /**
     * Show the user's profile settings page.
     */
    public function edit(Request $request): Response|JsonResponse
    {
        $user = $request->user()->load('role', 'company');

        if ($request->wantsJson()) {
            // Para API: retorna JSON com os dados do perfil
            return response()->json([
                'data' => new UserResource($user),
            ], 200);
        }

        // Para Web: renderiza página Inertia
        $roles = $this->roleService->getAll();

        return Inertia::render('settings/profile', [
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status' => $request->session()->get('status'),
            'isAdmin' => $request->user()->isAdmin(),
            'roles' => $roles,
        ]);
    }

    /**
     * Update the user's profile settings.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse|JsonResponse
    {
        $user = $request->user();
        $data = $request->validated();

        // Upload da imagem, se enviada
        if ($request->hasFile('image')) {
            // Remove a imagem anterior, se existir
            if ($user->image && Storage::disk('public')->exists($user->image)) {
                Storage::disk('public')->delete($user->image);
            }

            // Armazena a nova imagem
            $data['image'] = $request->file('image')->store("users/{$user->id}", 'public');
        } else {
            // Se não houver nova imagem, remove a chave do array
            unset($data['image']);
        }

        // Se o e-mail foi alterado, zera a verificação
        if ($user->email !== $data['email']) {
            $user->email_verified_at = null;
        }

        // Atualiza os dados
        $user->fill($data)->save();

        if ($request->wantsJson()) {
            // Para API: retorna JSON com o perfil atualizado
            $user->load('role', 'company');
            return response()->json([
                'message' => 'Perfil atualizado com sucesso',
                'data' => new UserResource($user),
            ], 200);
        }

        // Para Web: redireciona com mensagem de sucesso
        return to_route('profile.edit')->with('status', 'profile-updated');
    }


    /**
     * Atualiza a foto de perfil do usuário autenticado
     */
    public function updatePhoto(Request $request): JsonResponse|RedirectResponse
    {
        $request->validate([
            'image' => ['required', 'image', 'max:2048'],
        ], [
            'image.required' => 'A imagem é obrigatória.',
            'image.image' => 'O arquivo enviado deve ser uma imagem.',
            'image.max' => 'A imagem não pode ter mais de 2MB.',
        ]);

        $user = $request->user();

        // Remove a imagem anterior, se existir
        if ($user->image && Storage::disk('public')->exists($user->image)) {
            Storage::disk('public')->delete($user->image);
        }

        // Armazena a nova imagem
        $imagePath = $request->file('image')->store("users/{$user->id}", 'public');
        $user->image = $imagePath;
        $user->save();

        if ($request->wantsJson()) {
            // Para API: retorna JSON com o perfil atualizado
            $user->load('role', 'company');
            return response()->json([
                'message' => 'Foto de perfil atualizada com sucesso',
                'data' => new UserResource($user),
            ], 200);
        }

        // Para Web: redireciona com mensagem de sucesso
        return to_route('profile.edit')->with('status', 'photo-updated');
    }

    /**
     * Remove a foto de perfil do usuário autenticado
     */
    public function deletePhoto(Request $request): JsonResponse|RedirectResponse
    {
        $user = $request->user();

        // Remove a imagem do storage, se existir
        if ($user->image && Storage::disk('public')->exists($user->image)) {
            Storage::disk('public')->delete($user->image);
        }

        // Remove referência da imagem do banco
        $user->image = null;
        $user->save();

        if ($request->wantsJson()) {
            // Para API: retorna JSON com o perfil atualizado
            $user->load('role', 'company');
            return response()->json([
                'message' => 'Foto de perfil removida com sucesso',
                'data' => new UserResource($user),
            ], 200);
        }

        // Para Web: redireciona com mensagem de sucesso
        return to_route('profile.edit')->with('status', 'photo-deleted');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    /**
     * Exibe os horários de atendimento do usuário
     */
    public function timezone(Request $request): Response|JsonResponse
    {
        $schedules = $this->userScheduleService->getByUser();

        if ($request->wantsJson()) {
            // Para API: retorna JSON com os horários
            return response()->json([
                'data' => $schedules,
            ], 200);
        }

        // Para Web: renderiza página Inertia
        return Inertia::render('settings/timezone', [
            'schedules' => $schedules
        ]);
    }

    /**
     * Atualiza os horários de atendimento do usuário
     */
    public function updateTimezone(UpdateScheduleRequest $request): RedirectResponse|JsonResponse
    {
        $this->userScheduleService->updateMySchedules(
            UpdateScheduleCollectionDTO::makeFromRequest($request->input('schedules'))
        );

        if ($request->wantsJson()) {
            // Para API: retorna JSON com sucesso
            return response()->json([
                'message' => 'Horários atualizados com sucesso',
                'data' => $this->userScheduleService->getByUser(),
            ], 200);
        }

        // Para Web: redireciona com mensagem de sucesso
        return to_route('profile.timezone')->with('status', 'timezone-updated');
    }

    public function whatsapp(): Response
    {
        $user = Auth::user();
        $company = $user->company;

        return Inertia::render('settings/whatsapp', [
            'messageTemplates' => [
                'day_before' => $company->whatsapp_message_day_before ?? $this->templateService->getDefaultDayBeforeMessage(),
                '3hours_before' => $company->whatsapp_message_3hours_before ?? $this->templateService->getDefault3HoursBeforeMessage(),
            ],
            'availableVariables' => $this->templateService->getVariableDescriptions(),
        ]);
    }

    public function updateWhatsappMessages(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'whatsapp_message_day_before' => 'nullable|string|max:1000',
            'whatsapp_message_3hours_before' => 'nullable|string|max:1000',
        ]);

        $user = Auth::user();
        $company = $user->company;

        $company->update([
            'whatsapp_message_day_before' => $validated['whatsapp_message_day_before'],
            'whatsapp_message_3hours_before' => $validated['whatsapp_message_3hours_before'],
        ]);

        return back()->with('status', 'whatsapp-messages-updated');
    }
}
