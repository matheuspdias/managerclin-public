<?php

namespace App\Http\Controllers;

use App\DTO\Telemedicine\CreateTelemedicineSessionDTO;
use App\DTO\Telemedicine\UpdateTelemedicineSessionDTO;
use App\Http\Controllers\Traits\Swagger\SwaggerTelemedicineDocs;
use App\Http\Requests\CreateTelemedicineSessionRequest;
use App\Http\Requests\UpdateTelemedicineSessionRequest;
use App\Services\Telemedicine\TelemedicineService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

/**
 * Controller para gerenciar sessões de telemedicina
 */
class TelemedicineController extends Controller
{
    use SwaggerTelemedicineDocs;

    public function __construct(
        protected TelemedicineService $service
    ) {}

    /**
     * Criar nova sessão de telemedicina
     */
    public function createSession(CreateTelemedicineSessionRequest $request): JsonResponse|RedirectResponse
    {
        try {
            $dto = CreateTelemedicineSessionDTO::makeFromRequest($request);
            $session = $this->service->createSession($dto);

            $appointment = $session->appointment;

            $responseData = [
                'session_id' => $session->id,
                'room_name' => $session->room_name,
                'server_url' => $session->server_url,
                'join_url' => $session->join_url,
                'status' => $session->status,
                'appointment' => [
                    'id' => $appointment->id,
                    'date' => $appointment->date,
                    'start_time' => $appointment->start_time,
                    'doctor' => $appointment->user ? [
                        'id' => $appointment->user->id,
                        'name' => $appointment->user->name,
                    ] : null,
                    'customer' => $appointment->customer ? [
                        'id' => $appointment->customer->id,
                        'name' => $appointment->customer->name,
                    ] : null,
                ],
            ];

            // API: Retorna JSON
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'data' => $responseData,
                    'message' => 'Sessão de telemedicina criada com sucesso.',
                ], 201);
            }

            // Web: Retorna JSON inline para uso via AJAX
            return response()->json([
                'success' => true,
                'data' => $responseData,
                'message' => 'Sessão de telemedicina criada com sucesso.',
            ], 201);

        } catch (\Exception $e) {
            Log::error('Erro ao criar sessão de telemedicina', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erro ao criar sessão de telemedicina: ' . $e->getMessage(),
                ], 500);
            }

            return back()->with('error', 'Erro ao criar sessão de telemedicina: ' . $e->getMessage());
        }
    }

    /**
     * Buscar sessão de telemedicina por ID do agendamento
     */
    public function getSessionByAppointment(Request $request, string $appointmentId): JsonResponse
    {
        try {
            $session = $this->service->getSessionByAppointment((int) $appointmentId);
            $appointment = $session->appointment;

            $responseData = [
                'session_id' => $session->id,
                'room_name' => $session->room_name,
                'server_url' => $session->server_url,
                'join_url' => $session->join_url,
                'status' => $session->status,
                'started_at' => $session->started_at?->toISOString(),
                'ended_at' => $session->ended_at?->toISOString(),
                'duration_minutes' => $session->duration_minutes,
                'calculated_duration' => $session->calculated_duration,
                'notes' => $session->notes,
                'appointment' => [
                    'id' => $appointment->id,
                    'date' => $appointment->date,
                    'start_time' => $appointment->start_time,
                    'end_time' => $appointment->end_time,
                    'status' => $appointment->status,
                    'doctor' => $appointment->user ? [
                        'id' => $appointment->user->id,
                        'name' => $appointment->user->name,
                        'email' => $appointment->user->email,
                    ] : null,
                    'customer' => $appointment->customer ? [
                        'id' => $appointment->customer->id,
                        'name' => $appointment->customer->name,
                        'email' => $appointment->customer->email,
                        'phone' => $appointment->customer->phone,
                    ] : null,
                    'service' => $appointment->service ? [
                        'id' => $appointment->service->id,
                        'name' => $appointment->service->name,
                    ] : null,
                    'room' => $appointment->room ? [
                        'id' => $appointment->room->id,
                        'name' => $appointment->room->name,
                    ] : null,
                ],
            ];

            return response()->json([
                'success' => true,
                'data' => $responseData,
                'message' => 'Sessão encontrada com sucesso.',
            ], 200);

        } catch (\Exception $e) {
            Log::error('Erro ao buscar sessão de telemedicina', [
                'appointment_id' => $appointmentId,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar sessão: ' . $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Atualizar status da sessão de telemedicina
     */
    public function updateSessionStatus(UpdateTelemedicineSessionRequest $request, string $sessionId): JsonResponse|RedirectResponse
    {
        try {
            $dto = UpdateTelemedicineSessionDTO::makeFromRequest($request);
            $session = $this->service->updateSessionStatus((int) $sessionId, $dto);

            $responseData = [
                'session_id' => $session->id,
                'status' => $session->status,
                'started_at' => $session->started_at?->toISOString(),
                'ended_at' => $session->ended_at?->toISOString(),
                'duration_minutes' => $session->duration_minutes,
                'notes' => $session->notes,
            ];

            // API: Retorna JSON
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'data' => $responseData,
                    'message' => 'Status da sessão atualizado com sucesso.',
                ], 200);
            }

            // Web: Retorna JSON inline
            return response()->json([
                'success' => true,
                'data' => $responseData,
                'message' => 'Status da sessão atualizado com sucesso.',
            ], 200);

        } catch (\Exception $e) {
            Log::error('Erro ao atualizar status da sessão', [
                'session_id' => $sessionId,
                'error' => $e->getMessage(),
            ]);

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erro ao atualizar status: ' . $e->getMessage(),
                ], 500);
            }

            return back()->with('error', 'Erro ao atualizar status: ' . $e->getMessage());
        }
    }

    /**
     * Finalizar sessão de telemedicina
     */
    public function endSession(Request $request, string $sessionId): JsonResponse|RedirectResponse
    {
        try {
            // Validar dados opcionais
            $request->validate([
                'end_reason' => 'nullable|string|max:255',
                'notes' => 'nullable|string|max:5000',
            ]);

            $session = $this->service->endSession(
                (int) $sessionId,
                $request->input('end_reason'),
                $request->input('notes')
            );

            $responseData = [
                'session_id' => $session->id,
                'status' => $session->status,
                'started_at' => $session->started_at?->toISOString(),
                'ended_at' => $session->ended_at?->toISOString(),
                'duration_minutes' => $session->duration_minutes,
                'notes' => $session->notes,
            ];

            // API: Retorna JSON
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'data' => $responseData,
                    'message' => 'Sessão finalizada com sucesso.',
                ], 200);
            }

            // Web: Retorna JSON inline
            return response()->json([
                'success' => true,
                'data' => $responseData,
                'message' => 'Sessão finalizada com sucesso.',
            ], 200);

        } catch (\Exception $e) {
            Log::error('Erro ao finalizar sessão', [
                'session_id' => $sessionId,
                'error' => $e->getMessage(),
            ]);

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erro ao finalizar sessão: ' . $e->getMessage(),
                ], 500);
            }

            return back()->with('error', 'Erro ao finalizar sessão: ' . $e->getMessage());
        }
    }

    /**
     * Listar sessões ativas de telemedicina
     */
    public function getActiveSessions(Request $request): JsonResponse|InertiaResponse
    {
        try {
            // Validar parâmetros de query
            $request->validate([
                'doctor_id' => 'nullable|integer|exists:users,id',
                'page' => 'nullable|integer|min:1',
                'per_page' => 'nullable|integer|min:1|max:100',
            ]);

            $doctorId = $request->input('doctor_id');
            $page = $request->input('page', 1);
            $perPage = $request->input('per_page', 15);

            $sessions = $this->service->getActiveSessions($doctorId, $page, $perPage);

            $data = collect($sessions->items())->map(function ($session) {
                $appointment = $session->appointment;

                return [
                    'session_id' => $session->id,
                    'room_name' => $session->room_name,
                    'join_url' => $session->join_url,
                    'status' => $session->status,
                    'started_at' => $session->started_at?->toISOString(),
                    'current_duration' => $session->started_at ? $session->started_at->diffInMinutes(now()) : 0,
                    'appointment' => [
                        'id' => $appointment->id,
                        'date' => $appointment->date,
                        'start_time' => $appointment->start_time,
                        'doctor' => $appointment->user ? [
                            'id' => $appointment->user->id,
                            'name' => $appointment->user->name,
                        ] : null,
                        'customer' => $appointment->customer ? [
                            'id' => $appointment->customer->id,
                            'name' => $appointment->customer->name,
                        ] : null,
                        'service' => $appointment->service ? [
                            'id' => $appointment->service->id,
                            'name' => $appointment->service->name,
                        ] : null,
                    ],
                ];
            });

            // API: Retorna JSON com paginação
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'data' => $data,
                    'pagination' => [
                        'current_page' => $sessions->currentPage(),
                        'per_page' => $sessions->perPage(),
                        'total' => $sessions->total(),
                        'last_page' => $sessions->lastPage(),
                        'from' => $sessions->firstItem(),
                        'to' => $sessions->lastItem(),
                    ],
                    'message' => 'Sessões ativas listadas com sucesso.',
                ], 200);
            }

            // Web: Retorna página Inertia
            return Inertia::render('telemedicine/ActiveSessions', [
                'sessions' => $data,
                'pagination' => [
                    'current_page' => $sessions->currentPage(),
                    'per_page' => $sessions->perPage(),
                    'total' => $sessions->total(),
                    'last_page' => $sessions->lastPage(),
                ],
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao listar sessões ativas', [
                'error' => $e->getMessage(),
            ]);

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erro ao listar sessões ativas: ' . $e->getMessage(),
                ], 500);
            }

            return back()->with('error', 'Erro ao listar sessões ativas: ' . $e->getMessage());
        }
    }

    /**
     * Obter créditos de telemedicina disponíveis da empresa
     */
    public function getCredits(Request $request): JsonResponse
    {
        try {
            $company = auth()->user()->company;

            if (!$company) {
                return response()->json([
                    'success' => false,
                    'message' => 'Empresa não encontrada para o usuário autenticado.',
                ], 404);
            }

            $planLimit = $company->getTelemedicineCreditsLimit();
            $currentCredits = $company->telemedicine_credits ?? 0;
            $additionalCredits = $company->telemedicine_additional_credits ?? 0;
            $hasCredits = $company->hasTelemedicineCredits();
            $lastPurchase = $company->telemedicine_credits_last_purchase;

            // Obter plano atual
            $subscription = $company->subscription('default');
            $planName = null;

            if ($subscription) {
                $essencialPriceId = config('services.stripe.prices.plans.essencial');
                $proPriceId = config('services.stripe.prices.plans.pro');
                $premiumPriceId = config('services.stripe.prices.plans.premium');

                $planMap = [
                    $essencialPriceId => 'Essencial',
                    $proPriceId => 'Pro',
                    $premiumPriceId => 'Premium',
                ];

                foreach ($subscription->items as $item) {
                    if (isset($planMap[$item->stripe_price])) {
                        $planName = $planMap[$item->stripe_price];
                        break;
                    }
                }
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'credits_available' => $currentCredits,
                    'has_credits' => $hasCredits,
                    'plan_limit' => $planLimit,
                    'additional_credits' => $additionalCredits,
                    'plan_name' => $planName,
                    'last_purchase' => $lastPurchase?->toISOString(),
                    'status' => $hasCredits ? 'available' : 'depleted',
                ],
                'message' => 'Informações de créditos obtidas com sucesso.',
            ], 200);

        } catch (\Exception $e) {
            Log::error('Erro ao obter créditos de telemedicina', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao obter créditos: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Obter configurações do Jitsi/JaaS para o frontend
     */
    public function getConfig(Request $request): JsonResponse
    {
        try {
            $config = $this->service->getConfig();

            return response()->json([
                'success' => true,
                'data' => $config,
                'message' => 'Configurações obtidas com sucesso.',
            ], 200);

        } catch (\Exception $e) {
            Log::error('Erro ao obter configurações de telemedicina', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao obter configurações: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Notificar paciente via WhatsApp para entrar na teleconsulta
     */
    public function notifyPatient(Request $request, string $sessionId): JsonResponse|RedirectResponse
    {
        try {
            $result = $this->service->notifyPatient((int) $sessionId);

            // API: Retorna JSON
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'data' => $result,
                    'message' => 'Notificação enviada com sucesso para o paciente.',
                ], 200);
            }

            // Web: Retorna JSON inline
            return response()->json([
                'success' => true,
                'data' => $result,
                'message' => 'Notificação enviada com sucesso para o paciente.',
            ], 200);

        } catch (\Exception $e) {
            Log::error('Erro ao notificar paciente sobre teleconsulta', [
                'session_id' => $sessionId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erro ao enviar notificação: ' . $e->getMessage(),
                ], 500);
            }

            return back()->with('error', 'Erro ao enviar notificação: ' . $e->getMessage());
        }
    }
}
