import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import {
    TrendingUp,
    TrendingDown,
    DollarSign,
    Calendar,
    AlertTriangle,
    Plus,
    ArrowUpRight,
    ArrowDownRight,
    CreditCard,
    PiggyBank,
    Target,
    FileText
} from 'lucide-react';
import { Link } from '@inertiajs/react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Financeiro',
        href: '/financial',
    },
];

interface FinancialIndexProps {
    dashboardData: {
        balance_summary: {
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
        monthly_summary: {
            income: number;
            expenses: number;
            balance: number;
            formatted_income: string;
            formatted_expenses: string;
            formatted_balance: string;
            transactions_count: number;
            income_percent_change: number;
            expenses_percent_change: number;
        };
        overdue_transactions: any[];
        pending_transactions: any[];
        cash_flow: Array<{
            period: string;
            period_label: string;
            income: number;
            expenses: number;
            balance: number;
        }>;
    };
}

function formatPercentChange(percent: number): { text: string; icon: any; color: string } {
    const isPositive = percent > 0;
    const isNegative = percent < 0;

    if (percent === 0) {
        return {
            text: 'sem alteração',
            icon: null,
            color: 'text-muted-foreground'
        };
    }

    return {
        text: `${isPositive ? '+' : ''}${percent.toFixed(1)}% em relação ao período anterior`,
        icon: isPositive ? TrendingUp : TrendingDown,
        color: isPositive ? 'text-green-600' : 'text-red-600'
    };
}

export default function FinancialIndex({ dashboardData }: FinancialIndexProps) {
    const { balance_summary, monthly_summary, overdue_transactions, pending_transactions, cash_flow } = dashboardData;

    const incomeChange = formatPercentChange(monthly_summary.income_percent_change);
    const expensesChange = formatPercentChange(monthly_summary.expenses_percent_change);

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Dashboard Financeiro" />

            <div className="space-y-6 p-6">
                {/* Header */}
                <div className="flex flex-col justify-between gap-4 md:flex-row md:items-center">
                    <div>
                        <h1 className="text-3xl font-bold tracking-tight">Dashboard Financeiro</h1>
                        <p className="text-muted-foreground">
                            Visão geral das suas finanças e transações
                        </p>
                    </div>
                    <div className="flex gap-2">
                        <Button asChild>
                            <Link href="/financial/transactions">
                                <FileText className="mr-2 h-4 w-4" />
                                Ver Transações
                            </Link>
                        </Button>
                        <Button asChild>
                            <Link href="/financial/reports">
                                <Target className="mr-2 h-4 w-4" />
                                Relatórios
                            </Link>
                        </Button>
                    </div>
                </div>

                {/* Cards de Resumo */}
                <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Saldo Total</CardTitle>
                            <PiggyBank className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">
                                {new Intl.NumberFormat('pt-BR', {
                                    style: 'currency',
                                    currency: 'BRL'
                                }).format(balance_summary.total_balance)}
                            </div>
                            <p className="text-xs text-muted-foreground">
                                {balance_summary.accounts.length} conta(s) ativa(s)
                            </p>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Receitas do Mês</CardTitle>
                            <TrendingUp className="h-4 w-4 text-green-600" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-green-600">
                                {monthly_summary.formatted_income}
                            </div>
                            <p className={`text-xs ${incomeChange.color} flex items-center gap-1`}>
                                {incomeChange.icon && <incomeChange.icon className="h-3 w-3" />}
                                {incomeChange.text}
                            </p>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Despesas do Mês</CardTitle>
                            <TrendingDown className="h-4 w-4 text-red-600" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-red-600">
                                {monthly_summary.formatted_expenses}
                            </div>
                            <p className={`text-xs ${expensesChange.color} flex items-center gap-1`}>
                                {expensesChange.icon && <expensesChange.icon className="h-3 w-3" />}
                                {expensesChange.text}
                            </p>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Saldo do Mês</CardTitle>
                            <DollarSign className={`h-4 w-4 ${monthly_summary.balance >= 0 ? 'text-green-600' : 'text-red-600'}`} />
                        </CardHeader>
                        <CardContent>
                            <div className={`text-2xl font-bold ${monthly_summary.balance >= 0 ? 'text-green-600' : 'text-red-600'}`}>
                                {monthly_summary.formatted_balance}
                            </div>
                            <p className="text-xs text-muted-foreground">
                                {monthly_summary.transactions_count} transação(ões)
                            </p>
                        </CardContent>
                    </Card>
                </div>

                {/* Alertas e Pendências */}
                {(overdue_transactions.length > 0 || pending_transactions.length > 0) && (
                    <div className="grid gap-4 md:grid-cols-2">
                        {overdue_transactions.length > 0 && (
                            <Card className="border-red-200">
                                <CardHeader>
                                    <CardTitle className="flex items-center gap-2 text-red-600">
                                        <AlertTriangle className="h-5 w-5" />
                                        Transações em Atraso
                                    </CardTitle>
                                </CardHeader>
                                <CardContent>
                                    <div className="text-2xl font-bold text-red-600 mb-2">
                                        {overdue_transactions.length}
                                    </div>
                                    <p className="text-sm text-muted-foreground mb-4">
                                        Transações que precisam de atenção
                                    </p>
                                    <Button variant="outline" size="sm" asChild>
                                        <Link href="/financial/transactions/overdue">
                                            Ver Detalhes
                                        </Link>
                                    </Button>
                                </CardContent>
                            </Card>
                        )}

                        {pending_transactions.length > 0 && (
                            <Card className="border-yellow-200">
                                <CardHeader>
                                    <CardTitle className="flex items-center gap-2 text-yellow-600">
                                        <Calendar className="h-5 w-5" />
                                        Transações Pendentes
                                    </CardTitle>
                                </CardHeader>
                                <CardContent>
                                    <div className="text-2xl font-bold text-yellow-600 mb-2">
                                        {pending_transactions.length}
                                    </div>
                                    <p className="text-sm text-muted-foreground mb-4">
                                        Aguardando confirmação de pagamento
                                    </p>
                                    <Button variant="outline" size="sm" asChild>
                                        <Link href="/financial/transactions/pending">
                                            Ver Detalhes
                                        </Link>
                                    </Button>
                                </CardContent>
                            </Card>
                        )}
                    </div>
                )}

                {/* Contas */}
                <Card>
                    <CardHeader>
                        <CardTitle className="flex items-center justify-between">
                            <span>Contas</span>
                            <Button size="sm" asChild>
                                <Link href="/financial/accounts">
                                    <Plus className="mr-2 h-4 w-4" />
                                    Gerenciar
                                </Link>
                            </Button>
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div className="space-y-4">
                            {balance_summary.accounts.map((account) => (
                                <div key={account.id} className="flex items-center justify-between p-3 border rounded-lg">
                                    <div className="flex items-center gap-3">
                                        <div className="p-2 bg-blue-100 rounded-full">
                                            <CreditCard className="h-4 w-4 text-blue-600" />
                                        </div>
                                        <div>
                                            <p className="font-medium">{account.name}</p>
                                            <p className="text-sm text-muted-foreground">{account.type}</p>
                                        </div>
                                    </div>
                                    <div className="text-right">
                                        <p className="font-medium">{account.formatted_balance}</p>
                                    </div>
                                </div>
                            ))}
                        </div>
                    </CardContent>
                </Card>

                {/* Ações Rápidas */}
                <Card>
                    <CardHeader>
                        <CardTitle>Ações Rápidas</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                            <Button className="h-20 flex-col gap-2" variant="outline" asChild>
                                <Link href="/financial/transactions?type=INCOME">
                                    <ArrowUpRight className="h-6 w-6 text-green-600" />
                                    Nova Receita
                                </Link>
                            </Button>
                            <Button className="h-20 flex-col gap-2" variant="outline" asChild>
                                <Link href="/financial/transactions?type=EXPENSE">
                                    <ArrowDownRight className="h-6 w-6 text-red-600" />
                                    Nova Despesa
                                </Link>
                            </Button>
                            <Button className="h-20 flex-col gap-2" variant="outline" asChild>
                                <Link href="/financial/accounts">
                                    <CreditCard className="h-6 w-6" />
                                    Gerenciar Contas
                                </Link>
                            </Button>
                            <Button className="h-20 flex-col gap-2" variant="outline" asChild>
                                <Link href="/financial/categories">
                                    <Target className="h-6 w-6" />
                                    Categorias
                                </Link>
                            </Button>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}