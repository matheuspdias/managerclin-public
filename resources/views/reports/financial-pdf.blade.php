<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatório Financeiro</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #e5e7eb;
            padding-bottom: 20px;
        }
        .header h1 {
            color: #1f2937;
            margin: 0;
        }
        .header p {
            color: #6b7280;
            margin: 5px 0;
        }
        .section {
            margin: 30px 0;
        }
        .section h2 {
            color: #1f2937;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 10px;
        }
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin: 20px 0;
        }
        .summary-card {
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 15px;
            background: #f9fafb;
        }
        .summary-card h3 {
            margin: 0 0 10px 0;
            color: #374151;
            font-size: 14px;
        }
        .summary-card .value {
            font-size: 24px;
            font-weight: bold;
            margin: 0;
        }
        .value.positive { color: #059669; }
        .value.negative { color: #dc2626; }
        .value.neutral { color: #374151; }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        table th, table td {
            border: 1px solid #e5e7eb;
            padding: 10px;
            text-align: left;
        }
        table th {
            background: #f3f4f6;
            font-weight: bold;
            color: #374151;
        }
        table tr:nth-child(even) {
            background: #f9fafb;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .mt-4 {
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Relatório Financeiro</h1>
        <p>Período: {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} a {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}</p>
        <p>Gerado em: {{ now()->format('d/m/Y H:i:s') }}</p>
    </div>

    <div class="section">
        <h2>Resumo Executivo</h2>
        <div class="summary-grid">
            <div class="summary-card">
                <h3>Total de Receitas</h3>
                <p class="value positive">{{ $summary['formatted_income'] }}</p>
            </div>
            <div class="summary-card">
                <h3>Total de Despesas</h3>
                <p class="value negative">{{ $summary['formatted_expenses'] }}</p>
            </div>
            <div class="summary-card">
                <h3>Saldo Líquido</h3>
                <p class="value {{ $summary['balance'] >= 0 ? 'positive' : 'negative' }}">{{ $summary['formatted_balance'] }}</p>
            </div>
            <div class="summary-card">
                <h3>Total de Transações</h3>
                <p class="value neutral">{{ $summary['transactions_count'] }}</p>
            </div>
        </div>
    </div>

    <div class="section">
        <h2>Fluxo de Caixa</h2>
        <table>
            <thead>
                <tr>
                    <th>Período</th>
                    <th class="text-right">Receitas</th>
                    <th class="text-right">Despesas</th>
                    <th class="text-right">Saldo</th>
                </tr>
            </thead>
            <tbody>
                @foreach($cashFlow as $item)
                <tr>
                    <td>{{ $item['period_label'] }}</td>
                    <td class="text-right">R$ {{ number_format($item['income'], 2, ',', '.') }}</td>
                    <td class="text-right">R$ {{ number_format($item['expenses'], 2, ',', '.') }}</td>
                    <td class="text-right {{ $item['balance'] >= 0 ? 'positive' : 'negative' }}">
                        R$ {{ number_format($item['balance'], 2, ',', '.') }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="section">
        <h2>Distribuição por Contas</h2>
        <table>
            <thead>
                <tr>
                    <th>Conta</th>
                    <th>Tipo</th>
                    <th class="text-right">Saldo</th>
                    <th class="text-right">% do Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($balanceSummary['accounts'] as $account)
                <tr>
                    <td>{{ $account['name'] }}</td>
                    <td>
                        @switch($account['type'])
                            @case('CHECKING') Conta Corrente @break
                            @case('SAVINGS') Poupança @break
                            @case('CASH') Dinheiro @break
                            @case('CREDIT_CARD') Cartão de Crédito @break
                            @default {{ $account['type'] }}
                        @endswitch
                    </td>
                    <td class="text-right">{{ $account['formatted_balance'] }}</td>
                    <td class="text-right">
                        {{ $balanceSummary['total_balance'] > 0 ? number_format(($account['balance'] / $balanceSummary['total_balance']) * 100, 1) : 0 }}%
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="section">
        <h2>Resumo por Tipo de Conta</h2>
        <table>
            <thead>
                <tr>
                    <th>Tipo de Conta</th>
                    <th class="text-right">Saldo Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($balanceSummary['by_type'] as $type => $balance)
                <tr>
                    <td>
                        @switch($type)
                            @case('CHECKING') Conta Corrente @break
                            @case('SAVINGS') Poupança @break
                            @case('CASH') Dinheiro @break
                            @case('CREDIT_CARD') Cartão de Crédito @break
                            @default {{ $type }}
                        @endswitch
                    </td>
                    <td class="text-right">R$ {{ number_format($balance, 2, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="section mt-4">
        <h2>Indicadores Financeiros</h2>
        <div class="summary-grid">
            <div class="summary-card">
                <h3>Margem de Lucro</h3>
                <p class="value positive">
                    {{ $summary['income'] > 0 ? number_format(($summary['balance'] / $summary['income']) * 100, 1) : 0 }}%
                </p>
            </div>
            <div class="summary-card">
                <h3>Ticket Médio</h3>
                <p class="value neutral">
                    R$ {{ $summary['transactions_count'] > 0 ? number_format($summary['income'] / $summary['transactions_count'], 2, ',', '.') : '0,00' }}
                </p>
            </div>
            <div class="summary-card">
                <h3>Patrimônio Total</h3>
                <p class="value positive">R$ {{ number_format($balanceSummary['total_balance'], 2, ',', '.') }}</p>
            </div>
        </div>
    </div>
</body>
</html>