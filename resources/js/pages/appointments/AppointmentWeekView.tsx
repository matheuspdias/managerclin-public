import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Appointment } from '@/types/appointment';
import { User } from '@/types/user';
import { Room } from '@/types/room';
import { AppointmentStatusBadge } from '@/components/appointments/AppointmentStatusBadge';
import {
    appointmentStatusColors,
    appointmentCardStyles,
    appointmentIconSizes,
    appointmentAnimations,
} from '@/config/appointment-design-system';
import {
    Calendar,
    ChevronLeft,
    ChevronRight,
    Clock,
    User as UserIcon,
    MapPin,
    Edit,
    CheckCircle2,
    XCircle,
    PlayCircle,
    Eye
} from 'lucide-react';
import { useState, useEffect } from 'react';
import {
    format,
    addWeeks,
    subWeeks,
    startOfWeek,
    endOfWeek,
    eachDayOfInterval,
    isSameDay,
    parseISO,
    isToday
} from 'date-fns';
import { ptBR } from 'date-fns/locale';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';

interface AppointmentWeekViewProps {
    appointments: Appointment[];
    users: User[];
    rooms: Room[];
    onEdit: (appointment: Appointment) => void;
    onStatusUpdate: (id: number, status: string) => void;
    onView: (appointment: Appointment) => void;
}

