import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Appointment } from '@/types/appointment';
import { AppointmentStatusBadge } from '@/components/appointments/AppointmentStatusBadge';
import {
    Calendar,
    CheckCircle2,
    Clock,
    Edit,
    FileText,
    MoreHorizontal,
    Plus,
    Trash2,
    User,
    MapPin,
    XCircle,
    PlayCircle,
    Eye,
} from 'lucide-react';
import { formatDistanceToNow } from 'date-fns';
import { ptBR } from 'date-fns/locale';
import { router } from '@inertiajs/react';

interface AppointmentListViewProps {
    appointments: {
        data: Appointment[];
        total: number;
        current_page: number;
        per_page: number;
        last_page: number;
    };
    onEdit: (appointment: Appointment) => void;
    onDelete: (id: number) => void;
    onStatusUpdate: (id: number, status: string) => void;
    onView: (appointment: Appointment) => void;
    statusColors: Record<string, string>;
    statusLabels: Record<string, string>;
}

export function AppointmentListView({
    appointments,
    onEdit,
    onDelete,
    onStatusUpdate,
    onView,
    statusColors,
    statusLabels,
}: AppointmentListViewProps) {
    const formatTime = (time: string) => {
        return time.slice(0, 5);
    };

    const formatDate = (date: string) => {
        return new Date(date).toLocaleDateString('pt-BR');
    };

    const getTimeFromNow = (date: string, time: string) => {
        try {
            // Garantir formato correto da data e hora
            const dateStr = date.includes('T') ? date.split('T')[0] : date;
            const timeStr = time.slice(0, 8); // HH:MM:SS format
            const appointmentDate = new Date(`${dateStr}T${timeStr}`);

            if (isNaN(appointmentDate.getTime())) {
                return 'Data inválida';
            }

            return formatDistanceToNow(appointmentDate, {
                addSuffix: true,
                locale: ptBR
            });
        } catch (error) {
            return 'Data inválida';
        }
    };


    return (
        <Card>
            <CardHeader>
                <CardTitle className="flex items-center justify-between">
                    <span>Lista de Agendamentos</span>
                    <span className="text-sm font-normal text-muted-foreground">
                        {appointments.total} agendamento(s) encontrado(s)
                    </span>
                </CardTitle>
            </CardHeader>
            <CardContent>
                <div className="rounded-md border">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Paciente</TableHead>
                                <TableHead>Data/Hora</TableHead>
                                <TableHead>Profissional</TableHead>
                                <TableHead>Serviço</TableHead>
                                <TableHead>Local</TableHead>
                                <TableHead>Status</TableHead>
                                <TableHead className="w-16">Ações</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {appointments.data.map((appointment) => (
                                <TableRow key={appointment.id} className="group hover:bg-muted/50">
                                    <TableCell className="font-medium">
                                        <div className="flex items-center gap-2">
                                            <User className="h-4 w-4 text-muted-foreground" />
                                            <div>
                                                <p className="font-semibold">{appointment.customer.name}</p>
                                                <p className="text-xs text-muted-foreground">
                                                    {appointment.customer.phone}
                                                </p>
                                            </div>
                                        </div>
                                    </TableCell>
                                    <TableCell>
                                        <div className="flex items-center gap-2">
                                            <Calendar className="h-4 w-4 text-muted-foreground" />
                                            <div>
                                                <p className="font-medium">{formatDate(appointment.date)}</p>
                                                <p className="text-xs text-muted-foreground">
                                                    {formatTime(appointment.start_time)} - {formatTime(appointment.end_time)}
                                                </p>
                                                <p className="text-xs text-muted-foreground">
                                                    {getTimeFromNow(appointment.date, appointment.start_time)}
                                                </p>
                                            </div>
                                        </div>
                                    </TableCell>
                                    <TableCell>
                                        <div className="flex items-center gap-2">
                                            <div className="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center">
                                                <span className="text-xs font-medium text-blue-700">
                                                    {appointment.user?.name?.charAt(0).toUpperCase()}
                                                </span>
                                            </div>
                                            <span>{appointment.user?.name}</span>
                                        </div>
                                    </TableCell>
                                    <TableCell>
                                        <div>
                                            <p className="font-medium">{appointment.service?.name}</p>
                                            {appointment.service?.price && (
                                                <p className="text-xs text-muted-foreground">
                                                    {new Intl.NumberFormat('pt-BR', {
                                                        style: 'currency',
                                                        currency: 'BRL',
                                                    }).format(appointment.service.price)}
                                                </p>
                                            )}
                                        </div>
                                    </TableCell>
                                    <TableCell>
                                        <div className="flex items-center gap-2">
                                            <MapPin className="h-4 w-4 text-muted-foreground" />
                                            <span>{appointment.room?.name}</span>
                                        </div>
                                    </TableCell>
                                    <TableCell>
                                        <AppointmentStatusBadge status={appointment.status} />
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
                                                        setTimeout(() => onView(appointment), 0);
                                                    }}
                                                >
                                                    <Eye className="mr-2 h-4 w-4" />
                                                    Visualizar
                                                </DropdownMenuItem>
                                                <DropdownMenuSeparator />
                                                <DropdownMenuItem
                                                    onClick={(e) => {
                                                        e.preventDefault();
                                                        e.stopPropagation();
                                                        router.visit(`/medical-records/patient/${appointment.customer.id}`);
                                                    }}
                                                >
                                                    <FileText className="mr-2 h-4 w-4" />
                                                    Ver Prontuário
                                                </DropdownMenuItem>
                                                <DropdownMenuItem
                                                    onClick={(e) => {
                                                        e.preventDefault();
                                                        e.stopPropagation();
                                                        router.visit(`/medical-records/patient/${appointment.customer.id}/create?appointment=${appointment.id}`);
                                                    }}
                                                >
                                                    <Plus className="mr-2 h-4 w-4" />
                                                    Novo Prontuário
                                                </DropdownMenuItem>
                                                <DropdownMenuSeparator />
                                                <DropdownMenuItem
                                                    onClick={(e) => {
                                                        e.preventDefault();
                                                        e.stopPropagation();
                                                        onDelete(appointment.id);
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

                {appointments.data.length === 0 && (
                    <div className="text-center py-12">
                        <Calendar className="mx-auto h-12 w-12 text-muted-foreground" />
                        <h3 className="mt-4 text-lg font-medium">Nenhum agendamento encontrado</h3>
                        <p className="text-muted-foreground">
                            Não há agendamentos para os filtros selecionados.
                        </p>
                    </div>
                )}

                {/* Pagination info */}
                {appointments.total > 0 && (
                    <div className="flex items-center justify-between px-2 py-4">
                        <div className="text-sm text-muted-foreground">
                            Mostrando {((appointments.current_page - 1) * appointments.per_page) + 1} a{' '}
                            {Math.min(appointments.current_page * appointments.per_page, appointments.total)} de{' '}
                            {appointments.total} agendamentos
                        </div>
                        <div className="text-sm text-muted-foreground">
                            Página {appointments.current_page} de {appointments.last_page}
                        </div>
                    </div>
                )}
            </CardContent>
        </Card>
    );
}