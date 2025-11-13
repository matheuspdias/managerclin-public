import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogFooter,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import { useForm } from '@inertiajs/react';
import { toast } from 'sonner';
import { useEffect } from 'react';

interface FinancialTransaction {
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
    id_appointment?: number;
    id_transfer_account?: number;
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
}

interface Customer {
    id: number;
    name: string;
}

interface FinancialTransactionFormDialogProps {
    open: boolean;
    onClose: () => void;
    transaction?: FinancialTransaction | null;
    accounts: Account[];
    categories: Category[];
    customers?: Customer[];
    mode: 'create' | 'edit';
    defaultType?: 'INCOME' | 'EXPENSE' | 'TRANSFER';
}

export function FinancialTransactionFormDialog({
    open,
    onClose,
    transaction,
    accounts,
    categories,
    customers = [],
    mode,
    defaultType
}: FinancialTransactionFormDialogProps) {
    const { data, setData, post, patch, processing, errors, reset } = useForm({
        type: transaction?.type || defaultType || 'EXPENSE',
        amount: transaction?.amount || 0,
        description: transaction?.description || '',
        transaction_date: transaction?.transaction_date || new Date().toISOString().split('T')[0],
        due_date: transaction?.due_date || '',
        status: transaction?.status || 'PENDING',
        payment_method: transaction?.payment_method || '',
        document_number: transaction?.document_number || '',
        notes: transaction?.notes || '',
        id_financial_account: transaction?.id_financial_account || 0,
        id_financial_category: transaction?.id_financial_category || 0,
        id_customer: transaction?.id_customer || null,
        id_transfer_account: transaction?.id_transfer_account || null,
    });

    // Update form data when transaction prop changes
    useEffect(() => {
        if (transaction && mode === 'edit') {
            setData({
                type: transaction.type,
                amount: transaction.amount,
                description: transaction.description,
                transaction_date: transaction.transaction_date,
                due_date: transaction.due_date || '',
                status: transaction.status,
                payment_method: transaction.payment_method || '',
                document_number: transaction.document_number || '',
                notes: transaction.notes || '',
                id_financial_account: transaction.id_financial_account,
                id_financial_category: transaction.id_financial_category,
                id_customer: transaction.id_customer || null,
                id_transfer_account: transaction.id_transfer_account || null,
            });
        } else if (mode === 'create') {
            setData({
                type: defaultType || 'EXPENSE',
                amount: 0,
                description: '',
                transaction_date: new Date().toISOString().split('T')[0],
                due_date: '',
                status: 'PENDING',
                payment_method: '',
                document_number: '',
                notes: '',
                id_financial_account: 0,
                id_financial_category: 0,
                id_customer: null,
                id_transfer_account: null,
            });
        }
    }, [transaction, mode, defaultType, setData]);

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();

        const url = mode === 'edit' ? `/financial/transactions/${transaction?.id}` : '/financial/transactions';
        const method = mode === 'edit' ? patch : post;

        method(url, {
            onSuccess: () => {
                toast.success(mode === 'edit' ? 'Transação atualizada com sucesso!' : 'Transação criada com sucesso!');
                handleClose();
            },
            onError: () => {
                toast.error('Erro ao salvar transação');
            },
        });
    };

    const handleClose = () => {
        reset();
        onClose();
    };

    const transactionTypes = [
        { value: 'INCOME', label: 'Receita' },
        { value: 'EXPENSE', label: 'Despesa' },
        { value: 'TRANSFER', label: 'Transferência' },
    ];

    const statusOptions = [
        { value: 'PENDING', label: 'Pendente' },
        { value: 'PAID', label: 'Pago' },
        { value: 'OVERDUE', label: 'Em Atraso' },
        { value: 'CANCELLED', label: 'Cancelado' },
    ];

    const paymentMethods = [
        { value: 'CASH', label: 'Dinheiro' },
        { value: 'CARD', label: 'Cartão' },
        { value: 'TRANSFER', label: 'Transferência' },
        { value: 'PIX', label: 'PIX' },
        { value: 'CHECK', label: 'Cheque' },
    ];

    // Filter categories by type
    const filteredCategories = categories.filter(category =>
        category.type === data.type || data.type === 'TRANSFER'
    );

    return (
        <Dialog open={open} onOpenChange={handleClose}>
            <DialogContent className="sm:max-w-[600px] max-h-[90vh] overflow-y-auto">
                <DialogHeader>
                    <DialogTitle>
                        {mode === 'edit' ? 'Editar Transação' : 'Nova Transação'}
                    </DialogTitle>
                </DialogHeader>

                <form onSubmit={handleSubmit} className="space-y-4">
                    <div className="grid grid-cols-2 gap-4">
                        <div className="space-y-2">
                            <Label htmlFor="type">Tipo *</Label>
                            <Select
                                value={data.type}
                                onValueChange={(value) => {
                                    setData('type', value);
                                    // Clear category when type changes
                                    setData('id_financial_category', 0);
                                }}
                            >
                                <SelectTrigger>
                                    <SelectValue placeholder="Selecione o tipo" />
                                </SelectTrigger>
                                <SelectContent>
                                    {transactionTypes.map((type) => (
                                        <SelectItem key={type.value} value={type.value}>
                                            {type.label}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                            {errors.type && (
                                <p className="text-sm text-red-600">{errors.type}</p>
                            )}
                        </div>

                        <div className="space-y-2">
                            <Label htmlFor="amount">Valor *</Label>
                            <Input
                                id="amount"
                                type="number"
                                step="0.01"
                                value={data.amount}
                                onChange={(e) => setData('amount', parseFloat(e.target.value) || 0)}
                                placeholder="0.00"
                                required
                            />
                            {errors.amount && (
                                <p className="text-sm text-red-600">{errors.amount}</p>
                            )}
                        </div>
                    </div>

                    <div className="space-y-2">
                        <Label htmlFor="description">Descrição *</Label>
                        <Input
                            id="description"
                            value={data.description}
                            onChange={(e) => setData('description', e.target.value)}
                            placeholder="Ex: Pagamento de consulta"
                            required
                        />
                        {errors.description && (
                            <p className="text-sm text-red-600">{errors.description}</p>
                        )}
                    </div>

                    <div className="grid grid-cols-2 gap-4">
                        <div className="space-y-2">
                            <Label htmlFor="transaction_date">Data da Transação *</Label>
                            <Input
                                id="transaction_date"
                                type="date"
                                value={data.transaction_date}
                                onChange={(e) => setData('transaction_date', e.target.value)}
                                required
                            />
                            {errors.transaction_date && (
                                <p className="text-sm text-red-600">{errors.transaction_date}</p>
                            )}
                        </div>

                        <div className="space-y-2">
                            <Label htmlFor="due_date">Data de Vencimento</Label>
                            <Input
                                id="due_date"
                                type="date"
                                value={data.due_date}
                                onChange={(e) => setData('due_date', e.target.value)}
                            />
                            {errors.due_date && (
                                <p className="text-sm text-red-600">{errors.due_date}</p>
                            )}
                        </div>
                    </div>

                    <div className="grid grid-cols-2 gap-4">
                        <div className="space-y-2">
                            <Label htmlFor="id_financial_account">Conta *</Label>
                            <Select
                                value={data.id_financial_account > 0 ? data.id_financial_account.toString() : ''}
                                onValueChange={(value) => setData('id_financial_account', value ? parseInt(value) : 0)}
                            >
                                <SelectTrigger>
                                    <SelectValue placeholder="Selecione a conta" />
                                </SelectTrigger>
                                <SelectContent>
                                    {accounts.map((account) => (
                                        <SelectItem key={account.id} value={account.id.toString()}>
                                            {account.name} ({account.type})
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                            {errors.id_financial_account && (
                                <p className="text-sm text-red-600">{errors.id_financial_account}</p>
                            )}
                        </div>

                        <div className="space-y-2">
                            <Label htmlFor="id_financial_category">Categoria *</Label>
                            <Select
                                value={data.id_financial_category > 0 ? data.id_financial_category.toString() : ''}
                                onValueChange={(value) => setData('id_financial_category', value ? parseInt(value) : 0)}
                            >
                                <SelectTrigger>
                                    <SelectValue placeholder="Selecione a categoria" />
                                </SelectTrigger>
                                <SelectContent>
                                    {filteredCategories.map((category) => (
                                        <SelectItem key={category.id} value={category.id.toString()}>
                                            <div className="flex items-center gap-2">
                                                <div
                                                    className="w-3 h-3 rounded-full"
                                                    style={{ backgroundColor: category.color }}
                                                />
                                                {category.name}
                                            </div>
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                            {errors.id_financial_category && (
                                <p className="text-sm text-red-600">{errors.id_financial_category}</p>
                            )}
                        </div>
                    </div>

                    {data.type === 'TRANSFER' && (
                        <div className="space-y-2">
                            <Label htmlFor="id_transfer_account">Conta de Destino</Label>
                            <Select
                                value={data.id_transfer_account ? data.id_transfer_account.toString() : ''}
                                onValueChange={(value) => setData('id_transfer_account', value ? parseInt(value) : null)}
                            >
                                <SelectTrigger>
                                    <SelectValue placeholder="Selecione a conta de destino" />
                                </SelectTrigger>
                                <SelectContent>
                                    {accounts
                                        .filter(account => account.id !== data.id_financial_account)
                                        .map((account) => (
                                        <SelectItem key={account.id} value={account.id.toString()}>
                                            {account.name} ({account.type})
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                            {errors.id_transfer_account && (
                                <p className="text-sm text-red-600">{errors.id_transfer_account}</p>
                            )}
                        </div>
                    )}

                    <div className="grid grid-cols-2 gap-4">
                        <div className="space-y-2">
                            <Label htmlFor="status">Status</Label>
                            <Select value={data.status} onValueChange={(value) => setData('status', value)}>
                                <SelectTrigger>
                                    <SelectValue />
                                </SelectTrigger>
                                <SelectContent>
                                    {statusOptions.map((status) => (
                                        <SelectItem key={status.value} value={status.value}>
                                            {status.label}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                            {errors.status && (
                                <p className="text-sm text-red-600">{errors.status}</p>
                            )}
                        </div>

                        <div className="space-y-2">
                            <Label htmlFor="payment_method">Método de Pagamento</Label>
                            <Select
                                value={data.payment_method || 'none'}
                                onValueChange={(value) => setData('payment_method', value === 'none' ? '' : value)}
                            >
                                <SelectTrigger>
                                    <SelectValue placeholder="Selecione o método" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="none">Não especificado</SelectItem>
                                    {paymentMethods.map((method) => (
                                        <SelectItem key={method.value} value={method.value}>
                                            {method.label}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                            {errors.payment_method && (
                                <p className="text-sm text-red-600">{errors.payment_method}</p>
                            )}
                        </div>
                    </div>

                    <div className="grid grid-cols-2 gap-4">
                        <div className="space-y-2">
                            <Label htmlFor="document_number">Número do Documento</Label>
                            <Input
                                id="document_number"
                                value={data.document_number}
                                onChange={(e) => setData('document_number', e.target.value)}
                                placeholder="Ex: NF-001"
                            />
                            {errors.document_number && (
                                <p className="text-sm text-red-600">{errors.document_number}</p>
                            )}
                        </div>

                        {customers.length > 0 && (
                            <div className="space-y-2">
                                <Label htmlFor="id_customer">Cliente</Label>
                                <Select
                                    value={data.id_customer ? data.id_customer.toString() : 'none'}
                                    onValueChange={(value) => setData('id_customer', value === 'none' ? null : parseInt(value))}
                                >
                                    <SelectTrigger>
                                        <SelectValue placeholder="Selecione o cliente" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="none">Nenhum cliente</SelectItem>
                                        {customers.map((customer) => (
                                            <SelectItem key={customer.id} value={customer.id.toString()}>
                                                {customer.name}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                                {errors.id_customer && (
                                    <p className="text-sm text-red-600">{errors.id_customer}</p>
                                )}
                            </div>
                        )}
                    </div>

                    <div className="space-y-2">
                        <Label htmlFor="notes">Observações</Label>
                        <Textarea
                            id="notes"
                            value={data.notes}
                            onChange={(e) => setData('notes', e.target.value)}
                            placeholder="Observações adicionais"
                            rows={3}
                        />
                        {errors.notes && (
                            <p className="text-sm text-red-600">{errors.notes}</p>
                        )}
                    </div>

                    <DialogFooter>
                        <Button
                            type="button"
                            variant="outline"
                            onClick={handleClose}
                            disabled={processing}
                        >
                            Cancelar
                        </Button>
                        <Button type="submit" disabled={processing}>
                            {processing ? 'Salvando...' : mode === 'edit' ? 'Atualizar' : 'Criar Transação'}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}