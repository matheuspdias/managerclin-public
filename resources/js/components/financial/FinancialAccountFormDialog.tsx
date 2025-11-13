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
import { Checkbox } from '@/components/ui/checkbox';
import { useForm } from '@inertiajs/react';
import { toast } from 'sonner';
import { useEffect } from 'react';

interface FinancialAccount {
    id: number;
    name: string;
    type: string;
    bank_name?: string;
    account_number?: string;
    initial_balance: number;
    is_active: boolean;
    description?: string;
}

interface FinancialAccountFormDialogProps {
    open: boolean;
    onClose: () => void;
    account?: FinancialAccount | null;
    mode: 'create' | 'edit';
}

export function FinancialAccountFormDialog({
    open,
    onClose,
    account,
    mode
}: FinancialAccountFormDialogProps) {
    const { data, setData, post, patch, processing, errors, reset } = useForm({
        name: account?.name || '',
        type: account?.type || 'CHECKING',
        bank_name: account?.bank_name || '',
        account_number: account?.account_number || '',
        initial_balance: account?.initial_balance || 0,
        is_active: account?.is_active ?? true,
        description: account?.description || '',
    });

    // Update form data when account prop changes
    useEffect(() => {
        if (account && mode === 'edit') {
            setData({
                name: account.name,
                type: account.type,
                bank_name: account.bank_name || '',
                account_number: account.account_number || '',
                initial_balance: account.initial_balance,
                is_active: account.is_active,
                description: account.description || '',
            });
        } else if (mode === 'create') {
            setData({
                name: '',
                type: 'CHECKING',
                bank_name: '',
                account_number: '',
                initial_balance: 0,
                is_active: true,
                description: '',
            });
        }
    }, [account, mode, setData]);

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();

        const url = mode === 'edit' ? `/financial/accounts/${account?.id}` : '/financial/accounts';
        const method = mode === 'edit' ? patch : post;

        method(url, {
            onSuccess: () => {
                toast.success(mode === 'edit' ? 'Conta atualizada com sucesso!' : 'Conta criada com sucesso!');
                handleClose();
            },
            onError: () => {
                toast.error('Erro ao salvar conta');
            },
        });
    };

    const handleClose = () => {
        reset();
        onClose();
    };

    const accountTypes = [
        { value: 'CHECKING', label: 'Conta Corrente' },
        { value: 'SAVINGS', label: 'Poupança' },
        { value: 'CASH', label: 'Dinheiro' },
        { value: 'CREDIT_CARD', label: 'Cartão de Crédito' },
    ];

    return (
        <Dialog open={open} onOpenChange={handleClose}>
            <DialogContent className="sm:max-w-[500px]">
                <DialogHeader>
                    <DialogTitle>
                        {mode === 'edit' ? 'Editar Conta' : 'Nova Conta Financeira'}
                    </DialogTitle>
                </DialogHeader>

                <form onSubmit={handleSubmit} className="space-y-4">
                    <div className="grid grid-cols-2 gap-4">
                        <div className="space-y-2">
                            <Label htmlFor="name">Nome da Conta *</Label>
                            <Input
                                id="name"
                                value={data.name}
                                onChange={(e) => setData('name', e.target.value)}
                                placeholder="Ex: Conta Corrente Banco do Brasil"
                                required
                            />
                            {errors.name && (
                                <p className="text-sm text-red-600">{errors.name}</p>
                            )}
                        </div>

                        <div className="space-y-2">
                            <Label htmlFor="type">Tipo de Conta *</Label>
                            <Select value={data.type} onValueChange={(value) => setData('type', value)}>
                                <SelectTrigger>
                                    <SelectValue placeholder="Selecione o tipo" />
                                </SelectTrigger>
                                <SelectContent>
                                    {accountTypes.map((type) => (
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
                    </div>

                    <div className="grid grid-cols-2 gap-4">
                        <div className="space-y-2">
                            <Label htmlFor="bank_name">Nome do Banco</Label>
                            <Input
                                id="bank_name"
                                value={data.bank_name}
                                onChange={(e) => setData('bank_name', e.target.value)}
                                placeholder="Ex: Banco do Brasil"
                            />
                            {errors.bank_name && (
                                <p className="text-sm text-red-600">{errors.bank_name}</p>
                            )}
                        </div>

                        <div className="space-y-2">
                            <Label htmlFor="account_number">Número da Conta</Label>
                            <Input
                                id="account_number"
                                value={data.account_number}
                                onChange={(e) => setData('account_number', e.target.value)}
                                placeholder="Ex: 12345-6"
                            />
                            {errors.account_number && (
                                <p className="text-sm text-red-600">{errors.account_number}</p>
                            )}
                        </div>
                    </div>

                    <div className="space-y-2">
                        <Label htmlFor="initial_balance">Saldo Inicial *</Label>
                        <Input
                            id="initial_balance"
                            type="number"
                            step="0.01"
                            value={data.initial_balance}
                            onChange={(e) => setData('initial_balance', parseFloat(e.target.value) || 0)}
                            placeholder="0.00"
                            required
                        />
                        {errors.initial_balance && (
                            <p className="text-sm text-red-600">{errors.initial_balance}</p>
                        )}
                    </div>

                    <div className="space-y-2">
                        <Label htmlFor="description">Descrição</Label>
                        <Textarea
                            id="description"
                            value={data.description}
                            onChange={(e) => setData('description', e.target.value)}
                            placeholder="Descrição opcional da conta"
                            rows={3}
                        />
                        {errors.description && (
                            <p className="text-sm text-red-600">{errors.description}</p>
                        )}
                    </div>

                    <div className="flex items-center space-x-2">
                        <Checkbox
                            id="is_active"
                            checked={data.is_active}
                            onCheckedChange={(checked) => setData('is_active', checked as boolean)}
                        />
                        <Label htmlFor="is_active">Conta ativa</Label>
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
                            {processing ? 'Salvando...' : mode === 'edit' ? 'Atualizar' : 'Criar Conta'}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}