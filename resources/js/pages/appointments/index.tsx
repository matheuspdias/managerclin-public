import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Appointment } from '@/types/appointment';
import { Patient } from '@/types/patient';
import { Room } from '@/types/room';
import { Service } from '@/types/service';
import { User } from '@/types/user';
import { Head, router, useForm, usePage } from '@inertiajs/react';
import { useCallback, useEffect, useState } from 'react';
import { toast } from 'sonner';

import ConfirmDeleteDialog from '@/components/ConfirmDeleteDialog';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { CalendarDays, Calendar as CalendarIcon, Clock, Filter, List, Plus, Search } from 'lucide-react';
import { AppointmentCalendarView } from './AppointmentCalendarView';
import { AppointmentFormDialog } from './AppointmentFormDialog';
import { AppointmentListView } from './AppointmentListView';
import { AppointmentStatsCards } from './AppointmentStatsCards';
import { AppointmentTimelineView } from './AppointmentTimelineView';
import { AppointmentWeekView } from './AppointmentWeekView';
import { AppointmentViewDialog } from './AppointmentViewDialog';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Agendamentos',
        href: '/appointments',
    },
];

interface AppointmentPageProps extends Record<string, unknown> {
    appointments: {
        data: Appointment[];
        total: number;
        current_page: number;
        per_page: number;
        last_page: number;
    };
    appointmentsForCalendar: Appointment[];
    services: Service[];
    users: User[];
    customers: Patient[];
    rooms: Room[];
    period: {
        start_date: string;
        end_date: string;
    };
    filters: {
        search?: string;
        status?: string;
        user_id?: number;
        start_date: string;
        end_date: string;
    };
    pagination: {
        page: number;
        per_page: number;
    };
    currentView: string;
    stats: {
        total: number;
        scheduled: number;
        completed: number;
        cancelled: number;
        today: number;
        week: number;
        revenue: number;
    };
    currentUser: {
        id: number;
        is_admin: boolean;
    };
}