export function AppointmentWeekView({
    appointments,
    users,
    rooms,
    onEdit,
    onStatusUpdate,
    onView,
}: AppointmentWeekViewProps) {
    const [currentDate, setCurrentDate] = useState(new Date());
    const initialSelectedUser = users.length > 0 ? users[0].id.toString() : '';
    const [selectedUser, setSelectedUser] = useState<string>(initialSelectedUser);
    const [selectedRoom, setSelectedRoom] = useState<string>('all');
    const [workingHours, setWorkingHours] = useState<{ start_time: string; end_time: string }>({
        start_time: '08:00',
        end_time: '18:00',
    });

    // Obter dias da semana
    const weekStart = startOfWeek(currentDate, { weekStartsOn: 0 }); // Domingo
    const weekEnd = endOfWeek(currentDate, { weekStartsOn: 0 });
    const weekDays = eachDayOfInterval({ start: weekStart, end: weekEnd });

    const previousWeek = () => {
        setCurrentDate(subWeeks(currentDate, 1));
    };

    const nextWeek = () => {
        setCurrentDate(addWeeks(currentDate, 1));
    };

    const currentWeek = () => {
        setCurrentDate(new Date());
    };

    // Carregar horários de trabalho do profissional
    useEffect(() => {
        if (!selectedUser) return;

        const loadWorkingHours = async () => {
            try {
                const response = await fetch(`/api/users/${selectedUser}/working-hours`, {
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
                    });
                }
            } catch (error) {
                console.error('Erro ao carregar horários de trabalho:', error);
            }
        };

        loadWorkingHours();
    }, [selectedUser]);

    // Gerar slots de horário
    const generateTimeSlots = () => {
        const slots: string[] = [];
        const [startHour, startMinute] = workingHours.start_time.split(':').map(Number);
        const [endHour, endMinute] = workingHours.end_time.split(':').map(Number);

        let currentHour = startHour;
        let currentMinute = startMinute;

        while (
            currentHour < endHour ||
            (currentHour === endHour && currentMinute <= endMinute)
        ) {
            slots.push(
                `${currentHour.toString().padStart(2, '0')}:${currentMinute.toString().padStart(2, '0')}`
            );

            currentMinute += 60; // Slots de 1 hora para a view semanal
            if (currentMinute >= 60) {
                currentMinute = 0;
                currentHour++;
            }
        }

        return slots;
    };

    const timeSlots = generateTimeSlots();

    // Filtrar agendamentos por dia
    const getAppointmentsForDay = (day: Date) => {
        const filtered = appointments.filter(appointment => {
            const appointmentDate = parseISO(appointment.date);
            const isDateMatch = isSameDay(appointmentDate, day);
            const isUserMatch = appointment.id_user.toString() === selectedUser;
            const isRoomMatch = selectedRoom === 'all' || appointment.id_room?.toString() === selectedRoom;

            return isDateMatch && isUserMatch && isRoomMatch;
        }).sort((a, b) => a.start_time.localeCompare(b.start_time));

        return filtered;
    };

    // Verificar se há agendamento que se sobrepõe a um horário específico
    const getAppointmentForTimeSlot = (day: Date, time: string) => {
        const dayAppointments = getAppointmentsForDay(day);

        // Converter slot time para minutos desde meia-noite
        const [slotHour, slotMinute] = time.split(':').map(Number);
        const slotStartMinutes = slotHour * 60 + slotMinute;
        const slotEndMinutes = slotStartMinutes + 60; // Slot de 1 hora

        return dayAppointments.find(appointment => {
            const [appointmentStartHour, appointmentStartMinute] = appointment.start_time.slice(0, 5).split(':').map(Number);
            const [appointmentEndHour, appointmentEndMinute] = appointment.end_time.slice(0, 5).split(':').map(Number);

            const appointmentStartMinutes = appointmentStartHour * 60 + appointmentStartMinute;
            const appointmentEndMinutes = appointmentEndHour * 60 + appointmentEndMinute;

            // Verificar se o agendamento se sobrepõe ao slot
            // Um agendamento está no slot se:
            // - Começa dentro do slot, OU
            // - Termina dentro do slot, OU
            // - Começa antes e termina depois (abrange todo o slot)
            return (
                (appointmentStartMinutes >= slotStartMinutes && appointmentStartMinutes < slotEndMinutes) ||
                (appointmentEndMinutes > slotStartMinutes && appointmentEndMinutes <= slotEndMinutes) ||
                (appointmentStartMinutes <= slotStartMinutes && appointmentEndMinutes >= slotEndMinutes)
            );
        });
    };

    // Obter estatísticas da semana
    const weekAppointments = appointments.filter(appointment => {
        const appointmentDate = parseISO(appointment.date);
        return appointmentDate >= weekStart && appointmentDate <= weekEnd &&
               appointment.id_user.toString() === selectedUser &&
               (selectedRoom === 'all' || appointment.id_room?.toString() === selectedRoom);
    });

    return (
        <Card>
            <CardHeader>
                <CardTitle className="flex items-center justify-between">
                    <div className="flex items-center gap-2">
                        <Calendar className="h-5 w-5" />
                        <span>Visualização Semanal</span>
                    </div>
                    <div className="flex items-center gap-2">
                        <Button variant="outline" size="sm" onClick={previousWeek}>
                            <ChevronLeft className="h-4 w-4" />
                        </Button>
                        <Button variant="outline" size="sm" onClick={currentWeek}>
                            Semana Atual
                        </Button>
                        <div className="min-w-[200px] text-center">
                            <span className="font-semibold">
                                {format(weekStart, 'dd/MM', { locale: ptBR })} - {format(weekEnd, 'dd/MM/yyyy', { locale: ptBR })}
                            </span>
                        </div>
                        <Button variant="outline" size="sm" onClick={nextWeek}>
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

                {/* Grade da Semana */}
                <div className="overflow-x-auto">
                    <div className="min-w-[1200px]">
                        {/* Cabeçalho dos dias */}
                        <div className="grid grid-cols-8 gap-2 mb-2">
                            <div className="font-medium text-sm text-muted-foreground"></div>
                            {weekDays.map((day) => (
                                <div
                                    key={day.toISOString()}
                                    className={`p-3 text-center rounded-lg border ${
                                        isToday(day)
                                            ? 'bg-primary text-primary-foreground border-primary'
                                            : 'bg-muted/50 border-border'
                                    }`}
                                >
                                    <div className="font-semibold">
                                        {format(day, 'EEE', { locale: ptBR })}
                                    </div>
                                    <div className="text-sm">
                                        {format(day, 'dd/MM', { locale: ptBR })}
                                    </div>
                                    <div className="text-xs mt-1">
                                        {getAppointmentsForDay(day).length} agendamento(s)
                                    </div>
                                </div>
                            ))}
                        </div>

                        {/* Grade de horários */}
                        <div className="space-y-1 max-h-[600px] overflow-y-auto">
                            {timeSlots.map((time) => (
                                <div key={time} className="grid grid-cols-8 gap-2">
                                    <div className="flex items-start justify-end pt-2 pr-2">
                                        <span className="text-xs font-medium text-muted-foreground">
                                            {time}
                                        </span>
                                    </div>
                                    {weekDays.map((day) => {
                                        const appointment = getAppointmentForTimeSlot(day, time);

                                        return (
                                            <div
                                                key={`${day.toISOString()}-${time}`}
                                                className="min-h-[60px]"
                                            >
                                                {appointment ? (
                                                    <div
                                                        className={`
                                                            ${appointmentCardStyles.base}
                                                            ${appointmentCardStyles.compact}
                                                            ${appointmentCardStyles.interactive}
                                                            ${appointmentStatusColors[appointment.status].card}
                                                            ${appointmentStatusColors[appointment.status].cardHover}
                                                            ${appointmentAnimations.transition}
                                                            h-full
                                                        `}
                                                    >
                                                        <div className="flex items-start justify-between gap-1">
                                                            <div className="flex-1 min-w-0">
                                                                <div className="text-xs font-semibold truncate">
                                                                    {appointment.customer.name}
                                                                </div>
                                                                <div className="flex items-center gap-1 text-xs mt-1">
                                                                    <Clock className={`${appointmentIconSizes.xs} flex-shrink-0`} />
                                                                    <span className="truncate">
                                                                        {appointment.start_time.slice(0, 5)} - {appointment.end_time.slice(0, 5)}
                                                                    </span>
                                                                </div>
                                                                {appointment.room && (
                                                                    <div className="flex items-center gap-1 text-xs mt-1">
                                                                        <MapPin className={`${appointmentIconSizes.xs} flex-shrink-0`} />
                                                                        <span className="truncate">
                                                                            {appointment.room.name}
                                                                        </span>
                                                                    </div>
                                                                )}
                                                            </div>

                                                            <DropdownMenu modal={false}>
                                                                <DropdownMenuTrigger asChild>
                                                                    <Button
                                                                        variant="ghost"
                                                                        size="sm"
                                                                        className="h-6 w-6 p-0 hover:bg-background/50"
                                                                    >
                                                                        <Edit className={appointmentIconSizes.xs} />
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
                                                                        <Eye className={`mr-2 ${appointmentIconSizes.sm}`} />
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
                                                                                className={appointmentStatusColors.IN_PROGRESS.text}
                                                                            >
                                                                                <PlayCircle className={`mr-2 ${appointmentIconSizes.sm}`} />
                                                                                Iniciar Atendimento
                                                                            </DropdownMenuItem>
                                                                            <DropdownMenuItem
                                                                                onClick={(e) => {
                                                                                    e.preventDefault();
                                                                                    e.stopPropagation();
                                                                                    onStatusUpdate(appointment.id, 'COMPLETED');
                                                                                }}
                                                                                className={appointmentStatusColors.COMPLETED.text}
                                                                            >
                                                                                <CheckCircle2 className={`mr-2 ${appointmentIconSizes.sm}`} />
                                                                                Marcar como Concluído
                                                                            </DropdownMenuItem>
                                                                            <DropdownMenuItem
                                                                                onClick={(e) => {
                                                                                    e.preventDefault();
                                                                                    e.stopPropagation();
                                                                                    onStatusUpdate(appointment.id, 'CANCELLED');
                                                                                }}
                                                                                className={appointmentStatusColors.CANCELLED.text}
                                                                            >
                                                                                <XCircle className={`mr-2 ${appointmentIconSizes.sm}`} />
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
                                                                            className={appointmentStatusColors.COMPLETED.text}
                                                                        >
                                                                            <CheckCircle2 className={`mr-2 ${appointmentIconSizes.sm}`} />
                                                                            Finalizar Atendimento
                                                                        </DropdownMenuItem>
                                                                    )}
                                                                </DropdownMenuContent>
                                                            </DropdownMenu>
                                                        </div>
                                                    </div>
                                                ) : (
                                                    <div className="h-full border border-dashed border-border/30 rounded-lg hover:border-border/60 transition-colors" />
                                                )}
                                            </div>
                                        );
                                    })}
                                </div>
                            ))}
                        </div>
                    </div>
                </div>

                {/* Resumo da semana */}
                <div className="mt-6 p-4 bg-muted/30 rounded-lg">
                    <h4 className="font-semibold mb-2">Resumo da Semana</h4>
                    <div className="grid grid-cols-2 md:grid-cols-5 gap-4 text-sm">
                        <div>
                            <span className="font-medium">Total:</span>
                            <span className="ml-2">{weekAppointments.length}</span>
                        </div>
                        <div>
                            <span className="font-medium">Agendados:</span>
                            <span className="ml-2">
                                {weekAppointments.filter(a => a.status === 'SCHEDULED').length}
                            </span>
                        </div>
                        <div>
                            <span className="font-medium">Em Andamento:</span>
                            <span className="ml-2">
                                {weekAppointments.filter(a => a.status === 'IN_PROGRESS').length}
                            </span>
                        </div>
                        <div>
                            <span className="font-medium">Concluídos:</span>
                            <span className="ml-2">
                                {weekAppointments.filter(a => a.status === 'COMPLETED').length}
                            </span>
                        </div>
                        <div>
                            <span className="font-medium">Cancelados:</span>
                            <span className="ml-2">
                                {weekAppointments.filter(a => a.status === 'CANCELLED').length}
                            </span>
                        </div>
                    </div>
                </div>
            </CardContent>
        </Card>
    );
}
