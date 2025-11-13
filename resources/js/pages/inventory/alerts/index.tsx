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
    AlertTriangle,
    Calendar,
    XCircle,
    Package,
    Eye,
    Download,
    Bell,
    Clock,
    TrendingDown
} from 'lucide-react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Controle de Estoque',
        href: '/inventory',
    },
    {
        title: 'Alertas',
        href: '/inventory/alerts',
    },
];

interface Product {
    id: number;
    name: string;
    current_stock: number;
    minimum_stock: number;
    unit: string;
    expiry_date?: string;
    batch_number?: string;
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

interface AlertsIndexProps {
    lowStockProducts: Product[];
    expiredProducts: Product[];
    expiringSoonProducts: Product[];
}

export default function AlertsIndex({ lowStockProducts, expiredProducts, expiringSoonProducts }: AlertsIndexProps) {
    const formatDate = (dateString?: string) => {
        if (!dateString) return '-';
        return new Date(dateString).toLocaleDateString('pt-BR');
    };

    const getDaysUntilExpiry = (expiryDate: string) => {
        const today = new Date();
        const expiry = new Date(expiryDate);
        const diffTime = expiry.getTime() - today.getTime();
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
        return diffDays;
    };

    const getExpiryStatus = (expiryDate: string) => {
        const daysUntilExpiry = getDaysUntilExpiry(expiryDate);

        if (daysUntilExpiry < 0) {
            return {
                label: `Vencido há ${Math.abs(daysUntilExpiry)} dias`,
                variant: 'destructive' as const,
                color: 'text-red-600'
            };
        } else if (daysUntilExpiry === 0) {
            return {
                label: 'Vence hoje',
                variant: 'destructive' as const,
                color: 'text-red-600'
            };
        } else if (daysUntilExpiry <= 7) {
            return {
                label: `${daysUntilExpiry} dias`,
                variant: 'destructive' as const,
                color: 'text-red-600'
            };
        } else if (daysUntilExpiry <= 30) {
            return {
                label: `${daysUntilExpiry} dias`,
                variant: 'secondary' as const,
                color: 'text-yellow-600'
            };
        } else {
            return {
                label: `${daysUntilExpiry} dias`,
                variant: 'default' as const,
                color: 'text-green-600'
            };
        }
    };

    const getStockPercentage = (current: number, minimum: number) => {
        if (minimum === 0) return 100;
        return Math.round((current / minimum) * 100);
    };

    const getStockStatus = (current: number, minimum: number) => {
        const percentage = getStockPercentage(current, minimum);

        if (current === 0) {
            return { label: 'Sem Estoque', variant: 'destructive' as const, color: 'text-red-600' };
        } else if (percentage <= 50) {
            return { label: 'Crítico', variant: 'destructive' as const, color: 'text-red-600' };
        } else if (percentage <= 100) {
            return { label: 'Baixo', variant: 'secondary' as const, color: 'text-orange-600' };
        } else {
            return { label: 'Normal', variant: 'default' as const, color: 'text-green-600' };
        }
    };

    const totalAlerts = lowStockProducts.length + expiredProducts.length + expiringSoonProducts.length;

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Alertas - Controle de Estoque" />

            <div className="space-y-6 p-6">
                {/* Header */}
                <div className="flex flex-col justify-between gap-4 md:flex-row md:items-center">
                    <div>
                        <h1 className="text-3xl font-bold tracking-tight text-foreground">Alertas do Estoque</h1>
                        <p className="text-muted-foreground">
                            Monitore produtos com estoque baixo, vencidos ou próximos ao vencimento
                        </p>
                    </div>
                    <div className="flex gap-2">
                        <Button variant="outline" asChild>
                            <Link href="/inventory/products">
                                <Package className="mr-2 h-4 w-4" />
                                Gerenciar Produtos
                            </Link>
                        </Button>
                        <Button variant="outline" asChild>
                            <Link href="/inventory/movements">
                                <TrendingDown className="mr-2 h-4 w-4" />
                                Movimentações
                            </Link>
                        </Button>
                    </div>
                </div>

                {/* Summary Cards */}
                <div className="grid gap-4 md:grid-cols-3">
                    <Card className="border-orange-200 bg-orange-50 dark:bg-orange-950">
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium text-orange-700 dark:text-orange-300">
                                Estoque Baixo
                            </CardTitle>
                            <AlertTriangle className="h-4 w-4 text-orange-600" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-orange-600">{lowStockProducts.length}</div>
                            <p className="text-xs text-orange-700 dark:text-orange-300">
                                Produtos abaixo do estoque mínimo
                            </p>
                        </CardContent>
                    </Card>

                    <Card className="border-red-200 bg-red-50 dark:bg-red-950">
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium text-red-700 dark:text-red-300">
                                Produtos Vencidos
                            </CardTitle>
                            <XCircle className="h-4 w-4 text-red-600" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-red-600">{expiredProducts.length}</div>
                            <p className="text-xs text-red-700 dark:text-red-300">
                                Produtos com validade expirada
                            </p>
                        </CardContent>
                    </Card>

                    <Card className="border-yellow-200 bg-yellow-50 dark:bg-yellow-950">
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium text-yellow-700 dark:text-yellow-300">
                                Vencendo em Breve
                            </CardTitle>
                            <Clock className="h-4 w-4 text-yellow-600" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-yellow-600">{expiringSoonProducts.length}</div>
                            <p className="text-xs text-yellow-700 dark:text-yellow-300">
                                Produtos vencendo nos próximos 30 dias
                            </p>
                        </CardContent>
                    </Card>
                </div>

                {totalAlerts === 0 && (
                    <Card className="border-green-200 bg-green-50 dark:bg-green-950">
                        <CardContent className="flex flex-col items-center justify-center py-12">
                            <div className="rounded-full bg-green-100 dark:bg-green-900 p-3 mb-4">
                                <Bell className="h-8 w-8 text-green-600" />
                            </div>
                            <h3 className="text-lg font-semibold text-green-700 dark:text-green-300 mb-2">
                                Nenhum Alerta Ativo
                            </h3>
                            <p className="text-green-600 dark:text-green-400 text-center">
                                Todos os produtos estão com estoque adequado e dentro da validade!
                            </p>
                        </CardContent>
                    </Card>
                )}

                {totalAlerts > 0 && (
                    <Tabs defaultValue="low-stock" className="w-full">
                        <TabsList className="grid w-full grid-cols-3">
                            <TabsTrigger value="low-stock" className="flex items-center gap-2">
                                <AlertTriangle className="h-4 w-4" />
                                Estoque Baixo ({lowStockProducts.length})
                            </TabsTrigger>
                            <TabsTrigger value="expired" className="flex items-center gap-2">
                                <XCircle className="h-4 w-4" />
                                Vencidos ({expiredProducts.length})
                            </TabsTrigger>
                            <TabsTrigger value="expiring-soon" className="flex items-center gap-2">
                                <Calendar className="h-4 w-4" />
                                Vencendo ({expiringSoonProducts.length})
                            </TabsTrigger>
                        </TabsList>

                        <TabsContent value="low-stock" className="space-y-4">
                            <Card>
                                <CardHeader>
                                    <CardTitle className="flex items-center gap-2 text-orange-600">
                                        <AlertTriangle className="h-5 w-5" />
                                        Produtos com Estoque Baixo
                                    </CardTitle>
                                </CardHeader>
                                <CardContent>
                                    {lowStockProducts.length > 0 ? (
                                        <Table>
                                            <TableHeader>
                                                <TableRow>
                                                    <TableHead>Produto</TableHead>
                                                    <TableHead>Categoria</TableHead>
                                                    <TableHead>Estoque Atual</TableHead>
                                                    <TableHead>Estoque Mínimo</TableHead>
                                                    <TableHead>Status</TableHead>
                                                    <TableHead>Fornecedor</TableHead>
                                                    <TableHead>Ações</TableHead>
                                                </TableRow>
                                            </TableHeader>
                                            <TableBody>
                                                {lowStockProducts.map((product) => {
                                                    const stockStatus = getStockStatus(product.current_stock, product.minimum_stock);
                                                    const percentage = getStockPercentage(product.current_stock, product.minimum_stock);

                                                    return (
                                                        <TableRow key={product.id}>
                                                            <TableCell>
                                                                <div className="font-medium">{product.name}</div>
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
                                                                <div className={`font-medium ${stockStatus.color}`}>
                                                                    {product.current_stock} {product.unit}
                                                                </div>
                                                            </TableCell>
                                                            <TableCell>
                                                                <div className="text-muted-foreground">
                                                                    {product.minimum_stock} {product.unit}
                                                                </div>
                                                            </TableCell>
                                                            <TableCell>
                                                                <div className="flex items-center gap-2">
                                                                    <Badge variant={stockStatus.variant}>
                                                                        {stockStatus.label}
                                                                    </Badge>
                                                                    <span className="text-sm text-muted-foreground">
                                                                        ({percentage}%)
                                                                    </span>
                                                                </div>
                                                            </TableCell>
                                                            <TableCell>
                                                                <div className="text-muted-foreground">
                                                                    {product.supplier?.name || '-'}
                                                                </div>
                                                            </TableCell>
                                                            <TableCell>
                                                                <Button variant="outline" size="sm" asChild>
                                                                    <Link href="/inventory/movements">
                                                                        Registrar Entrada
                                                                    </Link>
                                                                </Button>
                                                            </TableCell>
                                                        </TableRow>
                                                    );
                                                })}
                                            </TableBody>
                                        </Table>
                                    ) : (
                                        <div className="text-center py-8 text-muted-foreground">
                                            Nenhum produto com estoque baixo
                                        </div>
                                    )}
                                </CardContent>
                            </Card>
                        </TabsContent>

                        <TabsContent value="expired" className="space-y-4">
                            <Card>
                                <CardHeader>
                                    <CardTitle className="flex items-center gap-2 text-red-600">
                                        <XCircle className="h-5 w-5" />
                                        Produtos Vencidos
                                    </CardTitle>
                                </CardHeader>
                                <CardContent>
                                    {expiredProducts.length > 0 ? (
                                        <Table>
                                            <TableHeader>
                                                <TableRow>
                                                    <TableHead>Produto</TableHead>
                                                    <TableHead>Categoria</TableHead>
                                                    <TableHead>Lote</TableHead>
                                                    <TableHead>Data de Validade</TableHead>
                                                    <TableHead>Status</TableHead>
                                                    <TableHead>Estoque</TableHead>
                                                    <TableHead>Ações</TableHead>
                                                </TableRow>
                                            </TableHeader>
                                            <TableBody>
                                                {expiredProducts.map((product) => {
                                                    const expiryStatus = getExpiryStatus(product.expiry_date!);

                                                    return (
                                                        <TableRow key={product.id}>
                                                            <TableCell>
                                                                <div className="font-medium">{product.name}</div>
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
                                                                <div className="text-muted-foreground">
                                                                    {product.batch_number || '-'}
                                                                </div>
                                                            </TableCell>
                                                            <TableCell>
                                                                <div className="text-red-600 font-medium">
                                                                    {formatDate(product.expiry_date)}
                                                                </div>
                                                            </TableCell>
                                                            <TableCell>
                                                                <Badge variant={expiryStatus.variant}>
                                                                    {expiryStatus.label}
                                                                </Badge>
                                                            </TableCell>
                                                            <TableCell>
                                                                <div className="font-medium">
                                                                    {product.current_stock} {product.unit}
                                                                </div>
                                                            </TableCell>
                                                            <TableCell>
                                                                <Button variant="outline" size="sm" asChild>
                                                                    <Link href="/inventory/movements">
                                                                        Registrar Saída
                                                                    </Link>
                                                                </Button>
                                                            </TableCell>
                                                        </TableRow>
                                                    );
                                                })}
                                            </TableBody>
                                        </Table>
                                    ) : (
                                        <div className="text-center py-8 text-muted-foreground">
                                            Nenhum produto vencido
                                        </div>
                                    )}
                                </CardContent>
                            </Card>
                        </TabsContent>

                        <TabsContent value="expiring-soon" className="space-y-4">
                            <Card>
                                <CardHeader>
                                    <CardTitle className="flex items-center gap-2 text-yellow-600">
                                        <Calendar className="h-5 w-5" />
                                        Produtos Vencendo em Breve
                                    </CardTitle>
                                </CardHeader>
                                <CardContent>
                                    {expiringSoonProducts.length > 0 ? (
                                        <Table>
                                            <TableHeader>
                                                <TableRow>
                                                    <TableHead>Produto</TableHead>
                                                    <TableHead>Categoria</TableHead>
                                                    <TableHead>Lote</TableHead>
                                                    <TableHead>Data de Validade</TableHead>
                                                    <TableHead>Dias Restantes</TableHead>
                                                    <TableHead>Estoque</TableHead>
                                                    <TableHead>Ações</TableHead>
                                                </TableRow>
                                            </TableHeader>
                                            <TableBody>
                                                {expiringSoonProducts.map((product) => {
                                                    const expiryStatus = getExpiryStatus(product.expiry_date!);
                                                    const daysUntilExpiry = getDaysUntilExpiry(product.expiry_date!);

                                                    return (
                                                        <TableRow key={product.id}>
                                                            <TableCell>
                                                                <div className="font-medium">{product.name}</div>
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
                                                                <div className="text-muted-foreground">
                                                                    {product.batch_number || '-'}
                                                                </div>
                                                            </TableCell>
                                                            <TableCell>
                                                                <div className={`font-medium ${expiryStatus.color}`}>
                                                                    {formatDate(product.expiry_date)}
                                                                </div>
                                                            </TableCell>
                                                            <TableCell>
                                                                <Badge variant={expiryStatus.variant}>
                                                                    {daysUntilExpiry} dias
                                                                </Badge>
                                                            </TableCell>
                                                            <TableCell>
                                                                <div className="font-medium">
                                                                    {product.current_stock} {product.unit}
                                                                </div>
                                                            </TableCell>
                                                            <TableCell>
                                                                <Button variant="outline" size="sm" asChild>
                                                                    <Link href="/inventory/movements">
                                                                        Promover Venda
                                                                    </Link>
                                                                </Button>
                                                            </TableCell>
                                                        </TableRow>
                                                    );
                                                })}
                                            </TableBody>
                                        </Table>
                                    ) : (
                                        <div className="text-center py-8 text-muted-foreground">
                                            Nenhum produto vencendo em breve
                                        </div>
                                    )}
                                </CardContent>
                            </Card>
                        </TabsContent>
                    </Tabs>
                )}
            </div>
        </AppLayout>
    );
}