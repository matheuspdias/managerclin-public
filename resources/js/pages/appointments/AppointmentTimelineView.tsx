import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Appointment } from '@/types/appointment';
import { User } from '@/types/user';
import { Room } from '@/types/room';
import { AppointmentStatusBadge } from '@/components/appointments/AppointmentStatusBadge';
import {
    Clock,
    User as UserIcon,
    MapPin,
    ChevronLeft,
    ChevronRight,
    Edit,
    CheckCircle2,
    XCircle,
    PlayCircle,
    Eye
} from 'lucide-react';
import { useState, useEffect } from 'react';
import {
    format,
    addDays,
    subDays,
    isSameDay,
    parseISO
} from 'date-fns';
import { ptBR } from 'date-fns/locale';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';

interface AppointmentTimelineViewProps {
    appointments: Appointment[];
    users: User[];
    rooms: Room[];
    onEdit: (appointment: Appointment) => void;
    onStatusUpdate: (id: number, status: string) => void;
    onView: (appointment: Appointment) => void;
}

export function AppointmentTimelineView({
    appointments,
    users,
    rooms,
    onEdit,
    onStatusUpdate,
    onView,
}: AppointmentTimelineViewProps) {
    const [currentDate, setCurrentDate] = useState(new Date());

    // Backend já filtra os usuários conforme permissão
    // Sempre seleciona o primeiro usuário disponível
    const initialSelectedUser = users.length > 0 ? users[0].id.toString() : '';

    const [selectedUser, setSelectedUser] = useState<string>(initialSelectedUser);
    const [selectedRoom, setSelectedRoom] = useState<string>('all');
    const [workingHours, setWorkingHours] = useState<{ start_time: string; end_time: string; has_schedule: boolean }>({
        start_time: '08:00',
        end_time: '18:00',
        has_schedule: false,
    });
    const [isLoadingHours, setIsLoadingHours] = useState(false);

    const previousDay = () => {
        setCurrentDate(subDays(currentDate, 1));
    };

    const nextDay = () => {
        setCurrentDate(addDays(currentDate, 1));
    };

    const today = () => {
        setCurrentDate(new Date());
    };

    // Carregar horários de trabalho do profissional selecionado
    useEffect(() => {
        if (!selectedUser) return;

        const loadWorkingHours = async () => {
            setIsLoadingHours(true);
            try {
                const dateParam = format(currentDate, 'yyyy-MM-dd');
                const response = await fetch(`/api/users/${selectedUser}/working-hours?date=${dateParam}`, {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                    },
                });

                if (response.ok) {
                    const data = await response.json();
                    setWorkingHours({
                        start_time: data.start_time,
                        end_time: data.end_time,
                        has_schedule: data.has_schedule,
                    });
                }
            } catch (error) {
                console.error('Erro ao carregar horários de trabalho:', error);
            } finally {
                setIsLoadingHours(false);
            }
        };

        loadWorkingHours();
    }, [selectedUser, currentDate]);

    // Filtrar agendamentos por data e filtros selecionados
    const filteredAppointments = appointments.filter(appointment => {
        const appointmentDate = parseISO(appointment.date);
        const isDateMatch = isSameDay(appointmentDate, currentDate);
        const isUserMatch = appointment.id_user.toString() === selectedUser;
        const isRoomMatch = selectedRoom === 'all' || appointment.id_room?.toString() === selectedRoom;

        return isDateMatch && isUserMatch && isRoomMatch;
    });

    // Ordenar por horário
    const sortedAppointments = filteredAppointments.sort((a, b) => {
        return a.start_time.localeCompare(b.start_time);
    });

    // Gerar slots de horário baseado no horário real do profissional
    const generateTimeSlots = () => {
        const slots: string[] = [];

        // Extrair hora de início e fim
        const [startHour, startMinute] = workingHours.start_time.split(':').map(Number);
        const [endHour, endMinute] = workingHours.end_time.split(':').map(Number);

        // Criar slots de 30 em 30 minutos
        let currentHour = startHour;
        let currentMinute = startMinute;

        while (
            currentHour < endHour ||
            (currentHour === endHour && currentMinute <= endMinute)
        ) {
            slots.push(
                `${currentHour.toString().padStart(2, '0')}:${currentMinute.toString().padStart(2, '0')}`
            );

            // Avançar 30 minutos
            currentMinute += 30;
            if (currentMinute >= 60) {
                currentMinute = 0;
                currentHour++;
            }
        }

        return slots;
    };

    const timeSlots = generateTimeSlots();

    const getAppointmentForTime = (time: string) => {
        return sortedAppointments.find(appointment => {
            const startTime = appointment.start_time.slice(0, 5);
            return startTime === time;
        });
    };

    const statusColors = {
        SCHEDULED: 'bg-blue-100 text-blue-800 border-blue-200',
        IN_PROGRESS: 'bg-amber-100 text-amber-800 border-amber-200',
        COMPLETED: 'bg-green-100 text-green-800 border-green-200',
        CANCELLED: 'bg-red-100 text-red-800 border-red-200',
    };

    return (
        <Card>
            <CardHeader>
                <CardTitle className="flex items-center justify-between">
                    <div className="flex items-center gap-2">
                        <Clock className="h-5 w-5" />
                        <span>Timeline do Dia</span>
                    </div>
                    <div className="flex items-center gap-2">
                        <Button variant="outline" size="sm" onClick={previousDay}>
                            <ChevronLeft className="h-4 w-4" />
                        </Button>
                        <Button variant="outline" size="sm" onClick={today}>
                            Hoje
                        </Button>
                        <div className="min-w-[120px] text-center">
                            <span className="font-semibold">
                                {format(currentDate, 'dd/MM/yyyy', { locale: ptBR })}
                            </span>
                        </div>
                        <Button variant="outline" size="sm" onClick={nextDay}>
                            <ChevronRight className="h-4 w-4" />
                        </Button>
                    </div>
                </CardTitle>
            </CardHeader>
            <CardContent>
                {/* Filtros */}
                <div className="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                    <div className="space-y-2">
                        <label className="text-sm font-medium">Profissional</label>
                        <Select value={selectedUser} onValueChange={setSelectedUser}>
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

                    <div className="space-y-2">
                        <label className="text-sm font-medium">Consultório</label>
                        <Select value={selectedRoom} onValueChange={setSelectedRoom}>
                            <SelectTrigger>
                                <SelectValue placeholder="Todos os consultórios" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">Todos os consultórios</SelectItem>
                                {rooms.map((room) => (
                                    <SelectItem key={room.id} value={room.id.toString()}>
                                        {room.name}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                    </div>
                </div>

                {/* Informação sobre horários */}
                {!workingHours.has_schedule && (
                    <div className="mb-4 p-3 bg-amber-50 dark:bg-amber-950/20 border border-amber-200 dark:border-amber-800 rounded-lg">
                        <div className="flex items-center gap-2 text-amber-800 dark:text-amber-200">
                            <Clock className="h-4 w-4" />
                            <span className="text-sm">
                                Horário padrão (08:00 - 18:00). Configure o horário de trabalho do profissional para personalizar.
                            </span>
                        </div>
                    </div>
                )}

                {isLoadingHours && (
                    <div className="mb-4 p-3 bg-blue-50 dark:bg-blue-950/20 border border-blue-200 dark:border-blue-800 rounded-lg">
                        <div className="flex items-center gap-2 text-blue-800 dark:text-blue-200">
                            <Clock className="h-4 w-4 animate-spin" />
                            <span className="text-sm">Carregando horários de trabalho...</span>
                        </div>
                    </div>
                )}

                {/* Timeline */}
                <div className="space-y-2 max-h-[600px] overflow-y-auto">
                    {timeSlots.map((time) => {
                        const appointment = getAppointmentForTime(time);

                        return (
                            <div key={time} className="flex items-center gap-4 p-2 border-b border-border/50">
                                <div className="w-16 text-sm font-medium text-muted-foreground text-right">
                                    {time}
                                </div>

                                {appointment ? (
                                    <div className={`
                                        flex-1 p-3 rounded-lg border transition-all hover:shadow-sm cursor-pointer
                                        ${statusColors[appointment.status]}
                                    `}>
                                        <div className="flex items-center justify-between">
                                            <div className="flex-1">
                                                <div className="flex items-center gap-3 mb-2">
                                                    <div className="flex items-center gap-2">
                                                        <UserIcon className="h-4 w-4" />
                                                        <span className="font-semibold">
                                                            {appointment.customer.name}
                                                        </span>
                                                    </div>
                                                    <AppointmentStatusBadge status={appointment.status} className="text-xs" />
                                                </div>

                                                <div className="grid grid-cols-1 md:grid-cols-3 gap-2 text-sm">
                                                    <div className="flex items-center gap-2">
                                                        <Clock className="h-3 w-3" />
                                                        <span>
                                                            {appointment.start_time.slice(0, 5)} - {appointment.end_time.slice(0, 5)}
                                                        </span>
                                                    </div>

                                                    <div className="flex items-center gap-2">
                                                        <UserIcon className="h-3 w-3" />
                                                        <span>{appointment.user?.name}</span>
                                                    </div>

                                                    <div className="flex items-center gap-2">
                                                        <MapPin className="h-3 w-3" />
                                                        <span>{appointment.room?.name}</span>
                                                    </div>
                                                </div>

                                                {appointment.service && (
                                                    <div className="mt-2 text-sm font-medium">
                                                        {appointment.service.name}
                                                        {appointment.service.price && (
                                                            <span className="ml-2 text-muted-foreground">
                                                                ({new Intl.NumberFormat('pt-BR', {
                                                                    style: 'currency',
                                                                    currency: 'BRL',
                                                                }).format(appointment.service.price)})
                                                            </span>
                                                        )}
                                                    </div>
                                                )}

                                                {appointment.notes && (
                                                    <div className="mt-2 text-xs text-muted-foreground">
                                                        {appointment.notes}
                                                    </div>
                                                )}
                                            </div>

                                            <DropdownMenu modal={false}>
                                                <DropdownMenuTrigger asChild>
                                                    <Button variant="ghost" size="sm" className="h-8 w-8 p-0">
                                                        <Edit className="h-4 w-4" />
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
                                                    {appointment.status === 'SCHEDULED' && (
                                                        <>
                                                            <DropdownMenuItem
                                                                onClick={(e) => {
                                                                    e.preventDefault();
                                                                    e.stopPropagation();
                                                                    onStatusUpdate(appointment.id, 'IN_PROGRESS');
                                                                }}
                                                                className="text-amber-600"
                                                            >
                                                                <PlayCircle className="mr-2 h-4 w-4" />
                                                                Iniciar Atendimento
                                                            </DropdownMenuItem>
                                                            <DropdownMenuItem
                                                                onClick={(e) => {
                                                                    e.preventDefault();
                                                                    e.stopPropagation();
                                                                    onStatusUpdate(appointment.id, 'COMPLETED');
                                                                }}
                                                            >
                                                                <CheckCircle2 className="mr-2 h-4 w-4" />
                                                                Marcar como Concluído
                                                            </DropdownMenuItem>
                                                            <DropdownMenuItem
                                                                onClick={(e) => {
                                                                    e.preventDefault();
                                                                    e.stopPropagation();
                                                                    onStatusUpdate(appointment.id, 'CANCELLED');
                                                                }}
                                                            >
                                                                <XCircle className="mr-2 h-4 w-4" />
                                                                Cancelar
                                                            </DropdownMenuItem>
                                                        </>
                                                    )}
                                                    {appointment.status === 'IN_PROGRESS' && (
                                                        <DropdownMenuItem
                                                            onClick={(e) => {
                                                                e.preventDefault();
                                                                e.stopPropagation();
                                                                onStatusUpdate(appointment.id, 'COMPLETED');
                                                            }}
                                                            className="text-green-600"
                                                        >
                                                            <CheckCircle2 className="mr-2 h-4 w-4" />
                                                            Finalizar Atendimento
                                                        </DropdownMenuItem>
                                                    )}
                                                </DropdownMenuContent>
                                            </DropdownMenu>
                                        </div>
                                    </div>
                                ) : (
                                    <div className="flex-1 p-3 rounded-lg border border-dashed border-border/50 text-center text-muted-foreground">
                                        <span className="text-sm">Horário disponível</span>
                                    </div>
                                )}
                            </div>
                        );
                    })}
                </div>

                {/* Resumo do dia */}
                <div className="mt-6 p-4 bg-muted/30 rounded-lg">
                    <h4 className="font-semibold mb-2">Resumo do Dia</h4>
                    <div className="grid grid-cols-1 md:grid-cols-4 gap-4 text-sm">
                        <div>
                            <span className="font-medium">Total:</span>
                            <span className="ml-2">{sortedAppointments.length} agendamento(s)</span>
                        </div>
                        <div>
                            <span className="font-medium">Agendados:</span>
                            <span className="ml-2">
                                {sortedAppointments.filter(a => a.status === 'SCHEDULED').length}
                            </span>
                        </div>
                        <div>
                            <span className="font-medium">Concluídos:</span>
                            <span className="ml-2">
                                {sortedAppointments.filter(a => a.status === 'COMPLETED').length}
                            </span>
                        </div>
                        <div>
                            <span className="font-medium">Cancelados:</span>
                            <span className="ml-2">
                                {sortedAppointments.filter(a => a.status === 'CANCELLED').length}
                            </span>
                        </div>
                    </div>
                </div>
            </CardContent>
        </Card>
    );
}