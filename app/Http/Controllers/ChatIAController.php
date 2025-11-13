<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Customer;
use App\Models\MedicalRecord;
use App\Models\FinancialTransaction;
use App\Models\FinancialAccount;
use App\Models\FinancialCategory;
use App\Models\InventoryProduct;
use App\Models\InventoryMovement;
use App\Models\InventoryCategory;
use App\Services\AICredits\AICreditsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Prism\Prism\Facades\Tool;
use Prism\Prism\Prism;
use Prism\Prism\ValueObjects\Messages\AssistantMessage;
use Prism\Prism\ValueObjects\Messages\UserMessage;

class ChatIAController extends Controller
{
    public function __construct(
        protected AICreditsService $aiCreditsService
    ) {}

    public function streamResponse(Request $request)
    {
        // Validação da entrada
        $validator = Validator::make($request->all(), [
            'mensagem' => 'required|string|max:1000',
            'history' => 'array|max:50',
            'history.*.role' => 'required|string|in:user,assistant',
            'history.*.content' => 'required|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => 'Dados inválidos'], 400);
        }

        $user = Auth::user();
        $company = $user->company;

        if (!$company) {
            return response()->json(['error' => 'Company not found'], 404);
        }

        // Verificar se há créditos suficientes
        $totalCredits = $company->ai_credits + $company->ai_additional_credits;
        if ($totalCredits < 1) {
            return response()->json(['error' => 'Créditos insuficientes'], 402);
        }

        return response()->stream(function () use ($request, $user, $company) {
            try {
                $mensagem = $request->input('mensagem');
                $history = $request->input('history', []);

                Log::info('AI Chat: Starting conversation', [
                    'user_id' => $user->id,
                    'company_id' => $company->id,
                    'message_length' => strlen($mensagem),
                    'history_count' => count($history),
                ]);

                // Constrói o histórico corretamente com objetos de mensagem
                $messages = collect($history)->map(function ($item) {
                    return $item['role'] === 'user'
                        ? new UserMessage($item['content'])
                        : new AssistantMessage($item['content']);
                })->toArray();

                // Adiciona a nova mensagem do usuário ao final
                $messages[] = new UserMessage($mensagem);

                // Ferramentas disponíveis baseadas no role do usuário
                $tools = [];

                // Tools básicas para ADMIN, DOCTOR e RECEPTIONIST (exceto FINANCE)
                if ($user->role && in_array($user->role->type, ['ADMIN', 'DOCTOR', 'RECEPTIONIST'])) {
                    $tools[] = Tool::as('medicalRecordsList')
                        ->for('Acessar prontuários médicos dos pacientes')
                        ->using(function () use ($user, $company) {
                            try {
                                $records = MedicalRecord::with(['customer:id,name,phone,birthdate', 'user:id,name'])
                                    ->where('id_company', $company->id)
                                    ->latest()
                                    ->take(50)
                                    ->get(['id', 'id_customer', 'id_user', 'id_appointment', 'chief_complaint', 'diagnosis', 'treatment_plan', 'prescriptions', 'observations', 'created_at']);

                                return $records->toJson();
                            } catch (\Exception $e) {
                                Log::error("AI Chat: Error in medicalRecordsList tool", [
                                    'error' => $e->getMessage(),
                                ]);
                                return json_encode(['error' => 'Erro ao buscar prontuários', 'message' => $e->getMessage()]);
                            }
                        });

                    $tools[] = Tool::as('appointmentsList')
                        ->for('Consultar agendamentos e consultas do médico')
                        ->using(function () use ($user) {
                            return Appointment::where('id_user', $user->id)
                                ->with(['customer:id,name,phone,birthdate', 'user:id,name', 'room:id,name', 'service:id,name,price'])
                                ->select('id', 'id_customer', 'id_user', 'id_room', 'id_service', 'date', 'start_time', 'end_time', 'status', 'notes', 'created_at')
                                ->latest()
                                ->take(30)
                                ->get()
                                ->toJson();
                        });

                    $tools[] = Tool::as('patientsList')
                        ->for('Ver lista de pacientes cadastrados')
                        ->using(function () use ($company, $user) {
                            return Customer::where('id_company', $company->id)
                                ->select('id', 'name', 'birthdate', 'phone', 'email', 'cpf', 'created_at')
                                ->with(['appointments' => function ($query) use ($user) {
                                    $query->where('id_user', $user->id)
                                        ->select('id', 'id_customer', 'id_user', 'date', 'start_time', 'status', 'notes');
                                }])
                                ->latest()
                                ->take(50)
                                ->get()
                                ->toJson();
                        });
                }

                // Tools financeiras (disponíveis para ADMIN e FINANCE)
                if ($user->role && in_array($user->role->type, ['ADMIN', 'FINANCE'])) {
                    $tools[] = Tool::as('financialTransactionsList')
                        ->for('Consultar transações financeiras, receitas, despesas e transferências')
                        ->using(function () use ($company) {
                            return FinancialTransaction::where('id_company', $company->id)
                                ->with(['account:id,name,type', 'category:id,name', 'customer:id,name'])
                                ->select('id', 'type', 'amount', 'description', 'transaction_date', 'due_date', 'status', 'payment_method', 'id_financial_account', 'id_financial_category', 'id_customer', 'created_at')
                                ->latest()
                                ->take(50)
                                ->get()
                                ->toJson();
                        });

                    $tools[] = Tool::as('financialAccountsList')
                        ->for('Ver contas financeiras e saldos')
                        ->using(function () use ($company) {
                            return FinancialAccount::where('id_company', $company->id)
                                ->select('id', 'name', 'type', 'bank_name', 'account_number', 'current_balance', 'is_active')
                                ->limit(100) // Limite de segurança
                                ->get()
                                ->toJson();
                        });

                    $tools[] = Tool::as('financialCategoriesList')
                        ->for('Consultar categorias financeiras para receitas e despesas')
                        ->using(function () use ($company) {
                            return FinancialCategory::where('id_company', $company->id)
                                ->select('id', 'name', 'type', 'description', 'is_active')
                                ->limit(100) // Limite de segurança
                                ->get()
                                ->toJson();
                        });

                    $tools[] = Tool::as('financialSummary')
                        ->for('Obter resumo financeiro com receitas, despesas e saldo')
                        ->using(function () use ($company) {
                            try {
                                $currentMonth = now()->format('Y-m');
                                $monthStart = now()->startOfMonth()->format('Y-m-d');
                                $monthEnd = now()->endOfMonth()->format('Y-m-d');

                                // Otimizado: usar BETWEEN em vez de DATE_FORMAT + LIMIT para segurança
                                $totalIncome = FinancialTransaction::where('id_company', $company->id)
                                    ->where('type', 'INCOME')
                                    ->where('status', 'PAID')
                                    ->whereBetween('transaction_date', [$monthStart, $monthEnd])
                                    ->limit(1000) // Limite de segurança
                                    ->sum('amount');

                                $totalExpense = FinancialTransaction::where('id_company', $company->id)
                                    ->where('type', 'EXPENSE')
                                    ->where('status', 'PAID')
                                    ->whereBetween('transaction_date', [$monthStart, $monthEnd])
                                    ->limit(1000) // Limite de segurança
                                    ->sum('amount');

                                // Limitado a transações recentes apenas
                                $pendingIncome = FinancialTransaction::where('id_company', $company->id)
                                    ->where('type', 'INCOME')
                                    ->where('status', 'PENDING')
                                    ->where('created_at', '>=', now()->subDays(30)) // Apenas últimos 30 dias
                                    ->limit(500) // Limite de segurança
                                    ->sum('amount');

                                $pendingExpense = FinancialTransaction::where('id_company', $company->id)
                                    ->where('type', 'EXPENSE')
                                    ->where('status', 'PENDING')
                                    ->where('created_at', '>=', now()->subDays(30)) // Apenas últimos 30 dias
                                    ->limit(500) // Limite de segurança
                                    ->sum('amount');

                                $totalAccountsBalance = FinancialAccount::where('id_company', $company->id)
                                    ->where('is_active', true)
                                    ->limit(50) // Limite de segurança
                                    ->sum('current_balance');

                                return json_encode([
                                    'current_month' => $currentMonth,
                                    'income_paid' => $totalIncome,
                                    'expense_paid' => $totalExpense,
                                    'net_profit' => $totalIncome - $totalExpense,
                                    'pending_income' => $pendingIncome,
                                    'pending_expense' => $pendingExpense,
                                    'total_accounts_balance' => $totalAccountsBalance,
                                ]);
                            } catch (\Exception $e) {
                                Log::error("AI Chat: Error in financialSummary tool", [
                                    'error' => $e->getMessage()
                                ]);
                                return json_encode(['error' => 'Erro ao calcular resumo financeiro']);
                            }
                        });
                }

                // Tools de controle de estoque (disponíveis para ADMIN e FINANCE)
                if ($user->role && in_array($user->role->type, ['ADMIN', 'FINANCE'])) {
                    $tools[] = Tool::as('inventoryProductsList')
                        ->for('Consultar produtos em estoque, quantidades e alertas')
                        ->using(function () use ($company) {
                            return InventoryProduct::where('id_company', $company->id)
                                ->with(['category:id,name', 'supplier:id,name'])
                                ->select('id', 'name', 'code', 'current_stock', 'minimum_stock', 'maximum_stock', 'cost_price', 'sale_price', 'expiry_date', 'id_category', 'id_supplier', 'active')
                                ->latest()
                                ->take(100)
                                ->get()
                                ->toJson();
                        });

                    $tools[] = Tool::as('inventoryMovementsList')
                        ->for('Ver movimentações de estoque (entradas, saídas, ajustes)')
                        ->using(function () use ($company) {
                            return InventoryMovement::where('id_company', $company->id)
                                ->with(['product:id,name', 'user:id,name'])
                                ->select('id', 'id_product', 'id_user', 'type', 'quantity', 'unit_cost', 'total_cost', 'stock_before', 'stock_after', 'reason', 'movement_date', 'created_at')
                                ->latest()
                                ->take(50)
                                ->get()
                                ->toJson();
                        });

                    $tools[] = Tool::as('inventoryAlerts')
                        ->for('Obter alertas de estoque baixo e produtos vencendo')
                        ->using(function () use ($company) {
                            try {
                                // CRÍTICO: Adicionado limites para evitar sobrecarga
                                $lowStockProducts = InventoryProduct::where('id_company', $company->id)
                                    ->where('active', true)
                                    ->lowStock()
                                    ->with(['category:id,name'])
                                    ->select('id', 'name', 'current_stock', 'minimum_stock', 'id_category')
                                    ->limit(50) // Limite de segurança
                                    ->get();

                                $expiringSoonProducts = InventoryProduct::where('id_company', $company->id)
                                    ->where('active', true)
                                    ->expiringSoon(30)
                                    ->with(['category:id,name'])
                                    ->select('id', 'name', 'current_stock', 'expiry_date', 'id_category')
                                    ->limit(50) // Limite de segurança
                                    ->get();

                                $expiredProducts = InventoryProduct::where('id_company', $company->id)
                                    ->where('active', true)
                                    ->expired()
                                    ->with(['category:id,name'])
                                    ->select('id', 'name', 'current_stock', 'expiry_date', 'id_category')
                                    ->limit(50) // Limite de segurança
                                    ->get();

                                return json_encode([
                                    'low_stock_products' => $lowStockProducts,
                                    'expiring_soon_products' => $expiringSoonProducts,
                                    'expired_products' => $expiredProducts,
                                    'total_alerts' => $lowStockProducts->count() + $expiringSoonProducts->count() + $expiredProducts->count(),
                                    'note' => 'Limitado aos primeiros 50 produtos por categoria de alerta'
                                ]);
                            } catch (\Exception $e) {
                                Log::error("AI Chat: Error in inventoryAlerts tool", [
                                    'error' => $e->getMessage()
                                ]);
                                return json_encode(['error' => 'Erro ao buscar alertas de estoque']);
                            }
                        });

                    $tools[] = Tool::as('inventoryCategoriesList')
                        ->for('Consultar categorias de produtos do estoque')
                        ->using(function () use ($company) {
                            return InventoryCategory::where('id_company', $company->id)
                                ->select('id', 'name', 'description', 'is_active')
                                ->limit(50) // Limite de segurança
                                ->get()
                                ->toJson();
                        });
                }

                // Gerar resposta da IA
                $response = Prism::text()
                    ->using('openai', 'gpt-4o-mini')
                    ->withSystemPrompt(view('prompts.chat-assistente', [
                        'user' => $user
                    ])->render())
                    ->withMessages($messages)
                    ->withTools($tools)
                    ->withMaxSteps(4)
                    ->asStream();

                // Enviar resposta para o navegador em tempo real
                ob_start();
                $responseCompleted = false;

                try {
                    foreach ($response as $chunk) {
                        echo $chunk->text;
                        ob_flush();
                        flush();
                    }
                    $responseCompleted = true;
                } catch (\Exception $streamException) {
                    Log::error('AI Chat: Error during streaming', [
                        'user_id' => $user->id,
                        'company_id' => $company->id,
                        'error' => $streamException->getMessage(),
                    ]);
                    throw $streamException;
                }

                // Consumir 1 crédito apenas após resposta ser enviada com sucesso
                if ($responseCompleted) {
                    $this->aiCreditsService->consumeCredits($company, 1);

                    Log::info('AI Chat: Conversation completed successfully', [
                        'user_id' => $user->id,
                        'company_id' => $company->id,
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('AI Chat: Error processing request', [
                    'user_id' => $user->id,
                    'company_id' => $company->id,
                    'error' => $e->getMessage(),
                ]);

                echo "Desculpe, ocorreu um erro interno. Tente novamente.";
                ob_flush();
                flush();
            }
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'X-Accel-Buffering' => 'no',
        ]);
    }
}
