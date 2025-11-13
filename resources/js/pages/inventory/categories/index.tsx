import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/react';
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
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import {
    Plus,
    Edit,
    Trash2,
    Tags,
    Package
} from 'lucide-react';
import { useState } from 'react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Controle de Estoque',
        href: '/inventory',
    },
    {
        title: 'Categorias',
        href: '/inventory/categories',
    },
];

interface Category {
    id: number;
    name: string;
    description?: string;
    color: string;
    active: boolean;
    products_count: number;
    created_at: string;
    updated_at: string;
}

interface CategoriesIndexProps {
    categories: Category[];
}

const colorOptions = [
    '#3B82F6', '#EF4444', '#10B981', '#F59E0B', '#8B5CF6',
    '#EC4899', '#14B8A6', '#F97316', '#6366F1', '#84CC16',
    '#F43F5E', '#06B6D4', '#8B5A2B', '#64748B', '#DC2626'
];

export default function CategoriesIndex({ categories }: CategoriesIndexProps) {
    const [isCreateDialogOpen, setIsCreateDialogOpen] = useState(false);
    const [isEditDialogOpen, setIsEditDialogOpen] = useState(false);
    const [editingCategory, setEditingCategory] = useState<Category | null>(null);

    const { data, setData, post, patch, delete: destroy, processing, errors, reset } = useForm({
        name: '',
        description: '',
        color: '#3B82F6',
    });

    const handleCreateCategory = (e: React.FormEvent) => {
        e.preventDefault();
        post('/inventory/categories', {
            onSuccess: () => {
                setIsCreateDialogOpen(false);
                reset();
            }
        });
    };

    const handleEditCategory = (e: React.FormEvent) => {
        e.preventDefault();
        if (editingCategory) {
            patch(`/inventory/categories/${editingCategory.id}`, {
                onSuccess: () => {
                    setIsEditDialogOpen(false);
                    setEditingCategory(null);
                    reset();
                }
            });
        }
    };

    const handleDeleteCategory = (category: Category) => {
        if (category.products_count > 0) {
            alert('Não é possível excluir uma categoria que possui produtos associados.');
            return;
        }

        if (confirm(`Tem certeza que deseja excluir a categoria "${category.name}"?`)) {
            destroy(`/inventory/categories/${category.id}`);
        }
    };

    const openEditDialog = (category: Category) => {
        setEditingCategory(category);
        setData({
            name: category.name,
            description: category.description || '',
            color: category.color,
        });
        setIsEditDialogOpen(true);
    };

    const openCreateDialog = () => {
        reset();
        setData('color', '#3B82F6');
        setIsCreateDialogOpen(true);
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Categorias - Controle de Estoque" />

            <div className="space-y-6 p-6">
                {/* Header */}
                <div className="flex flex-col justify-between gap-4 md:flex-row md:items-center">
                    <div>
                        <h1 className="text-3xl font-bold tracking-tight text-foreground">Categorias</h1>
                        <p className="text-muted-foreground">
                            Organize seus produtos em categorias
                        </p>
                    </div>
                    <Dialog open={isCreateDialogOpen} onOpenChange={setIsCreateDialogOpen}>
                        <DialogTrigger asChild>
                            <Button onClick={openCreateDialog}>
                                <Plus className="mr-2 h-4 w-4" />
                                Nova Categoria
                            </Button>
                        </DialogTrigger>
                        <DialogContent className="max-w-md">
                            <DialogHeader>
                                <DialogTitle>Criar Nova Categoria</DialogTitle>
                                <DialogDescription>
                                    Preencha as informações da categoria
                                </DialogDescription>
                            </DialogHeader>
                            <form onSubmit={handleCreateCategory} className="space-y-4">
                                <div>
                                    <Label htmlFor="name">Nome da Categoria *</Label>
                                    <Input
                                        id="name"
                                        value={data.name}
                                        onChange={(e) => setData('name', e.target.value)}
                                        placeholder="Nome da categoria"
                                        required
                                    />
                                    {errors.name && <p className="text-sm text-red-500">{errors.name}</p>}
                                </div>

                                <div>
                                    <Label htmlFor="description">Descrição</Label>
                                    <Textarea
                                        id="description"
                                        value={data.description}
                                        onChange={(e) => setData('description', e.target.value)}
                                        placeholder="Descrição da categoria"
                                        rows={3}
                                    />
                                    {errors.description && <p className="text-sm text-red-500">{errors.description}</p>}
                                </div>

                                <div>
                                    <Label htmlFor="color">Cor da Categoria *</Label>
                                    <div className="flex gap-2 mt-2 flex-wrap">
                                        {colorOptions.map((color) => (
                                            <button
                                                key={color}
                                                type="button"
                                                className={`w-8 h-8 rounded-full border-2 transition-all ${
                                                    data.color === color
                                                        ? 'border-foreground scale-110'
                                                        : 'border-border hover:scale-105'
                                                }`}
                                                style={{ backgroundColor: color }}
                                                onClick={() => setData('color', color)}
                                            />
                                        ))}
                                    </div>
                                    <Input
                                        type="color"
                                        value={data.color}
                                        onChange={(e) => setData('color', e.target.value)}
                                        className="mt-2 w-20 h-10"
                                    />
                                    {errors.color && <p className="text-sm text-red-500">{errors.color}</p>}
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
                                        {processing ? 'Criando...' : 'Criar Categoria'}
                                    </Button>
                                </div>
                            </form>
                        </DialogContent>
                    </Dialog>
                </div>

                {/* Categories Grid */}
                <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                    {categories.map((category) => (
                        <Card key={category.id} className="bg-card transition-all duration-200 hover:shadow-lg">
                            <CardHeader className="pb-3">
                                <div className="flex items-center justify-between">
                                    <div className="flex items-center gap-3">
                                        <div
                                            className="w-6 h-6 rounded-full"
                                            style={{ backgroundColor: category.color }}
                                        />
                                        <CardTitle className="text-lg">{category.name}</CardTitle>
                                    </div>
                                    <Badge variant="secondary">
                                        {category.products_count} produtos
                                    </Badge>
                                </div>
                            </CardHeader>
                            <CardContent>
                                {category.description && (
                                    <p className="text-sm text-muted-foreground mb-4">
                                        {category.description}
                                    </p>
                                )}
                                <div className="flex justify-end gap-2">
                                    <Button
                                        variant="outline"
                                        size="sm"
                                        onClick={() => openEditDialog(category)}
                                    >
                                        <Edit className="h-4 w-4" />
                                    </Button>
                                    <Button
                                        variant="outline"
                                        size="sm"
                                        onClick={() => handleDeleteCategory(category)}
                                        disabled={category.products_count > 0}
                                    >
                                        <Trash2 className="h-4 w-4" />
                                    </Button>
                                </div>
                            </CardContent>
                        </Card>
                    ))}
                </div>

                {/* Table View */}
                <Card>
                    <CardHeader>
                        <CardTitle className="flex items-center gap-2">
                            <Tags className="h-5 w-5" />
                            Todas as Categorias ({categories.length})
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Categoria</TableHead>
                                    <TableHead>Descrição</TableHead>
                                    <TableHead>Produtos</TableHead>
                                    <TableHead>Criada em</TableHead>
                                    <TableHead>Ações</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {categories.map((category) => (
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
                                            <span className="text-muted-foreground">
                                                {category.description || '-'}
                                            </span>
                                        </TableCell>
                                        <TableCell>
                                            <Badge variant="secondary" className="flex items-center gap-1 w-fit">
                                                <Package className="h-3 w-3" />
                                                {category.products_count}
                                            </Badge>
                                        </TableCell>
                                        <TableCell>
                                            <span className="text-muted-foreground">
                                                {new Date(category.created_at).toLocaleDateString('pt-BR')}
                                            </span>
                                        </TableCell>
                                        <TableCell>
                                            <div className="flex gap-2">
                                                <Button
                                                    variant="outline"
                                                    size="sm"
                                                    onClick={() => openEditDialog(category)}
                                                >
                                                    <Edit className="h-4 w-4" />
                                                </Button>
                                                <Button
                                                    variant="outline"
                                                    size="sm"
                                                    onClick={() => handleDeleteCategory(category)}
                                                    disabled={category.products_count > 0}
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
                    <DialogContent className="max-w-md">
                        <DialogHeader>
                            <DialogTitle>Editar Categoria</DialogTitle>
                            <DialogDescription>
                                Atualize as informações da categoria
                            </DialogDescription>
                        </DialogHeader>
                        <form onSubmit={handleEditCategory} className="space-y-4">
                            <div>
                                <Label htmlFor="edit_name">Nome da Categoria *</Label>
                                <Input
                                    id="edit_name"
                                    value={data.name}
                                    onChange={(e) => setData('name', e.target.value)}
                                    placeholder="Nome da categoria"
                                    required
                                />
                                {errors.name && <p className="text-sm text-red-500">{errors.name}</p>}
                            </div>

                            <div>
                                <Label htmlFor="edit_description">Descrição</Label>
                                <Textarea
                                    id="edit_description"
                                    value={data.description}
                                    onChange={(e) => setData('description', e.target.value)}
                                    placeholder="Descrição da categoria"
                                    rows={3}
                                />
                                {errors.description && <p className="text-sm text-red-500">{errors.description}</p>}
                            </div>

                            <div>
                                <Label htmlFor="edit_color">Cor da Categoria *</Label>
                                <div className="flex gap-2 mt-2 flex-wrap">
                                    {colorOptions.map((color) => (
                                        <button
                                            key={color}
                                            type="button"
                                            className={`w-8 h-8 rounded-full border-2 transition-all ${
                                                data.color === color
                                                    ? 'border-foreground scale-110'
                                                    : 'border-border hover:scale-105'
                                            }`}
                                            style={{ backgroundColor: color }}
                                            onClick={() => setData('color', color)}
                                        />
                                    ))}
                                </div>
                                <Input
                                    type="color"
                                    value={data.color}
                                    onChange={(e) => setData('color', e.target.value)}
                                    className="mt-2 w-20 h-10"
                                />
                                {errors.color && <p className="text-sm text-red-500">{errors.color}</p>}
                            </div>

                            <div className="flex justify-end gap-2">
                                <Button
                                    type="button"
                                    variant="outline"
                                    onClick={() => {
                                        setIsEditDialogOpen(false);
                                        setEditingCategory(null);
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