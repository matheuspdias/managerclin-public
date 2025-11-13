<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Traits\Swagger\SwaggerMarketingCampaignDocs;
use App\Http\Resources\Marketing\MarketingCampaignResource;
use App\Services\Marketing\MarketingCampaignService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class MarketingCampaignController extends Controller
{
    use SwaggerMarketingCampaignDocs;
    public function __construct(
        protected MarketingCampaignService $campaignService
    ) {}

    /**
     * Display listing of campaigns
     */
    public function index(Request $request): \Inertia\Response|JsonResponse
    {
        $search = $request->get('search');
        $page = $request->get('page', 1);
        $perPage = $request->get('per_page', 15);
        $order = $request->get('order');

        $campaigns = $this->campaignService->paginateCampaigns($search, $page, $perPage, $order);

        if ($request->wantsJson()) {
            return response()->json([
                'data' => MarketingCampaignResource::collection($campaigns->items()),
                'meta' => [
                    'current_page' => $campaigns->currentPage(),
                    'last_page' => $campaigns->lastPage(),
                    'per_page' => $campaigns->perPage(),
                    'total' => $campaigns->total(),
                ],
            ]);
        }

        return Inertia::render('marketing/campaigns/index', [
            'campaigns' => $campaigns,
            'filters' => [
                'search' => $search,
                'order' => $order,
            ],
        ]);
    }

    /**
     * Store a new campaign
     */
    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'message' => 'required|string',
            'target_audience' => 'required|in:all,with_appointments,without_appointments,custom',
            'target_filters' => 'nullable|array',
            'scheduled_at' => 'nullable|date',
            'media_type' => 'nullable|in:image,video,document,audio',
            'media_url' => 'nullable|string',
            'media_filename' => 'nullable|string',
            'media_file' => 'nullable|file|max:20480', // Max 20MB
        ]);

        $validated['status'] = $request->has('scheduled_at') ? 'scheduled' : 'draft';

        // Handle file upload
        if ($request->hasFile('media_file')) {
            $file = $request->file('media_file');
            $companyId = Auth::user()->id_company;

            // Criar diretório se não existir
            $directory = "marketing/{$companyId}";

            // Gerar nome único para o arquivo
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();

            // Armazenar arquivo
            $path = $file->storeAs($directory, $filename, 'public');

            // Salvar caminho local e gerar URL pública
            $validated['local_media_path'] = $path;
            $validated['media_url'] = asset('storage/' . $path);

            // Se não tiver filename definido, usa o nome original
            if (empty($validated['media_filename'])) {
                $validated['media_filename'] = $file->getClientOriginalName();
            }
        }

        try {
            $campaign = $this->campaignService->createCampaign($validated);

            if ($request->wantsJson()) {
                return (new MarketingCampaignResource($campaign))
                    ->response()
                    ->setStatusCode(201);
            }

            return redirect()->back()->with('success', 'Campanha criada com sucesso!');
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json(['error' => $e->getMessage()], 422);
            }
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Update an existing campaign
     */
    public function update(Request $request, int $id): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'message' => 'sometimes|required|string',
            'target_audience' => 'sometimes|required|in:all,with_appointments,without_appointments,custom',
            'target_filters' => 'nullable|array',
            'scheduled_at' => 'nullable|date',
            'media_type' => 'nullable|in:image,video,document,audio',
            'media_url' => 'nullable|string',
            'media_filename' => 'nullable|string',
            'media_file' => 'nullable|file|max:20480', // Max 20MB
        ]);

        // Handle file upload
        if ($request->hasFile('media_file')) {
            $file = $request->file('media_file');
            $companyId = Auth::user()->id_company;

            // Criar diretório se não existir
            $directory = "marketing/{$companyId}";

            // Gerar nome único para o arquivo
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();

            // Armazenar arquivo
            $path = $file->storeAs($directory, $filename, 'public');

            // Deletar arquivo antigo se existir
            $campaign = $this->campaignService->getCampaignById($id);
            if ($campaign && $campaign->local_media_path) {
                Storage::disk('public')->delete($campaign->local_media_path);
            }

            // Salvar caminho local e gerar URL pública
            $validated['local_media_path'] = $path;
            $validated['media_url'] = asset('storage/' . $path);

            // Se não tiver filename definido, usa o nome original
            if (empty($validated['media_filename'])) {
                $validated['media_filename'] = $file->getClientOriginalName();
            }
        }

        try {
            $campaign = $this->campaignService->updateCampaign($id, $validated);

            if ($request->wantsJson()) {
                return (new MarketingCampaignResource($campaign))
                    ->response()
                    ->setStatusCode(200);
            }

            return redirect()->back()->with('success', 'Campanha atualizada com sucesso!');
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json(['error' => $e->getMessage()], 422);
            }
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Delete a campaign
     */
    public function destroy(int $id, Request $request): JsonResponse|RedirectResponse
    {
        try {
            $this->campaignService->deleteCampaign($id);

            if ($request->wantsJson()) {
                return response()->json(null, 204);
            }

            return redirect()->back()->with('success', 'Campanha excluída com sucesso!');
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json(['error' => $e->getMessage()], 422);
            }
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Schedule a campaign
     */
    public function schedule(Request $request, int $id)
    {
        $validated = $request->validate([
            'scheduled_at' => 'required|date',
        ]);

        try {
            $campaign = $this->campaignService->scheduleCampaign($id, $validated['scheduled_at']);

            return redirect()->back()->with('success', 'Campanha agendada com sucesso!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Cancel a scheduled campaign
     */
    public function cancel(int $id)
    {
        try {
            $campaign = $this->campaignService->cancelCampaign($id);

            return redirect()->back()->with('success', 'Campanha cancelada com sucesso!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Send a campaign immediately
     */
    public function sendNow(int $id)
    {
        try {
            $campaign = $this->campaignService->sendNow($id);

            return redirect()->back()->with('success', 'Campanha enviada para processamento!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * View campaign details
     */
    public function show(int $id, Request $request): \Inertia\Response|JsonResponse
    {
        $campaign = $this->campaignService->getCampaignById($id);

        if (!$campaign) {
            if ($request->wantsJson()) {
                return response()->json(['error' => 'Campanha não encontrada.'], 404);
            }
            return redirect()->route('marketing.campaigns.index')
                ->with('error', 'Campanha não encontrada.');
        }

        if ($request->wantsJson()) {
            $campaign->load(['recipients.customer', 'createdBy']);
            return (new MarketingCampaignResource($campaign))
                ->response()
                ->setStatusCode(200);
        }

        // Load recipients with customer data and created by user
        $campaign->load(['recipients.customer', 'createdBy']);

        return Inertia::render('marketing/campaigns/show', [
            'campaign' => $campaign,
            'statistics' => [
                'success_rate' => $campaign->getSuccessRate(),
                'pending_count' => $campaign->recipients()->where('status', 'pending')->count(),
                'sent_count' => $campaign->sent_count,
                'failed_count' => $campaign->failed_count,
            ],
        ]);
    }
}
