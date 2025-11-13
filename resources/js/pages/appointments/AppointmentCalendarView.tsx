import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Appointment } from '@/types/appointment';
import {
    ChevronLeft,
    ChevronRight,
    Calendar,
    Clock,
    User,
    Plus,
    MoreHorizontal,
    FileText,
    Edit,
    PlayCircle,
    CheckCircle2,
    XCircle,
    Eye
} from 'lucide-react';
import { useState } from 'react';
import { router } from '@inertiajs/react';
import {
    addMonths,
    subMonths,
    startOfMonth,
    endOfMonth,
    startOfWeek,
    endOfWeek,
    format,
    addDays,
    isSameMonth,
    isSameDay,
    isToday,
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

interface AppointmentCalendarViewProps {
    appointments: Appointment[];
    onEdit: (appointment: Appointment) => void;
    onCreate: (date?: string) => void;
    onStatusUpdate: (id: number, status: string) => void;
    onView: (appointment: Appointment) => void;
}

export function AppointmentCalendarView({
    appointments,
    onEdit,
    onCreate,
    onStatusUpdate,
    onView,
}: AppointmentCalendarViewProps) {
    const [currentDate, setCurrentDate] = useState(new Date());

    const monthStart = startOfMonth(currentDate);
    const monthEnd = endOfMonth(monthStart);
    const startDate = startOfWeek(monthStart, { weekStartsOn: 0 });
    const endDate = endOfWeek(monthEnd, { weekStartsOn: 0 });

    const previousMonth = () => {
        setCurrentDate(subMonths(currentDate, 1));
    };

    const nextMonth = () => {
        setCurrentDate(addMonths(currentDate, 1));
    };

    const getAppointmentsForDate = (date: Date) => {
        return appointments.filter(appointment => {
            const appointmentDate = parseISO(appointment.date);
            return isSameDay(date, appointmentDate);
        });
    };

    const renderCalendarHeader = () => {
        const weekDays = ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb'];

        return (
            <div className="grid grid-cols-7 gap-1 mb-2">
                {weekDays.map(day => (
                    <div key={day} className="p-2 text-center font-medium text-muted-foreground text-sm">
                        {day}
                    </div>
                ))}
            </div>
        );
    };

    const renderCalendarDays = () => {
        const days = [];
        let day = startDate;

        while (day <= endDate) {
            days.push(day);
            day = addDays(day, 1);
        }

        return (
            <div className="grid grid-cols-7 gap-1">
                {days.map((day, index) => {
                    const dayAppointments = getAppointmentsForDate(day);
                    const isCurrentMonth = isSameMonth(day, currentDate);
                    const isCurrentDay = isToday(day);

                    return (
                        <div
                            key={index}
                            className={`
                                min-h-[120px] p-1 border border-border rounded-lg transition-colors
                                ${!isCurrentMonth ? 'bg-muted/30 text-muted-foreground' : 'bg-background'}
                                ${isCurrentDay ? 'ring-2 ring-blue-500 ring-offset-1' : ''}
                                hover:bg-muted/50 cursor-pointer
                            `}
                            onClick={() => onCreate(format(day, 'yyyy-MM-dd'))}
                        >
                            <div className="flex items-center justify-between mb-1">
                                <span className={`
                                    text-sm font-medium
                                    ${isCurrentDay ? 'text-blue-600' : ''}
                                    ${!isCurrentMonth ? 'text-muted-foreground' : ''}
                                `}>
                                    {format(day, 'd')}
                                </span>
                                {dayAppointments.length > 0 && (
                                    <Badge variant="secondary" className="h-5 text-xs">
                                        {dayAppointments.length}
                                    </Badge>
                                )}
                            </div>

                            <div className="space-y-1">
                                {dayAppointments.slice(0, 3).map((appointment) => {
                                    const statusColor = {
                                        SCHEDULED: 'bg-blue-100 text-blue-800 border-blue-200',
                                        IN_PROGRESS: 'bg-amber-100 text-amber-800 border-amber-200',
                                        COMPLETED: 'bg-green-100 text-green-800 border-green-200',
                                        CANCELLED: 'bg-red-100 text-red-800 border-red-200',
                                    }[appointment.status] || 'bg-gray-100 text-gray-800 border-gray-200';

                                    return (
                                        <div
                                            key={appointment.id}
                                            className={`
                                                p-1 rounded text-xs border
                                                ${statusColor}
                                                hover:shadow-sm transition-shadow group relative
                                            `}
                                        >
                                            <div className="flex items-center justify-between">
                                                <div className="flex-1">
                                                    <div className="flex items-center gap-1 mb-1">
                                                        <Clock className="h-3 w-3" />
                                                        <span className="font-medium">
                                                            {appointment.start_time.slice(0, 5)}
                                                        </span>
                                                    </div>
                                                    <div className="flex items-center gap-1">
                                                        <User className="h-3 w-3" />
                                                        <span className="truncate">
                                                            {appointment.customer.name}
                                                        </span>
                                                    </div>
                                                    {appointment.service && (
                                                        <div className="truncate text-xs opacity-75">
                                                            {appointment.service.name}
                                                        </div>
                                                    )}
                                                </div>
                                                <DropdownMenu>
                                                    <DropdownMenuTrigger asChild>
                                                        <Button
                                                            variant="ghost"
                                                            size="sm"
                                                            className="h-6 w-6 p-0 opacity-0 group-hover:opacity-100 transition-opacity"
                                                            onClick={(e) => e.stopPropagation()}
                                                        >
                                                            <MoreHorizontal className="h-3 w-3" />
                                                        </Button>
                                                    </DropdownMenuTrigger>
                                                    <DropdownMenuContent align="end">
                                                        <DropdownMenuItem
                                                            onClick={(e) => {
                                                                e.preventDefault();
                                                                e.stopPropagation();
                                                                onView(appointment);
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
                                                    </DropdownMenuContent>
                                                </DropdownMenu>
                                            </div>
                                        </div>
                                    );
                                })}

                                {dayAppointments.length > 3 && (
                                    <div className="text-xs text-muted-foreground text-center">
                                        +{dayAppointments.length - 3} mais
                                    </div>
                                )}
                            </div>
                        </div>
                    );
                })}
            </div>
        );
    };

    return (
        <Card>
            <CardHeader>
                <CardTitle className="flex items-center justify-between">
                    <div className="flex items-center gap-4">
                        <div className="flex items-center gap-2">
                            <Calendar className="h-5 w-5" />
                            <span>Calendário de Agendamentos</span>
                        </div>
                    </div>
                    <div className="flex items-center gap-2">
                        <Button
                            variant="outline"
                            size="sm"
                            onClick={previousMonth}
                        >
                            <ChevronLeft className="h-4 w-4" />
                        </Button>
                        <div className="min-w-[150px] text-center">
                            <span className="font-semibold">
                                {format(currentDate, 'MMMM yyyy', { locale: ptBR })}
                            </span>
                        </div>
                        <Button
                            variant="outline"
                            size="sm"
                            onClick={nextMonth}
                        >
                            <ChevronRight className="h-4 w-4" />
                        </Button>
                    </div>
                </CardTitle>
            </CardHeader>
            <CardContent>
                {renderCalendarHeader()}
                {renderCalendarDays()}

                {/* Legend */}
                <div className="flex items-center justify-center gap-6 mt-6 pt-4 border-t">
                    <div className="flex items-center gap-2">
                        <div className="w-3 h-3 rounded bg-blue-100 border border-blue-200"></div>
                        <span className="text-sm text-muted-foreground">Agendado</span>
                    </div>
                    <div className="flex items-center gap-2">
                        <div className="w-3 h-3 rounded bg-amber-100 border border-amber-200"></div>
                        <span className="text-sm text-muted-foreground">Em Andamento</span>
                    </div>
                    <div className="flex items-center gap-2">
                        <div className="w-3 h-3 rounded bg-green-100 border border-green-200"></div>
                        <span className="text-sm text-muted-foreground">Concluído</span>
                    </div>
                    <div className="flex items-center gap-2">
                        <div className="w-3 h-3 rounded bg-red-100 border border-red-200"></div>
                        <span className="text-sm text-muted-foreground">Cancelado</span>
                    </div>
                </div>

                <div className="text-center mt-4">
                    <Button
                        onClick={() => onCreate()}
                        className="gap-2"
                        size="sm"
                    >
                        <Plus className="h-4 w-4" />
                        Novo Agendamento
                    </Button>
                </div>
            </CardContent>
        </Card>
    );
}