export default function Appointments() {
    const pageProps = usePage<AppointmentPageProps>().props;

    const {
        appointments = { data: [], total: 0, current_page: 1, per_page: 15, last_page: 1 },
        appointmentsForCalendar = [],
        services = [],
        users = [],
        customers = [],
        rooms = [],
        filters = { search: '', status: '', user_id: '', start_date: '', end_date: '' },
        pagination = { page: 1, per_page: 15 },
        currentView = 'calendar',
        stats = { total: 0, scheduled: 0, completed: 0, cancelled: 0, today: 0, week: 0, revenue: 0 },
        currentUser = { id: 0, is_admin: false },
    } = pageProps;

    const [openModal, setOpenModal] = useState(false);
    const [selectedAppointmentId, setSelectedAppointmentId] = useState<number | null>(null);
    const [isFormModalOpen, setIsFormModalOpen] = useState(false);
    const [formMode, setFormMode] = useState<'edit' | 'create'>('create');
    const [editAppointment, setEditAppointment] = useState<Appointment | null>(null);
    const [activeView, setActiveView] = useState(currentView || 'calendar');
    const [isViewModalOpen, setIsViewModalOpen] = useState(false);
    const [viewAppointment, setViewAppointment] = useState<Appointment | null>(null);

    // Function to properly close and reset the form modal
    const closeFormModal = useCallback(() => {
        setIsFormModalOpen(false);
        // Use setTimeout to ensure the modal closes before resetting state
        setTimeout(() => {
            setEditAppointment(null);
            setFormMode('create');
        }, 150);
    }, []);

    // Function to open view modal
    const openViewModal = useCallback((appointment: Appointment) => {
        setViewAppointment(appointment);
        setIsViewModalOpen(true);
    }, []);

    // Function to close view modal
    const closeViewModal = useCallback(() => {
        setIsViewModalOpen(false);
        setTimeout(() => {
            setViewAppointment(null);
        }, 150);
    }, []);

    const { data, setData } = useForm({
        search: filters.search || '',
        status: filters.status || 'all',
        user_id: filters.user_id ? filters.user_id.toString() : '',
        start_date: filters.start_date,
        end_date: filters.end_date,
        view: activeView,
        page: pagination.page,
        per_page: pagination.per_page,
    });

    const handleGet = useCallback(() => {
        // Converter "all" para string vazia antes de enviar
        const queryParams = new URLSearchParams();

        if (data.search) queryParams.append('search', data.search);
        if (data.status && data.status !== 'all') queryParams.append('status', data.status);
        if (data.user_id) queryParams.append('user_id', data.user_id);
        if (data.start_date) queryParams.append('start_date', data.start_date);
        if (data.end_date) queryParams.append('end_date', data.end_date);
        if (data.view) queryParams.append('view', data.view);
        if (data.page) queryParams.append('page', data.page.toString());
        if (data.per_page) queryParams.append('per_page', data.per_page.toString());

        const url = `/appointments${queryParams.toString() ? '?' + queryParams.toString() : ''}`;

        router.get(
            url,
            {},
            {
                preserveScroll: true,
                preserveState: true,
                only: ['appointments', 'appointmentsForCalendar', 'stats'],
            },
        );
    }, [data]);

    // Auto-search with debounce
    useEffect(() => {
        const timeout = setTimeout(() => {
            handleGet();
        }, 500);

        return () => clearTimeout(timeout);
    }, [data.search, handleGet]);

    // Inicializar filtro de profissional quando users estiver disponível
    useEffect(() => {
        if (users.length > 0 && (!data.user_id || data.user_id === 'all')) {
            setData('user_id', users[0].id.toString());
        }
    }, [users, data.user_id, setData]);

    // Update filters immediately
    useEffect(() => {
        handleGet();
    }, [data.status, data.user_id, data.start_date, data.end_date, data.view, data.per_page, handleGet]);

    function handleDelete(appointmentId: number) {
        router.delete(`/appointments/${appointmentId}`, {
            onSuccess: () => {
                toast.success('Agendamento excluído com sucesso!');
            },
            onError: () => {
                toast.error('Erro ao excluir agendamento');
            },
        });
    }

    function handleStatusUpdate(appointmentId: number, newStatus: string, onSuccessCallback?: () => void) {
        router.patch(
            `/appointments/${appointmentId}/status`,
            { status: newStatus },
            {
                onSuccess: () => {
                    toast.success('Status atualizado com sucesso!');
                    if (onSuccessCallback) {
                        onSuccessCallback();
                    }
                },
                onError: () => {
                    toast.error('Erro ao atualizar status');
                },
            },
        );
    }

    function createNewAppointment() {
        const now = new Date();
        const end = new Date(now.getTime() + 60 * 60 * 1000);

        setFormMode('create');
        setEditAppointment({
            id: 0,
            id_customer: customers[0]?.id ?? 0,
            id_service: services[0]?.id ?? 0,
            id_room: rooms[0]?.id ?? 0,
            id_user: currentUser.is_admin ? (users[0]?.id ?? 0) : currentUser.id,
            date: now.toISOString().split('T')[0],
            start_time: now.toTimeString().slice(0, 5),
            end_time: end.toTimeString().slice(0, 5),
            status: 'SCHEDULED',
            notes: '',
        } as Appointment);
        setIsFormModalOpen(true);
    }

    function editExistingAppointment(appointment: Appointment) {
        setFormMode('edit');
        setEditAppointment({
            ...appointment,
            date: appointment.date.split('T')[0],
        });
        setIsFormModalOpen(true);
    }

    const statusColors = {
        SCHEDULED: 'bg-blue-100 text-blue-800',
        COMPLETED: 'bg-green-100 text-green-800',
        CANCELLED: 'bg-red-100 text-red-800',
    };

    const statusLabels = {
        SCHEDULED: 'Agendado',
        COMPLETED: 'Concluído',
        CANCELLED: 'Cancelado',
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Agendamentos" />

            <div className="space-y-6 p-6">
                {/* Header Section */}
                <div className="flex flex-col justify-between gap-4 md:flex-row md:items-center">
                    <div>
                        <h1 className="text-2xl font-bold tracking-tight">Agenda Médica</h1>
                        <p className="text-muted-foreground">Gerencie consultas, horários e acompanhe o atendimento da clínica</p>
                    </div>
                    <Button
                        onClick={createNewAppointment}
                        className="gap-2 bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700"
                    >
                        <Plus className="h-4 w-4" />
                        Novo Agendamento
                    </Button>
                </div>

                {/* Stats Cards */}
                <AppointmentStatsCards
                    stats={stats}
                    onFilterByStatus={(status) => {
                        setData('status', status || 'all');
                        setData('page', 1);
                    }}
                />

                {/* Filters Section */}
                <Card>
                    <CardHeader className="pb-4">
                        <CardTitle className="flex items-center gap-2 text-lg">
                            <Filter className="h-5 w-5" />
                            Filtros e Busca
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div className={`grid grid-cols-1 gap-4 ${currentUser.is_admin ? 'md:grid-cols-6' : 'md:grid-cols-5'}`}>
                            {/* Search */}
                            <div className="space-y-2 md:col-span-2">
                                <label className="text-sm font-medium">Buscar</label>
                                <div className="relative">
                                    <Search className="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                                    <Input
                                        placeholder="Paciente, médico ou serviço..."
                                        value={data.search}
                                        onChange={(e) => setData('search', e.target.value)}
                                        className="pl-10"
                                    />
                                </div>
                            </div>

                            {/* Status Filter */}
                            <div className="space-y-2">
                                <label className="text-sm font-medium">Status</label>
                                <Select value={data.status} onValueChange={(value) => setData('status', value)}>
                                    <SelectTrigger>
                                        <SelectValue placeholder="Todos" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="all">Todos</SelectItem>
                                        <SelectItem value="SCHEDULED">Agendado</SelectItem>
                                        <SelectItem value="COMPLETED">Concluído</SelectItem>
                                        <SelectItem value="CANCELLED">Cancelado</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>

                            {/* Professional Filter - Show for admins and receptionists */}
                            {(currentUser.is_admin || users.length > 1) && (
                                <div className="space-y-2">
                                    <label className="text-sm font-medium">Profissional</label>
                                    <Select value={data.user_id.toString()} onValueChange={(value) => setData('user_id', value)}>
                                        <SelectTrigger>
                                            <SelectValue placeholder="Selecione um profissional" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            {users.map((user) => (
                                                <SelectItem key={user.id} value={user.id.toString()}>
                                                    {user.name}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                </div>
                            )}

                            {/* Date Range */}
                            <div className="space-y-2">
                                <label className="text-sm font-medium">Data inicial</label>
                                <Input type="date" value={data.start_date} onChange={(e) => setData('start_date', e.target.value)} />
                            </div>

                            <div className="space-y-2">
                                <label className="text-sm font-medium">Data final</label>
                                <Input type="date" value={data.end_date} onChange={(e) => setData('end_date', e.target.value)} />
                            </div>
                        </div>
                    </CardContent>
                </Card>

                {/* View Tabs */}
                <Tabs
                    value={activeView}
                    onValueChange={(value) => {
                        setActiveView(value);
                        setData('view', value);
                    }}
                >
                    <TabsList className="grid w-full grid-cols-4">
                        <TabsTrigger value="calendar" className="flex items-center gap-2">
                            <CalendarDays className="h-4 w-4" />
                            Calendário
                        </TabsTrigger>
                        <TabsTrigger value="week" className="flex items-center gap-2">
                            <CalendarIcon className="h-4 w-4" />
                            Semana
                        </TabsTrigger>
                        <TabsTrigger value="list" className="flex items-center gap-2">
                            <List className="h-4 w-4" />
                            Lista
                        </TabsTrigger>
                        <TabsTrigger value="timeline" className="flex items-center gap-2">
                            <Clock className="h-4 w-4" />
                            Timeline
                        </TabsTrigger>
                    </TabsList>

                    <TabsContent value="calendar" className="mt-6">
                        <AppointmentCalendarView
                            appointments={appointmentsForCalendar}
                            onEdit={editExistingAppointment}
                            onCreate={createNewAppointment}
                            onStatusUpdate={handleStatusUpdate}
                            onView={openViewModal}
                        />
                    </TabsContent>

                    <TabsContent value="week" className="mt-6">
                        <AppointmentWeekView
                            appointments={appointmentsForCalendar}
                            users={users}
                            rooms={rooms}
                            onEdit={editExistingAppointment}
                            onStatusUpdate={handleStatusUpdate}
                            onView={openViewModal}
                        />
                    </TabsContent>

                    <TabsContent value="list" className="mt-6">
                        <AppointmentListView
                            appointments={appointments}
                            onEdit={editExistingAppointment}
                            onDelete={(id) => {
                                setSelectedAppointmentId(id);
                                setOpenModal(true);
                            }}
                            onStatusUpdate={handleStatusUpdate}
                            onView={openViewModal}
                            statusColors={statusColors}
                            statusLabels={statusLabels}
                        />
                    </TabsContent>

                    <TabsContent value="timeline" className="mt-6">
                        <AppointmentTimelineView
                            appointments={appointmentsForCalendar}
                            users={users}
                            rooms={rooms}
                            onEdit={editExistingAppointment}
                            onStatusUpdate={handleStatusUpdate}
                            onView={openViewModal}
                        />
                    </TabsContent>
                </Tabs>
            </div>

            {/* Dialogs */}
            <ConfirmDeleteDialog
                confirmText="Tem certeza que deseja excluir este agendamento?"
                description="Esta ação não pode ser desfeita. Todos os dados relacionados ao agendamento serão perdidos."
                open={openModal}
                onClose={() => setOpenModal(false)}
                onConfirm={() => {
                    if (selectedAppointmentId !== null) {
                        handleDelete(selectedAppointmentId);
                        setOpenModal(false);
                    }
                }}
            />

            <AppointmentFormDialog
                open={isFormModalOpen}
                onClose={closeFormModal}
                appointment={editAppointment}
                setAppointment={setEditAppointment}
                services={services}
                users={users}
                customers={customers}
                rooms={rooms}
                mode={formMode}
                currentUser={currentUser}
            />

            <AppointmentViewDialog
                open={isViewModalOpen}
                onClose={closeViewModal}
                appointment={viewAppointment}
                onEdit={editExistingAppointment}
                onStatusUpdate={handleStatusUpdate}
            />
        </AppLayout>
    );
}
