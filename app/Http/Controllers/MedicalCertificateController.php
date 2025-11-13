<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Traits\Swagger\SwaggerMedicalCertificateDocs;
use App\Http\Resources\MedicalCertificate\MedicalCertificateResource;
use App\Models\Customer;
use App\Models\MedicalCertificate;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Response;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class MedicalCertificateController extends Controller
{
    use SwaggerMedicalCertificateDocs;

    public function index(Request $request): Response|JsonResponse
    {
        // Obter filtros com valores padrão
        $search = $request->input('search', '');
        $customerFilter = $request->input('customer', 'all');
        $statusFilter = $request->input('status', 'all');

        // Preparar filtros para retornar ao frontend
        $filters = [
            'search' => $search,
            'customer' => $customerFilter,
            'status' => $statusFilter
        ];

        $query = MedicalCertificate::with(['user', 'customer'])
            ->when(!empty($search), function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('content', 'like', "%{$search}%")
                        ->orWhere('validation_hash', 'like', "%{$search}%")
                        ->orWhereHas('customer', function ($q) use ($search) {
                            $q->where('name', 'like', "%{$search}%");
                        })
                        ->orWhereHas('user', function ($q) use ($search) {
                            $q->where('name', 'like', "%{$search}%");
                        });
                });
            })
            ->when($customerFilter !== 'all', function ($query) use ($customerFilter) {
                $query->where('id_customer', $customerFilter);
            })
            ->when($statusFilter !== 'all', function ($query) use ($statusFilter) {
                if ($statusFilter === 'valid') {
                    $query->where('valid_until', '>=', now());
                } elseif ($statusFilter === 'expired') {
                    $query->where('valid_until', '<', now());
                }
            })
            ->orderBy('created_at', 'desc');

        $certificates = $query->paginate(15)->withQueryString();

        if ($request->wantsJson()) {
            return response()->json([
                'data' => MedicalCertificateResource::collection($certificates->items()),
                'meta' => [
                    'current_page' => $certificates->currentPage(),
                    'last_page' => $certificates->lastPage(),
                    'per_page' => $certificates->perPage(),
                    'total' => $certificates->total(),
                ],
            ]);
        }

        $customers = Customer::orderBy('name')
            ->get(['id', 'name']);

        return Inertia::render('medicalCertificates/index', [
            'certificates' => $certificates,
            'customers' => $customers,
            'filters' => $filters
        ]);
    }

    public function create()
    {
        $user = Auth::user();
        $user->load('role');

        // Verificar se pode emitir atestados
        if (!$user->isDoctor() && !$user->isAdmin()) {
            return redirect()->route('medical-certificates.index')
                ->withErrors(['authorization' => 'Apenas médicos ou administradores podem acessar esta funcionalidade.']);
        }

        $customers = Customer::orderBy('name')
            ->get(['id', 'name', 'email']);

        return Inertia::render('medicalCertificates/create', [
            'customers' => $customers,
            'doctor' => $user,
            'canIssue' => !empty($user->crm),
            'userRole' => $user->isAdmin() ? 'admin' : 'doctor'
        ]);
    }

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $request->validate([
            'id_customer' => 'required|exists:customers,id',
            'content' => 'required|string|max:1000',
            'days_off' => 'required|integer|min:0|max:365',
            'valid_until' => 'required|date|after:today|before:' . now()->addYear()->format('Y-m-d')
        ]);

        $user = Auth::user();
        $user->load('role');

        // Verificar se o usuário é médico ou admin (que pode ter função médica) e tem CRM
        if (!$user->isDoctor() && !$user->isAdmin()) {
            if ($request->wantsJson()) {
                return response()->json(['error' => 'Apenas médicos ou administradores com CRM podem emitir atestados médicos.'], 403);
            }
            return back()->withErrors(['authorization' => 'Apenas médicos ou administradores com CRM podem emitir atestados médicos.']);
        }

        if (empty($user->crm)) {
            $role = $user->isAdmin() ? 'administrador' : 'médico';
            $errorMessage = "É necessário cadastrar o CRM no perfil para que {$role}s possam emitir atestados médicos.";
            if ($request->wantsJson()) {
                return response()->json(['error' => $errorMessage], 422);
            }
            return back()->withErrors(['crm' => $errorMessage]);
        }

        $certificate = MedicalCertificate::create([
            'id_user' => $user->id,
            'id_customer' => $request->id_customer,
            'content' => $request->content,
            'days_off' => $request->days_off,
            'issue_date' => now(),
            'valid_until' => $request->valid_until,
            'digital_signature' => $this->generateDigitalSignature($user)
        ]);

        // Gerar hash de validação após criar o registro (para ter o ID)
        $certificate->update([
            'validation_hash' => $this->generateValidationHash($certificate)
        ]);

        if ($request->wantsJson()) {
            // Carrega relacionamentos para a resposta da API
            $certificate->load(['user', 'customer']);

            return (new MedicalCertificateResource($certificate))
                ->response()
                ->setStatusCode(201);
        }

        return redirect()->route('medical-certificates.show', $certificate->id)
            ->with('success', 'Atestado médico gerado com sucesso.');
    }

    public function show(MedicalCertificate $medicalCertificate, Request $request): Response|JsonResponse
    {
        $medicalCertificate->load(['user', 'customer']);

        if ($request->wantsJson()) {
            return (new MedicalCertificateResource($medicalCertificate))
                ->response()
                ->setStatusCode(200);
        }

        // Gerar QR Code para verificação pública
        $verificationUrl = route('medical-certificates.verify', $medicalCertificate->validation_hash);
        $qrCode = base64_encode(QrCode::format('png')->size(150)->generate($verificationUrl));

        return Inertia::render('medicalCertificates/show', [
            'certificate' => $medicalCertificate,
            'qrCode' => $qrCode,
            'verificationUrl' => $verificationUrl
        ]);
    }

    public function download(MedicalCertificate $medicalCertificate)
    {
        $medicalCertificate->load(['user', 'customer', 'user.company']);

        // Gerar QR Code para verificação
        $verificationUrl = route('medical-certificates.verify', $medicalCertificate->validation_hash);
        $qrCode = base64_encode(QrCode::format('png')->size(120)->generate($verificationUrl));

        $data = [
            'certificate' => $medicalCertificate,
            'qrCode' => $qrCode,
            'verificationUrl' => $verificationUrl,
            'issueDate' => now()->format('d/m/Y \à\s H:i'),
        ];

        $pdf = PDF::loadView('medical-certificates.pdf', $data)
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'margin-top' => 15,
                'margin-right' => 15,
                'margin-bottom' => 15,
                'margin-left' => 15,
                'font-size' => 12,
                'default-font' => 'DejaVu Sans'
            ]);

        // Sanitizar nome do cliente para filename seguro
        $customerName = preg_replace('/[^A-Za-z0-9\-_]/', '', str_replace(' ', '-', $medicalCertificate->customer->name));
        $filename = "atestado-medico-{$customerName}-{$medicalCertificate->id}.pdf";

        return $pdf->download($filename);
    }



    public function destroy(MedicalCertificate $medicalCertificate, Request $request): JsonResponse|RedirectResponse
    {
        $medicalCertificate->delete();

        if ($request->wantsJson()) {
            return response()->json(null, 204);
        }

        return redirect()->route('medical-certificates.index')
            ->with('success', 'Atestado excluído com sucesso.');
    }

    public function verify($hash, Request $request): Response|JsonResponse
    {
        $certificate = MedicalCertificate::where('validation_hash', $hash)
            ->with(['user', 'customer'])
            ->first();

        // Caso 1: Hash não encontrado
        if (!$certificate) {
            if ($request->wantsJson()) {
                return response()->json([
                    'certificate' => null,
                    'isValid' => false,
                    'isExpired' => false,
                    'error' => 'Atestado não encontrado no sistema.'
                ], 404);
            }
            return Inertia::render('medicalCertificates/verify', [
                'certificate' => null,
                'isValid' => false,
                'isExpired' => false,
                'error' => 'Atestado não encontrado no sistema.'
            ]);
        }

        // Caso 2: Hash encontrado mas inválido (dados adulterados)
        $expectedHash = $this->generateValidationHash($certificate);
        $isValid = hash_equals($expectedHash, $certificate->validation_hash);
        $isExpired = $certificate->valid_until < now();

        if ($request->wantsJson()) {
            return response()->json([
                'certificate' => new MedicalCertificateResource($certificate),
                'isValid' => $isValid,
                'isExpired' => $isExpired,
                'verificationDate' => now()->toIso8601String()
            ]);
        }

        return Inertia::render('medicalCertificates/verify', [
            'certificate' => $certificate,
            'isValid' => $isValid,
            'isExpired' => $isExpired,
            'verificationDate' => now()
        ]);
    }

    private function generateValidationHash(MedicalCertificate $certificate)
    {
        // Dados que compõem o hash único
        $hashData = [
            $certificate->id,
            $certificate->id_user,
            $certificate->id_customer,
            $certificate->issue_date,
            $certificate->content,
            config('app.key') // Chave secreta do Laravel
        ];

        return hash('sha256', implode('|', $hashData));
    }

    private function generateDigitalSignature($doctor)
    {
        // Assinatura baseada nos dados do médico + timestamp
        $signatureData = [
            $doctor->id,
            $doctor->name,
            $doctor->crm ?? 'SEM_CRM',
            now()->format('Y-m-d H:i:s'),
            config('app.key') // Chave secreta do Laravel
        ];

        return hash('sha256', implode('|', $signatureData));
    }
}
