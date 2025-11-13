import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, router, useForm } from '@inertiajs/react';
import { useCallback, useEffect, useState } from 'react';
import { toast } from 'sonner';

import ConfirmDeleteDialog from '@/components/ConfirmDeleteDialog';
import { FinancialAccountFormDialog } from '@/components/financial/FinancialAccountFormDialog';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import {
    Plus,
    Search,
    MoreHorizontal,
    Edit,
    Trash2,
    CreditCard,
    PiggyBank,
    Wallet,
    Banknote,
    TrendingUp,
    TrendingDown
} from 'lucide-react';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Financeiro', href: '/financial' },
    { title: 'Contas', href: '/financial/accounts' },
];

interface Account {
    id: number;
    name: string;
    type: string;
    bank_name?: string;
    account_number?: string;
    current_balance: number;
    initial_balance: number;
    is_active: boolean;
    description?: string;
    formatted_balance: string;
}

interface AccountsPageProps {
    accounts: {
        data: Account[];
        total: number;
        current_page: number;
        per_page: number;
        last_page: number;
    };
    balanceSummary: {
        total_balance: number;
        by_type: Record<string, number>;
        accounts: Account[];
    };
    filters: {
        search?: string;
        page: number;
        per_page: number;
        order: string;
    };
}

export default function AccountsIndex({ accounts, balanceSummary, filters }: AccountsPageProps) {
    const [openDeleteModal, setOpenDeleteModal] = useState(false);
    const [openFormModal, setOpenFormModal] = useState(false);
    const [selectedAccountId, setSelectedAccountId] = useState<number | null>(null);
    const [editingAccount, setEditingAccount] = useState<Account | null>(null);
    const [formMode, setFormMode] = useState<'create' | 'edit'>('create');

    const { data, setData } = useForm({
        search: filters.search || '',
        page: filters.page,
        per_page: filters.per_page,
        order: filters.order,
    });

    const handleGet = useCallback(() => {
        const queryParams = new URLSearchParams();

        if (data.search) queryParams.append('search', data.search);
        if (data.page) queryParams.append('page', data.page.toString());
        if (data.per_page) queryParams.append('per_page', data.per_page.toString());
        if (data.order) queryParams.append('order', data.order);

        const url = `/financial/accounts${queryParams.toString() ? '?' + queryParams.toString() : ''}`;

        router.get(url, {}, {
            preserveScroll: true,
            preserveState: true,
            only: ['accounts']
        });
    }, [data]);

    // Auto-search with debounce
    useEffect(() => {
        const timeout = setTimeout(() => {
            handleGet();
        }, 500);

        return () => clearTimeout(timeout);
    }, [data.search, handleGet]);

    function handleDelete(accountId: number) {
        router.delete(`/financial/accounts/${accountId}`, {
            onSuccess: () => {
                toast.success('Conta excluída com sucesso!');
            },
            onError: () => {
                toast.error('Erro ao excluir conta');
            },
        });
    }

    const getAccountIcon = (type: string) => {
        switch (type) {
            case 'CHECKING':
                return <CreditCard className="h-4 w-4 text-blue-600" />;
            case 'SAVINGS':
                return <PiggyBank className="h-4 w-4 text-green-600" />;
            case 'CASH':
                return <Wallet className="h-4 w-4 text-yellow-600" />;
            case 'CREDIT_CARD':
                return <Banknote className="h-4 w-4 text-purple-600" />;
            default:
                return <CreditCard className="h-4 w-4 text-gray-600" />;
        }
    };

    const getAccountTypeName = (type: string) => {
        switch (type) {
            case 'CHECKING': return 'Conta Corrente';
            case 'SAVINGS': return 'Poupança';
            case 'CASH': return 'Dinheiro';
            case 'CREDIT_CARD': return 'Cartão de Crédito';
            default: return type;
        }
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Contas Financeiras" />

            <div className="space-y-6 p-6">
                {/* Header */}
                <div className="flex flex-col justify-between gap-4 md:flex-row md:items-center">
                    <div>
                        <h1 className="text-2xl font-bold tracking-tight">Contas Financeiras</h1>
                        <p className="text-muted-foreground">
                            Gerencie suas contas bancárias, dinheiro e cartões
                        </p>
                    </div>
                    <Button
                        className="gap-2"
                        onClick={() => {
                            setEditingAccount(null);
                            setFormMode('create');
                            setOpenFormModal(true);
                        }}
                    >
                        <Plus className="h-4 w-4" />
                        Nova Conta
                    </Button>
                </div>

                {/* Resumo de Saldos */}
                <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Saldo Total</CardTitle>
                            <TrendingUp className="h-4 w-4 text-green-600" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">
                                R$ {balanceSummary.total_balance.toLocaleString('pt-BR', { minimumFractionDigits: 2 })}
                            </div>
                            <p className="text-xs text-muted-foreground">
                                {balanceSummary.accounts.length} conta(s) ativa(s)
                            </p>
                        </CardContent>
                    </Card>

                    {Object.entries(balanceSummary.by_type).map(([type, balance]) => (
                        <Card key={type}>
                            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                <CardTitle className="text-sm font-medium">{getAccountTypeName(type)}</CardTitle>
                                {getAccountIcon(type)}
                            </CardHeader>
                            <CardContent>
                                <div className="text-2xl font-bold">
                                    R$ {balance.toLocaleString('pt-BR', { minimumFractionDigits: 2 })}
                                </div>
                                <p className="text-xs text-muted-foreground">
                                    {balanceSummary.accounts.filter(acc => acc.type === type).length} conta(s)
                                </p>
                            </CardContent>
                        </Card>
                    ))}
                </div>

                {/* Filtros */}
                <Card>
                    <CardHeader className="pb-4">
                        <CardTitle className="text-lg">Buscar Contas</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div className="relative">
                            <Search className="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                            <Input
                                placeholder="Nome da conta, banco..."
                                value={data.search}
                                onChange={(e) => setData('search', e.target.value)}
                                className="pl-10"
                            />
                        </div>
                    </CardContent>
                </Card>

                {/* Tabela de Contas */}
                <Card>
                    <CardHeader>
                        <CardTitle className="flex items-center justify-between">
                            <span>Contas</span>
                            <span className="text-sm font-normal text-muted-foreground">
                                {accounts.total} conta(s) encontrada(s)
                            </span>
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div className="rounded-md border">
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>Conta</TableHead>
                                        <TableHead>Tipo</TableHead>
                                        <TableHead>Banco</TableHead>
                                        <TableHead>Saldo Atual</TableHead>
                                        <TableHead>Status</TableHead>
                                        <TableHead className="w-16">Ações</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {accounts.data.map((account) => (
                                        <TableRow key={account.id} className="group hover:bg-muted/50">
                                            <TableCell>
                                                <div className="flex items-center gap-3">
                                                    {getAccountIcon(account.type)}
                                                    <div>
                                                        <p className="font-medium">{account.name}</p>
                                                        {account.account_number && (
                                                            <p className="text-xs text-muted-foreground">
                                                                {account.account_number}
                                                            </p>
                                                        )}
                                                    </div>
                                                </div>
                                            </TableCell>
                                            <TableCell>
                                                <Badge variant="secondary">
                                                    {getAccountTypeName(account.type)}
                                                </Badge>
                                            </TableCell>
                                            <TableCell>
                                                {account.bank_name || '-'}
                                            </TableCell>
                                            <TableCell>
                                                <div className={`font-medium ${
                                                    account.current_balance >= 0 ? 'text-green-600' : 'text-red-600'
                                                }`}>
                                                    {account.formatted_balance}
                                                </div>
                                                {account.current_balance !== account.initial_balance && (
                                                    <div className="flex items-center gap-1 text-xs text-muted-foreground">
                                                        {account.current_balance > account.initial_balance ? (
                                                            <TrendingUp className="h-3 w-3 text-green-600" />
                                                        ) : (
                                                            <TrendingDown className="h-3 w-3 text-red-600" />
                                                        )}
                                                        {((account.current_balance - account.initial_balance) / account.initial_balance * 100).toFixed(1)}%
                                                    </div>
                                                )}
                                            </TableCell>
                                            <TableCell>
                                                <Badge variant={account.is_active ? 'default' : 'secondary'}>
                                                    {account.is_active ? 'Ativa' : 'Inativa'}
                                                </Badge>
                                            </TableCell>
                                            <TableCell>
                                                <DropdownMenu modal={false}>
                                                    <DropdownMenuTrigger asChild>
                                                        <Button variant="ghost" size="sm" className="h-8 w-8 p-0">
                                                            <MoreHorizontal className="h-4 w-4" />
                                                        </Button>
                                                    </DropdownMenuTrigger>
                                                    <DropdownMenuContent align="end">
                                                        <DropdownMenuItem
                                                            onClick={(e) => {
                                                                e.preventDefault();
                                                                e.stopPropagation();
                                                                setEditingAccount(account);
                                                                setFormMode('edit');
                                                                setOpenFormModal(true);
                                                            }}
                                                        >
                                                            <Edit className="mr-2 h-4 w-4" />
                                                            Editar
                                                        </DropdownMenuItem>
                                                        <DropdownMenuSeparator />
                                                        <DropdownMenuItem
                                                            onClick={(e) => {
                                                                e.preventDefault();
                                                                e.stopPropagation();
                                                                setSelectedAccountId(account.id);
                                                                setOpenDeleteModal(true);
                                                            }}
                                                            className="text-red-600"
                                                        >
                                                            <Trash2 className="mr-2 h-4 w-4" />
                                                            Excluir
                                                        </DropdownMenuItem>
                                                    </DropdownMenuContent>
                                                </DropdownMenu>
                                            </TableCell>
                                        </TableRow>
                                    ))}
                                </TableBody>
                            </Table>
                        </div>

                        {accounts.data.length === 0 && (
                            <div className="text-center py-12">
                                <CreditCard className="mx-auto h-12 w-12 text-muted-foreground" />
                                <h3 className="mt-4 text-lg font-medium">Nenhuma conta encontrada</h3>
                                <p className="text-muted-foreground">
                                    Crie sua primeira conta para começar a gerenciar suas finanças.
                                </p>
                                <Button
                                    className="mt-4"
                                    onClick={() => {
                                        setEditingAccount(null);
                                        setFormMode('create');
                                        setOpenFormModal(true);
                                    }}
                                >
                                    <Plus className="mr-2 h-4 w-4" />
                                    Nova Conta
                                </Button>
                            </div>
                        )}

                        {/* Pagination info */}
                        {accounts.total > 0 && (
                            <div className="flex items-center justify-between px-2 py-4">
                                <div className="text-sm text-muted-foreground">
                                    Mostrando {((accounts.current_page - 1) * accounts.per_page) + 1} a{' '}
                                    {Math.min(accounts.current_page * accounts.per_page, accounts.total)} de{' '}
                                    {accounts.total} contas
                                </div>
                                <div className="text-sm text-muted-foreground">
                                    Página {accounts.current_page} de {accounts.last_page}
                                </div>
                            </div>
                        )}
                    </CardContent>
                </Card>
            </div>

            {/* Account Form Dialog */}
            <FinancialAccountFormDialog
                open={openFormModal}
                onClose={() => {
                    setOpenFormModal(false);
                    setEditingAccount(null);
                }}
                account={editingAccount}
                mode={formMode}
            />

            {/* Delete Dialog */}
            <ConfirmDeleteDialog
                confirmText="Tem certeza que deseja excluir esta conta?"
                description="Esta ação não pode ser desfeita. A conta será removida permanentemente, mas as transações associadas serão mantidas."
                open={openDeleteModal}
                onClose={() => setOpenDeleteModal(false)}
                onConfirm={() => {
                    if (selectedAccountId !== null) {
                        handleDelete(selectedAccountId);
                        setOpenDeleteModal(false);
                    }
                }}
            />
        </AppLayout>
    );
}