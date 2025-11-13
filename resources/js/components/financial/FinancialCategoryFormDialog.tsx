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

interface FinancialCategory {
    id: number;
    name: string;
    type: string;
    color: string;
    icon?: string;
    is_active: boolean;
    description?: string;
}

interface FinancialCategoryFormDialogProps {
    open: boolean;
    onClose: () => void;
    category?: FinancialCategory | null;
    mode: 'create' | 'edit';
}

export function FinancialCategoryFormDialog({
    open,
    onClose,
    category,
    mode
}: FinancialCategoryFormDialogProps) {
    const { data, setData, post, patch, processing, errors, reset } = useForm({
        name: category?.name || '',
        type: category?.type || 'EXPENSE',
        color: category?.color || '#3B82F6',
        icon: category?.icon || '',
        is_active: category?.is_active ?? true,
        description: category?.description || '',
    });

    // Update form data when category prop changes
    useEffect(() => {
        if (category && mode === 'edit') {
            setData({
                name: category.name,
                type: category.type,
                color: category.color,
                icon: category.icon || '',
                is_active: category.is_active,
                description: category.description || '',
            });
        } else if (mode === 'create') {
            setData({
                name: '',
                type: 'EXPENSE',
                color: '#3B82F6',
                icon: '',
                is_active: true,
                description: '',
            });
        }
    }, [category, mode, setData]);

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();

        const url = mode === 'edit' ? `/financial/categories/${category?.id}` : '/financial/categories';
        const method = mode === 'edit' ? patch : post;

        method(url, {
            onSuccess: () => {
                toast.success(mode === 'edit' ? 'Categoria atualizada com sucesso!' : 'Categoria criada com sucesso!');
                handleClose();
            },
            onError: () => {
                toast.error('Erro ao salvar categoria');
            },
        });
    };

    const handleClose = () => {
        reset();
        onClose();
    };

    const categoryTypes = [
        { value: 'INCOME', label: 'Receita' },
        { value: 'EXPENSE', label: 'Despesa' },
    ];

    const predefinedColors = [
        '#3B82F6', '#EF4444', '#10B981', '#F59E0B', '#8B5CF6',
        '#EC4899', '#14B8A6', '#F97316', '#6366F1', '#84CC16',
        '#06B6D4', '#F43F5E', '#8B5A2B', '#6B7280', '#374151'
    ];

    const commonIcons = [
        'DollarSign', 'CreditCard', 'Home', 'Car', 'Utensils', 'ShoppingBag',
        'Stethoscope', 'GraduationCap', 'Plane', 'Gift', 'Coffee', 'Fuel',
        'Zap', 'Wifi', 'Phone', 'GameController2', 'Book', 'Music'
    ];

    return (
        <Dialog open={open} onOpenChange={handleClose}>
            <DialogContent className="sm:max-w-[500px]">
                <DialogHeader>
                    <DialogTitle>
                        {mode === 'edit' ? 'Editar Categoria' : 'Nova Categoria'}
                    </DialogTitle>
                </DialogHeader>

                <form onSubmit={handleSubmit} className="space-y-4">
                    <div className="grid grid-cols-2 gap-4">
                        <div className="space-y-2">
                            <Label htmlFor="name">Nome da Categoria *</Label>
                            <Input
                                id="name"
                                value={data.name}
                                onChange={(e) => setData('name', e.target.value)}
                                placeholder="Ex: Alimentação"
                                required
                            />
                            {errors.name && (
                                <p className="text-sm text-red-600">{errors.name}</p>
                            )}
                        </div>

                        <div className="space-y-2">
                            <Label htmlFor="type">Tipo *</Label>
                            <Select value={data.type} onValueChange={(value) => setData('type', value)}>
                                <SelectTrigger>
                                    <SelectValue placeholder="Selecione o tipo" />
                                </SelectTrigger>
                                <SelectContent>
                                    {categoryTypes.map((type) => (
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

                    <div className="space-y-2">
                        <Label htmlFor="color">Cor *</Label>
                        <div className="flex items-center gap-2">
                            <Input
                                id="color"
                                type="color"
                                value={data.color}
                                onChange={(e) => setData('color', e.target.value)}
                                className="w-16 h-10 p-1 rounded cursor-pointer"
                            />
                            <Input
                                value={data.color}
                                onChange={(e) => setData('color', e.target.value)}
                                placeholder="#3B82F6"
                                className="flex-1"
                            />
                        </div>
                        <div className="flex flex-wrap gap-1 mt-2">
                            {predefinedColors.map((color) => (
                                <button
                                    key={color}
                                    type="button"
                                    className="w-6 h-6 rounded border-2 border-gray-300 hover:border-gray-500"
                                    style={{ backgroundColor: color }}
                                    onClick={() => setData('color', color)}
                                    title={color}
                                />
                            ))}
                        </div>
                        {errors.color && (
                            <p className="text-sm text-red-600">{errors.color}</p>
                        )}
                    </div>

                    <div className="space-y-2">
                        <Label htmlFor="icon">Ícone (opcional)</Label>
                        <Select value={data.icon || 'none'} onValueChange={(value) => setData('icon', value === 'none' ? '' : value)}>
                            <SelectTrigger>
                                <SelectValue placeholder="Selecione um ícone" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="none">Nenhum ícone</SelectItem>
                                {commonIcons.map((icon) => (
                                    <SelectItem key={icon} value={icon}>
                                        {icon}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                        {errors.icon && (
                            <p className="text-sm text-red-600">{errors.icon}</p>
                        )}
                    </div>

                    <div className="space-y-2">
                        <Label htmlFor="description">Descrição</Label>
                        <Textarea
                            id="description"
                            value={data.description}
                            onChange={(e) => setData('description', e.target.value)}
                            placeholder="Descrição opcional da categoria"
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
                        <Label htmlFor="is_active">Categoria ativa</Label>
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
                            {processing ? 'Salvando...' : mode === 'edit' ? 'Atualizar' : 'Criar Categoria'}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}