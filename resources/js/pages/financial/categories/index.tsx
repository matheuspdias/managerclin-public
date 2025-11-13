import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, router, useForm } from '@inertiajs/react';
import { useCallback, useEffect, useState } from 'react';
import { toast } from 'sonner';

import ConfirmDeleteDialog from '@/components/ConfirmDeleteDialog';
import { FinancialCategoryFormDialog } from '@/components/financial/FinancialCategoryFormDialog';
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
    Tag,
    TrendingUp,
    TrendingDown,
    Palette
} from 'lucide-react';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Financeiro', href: '/financial' },
    { title: 'Categorias', href: '/financial/categories' },
];

interface Category {
    id: number;
    name: string;
    type: string;
    color: string;
    icon?: string;
    is_active: boolean;
    description?: string;
}

interface CategoriesPageProps {
    categories: {
        data: Category[];
        total: number;
        current_page: number;
        per_page: number;
        last_page: number;
    };
    filters: {
        search?: string;
        page: number;
        per_page: number;
        order: string;
    };
}

export default function CategoriesIndex({ categories, filters }: CategoriesPageProps) {
    const [openDeleteModal, setOpenDeleteModal] = useState(false);
    const [openFormModal, setOpenFormModal] = useState(false);
    const [selectedCategoryId, setSelectedCategoryId] = useState<number | null>(null);
    const [editingCategory, setEditingCategory] = useState<Category | null>(null);
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

        const url = `/financial/categories${queryParams.toString() ? '?' + queryParams.toString() : ''}`;

        router.get(url, {}, {
            preserveScroll: true,
            preserveState: true,
            only: ['categories']
        });
    }, [data]);

    // Auto-search with debounce
    useEffect(() => {
        const timeout = setTimeout(() => {
            handleGet();
        }, 500);

        return () => clearTimeout(timeout);
    }, [data.search, handleGet]);

    function handleDelete(categoryId: number) {
        router.delete(`/financial/categories/${categoryId}`, {
            onSuccess: () => {
                toast.success('Categoria excluída com sucesso!');
            },
            onError: () => {
                toast.error('Erro ao excluir categoria');
            },
        });
    }

    const incomeCategories = categories.data.filter(cat => cat.type === 'INCOME');
    const expenseCategories = categories.data.filter(cat => cat.type === 'EXPENSE');

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Categorias Financeiras" />

            <div className="space-y-6 p-6">
                {/* Header */}
                <div className="flex flex-col justify-between gap-4 md:flex-row md:items-center">
                    <div>
                        <h1 className="text-2xl font-bold tracking-tight">Categorias Financeiras</h1>
                        <p className="text-muted-foreground">
                            Organize suas receitas e despesas por categorias
                        </p>
                    </div>
                    <Button
                        className="gap-2"
                        onClick={() => {
                            setEditingCategory(null);
                            setFormMode('create');
                            setOpenFormModal(true);
                        }}
                    >
                        <Plus className="h-4 w-4" />
                        Nova Categoria
                    </Button>
                </div>

                {/* Resumo */}
                <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Total de Categorias</CardTitle>
                            <Tag className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{categories.total}</div>
                            <p className="text-xs text-muted-foreground">
                                Ativas e inativas
                            </p>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Receitas</CardTitle>
                            <TrendingUp className="h-4 w-4 text-green-600" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-green-600">{incomeCategories.length}</div>
                            <p className="text-xs text-muted-foreground">
                                Categorias de entrada
                            </p>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Despesas</CardTitle>
                            <TrendingDown className="h-4 w-4 text-red-600" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-red-600">{expenseCategories.length}</div>
                            <p className="text-xs text-muted-foreground">
                                Categorias de saída
                            </p>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Ativas</CardTitle>
                            <Palette className="h-4 w-4 text-blue-600" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-blue-600">
                                {categories.data.filter(cat => cat.is_active).length}
                            </div>
                            <p className="text-xs text-muted-foreground">
                                Em uso
                            </p>
                        </CardContent>
                    </Card>
                </div>

                {/* Filtros */}
                <Card>
                    <CardHeader className="pb-4">
                        <CardTitle className="text-lg">Buscar Categorias</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div className="relative">
                            <Search className="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                            <Input
                                placeholder="Nome da categoria..."
                                value={data.search}
                                onChange={(e) => setData('search', e.target.value)}
                                className="pl-10"
                            />
                        </div>
                    </CardContent>
                </Card>

                {/* Tabela de Categorias */}
                <Card>
                    <CardHeader>
                        <CardTitle className="flex items-center justify-between">
                            <span>Categorias</span>
                            <span className="text-sm font-normal text-muted-foreground">
                                {categories.total} categoria(s) encontrada(s)
                            </span>
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div className="rounded-md border">
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>Categoria</TableHead>
                                        <TableHead>Tipo</TableHead>
                                        <TableHead>Cor</TableHead>
                                        <TableHead>Status</TableHead>
                                        <TableHead>Descrição</TableHead>
                                        <TableHead className="w-16">Ações</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {categories.data.map((category) => (
                                        <TableRow key={category.id} className="group hover:bg-muted/50">
                                            <TableCell>
                                                <div className="flex items-center gap-3">
                                                    <div
                                                        className="w-4 h-4 rounded-full border"
                                                        style={{ backgroundColor: category.color }}
                                                    />
                                                    <div>
                                                        <p className="font-medium">{category.name}</p>
                                                        {category.icon && (
                                                            <p className="text-xs text-muted-foreground">
                                                                Ícone: {category.icon}
                                                            </p>
                                                        )}
                                                    </div>
                                                </div>
                                            </TableCell>
                                            <TableCell>
                                                <Badge
                                                    variant={category.type === 'INCOME' ? 'default' : 'secondary'}
                                                    className={category.type === 'INCOME' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}
                                                >
                                                    {category.type === 'INCOME' ? (
                                                        <>
                                                            <TrendingUp className="mr-1 h-3 w-3" />
                                                            Receita
                                                        </>
                                                    ) : (
                                                        <>
                                                            <TrendingDown className="mr-1 h-3 w-3" />
                                                            Despesa
                                                        </>
                                                    )}
                                                </Badge>
                                            </TableCell>
                                            <TableCell>
                                                <div className="flex items-center gap-2">
                                                    <div
                                                        className="w-8 h-4 rounded border"
                                                        style={{ backgroundColor: category.color }}
                                                    />
                                                    <span className="text-xs font-mono">{category.color}</span>
                                                </div>
                                            </TableCell>
                                            <TableCell>
                                                <Badge variant={category.is_active ? 'default' : 'secondary'}>
                                                    {category.is_active ? 'Ativa' : 'Inativa'}
                                                </Badge>
                                            </TableCell>
                                            <TableCell>
                                                <p className="text-sm text-muted-foreground max-w-xs truncate">
                                                    {category.description || '-'}
                                                </p>
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
                                                                setEditingCategory(category);
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
                                                                setSelectedCategoryId(category.id);
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

                        {categories.data.length === 0 && (
                            <div className="text-center py-12">
                                <Tag className="mx-auto h-12 w-12 text-muted-foreground" />
                                <h3 className="mt-4 text-lg font-medium">Nenhuma categoria encontrada</h3>
                                <p className="text-muted-foreground">
                                    Crie categorias para organizar melhor suas transações financeiras.
                                </p>
                                <Button
                                    className="mt-4"
                                    onClick={() => {
                                        setEditingCategory(null);
                                        setFormMode('create');
                                        setOpenFormModal(true);
                                    }}
                                >
                                    <Plus className="mr-2 h-4 w-4" />
                                    Nova Categoria
                                </Button>
                            </div>
                        )}

                        {/* Pagination info */}
                        {categories.total > 0 && (
                            <div className="flex items-center justify-between px-2 py-4">
                                <div className="text-sm text-muted-foreground">
                                    Mostrando {((categories.current_page - 1) * categories.per_page) + 1} a{' '}
                                    {Math.min(categories.current_page * categories.per_page, categories.total)} de{' '}
                                    {categories.total} categorias
                                </div>
                                <div className="text-sm text-muted-foreground">
                                    Página {categories.current_page} de {categories.last_page}
                                </div>
                            </div>
                        )}
                    </CardContent>
                </Card>

                {/* Cores Mais Usadas */}
                <Card>
                    <CardHeader>
                        <CardTitle>Paleta de Cores Usadas</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div className="flex flex-wrap gap-2">
                            {Array.from(new Set(categories.data.map(cat => cat.color))).map((color) => (
                                <div
                                    key={color}
                                    className="w-8 h-8 rounded border-2 border-background shadow"
                                    style={{ backgroundColor: color }}
                                    title={color}
                                />
                            ))}
                        </div>
                    </CardContent>
                </Card>
            </div>

            {/* Category Form Dialog */}
            <FinancialCategoryFormDialog
                open={openFormModal}
                onClose={() => {
                    setOpenFormModal(false);
                    setEditingCategory(null);
                }}
                category={editingCategory}
                mode={formMode}
            />

            {/* Delete Dialog */}
            <ConfirmDeleteDialog
                confirmText="Tem certeza que deseja excluir esta categoria?"
                description="Esta ação não pode ser desfeita. A categoria será removida permanentemente, mas as transações associadas serão mantidas."
                open={openDeleteModal}
                onClose={() => setOpenDeleteModal(false)}
                onConfirm={() => {
                    if (selectedCategoryId !== null) {
                        handleDelete(selectedCategoryId);
                        setOpenDeleteModal(false);
                    }
                }}
            />
        </AppLayout>
    );
}