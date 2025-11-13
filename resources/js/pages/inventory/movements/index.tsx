import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, router, useForm } from '@inertiajs/react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import {
    Plus,
    Search,
    Filter,
    Download,
    TrendingUp,
    TrendingDown,
    RefreshCw,
    ArrowRightLeft,
    RotateCcw,
    Calendar,
    User,
    Package
} from 'lucide-react';
import { useState } from 'react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Controle de Estoque',
        href: '/inventory',
    },
    {
        title: 'Movimentações',
        href: '/inventory/movements',
    },
];

interface Product {
    id: number;
    name: string;
    unit: string;
    category: {
        id: number;
        name: string;
        color: string;
    };
}

interface Movement {
    id: number;
    type: 'IN' | 'OUT' | 'ADJUSTMENT' | 'TRANSFER' | 'RETURN';
    quantity: number;
    unit_cost?: number;
    total_cost?: number;
    stock_before: number;
    stock_after: number;
    reason: string;
    notes?: string;
    document_number?: string;
    batch_number?: string;
    expiry_date?: string;
    movement_date: string;
    created_at: string;
    product: Product;
    user: {
        id: number;
        name: string;
    };
}

interface MovementsIndexProps {
    movements: Movement[];
    products: Product[];
    filters: {
        product_id?: number;
        start_date: string;
        end_date: string;
    };
}

const movementTypes = {
    IN: { label: 'Entrada', icon: TrendingUp, color: 'text-green-600', bg: 'bg-green-100' },
    OUT: { label: 'Saída', icon: TrendingDown, color: 'text-red-600', bg: 'bg-red-100' },
    ADJUSTMENT: { label: 'Ajuste', icon: RefreshCw, color: 'text-blue-600', bg: 'bg-blue-100' },
    TRANSFER: { label: 'Transferência', icon: ArrowRightLeft, color: 'text-purple-600', bg: 'bg-purple-100' },
    RETURN: { label: 'Devolução', icon: RotateCcw, color: 'text-orange-600', bg: 'bg-orange-100' },
};

