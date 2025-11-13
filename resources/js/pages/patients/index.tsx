import ConfirmDeleteDialog from '@/components/ConfirmDeleteDialog';
import LaravelPagination from '@/components/laravel-pagination';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/app-layout';
import { SharedData, type BreadcrumbItem } from '@/types';
import { Patient, PatientProps } from '@/types/patient';
import { formatPhoneBR } from '@/utils/formatPhone';
import { Head, Link, router, useForm, usePage } from '@inertiajs/react';
import { format, parseISO } from 'date-fns';
import { Calendar, CalendarPlus, Edit, Filter, Phone, Search, StickyNote, Trash2, UserPlus } from 'lucide-react';
import { useEffect, useState } from 'react';
import { toast } from 'sonner';
import { PatientFormDialog } from './PatientFormDialog';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Pacientes',
        href: '/patients',
    },
];

export default function Patients() {
    const { patients } = usePage<{ patients: PatientProps }>().props;
    const [openModal, setOpenModal] = useState(false);
    const [selectedPatientId, setSelectedPatientId] = useState<number | null>(null);
    const [editPatient, setEditPatient] = useState<PatientProps['data'][number] | null>(null);
    const [isFormModalOpen, setIsFormModalOpen] = useState(false);
    const [formMode, setFormMode] = useState<'edit' | 'create'>('create');

    const { auth } = usePage<SharedData>().props;

    const confirmDelete = (id: number) => {
        setSelectedPatientId(id);
        setOpenModal(true);
    };

    function handleDelete(patientId: number) {
        router.delete(`/patients/${patientId}`, {
            onSuccess: () => {
                toast.success('Paciente excluído com sucesso!');
            },
            onError: () => {
                toast.error('Erro ao excluir paciente');
            },
        });
    }

    const { data, setData, get } = useForm({
        search: patients.filters?.search ?? '',
        per_page: patients.filters?.per_page?.toString() ?? '10',
        order: patients.filters?.order ?? 'name:asc',
        page: patients.filters?.page ?? 1,
    });

    // Executa automaticamente quando search muda (com debounce)
    useEffect(() => {
        const timeout = setTimeout(() => {
            get(route('patients.index'), { preserveScroll: true, preserveState: true });
        }, 500);
        return () => clearTimeout(timeout);
    }, [data.search, data.page, get]);

    // Atualiza ao mudar per_page ou order (sem debounce)
    useEffect(() => {
        get(route('patients.index'), { preserveScroll: true, preserveState: true });
    }, [data.search, data.per_page, data.order, data.page, get]);

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Pacientes" />
            <div className="space-y-6 p-6">
                {/* Header Section */}
                <div className="flex flex-col justify-between gap-4 md:flex-row md:items-center">
                    <div>
                        <h1 className="text-2xl font-bold tracking-tight">Gestão de Pacientes</h1>
                        <p className="text-muted-foreground">Gerencie todos os pacientes da sua clínica</p>
                    </div>
                    <Button
                        onClick={() => {
                            setFormMode('create');
                            setEditPatient({
                                name: '',
                                phone: '',
                                birthdate: '',
                                notes: '',
                            } as Patient);
                            setIsFormModalOpen(true);
                        }}
                        className="gap-2"
                    >
                        <UserPlus className="h-4 w-4" />
                        Novo Paciente
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
                                        placeholder="Digite o nome do paciente"
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

                {/* Patients Table */}
                <Card className="border-border bg-card">
                    <CardHeader>
                        <div className="flex items-center justify-between">
                            <div>
                                <CardTitle className="text-lg font-semibold">Lista de Pacientes</CardTitle>
                                <CardDescription>{patients.total} paciente(s) encontrado(s)</CardDescription>
                            </div>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div className="rounded-md border">
                            <Table>
                                <TableHeader>
                                    <TableRow className="hover:bg-transparent">
                                        <TableHead className="w-12">Agendar</TableHead>
                                        <TableHead>Nome</TableHead>
                                        <TableHead>Telefone</TableHead>
                                        <TableHead>Data de Nascimento</TableHead>
                                        <TableHead>Anotações</TableHead>
                                        <TableHead className="w-40 text-right">Ações</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {patients.data.map((patient) => (
                                        <TableRow key={patient.id} className="group hover:bg-muted/50">
                                            <TableCell>
                                                <Link href={`/patients/${patient.id}/appointments`}>
                                                    <Button variant="ghost" size="icon" className="h-8 w-8">
                                                        <CalendarPlus className="h-4 w-4" />
                                                    </Button>
                                                </Link>
                                            </TableCell>
                                            <TableCell>
                                                <div className="font-medium">{patient.name}</div>
                                            </TableCell>
                                            <TableCell>
                                                <div className="flex items-center gap-2 text-muted-foreground">
                                                    <Phone className="h-4 w-4" />
                                                    {patient.phone ? formatPhoneBR(patient.phone) : 'Não informado'}
                                                </div>
                                            </TableCell>
                                            <TableCell>
                                                <div className="flex items-center gap-2 text-muted-foreground">
                                                    <Calendar className="h-4 w-4" />
                                                    {patient.birthdate ? format(parseISO(patient.birthdate), 'dd/MM/yyyy') : 'Não informado'}
                                                </div>
                                            </TableCell>
                                            <TableCell>
                                                <div className="flex items-center gap-2 text-muted-foreground">
                                                    <StickyNote className="h-4 w-4" />
                                                    {patient.notes
                                                        ? patient.notes.slice(0, 50) + (patient.notes.length > 50 ? '...' : '')
                                                        : 'Nenhuma anotação'}
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
                                                            setEditPatient(patient);
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
                                                        onClick={() => confirmDelete(patient.id)}
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

                        {patients.data.length === 0 && (
                            <div className="flex flex-col items-center justify-center py-12 text-center">
                                <Search className="mb-4 h-12 w-12 text-muted-foreground" />
                                <h3 className="text-lg font-medium">Nenhum paciente encontrado</h3>
                                <p className="text-muted-foreground">
                                    {data.search ? 'Tente ajustar os termos da busca' : 'Comece adicionando seu primeiro paciente'}
                                </p>
                            </div>
                        )}

                        <div className="mt-6">
                            <LaravelPagination links={patients.links} onPageChange={(page) => setData('page', page)} />
                        </div>
                    </CardContent>
                </Card>
            </div>

            <ConfirmDeleteDialog
                confirmText="Tem certeza que deseja excluir este paciente?"
                description="Esta ação não pode ser desfeita. Todos os dados relacionados a este paciente serão perdidos."
                open={openModal}
                onClose={() => setOpenModal(false)}
                onConfirm={() => {
                    if (selectedPatientId !== null) {
                        handleDelete(selectedPatientId);
                        setOpenModal(false);
                    }
                }}
            />

            <PatientFormDialog
                open={isFormModalOpen}
                onClose={() => setIsFormModalOpen(false)}
                patient={editPatient}
                setPatient={setEditPatient}
                mode={formMode}
            />

        </AppLayout>
    );
}
