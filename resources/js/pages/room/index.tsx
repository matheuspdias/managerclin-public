import ConfirmDeleteDialog from '@/components/ConfirmDeleteDialog';
import LaravelPagination from '@/components/laravel-pagination';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Room, RoomProps } from '@/types/room';
import { Head, router, useForm, usePage } from '@inertiajs/react';
import { Building, DoorOpen, Edit, Filter, MapPin, Plus, Search, Trash2 } from 'lucide-react';
import { useEffect, useState } from 'react';
import { toast } from 'sonner';
import { RoomFormDialog } from './RoomFormDialog';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Consultórios',
        href: '/room',
    },
];

export default function Rooms() {
    const { rooms } = usePage<{ rooms: RoomProps }>().props;
    const [openModal, setOpenModal] = useState(false);
    const [selectedRoomId, setSelectedRoomId] = useState<number | null>(null);
    const [editRoom, setEditRoom] = useState<RoomProps['data'][number] | null>(null);
    const [isFormModalOpen, setIsFormModalOpen] = useState(false);
    const [formMode, setFormMode] = useState<'edit' | 'create'>('create');

    const confirmDelete = (id: number) => {
        setSelectedRoomId(id);
        setOpenModal(true);
    };

    function handleDelete(roomId: number) {
        router.delete(`/rooms/${roomId}`, {
            onSuccess: () => {
                toast.success('Consultório excluído com sucesso!');
            },
            onError: () => {
                toast.error('Erro ao excluir Consultório');
            },
        });
    }

    const { data, setData, get } = useForm({
        search: rooms.filters?.search ?? '',
        per_page: rooms.filters?.per_page?.toString() ?? '10',
        order: rooms.filters?.order ?? 'name:asc',
        page: rooms.filters?.page ?? 1,
    });

    // Executa automaticamente quando search muda (com debounce)
    useEffect(() => {
        const timeout = setTimeout(() => {
            get(route('rooms.index'), { preserveScroll: true, preserveState: true });
        }, 500);
        return () => clearTimeout(timeout);
    }, [data.search, data.page, get]);

    // Atualiza ao mudar per_page ou order (sem debounce)
    useEffect(() => {
        get(route('rooms.index'), { preserveScroll: true, preserveState: true });
    }, [data.search, data.per_page, data.order, data.page, get]);

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Consultórios" />
            <div className="space-y-6 p-6">
                {/* Header Section */}
                <div className="flex flex-col justify-between gap-4 md:flex-row md:items-center">
                    <div>
                        <h1 className="text-2xl font-bold tracking-tight">Gestão de Consultórios</h1>
                        <p className="text-muted-foreground">Gerencie todos os consultórios da sua clínica</p>
                    </div>
                    <Button
                        onClick={() => {
                            setFormMode('create');
                            setEditRoom({
                                name: '',
                                location: '',
                            } as Room);
                            setIsFormModalOpen(true);
                        }}
                        className="gap-2"
                    >
                        <Plus className="h-4 w-4" />
                        Novo Consultório
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
                                        placeholder="Digite o nome do consultório"
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
                                        <SelectItem value="location:asc">Local (A-Z)</SelectItem>
                                        <SelectItem value="location:desc">Local (Z-A)</SelectItem>
                                        <SelectItem value="created_at:desc">Mais recentes</SelectItem>
                                        <SelectItem value="created_at:asc">Mais antigos</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                {/* Rooms Table */}
                <Card className="border-border bg-card">
                    <CardHeader>
                        <div className="flex items-center justify-between">
                            <div>
                                <CardTitle className="text-lg font-semibold">Lista de Consultórios</CardTitle>
                                <CardDescription>{rooms.total} consultório(s) encontrado(s)</CardDescription>
                            </div>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div className="rounded-md border">
                            <Table>
                                <TableHeader>
                                    <TableRow className="hover:bg-transparent">
                                        <TableHead>Consultório</TableHead>
                                        <TableHead>Localização</TableHead>
                                        <TableHead className="w-32 text-right">Ações</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {rooms.data.map((room) => (
                                        <TableRow key={room.id} className="group hover:bg-muted/50">
                                            <TableCell>
                                                <div className="flex items-center gap-3">
                                                    <div className="flex h-10 w-10 items-center justify-center rounded-full bg-primary/10">
                                                        <DoorOpen className="h-5 w-5 text-primary" />
                                                    </div>
                                                    <div>
                                                        <div className="font-medium">{room.name}</div>
                                                        <div className="text-xs text-muted-foreground">ID: {room.id}</div>
                                                    </div>
                                                </div>
                                            </TableCell>
                                            <TableCell>
                                                <div className="flex items-center gap-2 text-muted-foreground">
                                                    <MapPin className="h-4 w-4 flex-shrink-0" />
                                                    <span>{room.location || 'Local não especificado'}</span>
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
                                                            setEditRoom(room);
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
                                                        onClick={() => confirmDelete(room.id)}
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

                        {rooms.data.length === 0 && (
                            <div className="flex flex-col items-center justify-center py-12 text-center">
                                <Building className="mb-4 h-12 w-12 text-muted-foreground" />
                                <h3 className="text-lg font-medium">Nenhum consultório encontrado</h3>
                                <p className="text-muted-foreground">
                                    {data.search ? 'Tente ajustar os termos da busca' : 'Comece adicionando seu primeiro consultório'}
                                </p>
                            </div>
                        )}

                        <div className="mt-6">
                            <LaravelPagination links={rooms.links} onPageChange={(page) => setData('page', page)} />
                        </div>
                    </CardContent>
                </Card>
            </div>

            <ConfirmDeleteDialog
                confirmText="Tem certeza que deseja excluir este consultório?"
                description="Esta ação não pode ser desfeita. Todos os dados relacionados a este consultório serão perdidos."
                open={openModal}
                onClose={() => setOpenModal(false)}
                onConfirm={() => {
                    if (selectedRoomId !== null) {
                        handleDelete(selectedRoomId);
                        setOpenModal(false);
                    }
                }}
            />

            <RoomFormDialog open={isFormModalOpen} onClose={() => setIsFormModalOpen(false)} room={editRoom} setRoom={setEditRoom} mode={formMode} />
        </AppLayout>
    );
}