export default function MovementsIndex({ movements, products, filters }: MovementsIndexProps) {
    const [isCreateDialogOpen, setIsCreateDialogOpen] = useState(false);
    const [selectedProduct, setSelectedProduct] = useState(filters.product_id?.toString() || 'all');
    const [startDate, setStartDate] = useState(filters.start_date);
    const [endDate, setEndDate] = useState(filters.end_date);

    const { data, setData, post, processing, errors, reset } = useForm({
        id_product: '',
        type: '',
        quantity: '',
        reason: '',
        unit_cost: '',
        document_number: '',
        notes: '',
        batch_number: '',
        expiry_date: '',
        movement_date: new Date().toISOString().split('T')[0],
    });

    const handleSearch = () => {
        router.get('/inventory/movements', {
            product_id: selectedProduct === 'all' ? '' : selectedProduct,
            start_date: startDate,
            end_date: endDate,
        }, {
            preserveState: true,
            preserveScroll: true,
        });
    };

    const handleCreateMovement = (e: React.FormEvent) => {
        e.preventDefault();
        post('/inventory/movements', {
            onSuccess: () => {
                setIsCreateDialogOpen(false);
                reset();
            }
        });
    };

    const formatCurrency = (value?: number) => {
        if (!value) return '-';
        return new Intl.NumberFormat('pt-BR', {
            style: 'currency',
            currency: 'BRL'
        }).format(value);
    };

    const formatDate = (dateString: string) => {
        return new Date(dateString).toLocaleDateString('pt-BR');
    };

    const formatDateTime = (dateString: string) => {
        return new Date(dateString).toLocaleString('pt-BR');
    };

    const getMovementTypeInfo = (type: Movement['type']) => {
        return movementTypes[type];
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Movimentações - Controle de Estoque" />

            <div className="space-y-6 p-6">
                {/* Header */}
                <div className="flex flex-col justify-between gap-4 md:flex-row md:items-center">
                    <div>
                        <h1 className="text-3xl font-bold tracking-tight text-foreground">Movimentações</h1>
                        <p className="text-muted-foreground">
                            Registre e acompanhe as movimentações do estoque
                        </p>
                    </div>
                    <Dialog open={isCreateDialogOpen} onOpenChange={setIsCreateDialogOpen}>
                        <DialogTrigger asChild>
                            <Button>
                                <Plus className="mr-2 h-4 w-4" />
                                Nova Movimentação
                            </Button>
                        </DialogTrigger>
                        <DialogContent className="max-w-2xl">
                            <DialogHeader>
                                <DialogTitle>Registrar Movimentação</DialogTitle>
                                <DialogDescription>
                                    Registre uma movimentação de estoque
                                </DialogDescription>
                            </DialogHeader>
                            <form onSubmit={handleCreateMovement} className="space-y-4">
                                <div className="grid grid-cols-2 gap-4">
                                    <div className="col-span-2">
                                        <Label htmlFor="product">Produto *</Label>
                                        <Select value={data.id_product} onValueChange={(value) => setData('id_product', value)}>
                                            <SelectTrigger>
                                                <SelectValue placeholder="Selecione o produto" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                {products.map((product) => (
                                                    <SelectItem key={product.id} value={product.id.toString()}>
                                                        <div className="flex items-center gap-2">
                                                            <div
                                                                className="w-3 h-3 rounded-full"
                                                                style={{ backgroundColor: product.category?.color || '#6B7280' }}
                                                            />
                                                            {product.name} ({product.unit})
                                                        </div>
                                                    </SelectItem>
                                                ))}
                                            </SelectContent>
                                        </Select>
                                        {errors.id_product && <p className="text-sm text-red-500">{errors.id_product}</p>}
                                    </div>

                                    <div>
                                        <Label htmlFor="type">Tipo de Movimentação *</Label>
                                        <Select value={data.type} onValueChange={(value) => setData('type', value)}>
                                            <SelectTrigger>
                                                <SelectValue placeholder="Selecione o tipo" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                {Object.entries(movementTypes).map(([key, type]) => {
                                                    const Icon = type.icon;
                                                    return (
                                                        <SelectItem key={key} value={key}>
                                                            <div className="flex items-center gap-2">
                                                                <Icon className={`h-4 w-4 ${type.color}`} />
                                                                {type.label}
                                                            </div>
                                                        </SelectItem>
                                                    );
                                                })}
                                            </SelectContent>
                                        </Select>
                                        {errors.type && <p className="text-sm text-red-500">{errors.type}</p>}
                                    </div>

                                    <div>
                                        <Label htmlFor="quantity">Quantidade *</Label>
                                        <Input
                                            id="quantity"
                                            type="number"
                                            min="0.01"
                                            step="0.01"
                                            value={data.quantity}
                                            onChange={(e) => setData('quantity', e.target.value)}
                                            placeholder="0"
                                            required
                                        />
                                        {errors.quantity && <p className="text-sm text-red-500">{errors.quantity}</p>}
                                    </div>

                                    <div>
                                        <Label htmlFor="unit_cost">Custo Unitário</Label>
                                        <Input
                                            id="unit_cost"
                                            type="number"
                                            min="0"
                                            step="0.01"
                                            value={data.unit_cost}
                                            onChange={(e) => setData('unit_cost', e.target.value)}
                                            placeholder="0,00"
                                        />
                                    </div>

                                    <div>
                                        <Label htmlFor="movement_date">Data da Movimentação *</Label>
                                        <Input
                                            id="movement_date"
                                            type="date"
                                            value={data.movement_date}
                                            onChange={(e) => setData('movement_date', e.target.value)}
                                            required
                                        />
                                    </div>

                                    <div>
                                        <Label htmlFor="document_number">Número do Documento</Label>
                                        <Input
                                            id="document_number"
                                            value={data.document_number}
                                            onChange={(e) => setData('document_number', e.target.value)}
                                            placeholder="NF, recibo, etc."
                                        />
                                    </div>

                                    <div>
                                        <Label htmlFor="batch_number">Número do Lote</Label>
                                        <Input
                                            id="batch_number"
                                            value={data.batch_number}
                                            onChange={(e) => setData('batch_number', e.target.value)}
                                            placeholder="Lote"
                                        />
                                    </div>

                                    <div>
                                        <Label htmlFor="expiry_date">Data de Validade</Label>
                                        <Input
                                            id="expiry_date"
                                            type="date"
                                            value={data.expiry_date}
                                            onChange={(e) => setData('expiry_date', e.target.value)}
                                        />
                                    </div>

                                    <div className="col-span-2">
                                        <Label htmlFor="reason">Motivo *</Label>
                                        <Input
                                            id="reason"
                                            value={data.reason}
                                            onChange={(e) => setData('reason', e.target.value)}
                                            placeholder="Compra, venda, ajuste, etc."
                                            required
                                        />
                                        {errors.reason && <p className="text-sm text-red-500">{errors.reason}</p>}
                                    </div>

                                    <div className="col-span-2">
                                        <Label htmlFor="notes">Observações</Label>
                                        <Textarea
                                            id="notes"
                                            value={data.notes}
                                            onChange={(e) => setData('notes', e.target.value)}
                                            placeholder="Observações adicionais"
                                            rows={3}
                                        />
                                    </div>
                                </div>

                                <div className="flex justify-end gap-2">
                                    <Button
                                        type="button"
                                        variant="outline"
                                        onClick={() => setIsCreateDialogOpen(false)}
                                    >
                                        Cancelar
                                    </Button>
                                    <Button type="submit" disabled={processing}>
                                        {processing ? 'Registrando...' : 'Registrar Movimentação'}
                                    </Button>
                                </div>
                            </form>
                        </DialogContent>
                    </Dialog>
                </div>

                {/* Filters */}
                <Card>
                    <CardHeader>
                        <CardTitle className="flex items-center gap-2">
                            <Filter className="h-5 w-5" />
                            Filtros
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div>
                                <Label htmlFor="filter_product">Produto</Label>
                                <Select value={selectedProduct} onValueChange={setSelectedProduct}>
                                    <SelectTrigger>
                                        <SelectValue placeholder="Todos os produtos" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="all">Todos os produtos</SelectItem>
                                        {products.map((product) => (
                                            <SelectItem key={product.id} value={product.id.toString()}>
                                                <div className="flex items-center gap-2">
                                                    <div
                                                        className="w-3 h-3 rounded-full"
                                                        style={{ backgroundColor: product.category?.color || '#6B7280' }}
                                                    />
                                                    {product.name}
                                                </div>
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </div>
                            <div>
                                <Label htmlFor="start_date">Data Inicial</Label>
                                <Input
                                    id="start_date"
                                    type="date"
                                    value={startDate}
                                    onChange={(e) => setStartDate(e.target.value)}
                                />
                            </div>
                            <div>
                                <Label htmlFor="end_date">Data Final</Label>
                                <Input
                                    id="end_date"
                                    type="date"
                                    value={endDate}
                                    onChange={(e) => setEndDate(e.target.value)}
                                />
                            </div>
                            <div className="flex items-end">
                                <Button onClick={handleSearch} className="w-full">
                                    <Search className="mr-2 h-4 w-4" />
                                    Filtrar
                                </Button>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                {/* Summary Cards */}
                <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-5">
                    {Object.entries(movementTypes).map(([type, info]) => {
                        const Icon = info.icon;
                        const count = movements.filter(m => m.type === type).length;
                        const total = movements
                            .filter(m => m.type === type)
                            .reduce((sum, m) => sum + (Number(m.quantity) || 0), 0);

                        return (
                            <Card key={type} className="bg-card transition-all duration-200 hover:shadow-lg">
                                <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                    <CardTitle className="text-sm font-medium">{info.label}</CardTitle>
                                    <Icon className={`h-4 w-4 ${info.color}`} />
                                </CardHeader>
                                <CardContent>
                                    <div className={`text-2xl font-bold ${info.color}`}>{count}</div>
                                    <p className="text-xs text-muted-foreground">
                                        Total: {(Number(total) || 0).toFixed(2)} unidades
                                    </p>
                                </CardContent>
                            </Card>
                        );
                    })}
                </div>

                {/* Movements Table */}
                <Card>
                    <CardHeader>
                        <CardTitle className="flex items-center gap-2">
                            <Package className="h-5 w-5" />
                            Movimentações ({movements.length})
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Data/Hora</TableHead>
                                    <TableHead>Produto</TableHead>
                                    <TableHead>Tipo</TableHead>
                                    <TableHead>Quantidade</TableHead>
                                    <TableHead>Estoque</TableHead>
                                    <TableHead>Motivo</TableHead>
                                    <TableHead>Usuário</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {movements.map((movement) => {
                                    const typeInfo = getMovementTypeInfo(movement.type);
                                    const Icon = typeInfo.icon;

                                    return (
                                        <TableRow key={movement.id}>
                                            <TableCell>
                                                <div>
                                                    <div className="font-medium">
                                                        {formatDate(movement.movement_date)}
                                                    </div>
                                                    <div className="text-sm text-muted-foreground">
                                                        {formatDateTime(movement.created_at)}
                                                    </div>
                                                </div>
                                            </TableCell>
                                            <TableCell>
                                                <div className="flex items-center gap-2">
                                                    <div
                                                        className="w-3 h-3 rounded-full"
                                                        style={{ backgroundColor: movement.product.category?.color || '#6B7280' }}
                                                    />
                                                    <div>
                                                        <div className="font-medium">{movement.product.name}</div>
                                                        <div className="text-sm text-muted-foreground">
                                                            {movement.product.category?.name || 'Sem categoria'}
                                                        </div>
                                                    </div>
                                                </div>
                                            </TableCell>
                                            <TableCell>
                                                <Badge variant="secondary" className={`${typeInfo.bg} ${typeInfo.color}`}>
                                                    <Icon className="mr-1 h-3 w-3" />
                                                    {typeInfo.label}
                                                </Badge>
                                            </TableCell>
                                            <TableCell>
                                                <div>
                                                    <div className="font-medium">
                                                        {movement.type === 'OUT' ? '-' : '+'}{movement.quantity} {movement.product.unit}
                                                    </div>
                                                    {movement.unit_cost && (
                                                        <div className="text-sm text-muted-foreground">
                                                            {formatCurrency(movement.unit_cost)} por {movement.product.unit}
                                                        </div>
                                                    )}
                                                </div>
                                            </TableCell>
                                            <TableCell>
                                                <div className="text-sm">
                                                    <div>Antes: {movement.stock_before} {movement.product.unit}</div>
                                                    <div>Depois: {movement.stock_after} {movement.product.unit}</div>
                                                </div>
                                            </TableCell>
                                            <TableCell>
                                                <div>
                                                    <div className="font-medium">{movement.reason}</div>
                                                    {movement.document_number && (
                                                        <div className="text-sm text-muted-foreground">
                                                            Doc: {movement.document_number}
                                                        </div>
                                                    )}
                                                    {movement.batch_number && (
                                                        <div className="text-sm text-muted-foreground">
                                                            Lote: {movement.batch_number}
                                                        </div>
                                                    )}
                                                </div>
                                            </TableCell>
                                            <TableCell>
                                                <div className="flex items-center gap-2">
                                                    <User className="h-4 w-4 text-muted-foreground" />
                                                    <span className="text-sm">{movement.user.name}</span>
                                                </div>
                                            </TableCell>
                                        </TableRow>
                                    );
                                })}
                            </TableBody>
                        </Table>

                        {movements.length === 0 && (
                            <div className="text-center py-8 text-muted-foreground">
                                Nenhuma movimentação encontrada para os filtros selecionados
                            </div>
                        )}
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}