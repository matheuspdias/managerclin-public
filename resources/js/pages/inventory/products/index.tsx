import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router, useForm } from '@inertiajs/react';
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
import { Checkbox } from '@/components/ui/checkbox';
import {
    Plus,
    Search,
    Edit,
    Trash2,
    Package,
    AlertTriangle,
    Filter,
    Download,
    Eye
} from 'lucide-react';
import { useState, useEffect } from 'react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Controle de Estoque',
        href: '/inventory',
    },
    {
        title: 'Produtos',
        href: '/inventory/products',
    },
];

interface Category {
    id: number;
    name: string;
    color: string;
}

interface Product {
    id: number;
    name: string;
    code?: string;
    barcode?: string;
    description?: string;
    unit: string;
    current_stock: number;
    minimum_stock: number;
    maximum_stock?: number;
    cost_price?: number;
    sale_price?: number;
    expiry_date?: string;
    batch_number?: string;
    storage_location?: string;
    requires_prescription: boolean;
    controlled_substance: boolean;
    active: boolean;
    category: Category;
    supplier?: {
        id: number;
        name: string;
    };
    created_at: string;
    updated_at: string;
}

interface ProductsIndexProps {
    products: {
        data: Product[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };
    categories: Category[];
    filters: {
        search?: string;
        category_id?: number;
        supplier_id?: number;
    };
}

export default function ProductsIndex({ products, categories, filters }: ProductsIndexProps) {
    const [isCreateDialogOpen, setIsCreateDialogOpen] = useState(false);
    const [isEditDialogOpen, setIsEditDialogOpen] = useState(false);
    const [editingProduct, setEditingProduct] = useState<Product | null>(null);
    const [searchTerm, setSearchTerm] = useState(filters.search || '');
    const [selectedCategory, setSelectedCategory] = useState(filters.category_id?.toString() || 'all');

    const { data, setData, post, patch, delete: destroy, processing, errors, reset } = useForm({
        name: '',
        id_category: '',
        code: '',
        barcode: '',
        description: '',
        unit: '',
        minimum_stock: '',
        maximum_stock: '',
        cost_price: '',
        sale_price: '',
        expiry_date: '',
        batch_number: '',
        storage_location: '',
        requires_prescription: false,
        controlled_substance: false,
    });

    const handleSearch = () => {
        router.get('/inventory/products', {
            search: searchTerm,
            category_id: selectedCategory === 'all' ? '' : selectedCategory,
        }, {
            preserveState: true,
            preserveScroll: true,
        });
    };

    const handleCreateProduct = (e: React.FormEvent) => {
        e.preventDefault();
        post('/inventory/products', {
            onSuccess: () => {
                setIsCreateDialogOpen(false);
                reset();
            }
        });
    };

    const handleEditProduct = (e: React.FormEvent) => {
        e.preventDefault();
        if (editingProduct) {
            patch(`/inventory/products/${editingProduct.id}`, {
                onSuccess: () => {
                    setIsEditDialogOpen(false);
                    setEditingProduct(null);
                    reset();
                }
            });
        }
    };

    const handleDeleteProduct = (product: Product) => {
        if (confirm(`Tem certeza que deseja excluir o produto "${product.name}"?`)) {
            destroy(`/inventory/products/${product.id}`);
        }
    };

    const openEditDialog = (product: Product) => {
        setEditingProduct(product);
        setData({
            name: product.name,
            id_category: product.category.id.toString(),
            code: product.code || '',
            barcode: product.barcode || '',
            description: product.description || '',
            unit: product.unit,
            minimum_stock: product.minimum_stock.toString(),
            maximum_stock: product.maximum_stock?.toString() || '',
            cost_price: product.cost_price?.toString() || '',
            sale_price: product.sale_price?.toString() || '',
            expiry_date: product.expiry_date || '',
            batch_number: product.batch_number || '',
            storage_location: product.storage_location || '',
            requires_prescription: product.requires_prescription,
            controlled_substance: product.controlled_substance,
        });
        setIsEditDialogOpen(true);
    };

    const getStockStatus = (product: Product) => {
        if (product.current_stock <= 0) {
            return { label: 'Sem Estoque', variant: 'destructive' as const };
        }
        if (product.current_stock <= product.minimum_stock) {
            return { label: 'Estoque Baixo', variant: 'secondary' as const };
        }
        return { label: 'Normal', variant: 'default' as const };
    };

    const formatCurrency = (value?: number) => {
        if (!value) return '-';
        return new Intl.NumberFormat('pt-BR', {
            style: 'currency',
            currency: 'BRL'
        }).format(value);
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Produtos - Controle de Estoque" />

            <div className="space-y-6 p-6">
                {/* Header */}
                <div className="flex flex-col justify-between gap-4 md:flex-row md:items-center">
                    <div>
                        <h1 className="text-3xl font-bold tracking-tight text-foreground">Produtos</h1>
                        <p className="text-muted-foreground">
                            Gerencie os produtos do seu estoque
                        </p>
                    </div>
                    <Dialog open={isCreateDialogOpen} onOpenChange={setIsCreateDialogOpen}>
                        <DialogTrigger asChild>
                            <Button>
                                <Plus className="mr-2 h-4 w-4" />
                                Novo Produto
                            </Button>
                        </DialogTrigger>
                        <DialogContent className="max-w-2xl max-h-[90vh] overflow-y-auto">
                            <DialogHeader>
                                <DialogTitle>Criar Novo Produto</DialogTitle>
                                <DialogDescription>
                                    Preencha as informações do produto
                                </DialogDescription>
                            </DialogHeader>
                            <form onSubmit={handleCreateProduct} className="space-y-4">
                                <div className="grid grid-cols-2 gap-4">
                                    <div className="col-span-2">
                                        <Label htmlFor="name">Nome do Produto *</Label>
                                        <Input
                                            id="name"
                                            value={data.name}
                                            onChange={(e) => setData('name', e.target.value)}
                                            placeholder="Nome do produto"
                                            required
                                        />
                                        {errors.name && <p className="text-sm text-red-500">{errors.name}</p>}
                                    </div>

                                    <div>
                                        <Label htmlFor="category">Categoria *</Label>
                                        <Select value={data.id_category} onValueChange={(value) => setData('id_category', value)}>
                                            <SelectTrigger>
                                                <SelectValue placeholder="Selecione a categoria" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                {categories.map((category) => (
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
                                        {errors.id_category && <p className="text-sm text-red-500">{errors.id_category}</p>}
                                    </div>

                                    <div>
                                        <Label htmlFor="unit">Unidade *</Label>
                                        <Select value={data.unit} onValueChange={(value) => setData('unit', value)}>
                                            <SelectTrigger>
                                                <SelectValue placeholder="Selecione a unidade" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectItem value="un">Unidade (un)</SelectItem>
                                                <SelectItem value="kg">Quilograma (kg)</SelectItem>
                                                <SelectItem value="g">Grama (g)</SelectItem>
                                                <SelectItem value="l">Litro (l)</SelectItem>
                                                <SelectItem value="ml">Mililitro (ml)</SelectItem>
                                                <SelectItem value="cx">Caixa (cx)</SelectItem>
                                                <SelectItem value="pct">Pacote (pct)</SelectItem>
                                                <SelectItem value="fr">Frasco (fr)</SelectItem>
                                            </SelectContent>
                                        </Select>
                                        {errors.unit && <p className="text-sm text-red-500">{errors.unit}</p>}
                                    </div>

                                    <div>
                                        <Label htmlFor="code">Código</Label>
                                        <Input
                                            id="code"
                                            value={data.code}
                                            onChange={(e) => setData('code', e.target.value)}
                                            placeholder="Código do produto"
                                        />
                                    </div>

                                    <div>
                                        <Label htmlFor="barcode">Código de Barras</Label>
                                        <Input
                                            id="barcode"
                                            value={data.barcode}
                                            onChange={(e) => setData('barcode', e.target.value)}
                                            placeholder="Código de barras"
                                        />
                                    </div>

                                    <div>
                                        <Label htmlFor="minimum_stock">Estoque Mínimo *</Label>
                                        <Input
                                            id="minimum_stock"
                                            type="number"
                                            min="0"
                                            step="0.01"
                                            value={data.minimum_stock}
                                            onChange={(e) => setData('minimum_stock', e.target.value)}
                                            placeholder="0"
                                            required
                                        />
                                    </div>

                                    <div>
                                        <Label htmlFor="maximum_stock">Estoque Máximo</Label>
                                        <Input
                                            id="maximum_stock"
                                            type="number"
                                            min="0"
                                            step="0.01"
                                            value={data.maximum_stock}
                                            onChange={(e) => setData('maximum_stock', e.target.value)}
                                            placeholder="0"
                                        />
                                    </div>

                                    <div>
                                        <Label htmlFor="cost_price">Preço de Custo</Label>
                                        <Input
                                            id="cost_price"
                                            type="number"
                                            min="0"
                                            step="0.01"
                                            value={data.cost_price}
                                            onChange={(e) => setData('cost_price', e.target.value)}
                                            placeholder="0,00"
                                        />
                                    </div>

                                    <div>
                                        <Label htmlFor="sale_price">Preço de Venda</Label>
                                        <Input
                                            id="sale_price"
                                            type="number"
                                            min="0"
                                            step="0.01"
                                            value={data.sale_price}
                                            onChange={(e) => setData('sale_price', e.target.value)}
                                            placeholder="0,00"
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

                                    <div>
                                        <Label htmlFor="batch_number">Número do Lote</Label>
                                        <Input
                                            id="batch_number"
                                            value={data.batch_number}
                                            onChange={(e) => setData('batch_number', e.target.value)}
                                            placeholder="Lote"
                                        />
                                    </div>

                                    <div className="col-span-2">
                                        <Label htmlFor="storage_location">Local de Armazenamento</Label>
                                        <Input
                                            id="storage_location"
                                            value={data.storage_location}
                                            onChange={(e) => setData('storage_location', e.target.value)}
                                            placeholder="Prateleira, gaveta, etc."
                                        />
                                    </div>

                                    <div className="col-span-2">
                                        <Label htmlFor="description">Descrição</Label>
                                        <Textarea
                                            id="description"
                                            value={data.description}
                                            onChange={(e) => setData('description', e.target.value)}
                                            placeholder="Descrição do produto"
                                            rows={3}
                                        />
                                    </div>

                                    <div className="col-span-2 space-y-3">
                                        <div className="flex items-center space-x-2">
                                            <Checkbox
                                                id="requires_prescription"
                                                checked={data.requires_prescription}
                                                onCheckedChange={(checked) => setData('requires_prescription', !!checked)}
                                            />
                                            <Label htmlFor="requires_prescription">Requer Receita Médica</Label>
                                        </div>

                                        <div className="flex items-center space-x-2">
                                            <Checkbox
                                                id="controlled_substance"
                                                checked={data.controlled_substance}
                                                onCheckedChange={(checked) => setData('controlled_substance', !!checked)}
                                            />
                                            <Label htmlFor="controlled_substance">Substância Controlada</Label>
                                        </div>
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
                                        {processing ? 'Criando...' : 'Criar Produto'}
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
                        <div className="flex gap-4">
                            <div className="flex-1">
                                <Input
                                    placeholder="Buscar produtos..."
                                    value={searchTerm}
                                    onChange={(e) => setSearchTerm(e.target.value)}
                                    onKeyPress={(e) => e.key === 'Enter' && handleSearch()}
                                />
                            </div>
                            <div className="w-48">
                                <Select value={selectedCategory} onValueChange={setSelectedCategory}>
                                    <SelectTrigger>
                                        <SelectValue placeholder="Todas as categorias" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="all">Todas as categorias</SelectItem>
                                        {categories.map((category) => (
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
                            </div>
                            <Button onClick={handleSearch}>
                                <Search className="mr-2 h-4 w-4" />
                                Buscar
                            </Button>
                        </div>
                    </CardContent>
                </Card>

                {/* Products Table */}
                <Card>
                    <CardHeader>
                        <div className="flex items-center justify-between">
                            <CardTitle className="flex items-center gap-2">
                                <Package className="h-5 w-5" />
                                Produtos ({products.total})
                            </CardTitle>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Produto</TableHead>
                                    <TableHead>Categoria</TableHead>
                                    <TableHead>Estoque</TableHead>
                                    <TableHead>Status</TableHead>
                                    <TableHead>Preços</TableHead>
                                    <TableHead>Ações</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {products.data.map((product) => (
                                    <TableRow key={product.id}>
                                        <TableCell>
                                            <div>
                                                <div className="font-medium">{product.name}</div>
                                                <div className="text-sm text-muted-foreground">
                                                    {product.code && `Código: ${product.code}`}
                                                    {product.code && product.barcode && ' • '}
                                                    {product.barcode && `Barras: ${product.barcode}`}
                                                </div>
                                            </div>
                                        </TableCell>
                                        <TableCell>
                                            <div className="flex items-center gap-2">
                                                <div
                                                    className="w-3 h-3 rounded-full"
                                                    style={{ backgroundColor: product.category.color }}
                                                />
                                                {product.category.name}
                                            </div>
                                        </TableCell>
                                        <TableCell>
                                            <div>
                                                <div className="font-medium">
                                                    {product.current_stock} {product.unit}
                                                </div>
                                                <div className="text-sm text-muted-foreground">
                                                    Mín: {product.minimum_stock} {product.unit}
                                                </div>
                                            </div>
                                        </TableCell>
                                        <TableCell>
                                            <Badge variant={getStockStatus(product).variant}>
                                                {getStockStatus(product).label}
                                            </Badge>
                                            {product.current_stock <= product.minimum_stock && (
                                                <AlertTriangle className="inline-block ml-2 h-4 w-4 text-orange-500" />
                                            )}
                                        </TableCell>
                                        <TableCell>
                                            <div className="text-sm">
                                                <div>Custo: {formatCurrency(product.cost_price)}</div>
                                                <div>Venda: {formatCurrency(product.sale_price)}</div>
                                            </div>
                                        </TableCell>
                                        <TableCell>
                                            <div className="flex gap-2">
                                                <Button
                                                    variant="outline"
                                                    size="sm"
                                                    onClick={() => openEditDialog(product)}
                                                >
                                                    <Edit className="h-4 w-4" />
                                                </Button>
                                                <Button
                                                    variant="outline"
                                                    size="sm"
                                                    onClick={() => handleDeleteProduct(product)}
                                                >
                                                    <Trash2 className="h-4 w-4" />
                                                </Button>
                                            </div>
                                        </TableCell>
                                    </TableRow>
                                ))}
                            </TableBody>
                        </Table>
                    </CardContent>
                </Card>

                {/* Edit Dialog */}
                <Dialog open={isEditDialogOpen} onOpenChange={setIsEditDialogOpen}>
                    <DialogContent className="max-w-2xl max-h-[90vh] overflow-y-auto">
                        <DialogHeader>
                            <DialogTitle>Editar Produto</DialogTitle>
                            <DialogDescription>
                                Atualize as informações do produto
                            </DialogDescription>
                        </DialogHeader>
                        <form onSubmit={handleEditProduct} className="space-y-4">
                            {/* Same form fields as create dialog */}
                            <div className="grid grid-cols-2 gap-4">
                                <div className="col-span-2">
                                    <Label htmlFor="edit_name">Nome do Produto *</Label>
                                    <Input
                                        id="edit_name"
                                        value={data.name}
                                        onChange={(e) => setData('name', e.target.value)}
                                        placeholder="Nome do produto"
                                        required
                                    />
                                    {errors.name && <p className="text-sm text-red-500">{errors.name}</p>}
                                </div>

                                <div>
                                    <Label htmlFor="edit_category">Categoria *</Label>
                                    <Select value={data.id_category} onValueChange={(value) => setData('id_category', value)}>
                                        <SelectTrigger>
                                            <SelectValue placeholder="Selecione a categoria" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            {categories.map((category) => (
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
                                    {errors.id_category && <p className="text-sm text-red-500">{errors.id_category}</p>}
                                </div>

                                <div>
                                    <Label htmlFor="edit_unit">Unidade *</Label>
                                    <Select value={data.unit} onValueChange={(value) => setData('unit', value)}>
                                        <SelectTrigger>
                                            <SelectValue placeholder="Selecione a unidade" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="un">Unidade (un)</SelectItem>
                                            <SelectItem value="kg">Quilograma (kg)</SelectItem>
                                            <SelectItem value="g">Grama (g)</SelectItem>
                                            <SelectItem value="l">Litro (l)</SelectItem>
                                            <SelectItem value="ml">Mililitro (ml)</SelectItem>
                                            <SelectItem value="cx">Caixa (cx)</SelectItem>
                                            <SelectItem value="pct">Pacote (pct)</SelectItem>
                                            <SelectItem value="fr">Frasco (fr)</SelectItem>
                                        </SelectContent>
                                    </Select>
                                    {errors.unit && <p className="text-sm text-red-500">{errors.unit}</p>}
                                </div>

                                <div>
                                    <Label htmlFor="edit_code">Código</Label>
                                    <Input
                                        id="edit_code"
                                        value={data.code}
                                        onChange={(e) => setData('code', e.target.value)}
                                        placeholder="Código do produto"
                                    />
                                </div>

                                <div>
                                    <Label htmlFor="edit_barcode">Código de Barras</Label>
                                    <Input
                                        id="edit_barcode"
                                        value={data.barcode}
                                        onChange={(e) => setData('barcode', e.target.value)}
                                        placeholder="Código de barras"
                                    />
                                </div>

                                <div>
                                    <Label htmlFor="edit_minimum_stock">Estoque Mínimo *</Label>
                                    <Input
                                        id="edit_minimum_stock"
                                        type="number"
                                        min="0"
                                        step="0.01"
                                        value={data.minimum_stock}
                                        onChange={(e) => setData('minimum_stock', e.target.value)}
                                        placeholder="0"
                                        required
                                    />
                                </div>

                                <div>
                                    <Label htmlFor="edit_maximum_stock">Estoque Máximo</Label>
                                    <Input
                                        id="edit_maximum_stock"
                                        type="number"
                                        min="0"
                                        step="0.01"
                                        value={data.maximum_stock}
                                        onChange={(e) => setData('maximum_stock', e.target.value)}
                                        placeholder="0"
                                    />
                                </div>

                                <div>
                                    <Label htmlFor="edit_cost_price">Preço de Custo</Label>
                                    <Input
                                        id="edit_cost_price"
                                        type="number"
                                        min="0"
                                        step="0.01"
                                        value={data.cost_price}
                                        onChange={(e) => setData('cost_price', e.target.value)}
                                        placeholder="0,00"
                                    />
                                </div>

                                <div>
                                    <Label htmlFor="edit_sale_price">Preço de Venda</Label>
                                    <Input
                                        id="edit_sale_price"
                                        type="number"
                                        min="0"
                                        step="0.01"
                                        value={data.sale_price}
                                        onChange={(e) => setData('sale_price', e.target.value)}
                                        placeholder="0,00"
                                    />
                                </div>

                                <div>
                                    <Label htmlFor="edit_expiry_date">Data de Validade</Label>
                                    <Input
                                        id="edit_expiry_date"
                                        type="date"
                                        value={data.expiry_date}
                                        onChange={(e) => setData('expiry_date', e.target.value)}
                                    />
                                </div>

                                <div>
                                    <Label htmlFor="edit_batch_number">Número do Lote</Label>
                                    <Input
                                        id="edit_batch_number"
                                        value={data.batch_number}
                                        onChange={(e) => setData('batch_number', e.target.value)}
                                        placeholder="Lote"
                                    />
                                </div>

                                <div className="col-span-2">
                                    <Label htmlFor="edit_storage_location">Local de Armazenamento</Label>
                                    <Input
                                        id="edit_storage_location"
                                        value={data.storage_location}
                                        onChange={(e) => setData('storage_location', e.target.value)}
                                        placeholder="Prateleira, gaveta, etc."
                                    />
                                </div>

                                <div className="col-span-2">
                                    <Label htmlFor="edit_description">Descrição</Label>
                                    <Textarea
                                        id="edit_description"
                                        value={data.description}
                                        onChange={(e) => setData('description', e.target.value)}
                                        placeholder="Descrição do produto"
                                        rows={3}
                                    />
                                </div>

                                <div className="col-span-2 space-y-3">
                                    <div className="flex items-center space-x-2">
                                        <Checkbox
                                            id="edit_requires_prescription"
                                            checked={data.requires_prescription}
                                            onCheckedChange={(checked) => setData('requires_prescription', !!checked)}
                                        />
                                        <Label htmlFor="edit_requires_prescription">Requer Receita Médica</Label>
                                    </div>

                                    <div className="flex items-center space-x-2">
                                        <Checkbox
                                            id="edit_controlled_substance"
                                            checked={data.controlled_substance}
                                            onCheckedChange={(checked) => setData('controlled_substance', !!checked)}
                                        />
                                        <Label htmlFor="edit_controlled_substance">Substância Controlada</Label>
                                    </div>
                                </div>
                            </div>

                            <div className="flex justify-end gap-2">
                                <Button
                                    type="button"
                                    variant="outline"
                                    onClick={() => {
                                        setIsEditDialogOpen(false);
                                        setEditingProduct(null);
                                        reset();
                                    }}
                                >
                                    Cancelar
                                </Button>
                                <Button type="submit" disabled={processing}>
                                    {processing ? 'Salvando...' : 'Salvar Alterações'}
                                </Button>
                            </div>
                        </form>
                    </DialogContent>
                </Dialog>
            </div>
        </AppLayout>
    );
}