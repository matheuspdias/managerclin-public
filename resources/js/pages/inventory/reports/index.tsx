import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
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
    Tabs,
    TabsContent,
    TabsList,
    TabsTrigger,
} from '@/components/ui/tabs';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    FileText,
    Download,
    Package,
    TrendingUp,
    TrendingDown,
    DollarSign,
    BarChart3,
    PieChart,
    AlertTriangle,
    Calendar,
    Filter,
    Search
} from 'lucide-react';
import { useState } from 'react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Controle de Estoque',
        href: '/inventory',
    },
    {
        title: 'Relatórios',
        href: '/inventory/reports',
    },
];

interface Product {
    id: number;
    name: string;
    code?: string;
    current_stock: number;
    minimum_stock: number;
    maximum_stock?: number;
    unit: string;
    cost_price?: number;
    sale_price?: number;
    expiry_date?: string;
    batch_number?: string;
    storage_location?: string;
    category: {
        id: number;
        name: string;
        color: string;
    };
    supplier?: {
        id: number;
        name: string;
    };
}

interface ReportsIndexProps {
    stockReport: Product[];
}

export default function ReportsIndex({ stockReport }: ReportsIndexProps) {
    const [selectedCategory, setSelectedCategory] = useState('all');
    const [searchTerm, setSearchTerm] = useState('');
    const [sortBy, setSortBy] = useState('name');
    const [sortOrder, setSortOrder] = useState<'asc' | 'desc'>('asc');

    // Get unique categories
    const categories = Array.from(
        new Set(stockReport.map(product => JSON.stringify(product.category)))
    ).map(cat => JSON.parse(cat));

    // Filter and sort products
    const filteredProducts = stockReport
        .filter(product => {
            const matchesCategory = selectedCategory === 'all' || product.category.id.toString() === selectedCategory;
            const matchesSearch = !searchTerm ||
                product.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
                product.code?.toLowerCase().includes(searchTerm.toLowerCase()) ||
                product.category.name.toLowerCase().includes(searchTerm.toLowerCase());
            return matchesCategory && matchesSearch;
        })
        .sort((a, b) => {
            let aValue, bValue;

            switch (sortBy) {
                case 'name':
                    aValue = a.name.toLowerCase();
                    bValue = b.name.toLowerCase();
                    break;
                case 'category':
                    aValue = a.category.name.toLowerCase();
                    bValue = b.category.name.toLowerCase();
                    break;
                case 'stock':
                    aValue = a.current_stock;
                    bValue = b.current_stock;
                    break;
                case 'value':
                    aValue = (a.current_stock * (a.cost_price || 0));
                    bValue = (b.current_stock * (b.cost_price || 0));
                    break;
                default:
                    aValue = a.name.toLowerCase();
                    bValue = b.name.toLowerCase();
            }

            if (sortOrder === 'asc') {
                return aValue < bValue ? -1 : aValue > bValue ? 1 : 0;
            } else {
                return aValue > bValue ? -1 : aValue < bValue ? 1 : 0;
            }
        });

    // Calculate summary statistics
    const totalProducts = stockReport.length;
    const totalValue = stockReport.reduce((sum, product) =>
        sum + (product.current_stock * (product.cost_price || 0)), 0
    );
    const lowStockCount = stockReport.filter(product =>
        product.current_stock <= product.minimum_stock
    ).length;
    const outOfStockCount = stockReport.filter(product =>
        product.current_stock === 0
    ).length;

    // Category breakdown
    const categoryBreakdown = categories.map(category => {
        const categoryProducts = stockReport.filter(p => p.category.id === category.id);
        const categoryValue = categoryProducts.reduce((sum, product) =>
            sum + (product.current_stock * (product.cost_price || 0)), 0
        );
        const categoryLowStock = categoryProducts.filter(p => p.current_stock <= p.minimum_stock).length;

        return {
            ...category,
            productCount: categoryProducts.length,
            totalValue: categoryValue,
            lowStockCount: categoryLowStock,
            averageValue: categoryProducts.length > 0 ? categoryValue / categoryProducts.length : 0
        };
    });

    const formatCurrency = (value: number) => {
        return new Intl.NumberFormat('pt-BR', {
            style: 'currency',
            currency: 'BRL'
        }).format(value);
    };

    const getStockStatus = (product: Product) => {
        if (product.current_stock === 0) {
            return { label: 'Sem Estoque', variant: 'destructive' as const };
        }
        if (product.current_stock <= product.minimum_stock) {
            return { label: 'Estoque Baixo', variant: 'secondary' as const };
        }
        return { label: 'Normal', variant: 'default' as const };
    };

    const exportToCSV = () => {
        const headers = ['Produto', 'Categoria', 'Código', 'Estoque Atual', 'Estoque Mínimo', 'Unidade', 'Preço Custo', 'Valor Total', 'Localização'];
        const csvData = filteredProducts.map(product => [
            product.name,
            product.category.name,
            product.code || '',
            product.current_stock.toString(),
            product.minimum_stock.toString(),
            product.unit,
            (product.cost_price || 0).toString().replace('.', ','),
            ((product.current_stock * (product.cost_price || 0))).toString().replace('.', ','),
            product.storage_location || ''
        ]);

        const csvContent = [headers, ...csvData]
            .map(row => row.map(field => `"${field}"`).join(';'))
            .join('\n');

        const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        link.download = `relatorio-estoque-${new Date().toISOString().split('T')[0]}.csv`;
        link.click();
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Relatórios - Controle de Estoque" />

            <div className="space-y-6 p-6">
                {/* Header */}
                <div className="flex flex-col justify-between gap-4 md:flex-row md:items-center">
                    <div>
                        <h1 className="text-3xl font-bold tracking-tight text-foreground">Relatórios do Estoque</h1>
                        <p className="text-muted-foreground">
                            Análise detalhada do seu estoque e movimentações
                        </p>
                    </div>
                    <div className="flex gap-2">
                        <Button variant="outline" onClick={exportToCSV}>
                            <Download className="mr-2 h-4 w-4" />
                            Exportar CSV
                        </Button>
                        <Button variant="outline" asChild>
                            <Link href="/inventory/movements">
                                <BarChart3 className="mr-2 h-4 w-4" />
                                Movimentações
                            </Link>
                        </Button>
                    </div>
                </div>

                {/* Summary Cards */}
                <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                    <Card className="bg-card transition-all duration-200 hover:shadow-lg">
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium text-muted-foreground">Total de Produtos</CardTitle>
                            <Package className="h-4 w-4 text-blue-600" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-blue-600">{totalProducts}</div>
                            <p className="text-xs text-muted-foreground">Produtos cadastrados</p>
                        </CardContent>
                    </Card>

                    <Card className="bg-card transition-all duration-200 hover:shadow-lg">
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium text-muted-foreground">Valor Total</CardTitle>
                            <DollarSign className="h-4 w-4 text-green-600" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-green-600">{formatCurrency(totalValue)}</div>
                            <p className="text-xs text-muted-foreground">Valor do estoque</p>
                        </CardContent>
                    </Card>

                    <Card className="bg-card transition-all duration-200 hover:shadow-lg">
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium text-muted-foreground">Estoque Baixo</CardTitle>
                            <AlertTriangle className="h-4 w-4 text-orange-600" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-orange-600">{lowStockCount}</div>
                            <p className="text-xs text-muted-foreground">Produtos em falta</p>
                        </CardContent>
                    </Card>

                    <Card className="bg-card transition-all duration-200 hover:shadow-lg">
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium text-muted-foreground">Sem Estoque</CardTitle>
                            <TrendingDown className="h-4 w-4 text-red-600" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-red-600">{outOfStockCount}</div>
                            <p className="text-xs text-muted-foreground">Produtos zerados</p>
                        </CardContent>
                    </Card>
                </div>

                <Tabs defaultValue="products" className="w-full">
                    <TabsList className="grid w-full grid-cols-2">
                        <TabsTrigger value="products" className="flex items-center gap-2">
                            <Package className="h-4 w-4" />
                            Relatório de Produtos
                        </TabsTrigger>
                        <TabsTrigger value="categories" className="flex items-center gap-2">
                            <PieChart className="h-4 w-4" />
                            Análise por Categoria
                        </TabsTrigger>
                    </TabsList>

                    <TabsContent value="products" className="space-y-4">
                        {/* Filters */}
                        <Card>
                            <CardHeader>
                                <CardTitle className="flex items-center gap-2">
                                    <Filter className="h-5 w-5" />
                                    Filtros e Ordenação
                                </CardTitle>
                            </CardHeader>
                            <CardContent>
                                <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
                                    <div>
                                        <Label htmlFor="search">Buscar</Label>
                                        <Input
                                            id="search"
                                            placeholder="Nome ou código..."
                                            value={searchTerm}
                                            onChange={(e) => setSearchTerm(e.target.value)}
                                        />
                                    </div>
                                    <div>
                                        <Label htmlFor="category">Categoria</Label>
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
                                    <div>
                                        <Label htmlFor="sort">Ordenar por</Label>
                                        <Select value={sortBy} onValueChange={setSortBy}>
                                            <SelectTrigger>
                                                <SelectValue />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectItem value="name">Nome</SelectItem>
                                                <SelectItem value="category">Categoria</SelectItem>
                                                <SelectItem value="stock">Estoque</SelectItem>
                                                <SelectItem value="value">Valor</SelectItem>
                                            </SelectContent>
                                        </Select>
                                    </div>
                                    <div>
                                        <Label htmlFor="order">Ordem</Label>
                                        <Select value={sortOrder} onValueChange={(value: 'asc' | 'desc') => setSortOrder(value)}>
                                            <SelectTrigger>
                                                <SelectValue />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectItem value="asc">Crescente</SelectItem>
                                                <SelectItem value="desc">Decrescente</SelectItem>
                                            </SelectContent>
                                        </Select>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>

                        {/* Products Table */}
                        <Card>
                            <CardHeader>
                                <CardTitle className="flex items-center gap-2">
                                    <FileText className="h-5 w-5" />
                                    Relatório Detalhado ({filteredProducts.length} produtos)
                                </CardTitle>
                            </CardHeader>
                            <CardContent>
                                <Table>
                                    <TableHeader>
                                        <TableRow>
                                            <TableHead>Produto</TableHead>
                                            <TableHead>Categoria</TableHead>
                                            <TableHead>Estoque</TableHead>
                                            <TableHead>Status</TableHead>
                                            <TableHead>Preço Custo</TableHead>
                                            <TableHead>Valor Total</TableHead>
                                            <TableHead>Localização</TableHead>
                                        </TableRow>
                                    </TableHeader>
                                    <TableBody>
                                        {filteredProducts.map((product) => {
                                            const stockStatus = getStockStatus(product);
                                            const totalValue = product.current_stock * (product.cost_price || 0);

                                            return (
                                                <TableRow key={product.id}>
                                                    <TableCell>
                                                        <div>
                                                            <div className="font-medium">{product.name}</div>
                                                            {product.code && (
                                                                <div className="text-sm text-muted-foreground">
                                                                    Código: {product.code}
                                                                </div>
                                                            )}
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
                                                        <Badge variant={stockStatus.variant}>
                                                            {stockStatus.label}
                                                        </Badge>
                                                    </TableCell>
                                                    <TableCell>
                                                        <div className="font-medium">
                                                            {formatCurrency(product.cost_price || 0)}
                                                        </div>
                                                    </TableCell>
                                                    <TableCell>
                                                        <div className="font-medium text-green-600">
                                                            {formatCurrency(totalValue)}
                                                        </div>
                                                    </TableCell>
                                                    <TableCell>
                                                        <div className="text-muted-foreground">
                                                            {product.storage_location || '-'}
                                                        </div>
                                                    </TableCell>
                                                </TableRow>
                                            );
                                        })}
                                    </TableBody>
                                </Table>
                            </CardContent>
                        </Card>
                    </TabsContent>

                    <TabsContent value="categories" className="space-y-4">
                        {/* Category Analysis */}
                        <Card>
                            <CardHeader>
                                <CardTitle className="flex items-center gap-2">
                                    <PieChart className="h-5 w-5" />
                                    Análise por Categoria
                                </CardTitle>
                            </CardHeader>
                            <CardContent>
                                <Table>
                                    <TableHeader>
                                        <TableRow>
                                            <TableHead>Categoria</TableHead>
                                            <TableHead>Produtos</TableHead>
                                            <TableHead>Valor Total</TableHead>
                                            <TableHead>Valor Médio</TableHead>
                                            <TableHead>Estoque Baixo</TableHead>
                                            <TableHead>% do Total</TableHead>
                                        </TableRow>
                                    </TableHeader>
                                    <TableBody>
                                        {categoryBreakdown
                                            .sort((a, b) => b.totalValue - a.totalValue)
                                            .map((category) => {
                                                const percentage = totalValue > 0 ? (category.totalValue / totalValue) * 100 : 0;

                                                return (
                                                    <TableRow key={category.id}>
                                                        <TableCell>
                                                            <div className="flex items-center gap-3">
                                                                <div
                                                                    className="w-4 h-4 rounded-full"
                                                                    style={{ backgroundColor: category.color }}
                                                                />
                                                                <span className="font-medium">{category.name}</span>
                                                            </div>
                                                        </TableCell>
                                                        <TableCell>
                                                            <Badge variant="secondary">
                                                                {category.productCount} produtos
                                                            </Badge>
                                                        </TableCell>
                                                        <TableCell>
                                                            <div className="font-medium text-green-600">
                                                                {formatCurrency(category.totalValue)}
                                                            </div>
                                                        </TableCell>
                                                        <TableCell>
                                                            <div className="text-muted-foreground">
                                                                {formatCurrency(category.averageValue)}
                                                            </div>
                                                        </TableCell>
                                                        <TableCell>
                                                            {category.lowStockCount > 0 ? (
                                                                <Badge variant="secondary" className="text-orange-600">
                                                                    {category.lowStockCount} produtos
                                                                </Badge>
                                                            ) : (
                                                                <span className="text-muted-foreground">-</span>
                                                            )}
                                                        </TableCell>
                                                        <TableCell>
                                                            <div className="flex items-center gap-2">
                                                                <div className="text-sm font-medium">
                                                                    {percentage.toFixed(1)}%
                                                                </div>
                                                                <div className="w-20 bg-gray-200 rounded-full h-2">
                                                                    <div
                                                                        className="h-2 rounded-full"
                                                                        style={{
                                                                            width: `${percentage}%`,
                                                                            backgroundColor: category.color
                                                                        }}
                                                                    />
                                                                </div>
                                                            </div>
                                                        </TableCell>
                                                    </TableRow>
                                                );
                                            })}
                                    </TableBody>
                                </Table>
                            </CardContent>
                        </Card>
                    </TabsContent>
                </Tabs>
            </div>
        </AppLayout>
    );
}