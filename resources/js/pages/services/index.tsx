import ConfirmDeleteDialog from '@/components/ConfirmDeleteDialog';
import LaravelPagination from '@/components/laravel-pagination';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Service, ServiceProps } from '@/types/service';
import { Head, router, useForm, usePage } from '@inertiajs/react';
import { DollarSign, Edit, FileText, Filter, Plus, Search, Tag, Trash2 } from 'lucide-react';
import { useEffect, useState } from 'react';
import { toast } from 'sonner';
import { ServiceFormDialog } from './ServiceFormDialog';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Serviços',
        href: '/services',
    },
];

export default function Services() {
    const { services } = usePage<{ services: ServiceProps }>().props;
    const [openModal, setOpenModal] = useState(false);
    const [selectedServiceId, setSelectedServiceId] = useState<number | null>(null);
    const [editService, setEditService] = useState<ServiceProps['data'][number] | null>(null);
    const [isFormModalOpen, setIsFormModalOpen] = useState(false);
    const [formMode, setFormMode] = useState<'edit' | 'create'>('create');

    const confirmDelete = (id: number) => {
        setSelectedServiceId(id);
        setOpenModal(true);
    };

    function handleDelete(serviceId: number) {
        router.delete(`/services/${serviceId}`, {
            onSuccess: () => {
                toast.success('Serviço excluído com sucesso!');
            },
            onError: () => {
                toast.error('Erro ao excluir Serviço');
            },
        });
    }

    const { data, setData, get } = useForm({
        search: services.filters?.search ?? '',
        per_page: services.filters?.per_page?.toString() ?? '10',
        order: services.filters?.order ?? 'name:asc',
        page: services.filters?.page ?? 1,
    });

    // Executa automaticamente quando search muda (com debounce)
    useEffect(() => {
        const timeout = setTimeout(() => {
            get(route('services.index'), { preserveScroll: true, preserveState: true });
        }, 500);
        return () => clearTimeout(timeout);
    }, [data.search, data.page, get]);

    // Atualiza ao mudar per_page ou order (sem debounce)
    useEffect(() => {
        get(route('services.index'), { preserveScroll: true, preserveState: true });
    }, [data.search, data.per_page, data.order, data.page, get]);

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Serviços" />
            <div className="space-y-6 p-6">
                {/* Header Section */}
                <div className="flex flex-col justify-between gap-4 md:flex-row md:items-center">
                    <div>
                        <h1 className="text-2xl font-bold tracking-tight">Catálogo de Serviços</h1>
                        <p className="text-muted-foreground">Gerencie todos os serviços oferecidos pela clínica</p>
                    </div>
                    <Button
                        onClick={() => {
                            setFormMode('create');
                            setEditService({
                                name: '',
                                description: '',
                                price: 0,
                            } as Service);
                            setIsFormModalOpen(true);
                        }}
                        className="gap-2"
                    >
                        <Plus className="h-4 w-4" />
                        Novo Serviço
                    </Button>
                </div>

                {/* Filters Section */}
                <Card className="border-border bg-card">
                    <CardHeader className="pb-4">
                        <CardTitle className="flex items-center gap-2 text-lg font-semibold">
                            <Filter className="h-5 w-5 text-muted-foreground" />
                            Filtros e Busca
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div className="grid grid-cols-1 gap-4 md:grid-cols-3">
                            <div className="space-y-2">
                                <label className="text-sm font-medium">Buscar por nome</label>
                                <div className="relative">
                                    <Search className="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 transform text-muted-foreground" />
                                    <Input
                                        placeholder="Digite o nome do serviço"
                                        value={data.search}
                                        onChange={(e) => setData('search', e.target.value)}
                                        className="pl-10"
                                    />
                                </div>
                            </div>

                            <div className="space-y-2">
                                <label className="text-sm font-medium">Registros por página</label>
                                <Select value={data.per_page} onValueChange={(value) => setData('per_page', value)}>
                                    <SelectTrigger>
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="10">10 registros</SelectItem>
                                        <SelectItem value="25">25 registros</SelectItem>
                                        <SelectItem value="50">50 registros</SelectItem>
                                        <SelectItem value="100">100 registros</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>

                            <div className="space-y-2">
                                <label className="text-sm font-medium">Ordenar por</label>
                                <Select value={data.order} onValueChange={(value) => setData('order', value)}>
                                    <SelectTrigger>
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="name:asc">Nome (A-Z)</SelectItem>
                                        <SelectItem value="name:desc">Nome (Z-A)</SelectItem>
                                        <SelectItem value="price:asc">Preço (menor-maior)</SelectItem>
                                        <SelectItem value="price:desc">Preço (maior-menor)</SelectItem>
                                        <SelectItem value="created_at:desc">Mais recentes</SelectItem>
                                        <SelectItem value="created_at:asc">Mais antigos</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                {/* Services Table */}
                <Card className="border-border bg-card">
                    <CardHeader>
                        <div className="flex items-center justify-between">
                            <div>
                                <CardTitle className="text-lg font-semibold">Lista de Serviços</CardTitle>
                                <CardDescription>{services.total} serviço(s) encontrado(s)</CardDescription>
                            </div>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div className="rounded-md border">
                            <Table>
                                <TableHeader>
                                    <TableRow className="hover:bg-transparent">
                                        <TableHead>Serviço</TableHead>
                                        <TableHead>Descrição</TableHead>
                                        <TableHead className="text-right">Preço</TableHead>
                                        <TableHead className="w-32 text-right">Ações</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {services.data.map((service) => (
                                        <TableRow key={service.id} className="group hover:bg-muted/50">
                                            <TableCell>
                                                <div className="flex items-center gap-3">
                                                    <div className="flex h-10 w-10 items-center justify-center rounded-full bg-primary/10">
                                                        <Tag className="h-5 w-5 text-primary" />
                                                    </div>
                                                    <div>
                                                        <div className="font-medium">{service.name}</div>
                                                        <div className="text-xs text-muted-foreground">ID: {service.id}</div>
                                                    </div>
                                                </div>
                                            </TableCell>
                                            <TableCell>
                                                <div className="flex items-center gap-2 text-muted-foreground">
                                                    <FileText className="h-4 w-4 flex-shrink-0" />
                                                    <span className="line-clamp-2">{service.description || 'Sem descrição'}</span>
                                                </div>
                                            </TableCell>
                                            <TableCell>
                                                <div className="flex items-center justify-end gap-2">
                                                    <DollarSign className="h-4 w-4 text-muted-foreground" />
                                                    <span className="font-semibold">
                                                        {service.price
                                                            ? new Intl.NumberFormat('pt-BR', {
                                                                  style: 'currency',
                                                                  currency: 'BRL',
                                                              }).format(service.price)
                                                            : 'Gratuito'}
                                                    </span>
                                                    {service.price === 0 && (
                                                        <Badge variant="outline" className="ml-2">
                                                            Gratuito
                                                        </Badge>
                                                    )}
                                                </div>
                                            </TableCell>
                                            <TableCell>
                                                <div className="flex justify-end gap-1">
                                                    <Button
                                                        size="sm"
                                                        variant="outline"
                                                        className="h-8 w-8 p-0"
                                                        onClick={() => {
                                                            setFormMode('edit');
                                                            setEditService(service);
                                                            setIsFormModalOpen(true);
                                                        }}
                                                        title="Editar"
                                                    >
                                                        <Edit className="h-4 w-4" />
                                                    </Button>

                                                    <Button
                                                        size="sm"
                                                        variant="destructive"
                                                        className="h-8 w-8 p-0"
                                                        onClick={() => confirmDelete(service.id)}
                                                        title="Excluir"
                                                    >
                                                        <Trash2 className="h-4 w-4" />
                                                    </Button>
                                                </div>
                                            </TableCell>
                                        </TableRow>
                                    ))}
                                </TableBody>
                            </Table>
                        </div>

                        {services.data.length === 0 && (
                            <div className="flex flex-col items-center justify-center py-12 text-center">
                                <Search className="mb-4 h-12 w-12 text-muted-foreground" />
                                <h3 className="text-lg font-medium">Nenhum serviço encontrado</h3>
                                <p className="text-muted-foreground">
                                    {data.search ? 'Tente ajustar os termos da busca' : 'Comece adicionando seu primeiro serviço'}
                                </p>
                            </div>
                        )}

                        <div className="mt-6">
                            <LaravelPagination links={services.links} onPageChange={(page) => setData('page', page)} />
                        </div>
                    </CardContent>
                </Card>
            </div>

            <ConfirmDeleteDialog
                confirmText="Tem certeza que deseja excluir este serviço?"
                description="Esta ação não pode ser desfeita. Todos os dados relacionados a este serviço serão perdidos."
                open={openModal}
                onClose={() => setOpenModal(false)}
                onConfirm={() => {
                    if (selectedServiceId !== null) {
                        handleDelete(selectedServiceId);
                        setOpenModal(false);
                    }
                }}
            />

            <ServiceFormDialog
                open={isFormModalOpen}
                onClose={() => setIsFormModalOpen(false)}
                service={editService}
                setService={setEditService}
                mode={formMode}
            />
        </AppLayout>
    );
}
