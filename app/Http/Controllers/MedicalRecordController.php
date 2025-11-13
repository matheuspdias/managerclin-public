<?php

namespace App\Http\Controllers;

use App\DTO\MedicalRecord\CreateMedicalRecordDTO;
use App\DTO\MedicalRecord\UpdateMedicalRecordDTO;
use App\Http\Controllers\Traits\Swagger\SwaggerMedicalRecordDocs;
use App\Http\Requests\MedicalRecord\CreateMedicalRecordRequest;
use App\Http\Requests\MedicalRecord\UpdateMedicalRecordRequest;
use App\Http\Resources\MedicalRecord\MedicalRecordResource;
use App\Models\Appointment;
use App\Models\Customer;
use App\Models\MedicalRecord;
use App\Models\User;
use App\Services\MedicalRecord\MedicalRecordService;
use App\Services\Customer\CustomerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class MedicalRecordController extends Controller
{
    use SwaggerMedicalRecordDocs;
    public function __construct(
        protected MedicalRecordService $service,
        protected CustomerService $customerService,
    ) {}

    /**
     * Display medical records overview
     */
    public function index(Request $request): Response|JsonResponse
    {
        $user = Auth::user();
        $user->load('role');

        // Only doctors and admins can access medical records
        if (!$user->isDoctor() && !$user->isAdmin()) {
            if ($request->wantsJson()) {
                return response()->json(['error' => 'Acesso negado. Apenas médicos podem acessar prontuários.'], 403);
            }
            abort(403, 'Acesso negado. Apenas médicos podem acessar prontuários.');
        }

        $filters = $request->only(['search', 'status', 'date_from', 'date_to']);

        $query = MedicalRecord::with(['customer', 'user', 'appointment'])
            ->latest();

        // Apply search filter
        if ($filters['search'] ?? false) {
            $search = $filters['search'];
            $query->whereHas('customer', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Apply date filters
        if ($filters['date_from'] ?? false) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }
        if ($filters['date_to'] ?? false) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        $medicalRecords = $query->paginate(15)->withQueryString();

        if ($request->wantsJson()) {
            return response()->json([
                'data' => MedicalRecordResource::collection($medicalRecords->items()),
                'meta' => [
                    'current_page' => $medicalRecords->currentPage(),
                    'last_page' => $medicalRecords->lastPage(),
                    'per_page' => $medicalRecords->perPage(),
                    'total' => $medicalRecords->total(),
                ],
            ]);
        }

        // Get customers for the patient selector using CustomerService
        // Limit to 100 most recent customers to avoid loading too much data
        $customers = $this->customerService->getAll()
            ->sortByDesc('id')
            ->take(100)
            ->map(fn($customer) => [
                'id' => $customer->id,
                'name' => $customer->name,
                'email' => $customer->email,
                'phone' => $customer->phone,
            ])
            ->sortBy('name')
            ->values();

        return Inertia::render('medicalRecords/index', [
            'medicalRecords' => $medicalRecords,
            'filters' => $filters,
            'customers' => $customers,
            'stats' => [
                'total' => MedicalRecord::count(),
                'this_month' => MedicalRecord::whereMonth('created_at', now()->month)->count(),
                'today' => MedicalRecord::whereDate('created_at', now())->count(),
            ]
        ]);
    }

    /**
     * Show a specific medical record by ID (API endpoint)
     */
    public function showById(int $id, Request $request): JsonResponse
    {
        $user = Auth::user();
        $user->load('role');

        // Only doctors and admins can view medical records
        if (!$user->isDoctor() && !$user->isAdmin()) {
            return response()->json(['error' => 'Acesso negado. Apenas médicos podem visualizar prontuários.'], 403);
        }

        $medicalRecord = MedicalRecord::with(['user', 'customer', 'appointment'])
            ->find($id);

        if (!$medicalRecord) {
            return response()->json(['error' => 'Prontuário não encontrado.'], 404);
        }

        return (new MedicalRecordResource($medicalRecord))
            ->response()
            ->setStatusCode(200);
    }

    /**
     * Show detailed medical records for a specific patient
     */
    public function show(Customer $customer, Request $request): Response|JsonResponse
    {
        $user = Auth::user();
        $user->load('role');

        // Only doctors and admins can view medical records
        if (!$user->isDoctor() && !$user->isAdmin()) {
            if ($request->wantsJson()) {
                return response()->json(['error' => 'Acesso negado. Apenas médicos podem visualizar prontuários.'], 403);
            }
            abort(403, 'Acesso negado. Apenas médicos podem visualizar prontuários.');
        }

        // Load patient medical records
        $medicalRecords = MedicalRecord::with(['appointment', 'user'])
            ->where('id_customer', $customer->id)
            ->latest()
            ->paginate(10);

        if ($request->wantsJson()) {
            return response()->json([
                'customer' => $customer,
                'data' => MedicalRecordResource::collection($medicalRecords->items()),
                'meta' => [
                    'current_page' => $medicalRecords->currentPage(),
                    'last_page' => $medicalRecords->lastPage(),
                    'per_page' => $medicalRecords->perPage(),
                    'total' => $medicalRecords->total(),
                ],
            ]);
        }

        $customer->load(['medicalRecords']);

        return Inertia::render('medicalRecords/patient', [
            'customer' => $customer,
            'medicalRecords' => $medicalRecords,
        ]);
    }

    /**
     * Show form to create new medical record entry
     */
    public function create(Customer $customer, Request $request): Response|RedirectResponse
    {
        $user = Auth::user();
        $user->load('role');

        // Only doctors can create medical records
        if (!$user->isDoctor() && !$user->isAdmin()) {
            abort(403, 'Acesso negado. Apenas médicos podem criar entradas de prontuário.');
        }

        if (empty($user->crm)) {
            return redirect()->back()->with('error', 'É necessário cadastrar o CRM para criar entradas de prontuário.');
        }

        // Get customer appointments for selection
        $appointments = $customer->appointments()
            ->with(['user', 'service', 'room'])
            ->orderBy('date', 'desc')
            ->get();

        return Inertia::render('medicalRecords/create', [
            'customer' => $customer,
            'appointments' => $appointments,
            'preselectedAppointment' => $request->query('appointment'),
        ]);
    }

    /**
     * Store new medical record entry
     */
    public function store(CreateMedicalRecordRequest $request): JsonResponse|RedirectResponse
    {
        $user = Auth::user();
        $user->load('role');

        // Only doctors can create medical records
        if (!$user->isDoctor() && !$user->isAdmin()) {
            if ($request->wantsJson()) {
                return response()->json(['error' => 'Apenas médicos podem criar entradas de prontuário.'], 403);
            }
            return back()->withErrors(['authorization' => 'Apenas médicos podem criar entradas de prontuário.']);
        }

        if (empty($user->crm)) {
            if ($request->wantsJson()) {
                return response()->json(['error' => 'É necessário cadastrar o CRM para criar entradas de prontuário.'], 422);
            }
            return back()->with('error', 'É necessário cadastrar o CRM para criar entradas de prontuário.');
        }

        $medicalRecord = $this->service->store(CreateMedicalRecordDTO::makeFromRequest($request));

        if ($request->wantsJson()) {
            // Carrega relacionamentos para a resposta da API
            $medicalRecord->load(['user', 'customer', 'appointment']);

            return (new MedicalRecordResource($medicalRecord))
                ->response()
                ->setStatusCode(201);
        }

        return redirect()->route('medicalRecords.show', $request->id_customer)
            ->with('success', 'Entrada de prontuário criada com sucesso.');
    }

    /**
     * Show form to edit medical record entry
     * Redirects to patients page since medical records are managed there
     */
    public function edit(MedicalRecord $medicalRecord): Response
    {
        $user = Auth::user();
        $user->load('role');

        // Only the original doctor or admin can edit
        if (!$user->isAdmin() && $medicalRecord->id_user !== $user->id) {
            abort(403, 'Apenas o médico responsável ou administrador pode editar esta entrada.');
        }

        // Load relationships
        $medicalRecord->load(['customer', 'appointment', 'user']);

        // Get customer
        $customer = $medicalRecord->customer;

        // Get all appointments for this customer (for the appointment selector)
        $appointments = Appointment::with(['user', 'service', 'room'])
            ->where('id_customer', $customer->id)
            ->orderBy('date', 'desc')
            ->orderBy('start_time', 'desc')
            ->get();

        return Inertia::render('medicalRecords/edit', [
            'customer' => $customer,
            'appointments' => $appointments,
            'medicalRecord' => $medicalRecord,
        ]);
    }

    /**
     * Update medical record entry
     */
    public function update(UpdateMedicalRecordRequest $request, MedicalRecord $medicalRecord): JsonResponse|RedirectResponse
    {
        $user = Auth::user();
        $user->load('role');

        // Only the original doctor or admin can edit
        if (!$user->isAdmin() && $medicalRecord->id_user !== $user->id) {
            if ($request->wantsJson()) {
                return response()->json(['error' => 'Apenas o médico responsável ou administrador pode editar esta entrada.'], 403);
            }
            return back()->withErrors(['authorization' => 'Apenas o médico responsável ou administrador pode editar esta entrada.']);
        }

        $updatedRecord = $this->service->update($medicalRecord->id, UpdateMedicalRecordDTO::makeFromRequest($request));

        if ($request->wantsJson()) {
            // Carrega relacionamentos para a resposta da API
            $updatedRecord->load(['user', 'customer', 'appointment']);

            return (new MedicalRecordResource($updatedRecord))
                ->response()
                ->setStatusCode(200);
        }

        // Redirect to patient's medical records page
        return redirect()->route('medicalRecords.show', ['customer' => $medicalRecord->id_customer])
            ->with('success', 'Prontuário atualizado com sucesso.');
    }

    /**
     * Remove medical record entry
     */
    public function destroy(MedicalRecord $medicalRecord, Request $request): JsonResponse|RedirectResponse
    {
        $user = Auth::user();
        $user->load('role');

        // Only the original doctor or admin can delete
        if (!$user->isAdmin() && $medicalRecord->id_user !== $user->id) {
            if ($request->wantsJson()) {
                return response()->json(['error' => 'Apenas o médico responsável ou administrador pode excluir esta entrada.'], 403);
            }
            return back()->withErrors(['authorization' => 'Apenas o médico responsável ou administrador pode excluir esta entrada.']);
        }

        $this->service->destroy($medicalRecord->id);

        if ($request->wantsJson()) {
            return response()->json(null, 204);
        }

        return redirect()->route('patients.index')
            ->with('success', 'Entrada de prontuário removida com sucesso.');
    }

    /**
     * Get all medical records for a specific customer
     */
    public function getByCustomer(int $customerId, Request $request): JsonResponse
    {
        $user = Auth::user();
        $user->load('role');

        // Only doctors and admins can access medical records
        if (!$user->isDoctor() && !$user->isAdmin()) {
            return response()->json(['error' => 'Acesso negado. Apenas médicos podem acessar prontuários.'], 403);
        }

        $page = (int) $request->get('page', 1);
        $perPage = (int) $request->get('per_page', 15);

        $query = MedicalRecord::with(['user', 'appointment'])
            ->where('id_customer', $customerId)
            ->latest('created_at');

        $medicalRecords = $query->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'data' => MedicalRecordResource::collection($medicalRecords->items()),
            'meta' => [
                'current_page' => $medicalRecords->currentPage(),
                'last_page' => $medicalRecords->lastPage(),
                'per_page' => $medicalRecords->perPage(),
                'total' => $medicalRecords->total(),
            ],
        ]);
    }

    /**
     * Get patient summary for quick access
     */
    public function summary(Customer $customer)
    {
        $user = Auth::user();
        $user->load('role');

        if (!$user->isDoctor() && !$user->isAdmin()) {
            abort(403);
        }

        $customer->load([
            'medicalRecords' => function ($query) {
                $query->latest()->limit(5);
            }
        ]);

        return response()->json([
            'patient' => $customer,
            'recent_records' => $customer->medicalRecords,
            'total_records' => $customer->medicalRecords()->count(),
        ]);
    }
}
