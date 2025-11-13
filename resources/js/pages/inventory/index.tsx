import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import {
    Package,
    AlertTriangle,
    Calendar,
    TrendingUp,
    Eye,
    List,
    Tags,
    FileText
} from 'lucide-react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Controle de Estoque',
        href: '/inventory',
    },
];

interface InventoryIndexProps {
    dashboardData: {
        total_products: number;
        total_categories: number;
        low_stock_count: number;
        expired_count: number;
        expiring_soon_count: number;
        total_stock_value: number;
        low_stock_products: Array<{
            id: number;
            name: string;
            current_stock: number;
            minimum_stock: number;
            unit: string;
            category: {
                name: string;
                color: string;
            };
        }>;
        expiring_soon_products: Array<{
            id: number;
            name: string;
            expiry_date: string;
            batch_number: string;
            category: {
                name: string;
                color: string;
            };
        }>;
    };
}

export default function InventoryIndex({ dashboardData }: InventoryIndexProps) {
    const {
        total_products,
        total_categories,
        low_stock_count,
        expired_count,
        total_stock_value,
        low_stock_products,
        expiring_soon_products
    } = dashboardData;

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Controle de Estoque" />

            <div className="space-y-6 p-6">
                {/* Header */}
                <div className="flex flex-col justify-between gap-4 md:flex-row md:items-center">
                    <div>
                        <h1 className="text-3xl font-bold tracking-tight text-foreground">Controle de Estoque</h1>
                        <p className="text-muted-foreground">
                            Gerencie produtos, movimentações e monitore alertas do estoque
                        </p>
                    </div>
                    <div className="flex gap-2">
                        <Button asChild>
                            <Link href="/inventory/products">
                                <Package className="mr-2 h-4 w-4" />
                                Produtos
                            </Link>
                        </Button>
                        <Button variant="outline" asChild>
                            <Link href="/inventory/movements">
                                <List className="mr-2 h-4 w-4" />
                                Movimentações
                            </Link>
                        </Button>
                    </div>
                </div>

                {/* Cards de Resumo */}
                <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                    <Card className="bg-card transition-all duration-200 hover:shadow-lg">
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-3">
                            <CardTitle className="text-sm font-medium text-muted-foreground">Total de Produtos</CardTitle>
                            <Package className="h-5 w-5 text-blue-600" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-3xl font-bold text-blue-600">{total_products}</div>
                            <p className="text-xs text-muted-foreground mt-1">Produtos cadastrados</p>
                        </CardContent>
                    </Card>

                    <Card className="bg-card transition-all duration-200 hover:shadow-lg">
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-3">
                            <CardTitle className="text-sm font-medium text-muted-foreground">Categorias</CardTitle>
                            <Tags className="h-5 w-5 text-purple-600" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-3xl font-bold text-purple-600">{total_categories}</div>
                            <p className="text-xs text-muted-foreground mt-1">Categorias ativas</p>
                        </CardContent>
                    </Card>

                    <Card className="bg-card transition-all duration-200 hover:shadow-lg">
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-3">
                            <CardTitle className="text-sm font-medium text-muted-foreground">Estoque Baixo</CardTitle>
                            <AlertTriangle className="h-5 w-5 text-orange-600" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-3xl font-bold text-orange-600">{low_stock_count}</div>
                            <p className="text-xs text-muted-foreground mt-1">Produtos em falta</p>
                        </CardContent>
                    </Card>

                    <Card className="bg-card transition-all duration-200 hover:shadow-lg">
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-3">
                            <CardTitle className="text-sm font-medium text-muted-foreground">Valor do Estoque</CardTitle>
                            <TrendingUp className="h-5 w-5 text-green-600" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-3xl font-bold text-green-600">
                                {new Intl.NumberFormat('pt-BR', {
                                    style: 'currency',
                                    currency: 'BRL'
                                }).format(total_stock_value)}
                            </div>
                            <p className="text-xs text-muted-foreground mt-1">Valor total do estoque</p>
                        </CardContent>
                    </Card>
                </div>

                {/* Alertas */}
                {expired_count > 0 && (
                    <div className="grid gap-4">
                        <Card className="border-red-200 bg-red-50 dark:bg-red-950">
                            <CardHeader>
                                <CardTitle className="flex items-center gap-2 text-red-600">
                                    <AlertTriangle className="h-5 w-5" />
                                    Produtos Vencidos
                                </CardTitle>
                            </CardHeader>
                            <CardContent>
                                <div className="text-2xl font-bold text-red-600 mb-2">
                                    {expired_count}
                                </div>
                                <p className="text-sm text-muted-foreground mb-4">
                                    Produtos com data de validade expirada
                                </p>
                                <Button variant="outline" size="sm" asChild>
                                    <Link href="/inventory/alerts">
                                        <Eye className="mr-2 h-4 w-4" />
                                        Ver Detalhes
                                    </Link>
                                </Button>
                            </CardContent>
                        </Card>
                    </div>
                )}

                {/* Seção Principal */}
                <div className="grid gap-6 lg:grid-cols-2">
                    {/* Produtos com Estoque Baixo */}
                    <Card className="bg-card">
                        <CardHeader>
                            <div className="flex items-center justify-between">
                                <CardTitle className="flex items-center gap-2 text-foreground">
                                    <AlertTriangle className="h-5 w-5 text-orange-600" />
                                    Produtos com Estoque Baixo
                                </CardTitle>
                                <Button variant="outline" size="sm" asChild>
                                    <Link href="/inventory/alerts">
                                        Ver Todos
                                    </Link>
                                </Button>
                            </div>
                        </CardHeader>
                        <CardContent>
                            {low_stock_products.length > 0 ? (
                                <div className="space-y-3">
                                    {low_stock_products.map((product) => (
                                        <div key={product.id} className="flex items-center justify-between p-3 border border-border rounded-lg">
                                            <div className="flex items-center gap-3">
                                                <div
                                                    className="w-3 h-3 rounded-full"
                                                    style={{ backgroundColor: product.category.color }}
                                                />
                                                <div>
                                                    <p className="font-medium text-foreground">{product.name}</p>
                                                    <p className="text-sm text-muted-foreground">{product.category.name}</p>
                                                </div>
                                            </div>
                                            <div className="text-right">
                                                <p className="text-sm font-medium text-orange-600">
                                                    {product.current_stock} {product.unit}
                                                </p>
                                                <p className="text-xs text-muted-foreground">
                                                    Mín: {product.minimum_stock} {product.unit}
                                                </p>
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            ) : (
                                <p className="text-muted-foreground text-center py-4">
                                    Nenhum produto com estoque baixo
                                </p>
                            )}
                        </CardContent>
                    </Card>

                    {/* Produtos Vencendo */}
                    <Card className="bg-card">
                        <CardHeader>
                            <div className="flex items-center justify-between">
                                <CardTitle className="flex items-center gap-2 text-foreground">
                                    <Calendar className="h-5 w-5 text-yellow-600" />
                                    Produtos Vencendo em Breve
                                </CardTitle>
                                <Button variant="outline" size="sm" asChild>
                                    <Link href="/inventory/alerts">
                                        Ver Todos
                                    </Link>
                                </Button>
                            </div>
                        </CardHeader>
                        <CardContent>
                            {expiring_soon_products.length > 0 ? (
                                <div className="space-y-3">
                                    {expiring_soon_products.map((product) => (
                                        <div key={product.id} className="flex items-center justify-between p-3 border border-border rounded-lg">
                                            <div className="flex items-center gap-3">
                                                <div
                                                    className="w-3 h-3 rounded-full"
                                                    style={{ backgroundColor: product.category.color }}
                                                />
                                                <div>
                                                    <p className="font-medium text-foreground">{product.name}</p>
                                                    <p className="text-sm text-muted-foreground">
                                                        Lote: {product.batch_number || 'N/A'}
                                                    </p>
                                                </div>
                                            </div>
                                            <div className="text-right">
                                                <p className="text-sm font-medium text-yellow-600">
                                                    {new Date(product.expiry_date).toLocaleDateString('pt-BR')}
                                                </p>
                                                <p className="text-xs text-muted-foreground">
                                                    Vence em breve
                                                </p>
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            ) : (
                                <p className="text-muted-foreground text-center py-4">
                                    Nenhum produto vencendo em breve
                                </p>
                            )}
                        </CardContent>
                    </Card>
                </div>

                {/* Ações Rápidas */}
                <Card className="bg-card">
                    <CardHeader>
                        <CardTitle className="text-foreground">Ações Rápidas</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                            <Button className="h-20 flex-col gap-2" variant="outline" asChild>
                                <Link href="/inventory/products">
                                    <Package className="h-6 w-6" />
                                    Gerenciar Produtos
                                </Link>
                            </Button>
                            <Button className="h-20 flex-col gap-2" variant="outline" asChild>
                                <Link href="/inventory/categories">
                                    <Tags className="h-6 w-6" />
                                    Categorias
                                </Link>
                            </Button>
                            <Button className="h-20 flex-col gap-2" variant="outline" asChild>
                                <Link href="/inventory/movements">
                                    <List className="h-6 w-6" />
                                    Movimentações
                                </Link>
                            </Button>
                            <Button className="h-20 flex-col gap-2" variant="outline" asChild>
                                <Link href="/inventory/reports">
                                    <FileText className="h-6 w-6" />
                                    Relatórios
                                </Link>
                            </Button>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}