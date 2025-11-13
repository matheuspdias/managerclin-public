<?php

namespace App\Http\Controllers;

use App\DTO\Appointment\CreateAppointmentDTO;
use App\DTO\Appointment\UpdateAppointmentDTO;
use App\Http\Controllers\Traits\Swagger\SwaggerAppointmentDocs;
use App\Http\Requests\Appointment\CreateAppointmentRequest;
use App\Http\Requests\Appointment\UpdateAppointmentRequest;
use App\Http\Resources\Appointment\AppointmentResource;
use App\Services\Appointment\AppointmentService;
use App\Services\ClinicService\ClinicService;
use App\Services\Customer\CustomerService;
use App\Services\Room\RoomService;
use App\Services\Schedule\UserScheduleService;
use App\Services\User\UserService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;

class AppointmentController extends Controller
{
    use SwaggerAppointmentDocs;
    public function __construct(
        protected AppointmentService $service,
        protected ClinicService $clinicService,
        protected UserService $userService,
        protected CustomerService $customerService,
        protected RoomService $roomService,
        protected UserScheduleService $userScheduleService
    ) {}

    public function index(Request $request): \Inertia\Response|JsonResponse
    {
        // Parâmetros com valores padrão
        $period = [
            'start_date' => Carbon::parse($request->input('start_date', now()->startOfMonth()))->format('Y-m-d'),
            'end_date' => Carbon::parse($request->input('end_date', now()->endOfMonth()))->format('Y-m-d'),
        ];

        $filters = [
            'search' => $request->get('search'),
            'status' => $request->get('status'),
            'user_id' => $request->get('user_id'),
            'start_date' => $period['start_date'],
            'end_date' => $period['end_date'],
        ];

        $pagination = [
            'page' => (int) $request->get('page', 1),
            'per_page' => (int) $request->get('per_page', 15),
        ];

        $view = $request->get('view', 'calendar');

        // Validação dos parâmetros de entrada
        $validator = Validator::make($request->all(), [
            'start_date' => 'sometimes|date',
            'end_date' => 'sometimes|date|after_or_equal:start_date',
            'search' => 'sometimes|nullable|string|max:255',
            'page' => 'sometimes|integer|min:1',
            'per_page' => 'sometimes|integer|min:5|max:100',
            'status' => 'sometimes|nullable|in:all,SCHEDULED,IN_PROGRESS,COMPLETED,CANCELLED',
            'user_id' => 'sometimes|nullable|string',
            'view' => 'sometimes|in:calendar,list,timeline,week',
        ]);

        if ($validator->fails()) {
            if ($request->wantsJson()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            // Em caso de erro de validação, retornar com dados mínimos
            return Inertia::render('appointments/index', [
                'appointments' => ['data' => [], 'total' => 0, 'current_page' => 1, 'per_page' => 15, 'last_page' => 1],
                'appointmentsForCalendar' => [],
                'services' => [],
                'users' => [],
                'customers' => [],
                'rooms' => [],
                'period' => $period,
                'filters' => $filters,
                'pagination' => $pagination,
                'currentView' => $view,
                'stats' => ['total' => 0, 'scheduled' => 0, 'completed' => 0, 'cancelled' => 0, 'today' => 0, 'week' => 0, 'revenue' => 0],
                'currentUser' => [
                    'id' => auth()->id(),
                    'is_admin' => auth()->user()->isAdmin(),
                ],
                'errors' => $validator->errors(),
            ]);
        }

        try {

            // Buscar dados com filtros aplicados
            $appointments = $this->service->getAllPaginated($filters, $pagination['page'], $pagination['per_page']);

            if ($request->wantsJson()) {
                // Para API: retorna JSON com paginação
                return response()->json([
                    'data' => AppointmentResource::collection($appointments->items()),
                    'meta' => [
                        'current_page' => $appointments->currentPage(),
                        'last_page' => $appointments->lastPage(),
                        'per_page' => $appointments->perPage(),
                        'total' => $appointments->total(),
                    ],
                ]);
            }

            $appointmentsForCalendar = $this->service->getForPeriod($period['start_date'], $period['end_date']);

            // Dados de apoio
            $services = $this->clinicService->getAll();

            // Filtrar usuários conforme permissão:
            // Admin: todos os DOCTOR e ADMIN
            // Médico: apenas ele mesmo
            // Recepcionista: todos os DOCTOR e ADMIN (pode marcar para eles)
            if (auth()->user()->isAdmin()) {
                $users = $this->userService->getProfessionals();
            } elseif (auth()->user()->isDoctor()) {
                $users = collect([auth()->user()]);
            } else {
                // Recepcionista pode ver todos os profissionais para marcar agendamentos
                $users = $this->userService->getProfessionals();
            }

            $customers = $this->customerService->getAll();
            $rooms = $this->roomService->getAll();

            // Estatísticas do período
            $stats = $this->service->getStatsForPeriod($period['start_date'], $period['end_date']);


            return Inertia::render('appointments/index', [
                'appointments' => $appointments,
                'appointmentsForCalendar' => $appointmentsForCalendar,
                'services' => $services,
                'users' => $users,
                'customers' => $customers,
                'rooms' => $rooms,
                'period' => $period,
                'filters' => $filters,
                'pagination' => $pagination,
                'currentView' => $view,
                'stats' => $stats,
                'currentUser' => [
                    'id' => auth()->id(),
                    'is_admin' => auth()->user()->isAdmin(),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Error loading appointments', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);

            if ($request->wantsJson()) {
                return response()->json(['error' => 'Erro ao carregar agendamentos'], 500);
            }

            return Inertia::render('appointments/index', [
                'appointments' => ['data' => [], 'total' => 0, 'current_page' => 1, 'per_page' => 15, 'last_page' => 1],
                'appointmentsForCalendar' => [],
                'services' => [],
                'users' => [],
                'customers' => [],
                'rooms' => [],
                'period' => $period,
                'filters' => $filters,
                'pagination' => $pagination,
                'currentView' => $view,
                'stats' => ['total' => 0, 'scheduled' => 0, 'completed' => 0, 'cancelled' => 0, 'today' => 0, 'week' => 0, 'revenue' => 0],
                'currentUser' => [
                    'id' => auth()->id(),
                    'is_admin' => auth()->user()->isAdmin(),
                ],
                'error' => 'Erro ao carregar agendamentos. Tente novamente.',
            ]);
        }
    }

    public function show(int $id, Request $request): \Inertia\Response|JsonResponse
    {
        try {
            $appointment = $this->service->findById($id);

            if ($request->wantsJson()) {
                return (new AppointmentResource($appointment))
                    ->response()
                    ->setStatusCode(200);
            }

            return Inertia::render('appointments/show', [
                'appointment' => $appointment,
            ]);
        } catch (\Exception $e) {
            Log::error('Error loading appointment', [
                'appointment_id' => $id,
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);

            if ($request->wantsJson()) {
                return response()->json(['error' => 'Agendamento não encontrado'], 404);
            }

            return redirect()->route('appointments.index')
                ->with('error', 'Agendamento não encontrado');
        }
    }

    public function store(CreateAppointmentRequest $request): JsonResponse|RedirectResponse
    {
        try {
            $appointment = $this->service->store(CreateAppointmentDTO::makeFromRequest($request));

            Log::info('Appointment created successfully', [
                'appointment_id' => $appointment->id,
                'user_id' => auth()->id(),
                'customer_id' => $appointment->id_customer,
                'date' => $appointment->date,
            ]);

            if ($request->wantsJson()) {
                return (new AppointmentResource($appointment))
                    ->response()
                    ->setStatusCode(201);
            }

            return redirect()->route('appointments.index')
                ->with('success', 'Agendamento criado com sucesso');
        } catch (\Exception $e) {
            Log::error('Error creating appointment', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
                'request_data' => $request->except(['_token']),
            ]);

            if ($request->wantsJson()) {
                return response()->json(['error' => $e->getMessage()], 422);
            }

            return back()
                ->withInput()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function update(int $id, UpdateAppointmentRequest $request): JsonResponse|RedirectResponse
    {
        try {
            $appointment = $this->service->update($id, UpdateAppointmentDTO::makeFromRequest($request));

            Log::info('Appointment updated successfully', [
                'appointment_id' => $id,
                'user_id' => auth()->id(),
                'updated_fields' => array_keys($request->except(['_token', '_method'])),
            ]);

            if ($request->wantsJson()) {
                return (new AppointmentResource($appointment))
                    ->response()
                    ->setStatusCode(200);
            }

            return redirect()->route('appointments.index')
                ->with('success', 'Agendamento atualizado com sucesso');
        } catch (\Exception $e) {
            Log::error('Error updating appointment', [
                'appointment_id' => $id,
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);

            if ($request->wantsJson()) {
                return response()->json(['error' => $e->getMessage()], 422);
            }

            return back()
                ->withInput()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function destroy(int $id, Request $request): JsonResponse|RedirectResponse
    {
        try {
            $this->service->destroy($id);

            Log::info('Appointment deleted successfully', [
                'appointment_id' => $id,
                'user_id' => auth()->id(),
            ]);

            if ($request->wantsJson()) {
                return response()->json(null, 204);
            }

            return redirect()->route('appointments.index')
                ->with('success', 'Agendamento excluído com sucesso');
        } catch (\Exception $e) {
            Log::error('Error deleting appointment', [
                'appointment_id' => $id,
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);

            if ($request->wantsJson()) {
                return response()->json(['error' => $e->getMessage()], 422);
            }

            return back()->with('error', 'Erro ao excluir agendamento: ' . $e->getMessage());
        }
    }

    /**
     * API endpoint para buscar conflitos de horário
     */
    public function checkConflicts(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'user_id' => 'required|integer|exists:users,id',
            'room_id' => 'required|integer|exists:rooms,id',
            'appointment_id' => 'sometimes|integer|exists:appointments,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $conflicts = $this->service->checkTimeConflicts(
                $request->date,
                $request->start_time,
                $request->end_time,
                $request->user_id,
                $request->room_id,
                $request->appointment_id
            );

            return response()->json([
                'has_conflicts' => count($conflicts) > 0,
                'conflicts' => $conflicts,
            ]);
        } catch (\Exception $e) {
            Log::error('Error checking appointment conflicts', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
                'request_data' => $request->all(),
            ]);

            return response()->json(['error' => 'Erro ao verificar conflitos'], 500);
        }
    }

    /**
     * API endpoint para buscar horários disponíveis
     */
    public function getAvailableSlots(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'date' => 'required|date',
            'user_id' => 'required|integer|exists:users,id',
            'duration' => 'sometimes|integer|min:15|max:480', // em minutos
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $availableSlots = $this->service->getAvailableTimeSlots(
                $request->date,
                $request->user_id,
                $request->get('duration', 60)
            );

            return response()->json(['available_slots' => $availableSlots]);
        } catch (\Exception $e) {
            Log::error('Error getting available slots', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
                'request_data' => $request->all(),
            ]);

            return response()->json(['error' => 'Erro ao buscar horários disponíveis'], 500);
        }
    }

    /**
     * Atualizar status do agendamento via AJAX
     */
    public function updateStatus(int $id, Request $request): RedirectResponse|JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:SCHEDULED,IN_PROGRESS,COMPLETED,CANCELLED',
        ]);

        if ($validator->fails()) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erro de validação',
                    'errors' => $validator->errors()
                ], 422);
            }
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            $appointment = $this->service->updateStatus($id, $request->status);

            Log::info('Appointment status updated', [
                'appointment_id' => $id,
                'new_status' => $request->status,
                'user_id' => auth()->id(),
            ]);

            // API: Retorna JSON
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'data' => [
                        'id' => $appointment->id,
                        'status' => $appointment->status,
                        'date' => $appointment->date,
                        'start_time' => $appointment->start_time,
                        'end_time' => $appointment->end_time,
                        'customer' => [
                            'id' => $appointment->customer->id,
                            'name' => $appointment->customer->name,
                        ],
                        'user' => [
                            'id' => $appointment->user->id,
                            'name' => $appointment->user->name,
                        ],
                    ],
                    'message' => 'Status do agendamento atualizado com sucesso.',
                ], 200);
            }

            // Web: Redireciona com mensagem
            return redirect()->route('appointments.index')
                ->with('success', 'Status atualizado com sucesso');
        } catch (\Exception $e) {
            Log::error('Error updating appointment status', [
                'appointment_id' => $id,
                'status' => $request->status,
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erro ao atualizar status: ' . $e->getMessage(),
                ], 500);
            }

            return redirect()->back()->with('error', 'Erro ao atualizar status');
        }
    }
}
