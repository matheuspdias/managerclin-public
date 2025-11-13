import ConfirmDeleteDialog from '@/components/ConfirmDeleteDialog';
import LaravelPagination from '@/components/laravel-pagination';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { User, UserProps } from '@/types/user';
import { formatPhoneBR } from '@/utils/formatPhone';
import { Head, router, useForm, usePage } from '@inertiajs/react';
import { Edit, Filter, Mail, Phone, Search, Trash2, UserCog, UserPlus } from 'lucide-react';
import { useEffect, useState } from 'react';
import { toast } from 'sonner';
import { UserFormDialog } from './UserFormDialog';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Colaboradores',
        href: '/users',
    },
];

interface UserLimitInfo {
    current_users: number;
    max_allowed_users: number | null;
    included_users: number | null;
    additional_users: number;
    has_subscription: boolean;
    is_premium?: boolean;
    is_over_limit: boolean;
    remaining_users: number | null;
    message: string;
}

export default function Users() {
    const { users } = usePage<{ users: UserProps }>().props;
    const { roles } = usePage<{ roles: UserProps['roles'] }>().props;
    const { userLimitInfo } = usePage<{ userLimitInfo: UserLimitInfo }>().props;
    const [openModal, setOpenModal] = useState(false);
    const [selectedUserId, setSelectedUserId] = useState<number | null>(null);
    const [editUser, setEditUser] = useState<UserProps['data'][number] | null>(null);
    const [isFormModalOpen, setIsFormModalOpen] = useState(false);
    const [formMode, setFormMode] = useState<'edit' | 'create'>('create');

    const confirmDelete = (id: number) => {
        setSelectedUserId(id);
        setOpenModal(true);
    };

    function handleDelete(userId: number) {
        router.delete(`/users/${userId}`, {
            onSuccess: () => {
                toast.success('Colaborador excluído com sucesso!');
            },
        });
    }

    const { data, setData, get } = useForm({
        search: users.filters?.search ?? '',
        per_page: users.filters?.per_page?.toString() ?? '10',
        order: users.filters?.order ?? 'name:asc',
        page: users.filters?.page ?? 1,
    });

    // Executa automaticamente quando search muda (com debounce)
    useEffect(() => {
        const timeout = setTimeout(() => {
            get(route('users.index'), { preserveScroll: true, preserveState: true });
        }, 500);

        return () => clearTimeout(timeout);
    }, [data.search, get]);

    // Atualiza ao mudar per_page ou order (sem debounce)
    useEffect(() => {
        get(route('users.index'), { preserveScroll: true, preserveState: true });
    }, [data.per_page, data.order, get]);

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Colaboradores" />
            <div className="space-y-6 p-6">
                {/* Header Section */}
                <div className="flex flex-col justify-between gap-4 md:flex-row md:items-center">
                    <div>
                        <h1 className="text-2xl font-bold tracking-tight">Gestão de Colaboradores</h1>
                        <p className="text-muted-foreground">Gerencie todos os colaboradores da sua clínica</p>
                    </div>
                    <Button
                        onClick={() => {
                            setFormMode('create');
                            setEditUser({
                                name: '',
                                email: '',
                                id_role: 1,
                                phone: '',
                                email_verified_at: null,
                                image: null,
                                id: 0,
                            } as User);
                            setIsFormModalOpen(true);
                        }}
                        className="gap-2"
                        disabled={userLimitInfo.has_subscription && !userLimitInfo.is_premium && userLimitInfo.remaining_users !== null && userLimitInfo.remaining_users <= 0}
                    >
                        <UserPlus className="h-4 w-4" />
                        Novo Colaborador
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
                                        placeholder="Digite o nome do colaborador"
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
                                        <SelectItem value="created_at:desc">Mais recentes</SelectItem>
                                        <SelectItem value="created_at:asc">Mais antigos</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                {/* User status section */}
                {userLimitInfo.has_subscription && (
                    <div
                        className={`rounded-lg border p-4 ${
                            userLimitInfo.is_premium
                                ? 'border-blue-200 bg-blue-50 text-blue-800'
                                : userLimitInfo.is_over_limit || (userLimitInfo.remaining_users !== null && userLimitInfo.remaining_users <= 0)
                                    ? 'border-red-200 bg-red-50 text-red-800'
                                    : 'border-green-200 bg-green-50 text-green-800'
                        }`}
                    >
                        <div className="flex items-center justify-between">
                            <div className="flex items-start gap-3">
                                {userLimitInfo.is_premium ? (
                                    <div className="rounded-full bg-blue-100 p-2">
                                        <span className="text-blue-600">🎉</span>
                                    </div>
                                ) : userLimitInfo.is_over_limit || (userLimitInfo.remaining_users !== null && userLimitInfo.remaining_users <= 0) ? (
                                    <div className="rounded-full bg-red-100 p-2">
                                        <span className="text-red-600">⚠️</span>
                                    </div>
                                ) : (
                                    <div className="rounded-full bg-green-100 p-2">
                                        <span className="text-green-600">✅</span>
                                    </div>
                                )}
                                <div className="flex-1">
                                    <p className="font-medium">{userLimitInfo.message}</p>

                                    {/* Detalhes */}
                                    {!userLimitInfo.is_premium && (
                                        <div className="mt-2 flex flex-wrap gap-4 text-sm">
                                            <div className="flex items-center gap-2">
                                                <span className="font-medium">Incluído:</span>
                                                <span>{userLimitInfo.included_users} usuário</span>
                                            </div>

                                            {userLimitInfo.additional_users > 0 && (
                                                <div className="flex items-center gap-2">
                                                    <span className="font-medium">Adicionais:</span>
                                                    <span>{userLimitInfo.additional_users} usuário(s)</span>
                                                </div>
                                            )}

                                            <div className="flex items-center gap-2">
                                                <span className="font-medium">Total permitido:</span>
                                                <span className="font-bold">{userLimitInfo.max_allowed_users} usuário(s)</span>
                                            </div>
                                        </div>
                                    )}

                                    {/* Status de limite */}
                                    <div className="mt-2">
                                        <div className="flex items-center justify-between text-sm">
                                            <span>
                                                {userLimitInfo.is_premium
                                                    ? 'Usuários ilimitados - cadastre quantos precisar!'
                                                    : userLimitInfo.remaining_users !== null && userLimitInfo.remaining_users <= 0
                                                        ? 'Limite atingido - não é possível cadastrar mais usuários'
                                                        : userLimitInfo.remaining_users !== null
                                                            ? `${userLimitInfo.remaining_users} usuário(s) disponível(is)`
                                                            : 'Sem limite definido'}
                                            </span>
                                            <span className="font-medium">
                                                {userLimitInfo.is_premium
                                                    ? `${userLimitInfo.current_users} usuários`
                                                    : `${userLimitInfo.current_users} / ${userLimitInfo.max_allowed_users}`}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {!userLimitInfo.is_premium && (userLimitInfo.is_over_limit || (userLimitInfo.remaining_users !== null && userLimitInfo.remaining_users <= 0)) && (
                                <Button variant="default" asChild>
                                    <a href={route('billing.index')}>Fazer upgrade</a>
                                </Button>
                            )}
                        </div>
                    </div>
                )}
                {!userLimitInfo.has_subscription && (
                    <div className="rounded-lg border border-blue-200 bg-blue-50 p-4 text-blue-800">
                        <div className="flex items-center gap-3">
                            <div className="rounded-full bg-blue-100 p-2">
                                <span className="text-blue-600">ℹ️</span>
                            </div>
                            <div>
                                <p className="font-medium">{userLimitInfo.message}</p>
                                <Button variant="link" className="mt-1 h-auto p-0 text-blue-700" asChild>
                                    <a href={route('billing.index')}>Ver planos disponíveis</a>
                                </Button>
                            </div>
                        </div>
                    </div>
                )}

                {/* Users Table */}
                <Card className="border-border bg-card">
                    <CardHeader>
                        <div className="flex items-center justify-between">
                            <div>
                                <CardTitle className="text-lg font-semibold">Lista de Colaboradores</CardTitle>
                                <CardDescription>{users.total} colaborador(es) encontrado(s)</CardDescription>
                            </div>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div className="rounded-md border">
                            <Table>
                                <TableHeader>
                                    <TableRow className="hover:bg-transparent">
                                        <TableHead className="w-16">Avatar</TableHead>
                                        <TableHead>Nome</TableHead>
                                        <TableHead>Contato</TableHead>
                                        <TableHead>Função</TableHead>
                                        <TableHead className="w-32 text-right">Ações</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {users.data.map((user) => (
                                        <TableRow key={user.id} className="group hover:bg-muted/50">
                                            <TableCell>
                                                <Avatar className="h-10 w-10 border-2 border-muted">
                                                    {user.image ? (
                                                        <AvatarImage src={user.image_url} alt={user.name} />
                                                    ) : (
                                                        <AvatarFallback className="bg-primary/10 font-medium text-primary">
                                                            {user.name[0].toUpperCase()}
                                                        </AvatarFallback>
                                                    )}
                                                </Avatar>
                                            </TableCell>
                                            <TableCell>
                                                <div className="font-medium">{user.name}</div>
                                            </TableCell>
                                            <TableCell>
                                                <div className="space-y-1">
                                                    <div className="flex items-center gap-2 text-sm text-muted-foreground">
                                                        <Mail className="h-4 w-4" />
                                                        {user.email}
                                                    </div>
                                                    {user.phone && (
                                                        <div className="flex items-center gap-2 text-sm text-muted-foreground">
                                                            <Phone className="h-4 w-4" />
                                                            {formatPhoneBR(user.phone)}
                                                        </div>
                                                    )}
                                                </div>
                                            </TableCell>
                                            <TableCell>
                                                <Badge variant="secondary" className="flex w-fit items-center gap-1">
                                                    <UserCog className="h-3 w-3" />
                                                    {roles.find((role) => role.id === user.id_role)?.name || 'Sem função'}
                                                </Badge>
                                            </TableCell>
                                            <TableCell>
                                                <div className="flex justify-end gap-1">
                                                    <Button
                                                        size="sm"
                                                        variant="outline"
                                                        className="h-8 w-8 p-0"
                                                        onClick={() => {
                                                            setFormMode('edit');
                                                            setEditUser(user);
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
                                                        onClick={() => confirmDelete(user.id)}
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

                        {users.data.length === 0 && (
                            <div className="flex flex-col items-center justify-center py-12 text-center">
                                <Search className="mb-4 h-12 w-12 text-muted-foreground" />
                                <h3 className="text-lg font-medium">Nenhum colaborador encontrado</h3>
                                <p className="text-muted-foreground">
                                    {data.search ? 'Tente ajustar os termos da busca' : 'Comece adicionando seu primeiro colaborador'}
                                </p>
                            </div>
                        )}

                        <div className="mt-6">
                            <LaravelPagination links={users.links} onPageChange={(page) => setData('page', page)} />
                        </div>
                    </CardContent>
                </Card>
            </div>

            <ConfirmDeleteDialog
                confirmText="Tem certeza que deseja excluir este colaborador?"
                description="Esta ação não pode ser desfeita. Todos os dados do colaborador serão permanentemente excluídos."
                open={openModal}
                onClose={() => setOpenModal(false)}
                onConfirm={() => {
                    if (selectedUserId !== null) {
                        handleDelete(selectedUserId);
                        setOpenModal(false);
                    }
                }}
            />

            <UserFormDialog
                open={isFormModalOpen}
                onClose={() => setIsFormModalOpen(false)}
                mode={formMode}
                user={editUser}
                setUser={setEditUser}
                roles={roles}
            />
        </AppLayout>
    );
}
