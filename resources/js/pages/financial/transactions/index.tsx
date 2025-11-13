import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem, type PageProps } from '@/types';
import { Head, router, useForm, usePage } from '@inertiajs/react';
import { useCallback, useEffect, useState } from 'react';
import { toast } from 'sonner';

import ConfirmDeleteDialog from '@/components/ConfirmDeleteDialog';
import { FinancialTransactionFormDialog } from '@/components/financial/FinancialTransactionFormDialog';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuSeparator, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import {
    ArrowUpDown,
    Calendar,
    CheckCircle2,
    CreditCard,
    DollarSign,
    Edit,
    Filter,
    MoreHorizontal,
    Plus,
    Search,
    Trash2,
    TrendingDown,
    TrendingUp,
} from 'lucide-react';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Financeiro', href: '/financial' },
    { title: 'Transações', href: '/financial/transactions' },
];

interface Transaction {
    id: number;
    type: string;
    amount: number;
    description: string;
    transaction_date: string;
    due_date?: string;
    status: string;
    payment_method?: string;
    document_number?: string;
    notes?: string;
    id_financial_account: number;
    id_financial_category: number;
    id_customer?: number;
    id_transfer_account?: number;
    formatted_amount: string;
    status_label: string;
    status_color: string;
    account: {
        id: number;
        name: string;
        type: string;
    };
    category: {
        id: number;
        name: string;
        color: string;
        icon?: string;
    };
    customer?: {
        id: number;
        name: string;
    };
}

interface Account {
    id: number;
    name: string;
    type: string;
}

interface Category {
    id: number;
    name: string;
    type: string;
    color: string;
    icon?: string;
}

interface Customer {
    id: number;
    name: string;
}

interface TransactionsPageProps extends PageProps {
    transactions: {
        data: Transaction[];
        total: number;
        current_page: number;
        per_page: number;
        last_page: number;
    };
    accounts: Account[];
    categories: Category[];
    customers?: Customer[];
    filters: {
        search?: string;
        type?: string;
        status?: string;
        account_id?: number;
        category_id?: number;
        start_date?: string;
        end_date?: string;
        page: number;
        per_page: number;
    };
}

