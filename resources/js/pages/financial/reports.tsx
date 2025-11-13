import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import {
    TrendingUp,
    TrendingDown,
    DollarSign,
    Calendar,
    Download,
    BarChart3,
    PieChart,
    LineChart,
    Filter
} from 'lucide-react';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Financeiro', href: '/financial' },
    { title: 'Relatórios', href: '/financial/reports' },
];

interface FinancialReportsProps {
    summary: {
        income: number;
        expenses: number;
        balance: number;
        formatted_income: string;
        formatted_expenses: string;
        formatted_balance: string;
        transactions_count: number;
    };
    cashFlow: Array<{
        period: string;
        period_label: string;
        income: number;
        expenses: number;
        balance: number;
    }>;
    balanceSummary: {
        total_balance: number;
        by_type: Record<string, number>;
        accounts: Array<{
            id: number;
            name: string;
            type: string;
            balance: number;
            formatted_balance: string;
        }>;
    };
    filters: {
        start_date: string;
        end_date: string;
        period: string;
    };
}

export default function FinancialReports({ summary, cashFlow, balanceSummary, filters }: FinancialReportsProps) {
    const { data, setData, get, processing } = useForm({
        start_date: filters.start_date,
        end_date: filters.end_date,
        period: filters.period,
    });

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        get('/financial/reports', {
            only: ['summary', 'cashFlow', 'balanceSummary'],
            preserveState: true,
        });
    };

    const exportReport = (format: string) => {
        const queryParams = new URLSearchParams({
            start_date: data.start_date,
            end_date: data.end_date,
            period: data.period,
            export: format,
        });

        // Try the export route first, fallback to current route with export param
        const exportUrl = `/financial/reports?${queryParams}`;

        // Force download by setting the appropriate headers expectation
        const link = document.createElement('a');
        link.href = exportUrl;
        link.target = '_blank';
        link.rel = 'noopener noreferrer';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Relatórios Financeiros" />

            <div className="space-y-6 p-6">
                {/* Header */}
                <div className="flex flex-col justify-between gap-4 md:flex-row md:items-center">
                    <div>
                        <h1 className="text-2xl font-bold tracking-tight">Relatórios Financeiros</h1>
                        <p className="text-muted-foreground">
                            Análise detalhada das suas finanças e fluxo de caixa
                        </p>
                    </div>
                    <div className="flex gap-2">
                        <Button variant="outline" onClick={() => exportReport('pdf')}>
                            <Download className="mr-2 h-4 w-4" />
                            PDF
                        </Button>
                        <Button variant="outline" onClick={() => exportReport('excel')}>
                            <Download className="mr-2 h-4 w-4" />
                            Excel
                        </Button>
                    </div>
                </div>

                {/* Filtros */}
                <Card>
                    <CardHeader>
                        <CardTitle className="flex items-center gap-2">
                            <Filter className="h-5 w-5" />
                            Filtros do Relatório
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <form onSubmit={handleSubmit} className="grid grid-cols-1 gap-4 md:grid-cols-4">
                            <div className="space-y-2">
                                <label className="text-sm font-medium">Data Inicial</label>
                                <Input
                                    type="date"
                                    value={data.start_date}
                                    onChange={(e) => setData('start_date', e.target.value)}
                                />
                            </div>
                            <div className="space-y-2">
                                <label className="text-sm font-medium">Data Final</label>
                                <Input
                                    type="date"
                                    value={data.end_date}
                                    onChange={(e) => setData('end_date', e.target.value)}
                                />
                            </div>
                            <div className="space-y-2">
                                <label className="text-sm font-medium">Período</label>
                                <Select value={data.period} onValueChange={(value) => setData('period', value)}>
                                    <SelectTrigger>
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="day">Diário</SelectItem>
                                        <SelectItem value="month">Mensal</SelectItem>
                                        <SelectItem value="year">Anual</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                            <div className="flex items-end">
                                <Button type="submit" disabled={processing} className="w-full">
                                    <BarChart3 className="mr-2 h-4 w-4" />
                                    Gerar Relatório
                                </Button>
                            </div>
                        </form>
                    </CardContent>
                </Card>

                {/* Resumo Executivo */}
                <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Total de Receitas</CardTitle>
                            <TrendingUp className="h-4 w-4 text-green-600" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-green-600">{summary.formatted_income}</div>
                            <p className="text-xs text-muted-foreground">
                                Período selecionado
                            </p>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Total de Despesas</CardTitle>
                            <TrendingDown className="h-4 w-4 text-red-600" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-red-600">{summary.formatted_expenses}</div>
                            <p className="text-xs text-muted-foreground">
                                Período selecionado
                            </p>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Saldo Líquido</CardTitle>
                            <DollarSign className={`h-4 w-4 ${summary.balance >= 0 ? 'text-green-600' : 'text-red-600'}`} />
                        </CardHeader>
                        <CardContent>
                            <div className={`text-2xl font-bold ${summary.balance >= 0 ? 'text-green-600' : 'text-red-600'}`}>
                                {summary.formatted_balance}
                            </div>
                            <p className="text-xs text-muted-foreground">
                                Receitas - Despesas
                            </p>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Transações</CardTitle>
                            <Calendar className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{summary.transactions_count}</div>
                            <p className="text-xs text-muted-foreground">
                                Total de movimentações
                            </p>
                        </CardContent>
                    </Card>
                </div>

                {/* Gráfico de Fluxo de Caixa */}
                <Card>
                    <CardHeader>
                        <CardTitle className="flex items-center gap-2">
                            <LineChart className="h-5 w-5" />
                            Fluxo de Caixa
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div className="h-80 flex items-center justify-center bg-muted/30 rounded-lg">
                            <div className="text-center">
                                <BarChart3 className="mx-auto h-12 w-12 text-muted-foreground mb-4" />
                                <h3 className="text-lg font-medium mb-2">Gráfico de Fluxo de Caixa</h3>
                                <p className="text-muted-foreground">
                                    Integração com biblioteca de gráficos em desenvolvimento
                                </p>
                                <div className="mt-4 space-y-2">
                                    {cashFlow.slice(0, 6).map((item, index) => (
                                        <div key={index} className="flex justify-between items-center text-sm">
                                            <span>{item.period_label}</span>
                                            <div className="flex gap-4">
                                                <span className="text-green-600">
                                                    R$ {item.income.toLocaleString('pt-BR')}
                                                </span>
                                                <span className="text-red-600">
                                                    R$ {item.expenses.toLocaleString('pt-BR')}
                                                </span>
                                                <span className={item.balance >= 0 ? 'text-green-600 font-medium' : 'text-red-600 font-medium'}>
                                                    R$ {item.balance.toLocaleString('pt-BR')}
                                                </span>
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                {/* Distribuição por Contas */}
                <div className="grid gap-4 md:grid-cols-2">
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <PieChart className="h-5 w-5" />
                                Distribuição por Contas
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="space-y-4">
                                {balanceSummary.accounts.map((account) => (
                                    <div key={account.id} className="flex items-center justify-between">
                                        <div className="flex items-center gap-3">
                                            <div className="w-3 h-3 rounded-full bg-blue-500" />
                                            <div>
                                                <p className="font-medium">{account.name}</p>
                                                <p className="text-sm text-muted-foreground">{account.type}</p>
                                            </div>
                                        </div>
                                        <div className="text-right">
                                            <p className="font-medium">{account.formatted_balance}</p>
                                            <p className="text-xs text-muted-foreground">
                                                {((account.balance / balanceSummary.total_balance) * 100).toFixed(1)}%
                                            </p>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <BarChart3 className="h-5 w-5" />
                                Resumo por Tipo de Conta
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="space-y-4">
                                {Object.entries(balanceSummary.by_type).map(([type, balance]) => (
                                    <div key={type} className="flex items-center justify-between">
                                        <div className="flex items-center gap-3">
                                            <div className="w-3 h-3 rounded-full bg-green-500" />
                                            <span className="font-medium">
                                                {type === 'CHECKING' ? 'Conta Corrente' :
                                                 type === 'SAVINGS' ? 'Poupança' :
                                                 type === 'CASH' ? 'Dinheiro' : 'Cartão de Crédito'}
                                            </span>
                                        </div>
                                        <span className="font-medium">
                                            R$ {balance.toLocaleString('pt-BR', { minimumFractionDigits: 2 })}
                                        </span>
                                    </div>
                                ))}
                            </div>
                        </CardContent>
                    </Card>
                </div>

                {/* Insights e Indicadores */}
                <Card>
                    <CardHeader>
                        <CardTitle>Insights Financeiros</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                            <div className="p-4 border rounded-lg">
                                <h4 className="font-medium mb-2">Margem de Lucro</h4>
                                <div className="text-2xl font-bold text-green-600">
                                    {summary.income > 0 ? ((summary.balance / summary.income) * 100).toFixed(1) : 0}%
                                </div>
                                <p className="text-sm text-muted-foreground">
                                    Receita líquida sobre receita total
                                </p>
                            </div>

                            <div className="p-4 border rounded-lg">
                                <h4 className="font-medium mb-2">Ticket Médio</h4>
                                <div className="text-2xl font-bold">
                                    R$ {summary.transactions_count > 0 ? (summary.income / summary.transactions_count).toLocaleString('pt-BR') : '0'}
                                </div>
                                <p className="text-sm text-muted-foreground">
                                    Valor médio por transação
                                </p>
                            </div>

                            <div className="p-4 border rounded-lg">
                                <h4 className="font-medium mb-2">Patrimônio Total</h4>
                                <div className="text-2xl font-bold text-blue-600">
                                    R$ {balanceSummary.total_balance.toLocaleString('pt-BR', { minimumFractionDigits: 2 })}
                                </div>
                                <p className="text-sm text-muted-foreground">
                                    Soma de todas as contas
                                </p>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}