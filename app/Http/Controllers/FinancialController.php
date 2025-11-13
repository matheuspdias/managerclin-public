<?php

namespace App\Http\Controllers;

use App\Services\Financial\FinancialService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class FinancialController extends Controller
{
    public function __construct(
        protected FinancialService $financialService
    ) {}

    /**
     * Dashboard financeiro principal
     */
    public function index(Request $request): \Inertia\Response
    {
        $dashboardData = $this->financialService->getDashboardData();

        return Inertia::render('financial/index', [
            'dashboardData' => $dashboardData,
        ]);
    }

    /**
     * Relatórios financeiros
     */
    public function reports(Request $request)
    {
        $startDate = $request->get('start_date', now()->subMonths(11)->format('Y-m-01'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));
        $period = $request->get('period', 'month');
        $export = $request->get('export');

        $summary = $this->financialService->getFinancialSummary($startDate, $endDate);
        $cashFlow = $this->financialService->getCashFlowData($startDate, $endDate, $period);
        $balanceSummary = $this->financialService->getBalanceSummary();

        // Se for uma requisição de export, retorna o arquivo
        if ($export) {
            return $this->exportReport($export, $summary, $cashFlow, $balanceSummary, $startDate, $endDate);
        }

        return Inertia::render('financial/reports', [
            'summary' => $summary,
            'cashFlow' => $cashFlow,
            'balanceSummary' => $balanceSummary,
            'filters' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'period' => $period,
            ],
        ]);
    }

    /**
     * Exporta relatório em diferentes formatos
     */
    private function exportReport($format, $summary, $cashFlow, $balanceSummary, $startDate, $endDate)
    {
        $fileName = "relatorio-financeiro-{$startDate}-{$endDate}";

        if ($format === 'pdf') {
            return $this->exportToPdf($summary, $cashFlow, $balanceSummary, $fileName, $startDate, $endDate);
        } elseif ($format === 'excel') {
            return $this->exportToExcel($summary, $cashFlow, $balanceSummary, $fileName, $startDate, $endDate);
        }

        return redirect()->back()->with('error', 'Formato de export não suportado');
    }

    /**
     * Exporta para PDF
     */
    private function exportToPdf($summary, $cashFlow, $balanceSummary, $fileName, $startDate, $endDate)
    {
        $html = view('reports.financial-pdf', [
            'summary' => $summary,
            'cashFlow' => $cashFlow,
            'balanceSummary' => $balanceSummary,
            'startDate' => $startDate,
            'endDate' => $endDate,
        ])->render();

        // Você pode usar uma biblioteca como dompdf ou wkhtmltopdf
        // Por enquanto, vamos retornar uma resposta simples
        return response($html)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', "attachment; filename=\"{$fileName}.pdf\"");
    }

    /**
     * Exporta para Excel
     */
    private function exportToExcel($summary, $cashFlow, $balanceSummary, $fileName, $startDate, $endDate)
    {
        $csvContent = "Relatório Financeiro - {$startDate} a {$endDate}\n\n";

        // Resumo
        $csvContent .= "RESUMO EXECUTIVO\n";
        $csvContent .= "Total de Receitas,{$summary['formatted_income']}\n";
        $csvContent .= "Total de Despesas,{$summary['formatted_expenses']}\n";
        $csvContent .= "Saldo Líquido,{$summary['formatted_balance']}\n";
        $csvContent .= "Número de Transações,{$summary['transactions_count']}\n\n";

        // Fluxo de Caixa
        $csvContent .= "FLUXO DE CAIXA\n";
        $csvContent .= "Período,Receitas,Despesas,Saldo\n";
        foreach ($cashFlow as $item) {
            $csvContent .= "{$item['period_label']},R$ {$item['income']},R$ {$item['expenses']},R$ {$item['balance']}\n";
        }

        // Contas
        $csvContent .= "\nRESUMO POR CONTAS\n";
        $csvContent .= "Conta,Tipo,Saldo\n";
        foreach ($balanceSummary['accounts'] as $account) {
            $csvContent .= "{$account['name']},{$account['type']},{$account['formatted_balance']}\n";
        }

        return response($csvContent)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', "attachment; filename=\"{$fileName}.csv\"")
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }
}