export default function TransactionsIndex() {
    const pageProps = usePage<TransactionsPageProps>().props;
    const { transactions, accounts, categories, customers = [], filters } = pageProps;

    const [openDeleteModal, setOpenDeleteModal] = useState(false);
    const [openFormModal, setOpenFormModal] = useState(false);
    const [selectedTransactionId, setSelectedTransactionId] = useState<number | null>(null);
    const [editingTransaction, setEditingTransaction] = useState<Transaction | null>(null);
    const [formMode, setFormMode] = useState<'create' | 'edit'>('create');

    const { data, setData } = useForm({
        search: filters.search || '',
        type: filters.type || 'all',
        status: filters.status || 'all',
        account_id: filters.account_id ? filters.account_id.toString() : 'all',
        category_id: filters.category_id ? filters.category_id.toString() : 'all',
        start_date: filters.start_date || '',
        end_date: filters.end_date || '',
        page: filters.page,
        per_page: filters.per_page,
    });

    const handleGet = useCallback(() => {
        const queryParams = new URLSearchParams();

        if (data.search) queryParams.append('search', data.search);
        if (data.type && data.type !== 'all') queryParams.append('type', data.type);
        if (data.status && data.status !== 'all') queryParams.append('status', data.status);
        if (data.account_id && data.account_id !== 'all') queryParams.append('account_id', data.account_id);
        if (data.category_id && data.category_id !== 'all') queryParams.append('category_id', data.category_id);
        if (data.start_date) queryParams.append('start_date', data.start_date);
        if (data.end_date) queryParams.append('end_date', data.end_date);
        if (data.page) queryParams.append('page', data.page.toString());
        if (data.per_page) queryParams.append('per_page', data.per_page.toString());

        const url = `/financial/transactions${queryParams.toString() ? '?' + queryParams.toString() : ''}`;

        router.get(
            url,
            {},
            {
                preserveScroll: true,
                preserveState: true,
                only: ['transactions'],
            },
        );
    }, [data]);

    // Auto-search with debounce
    useEffect(() => {
        const timeout = setTimeout(() => {
            handleGet();
        }, 500);

        return () => clearTimeout(timeout);
    }, [data.search, handleGet]);

    // Update filters immediately
    useEffect(() => {
        handleGet();
    }, [data.type, data.status, data.account_id, data.category_id, data.start_date, data.end_date, data.per_page, handleGet]);

    function handleDelete(transactionId: number) {
        router.delete(`/financial/transactions/${transactionId}`, {
            onSuccess: () => {
                toast.success('Transação excluída com sucesso!');
            },
            onError: () => {
                toast.error('Erro ao excluir transação');
            },
        });
    }

    function markAsPaid(transactionId: number) {
        router.patch(
            `/financial/transactions/${transactionId}/mark-as-paid`,
            {},
            {
                onSuccess: () => {
                    toast.success('Transação marcada como paga!');
                },
                onError: () => {
                    toast.error('Erro ao marcar transação como paga');
                },
            },
        );
    }

    const getTypeIcon = (type: string) => {
        switch (type) {
            case 'INCOME':
                return <TrendingUp className="h-4 w-4 text-green-600" />;
            case 'EXPENSE':
                return <TrendingDown className="h-4 w-4 text-red-600" />;
            case 'TRANSFER':
                return <ArrowUpDown className="h-4 w-4 text-blue-600" />;
            default:
                return <DollarSign className="h-4 w-4" />;
        }
    };

    const getStatusBadge = (transaction: Transaction) => {
        const statusConfig = {
            PENDING: { color: 'bg-yellow-100 text-yellow-800 border-yellow-200', label: 'Pendente' },
            PAID: { color: 'bg-green-100 text-green-800 border-green-200', label: 'Pago' },
            OVERDUE: { color: 'bg-red-100 text-red-800 border-red-200', label: 'Em Atraso' },
            CANCELLED: { color: 'bg-gray-100 text-gray-800 border-gray-200', label: 'Cancelado' },
        };

        const config = statusConfig[transaction.status as keyof typeof statusConfig] || statusConfig.PENDING;
        const colorClass = transaction.status_color ?
            {
                green: 'bg-green-100 text-green-800 border-green-200',
                yellow: 'bg-yellow-100 text-yellow-800 border-yellow-200',
                red: 'bg-red-100 text-red-800 border-red-200',
                gray: 'bg-gray-100 text-gray-800 border-gray-200',
            }[transaction.status_color] || config.color : config.color;

        const label = transaction.status_label || config.label;

        return <Badge className={colorClass}>{label}</Badge>;
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Transações Financeiras" />

            <div className="space-y-6 p-6">
                {/* Header */}
                <div className="flex flex-col justify-between gap-4 md:flex-row md:items-center">
                    <div>
                        <h1 className="text-2xl font-bold tracking-tight">Transações Financeiras</h1>
                        <p className="text-muted-foreground">Gerencie todas as receitas, despesas e transferências</p>
                    </div>
                    <Button
                        className="gap-2"
                        onClick={() => {
                            setEditingTransaction(null);
                            setFormMode('create');
                            setOpenFormModal(true);
                        }}
                    >
                        <Plus className="h-4 w-4" />
                        Nova Transação
                    </Button>
                </div>

                {/* Filtros */}
                <Card>
                    <CardHeader className="pb-4">
                        <CardTitle className="flex items-center gap-2 text-lg">
                            <Filter className="h-5 w-5" />
                            Filtros e Busca
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div className="grid grid-cols-1 gap-4 md:grid-cols-6">
                            {/* Search */}
                            <div className="space-y-2 md:col-span-2">
                                <label className="text-sm font-medium">Buscar</label>
                                <div className="relative">
                                    <Search className="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                                    <Input
                                        placeholder="Descrição, documento..."
                                        value={data.search}
                                        onChange={(e) => setData('search', e.target.value)}
                                        className="pl-10"
                                    />
                                </div>
                            </div>

                            {/* Type Filter */}
                            <div className="space-y-2">
                                <label className="text-sm font-medium">Tipo</label>
                                <Select value={data.type} onValueChange={(value) => setData('type', value)}>
                                    <SelectTrigger>
                                        <SelectValue placeholder="Todos" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="all">Todos</SelectItem>
                                        <SelectItem value="INCOME">Receita</SelectItem>
                                        <SelectItem value="EXPENSE">Despesa</SelectItem>
                                        <SelectItem value="TRANSFER">Transferência</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>

                            {/* Status Filter */}
                            <div className="space-y-2">
                                <label className="text-sm font-medium">Status</label>
                                <Select value={data.status} onValueChange={(value) => setData('status', value)}>
                                    <SelectTrigger>
                                        <SelectValue placeholder="Todos" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="all">Todos</SelectItem>
                                        <SelectItem value="PENDING">Pendente</SelectItem>
                                        <SelectItem value="PAID">Pago</SelectItem>
                                        <SelectItem value="OVERDUE">Em Atraso</SelectItem>
                                        <SelectItem value="CANCELLED">Cancelado</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>

                            {/* Date Range */}
                            <div className="space-y-2">
                                <label className="text-sm font-medium">Data inicial</label>
                                <Input type="date" value={data.start_date} onChange={(e) => setData('start_date', e.target.value)} />
                            </div>

                            <div className="space-y-2">
                                <label className="text-sm font-medium">Data final</label>
                                <Input type="date" value={data.end_date} onChange={(e) => setData('end_date', e.target.value)} />
                            </div>
                        </div>
                    </CardContent>
                </Card>

                {/* Table */}
                <Card>
                    <CardHeader>
                        <CardTitle className="flex items-center justify-between">
                            <span>Transações</span>
                            <span className="text-sm font-normal text-muted-foreground">{transactions.total} transação(ões) encontrada(s)</span>
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div className="rounded-md border">
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>Tipo</TableHead>
                                        <TableHead>Descrição</TableHead>
                                        <TableHead>Conta</TableHead>
                                        <TableHead>Categoria</TableHead>
                                        <TableHead>Data</TableHead>
                                        <TableHead>Valor</TableHead>
                                        <TableHead>Status</TableHead>
                                        <TableHead className="w-16">Ações</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {transactions.data.map((transaction) => (
                                        <TableRow key={transaction.id} className="group hover:bg-muted/50">
                                            <TableCell>
                                                <div className="flex items-center gap-2">
                                                    {getTypeIcon(transaction.type)}
                                                    <span className="text-sm">
                                                        {transaction.type === 'INCOME'
                                                            ? 'Receita'
                                                            : transaction.type === 'EXPENSE'
                                                              ? 'Despesa'
                                                              : 'Transferência'}
                                                    </span>
                                                </div>
                                            </TableCell>
                                            <TableCell>
                                                <div>
                                                    <p className="font-medium">{transaction.description}</p>
                                                    {transaction.document_number && (
                                                        <p className="text-xs text-muted-foreground">Doc: {transaction.document_number}</p>
                                                    )}
                                                </div>
                                            </TableCell>
                                            <TableCell>
                                                <div className="flex items-center gap-2">
                                                    <CreditCard className="h-4 w-4 text-muted-foreground" />
                                                    <span>{transaction.account.name}</span>
                                                </div>
                                            </TableCell>
                                            <TableCell>
                                                <div className="flex items-center gap-2">
                                                    <div className="h-3 w-3 rounded-full" style={{ backgroundColor: transaction.category.color }} />
                                                    <span>{transaction.category.name}</span>
                                                </div>
                                            </TableCell>
                                            <TableCell>
                                                <div className="flex items-center gap-2">
                                                    <Calendar className="h-4 w-4 text-muted-foreground" />
                                                    <span>{new Date(transaction.transaction_date).toLocaleDateString('pt-BR')}</span>
                                                </div>
                                            </TableCell>
                                            <TableCell>
                                                <div
                                                    className={`font-medium ${
                                                        transaction.type === 'INCOME'
                                                            ? 'text-green-600'
                                                            : transaction.type === 'EXPENSE'
                                                              ? 'text-red-600'
                                                              : 'text-blue-600'
                                                    }`}
                                                >
                                                    {transaction.formatted_amount}
                                                </div>
                                            </TableCell>
                                            <TableCell>{getStatusBadge(transaction)}</TableCell>
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
                                                                setEditingTransaction(transaction);
                                                                setFormMode('edit');
                                                                setOpenFormModal(true);
                                                            }}
                                                        >
                                                            <Edit className="mr-2 h-4 w-4" />
                                                            Editar
                                                        </DropdownMenuItem>
                                                        {transaction.status === 'PENDING' && (
                                                            <>
                                                                <DropdownMenuItem
                                                                    onClick={(e) => {
                                                                        e.preventDefault();
                                                                        e.stopPropagation();
                                                                        markAsPaid(transaction.id);
                                                                    }}
                                                                >
                                                                    <CheckCircle2 className="mr-2 h-4 w-4" />
                                                                    Marcar como Pago
                                                                </DropdownMenuItem>
                                                                <DropdownMenuSeparator />
                                                            </>
                                                        )}
                                                        <DropdownMenuItem
                                                            onClick={(e) => {
                                                                e.preventDefault();
                                                                e.stopPropagation();
                                                                setSelectedTransactionId(transaction.id);
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

                        {transactions.data.length === 0 && (
                            <div className="py-12 text-center">
                                <DollarSign className="mx-auto h-12 w-12 text-muted-foreground" />
                                <h3 className="mt-4 text-lg font-medium">Nenhuma transação encontrada</h3>
                                <p className="text-muted-foreground">Não há transações para os filtros selecionados.</p>
                            </div>
                        )}

                        {/* Pagination info */}
                        {transactions.total > 0 && (
                            <div className="flex items-center justify-between px-2 py-4">
                                <div className="text-sm text-muted-foreground">
                                    Mostrando {(transactions.current_page - 1) * transactions.per_page + 1} a{' '}
                                    {Math.min(transactions.current_page * transactions.per_page, transactions.total)} de {transactions.total}{' '}
                                    transações
                                </div>
                                <div className="text-sm text-muted-foreground">
                                    Página {transactions.current_page} de {transactions.last_page}
                                </div>
                            </div>
                        )}
                    </CardContent>
                </Card>
            </div>

            {/* Transaction Form Dialog */}
            <FinancialTransactionFormDialog
                open={openFormModal}
                onClose={() => {
                    setOpenFormModal(false);
                    setEditingTransaction(null);
                }}
                transaction={editingTransaction}
                accounts={accounts}
                categories={categories}
                customers={customers}
                mode={formMode}
            />

            {/* Delete Dialog */}
            <ConfirmDeleteDialog
                confirmText="Tem certeza que deseja excluir esta transação?"
                description="Esta ação não pode ser desfeita. A transação será removida permanentemente."
                open={openDeleteModal}
                onClose={() => setOpenDeleteModal(false)}
                onConfirm={() => {
                    if (selectedTransactionId !== null) {
                        handleDelete(selectedTransactionId);
                        setOpenDeleteModal(false);
                    }
                }}
            />
        </AppLayout>
    );
}
