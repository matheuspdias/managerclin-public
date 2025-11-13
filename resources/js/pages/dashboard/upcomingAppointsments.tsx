import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { ScrollArea } from '@/components/ui/scroll-area';
import { Appointment } from '@/types/appointment';
import { Link } from '@inertiajs/react';
import { ArrowRight, CalendarIcon, ClockIcon, UserIcon } from 'lucide-react';
import { format } from 'date-fns';
import { ptBR } from 'date-fns/locale';

interface Props {
    appointments: Appointment[];
}

function formatDate(dateString: string): string {
    const date = new Date(dateString);
    return format(date, 'dd/MM/yyyy', { locale: ptBR });
}

function formatTime(timeString: string): string {
    return timeString.substring(0, 5); // Remove os segundos (HH:MM:SS -> HH:MM)
}

export default function UpcomingAppointments({ appointments }: Props) {
    return (
        <Card className="border-border bg-card transition-colors hover:shadow-md">
            <CardHeader className="flex flex-row items-center justify-between pb-4">
                <div>
                    <CardTitle className="text-lg font-semibold">Próximos Agendamentos</CardTitle>
                    <CardDescription>Agendamentos programados para hoje</CardDescription>
                </div>
                <Button size="sm" variant="outline" className="gap-2">
                    <Link href="/appointments" className="flex items-center gap-2">
                        Ver todos
                        <ArrowRight className="h-4 w-4" />
                    </Link>
                </Button>
            </CardHeader>
            <CardContent>
                {appointments.length > 0 ? (
                    <ScrollArea className="h-[300px]">
                        <div className="divide-y divide-border">
                            {appointments.map((appt) => (
                                <div key={appt.id} className="py-4 transition-colors first:pt-0 last:pb-0 hover:bg-muted/50">
                                    <div className="flex items-start justify-between">
                                        <div className="space-y-2">
                                            <p className="font-medium text-foreground">{appt.customer.name}</p>
                                            <div className="flex items-center gap-3 text-sm text-muted-foreground">
                                                <span className="flex items-center gap-1">
                                                    <UserIcon className="h-4 w-4" />
                                                    {appt.user.name}
                                                </span>
                                                <span className="text-border">•</span>
                                                <span>{appt.room.name}</span>
                                            </div>
                                            <div className="flex items-center gap-3 text-sm text-muted-foreground">
                                                <span className="flex items-center gap-1">
                                                    <CalendarIcon className="h-4 w-4" />
                                                    {formatDate(appt.date)}
                                                </span>
                                                <span className="text-border">•</span>
                                                <span className="flex items-center gap-1">
                                                    <ClockIcon className="h-4 w-4" />
                                                    {formatTime(appt.start_time)} - {formatTime(appt.end_time)}
                                                </span>
                                            </div>
                                        </div>
                                        <div className={`rounded-full px-3 py-1 text-xs font-medium text-white ${statusColor(appt.status)}`}>
                                            {statusText(appt.status)}
                                        </div>
                                    </div>
                                </div>
                            ))}
                        </div>
                    </ScrollArea>
                ) : (
                    <div className="flex h-[200px] flex-col items-center justify-center text-center">
                        <CalendarIcon className="mb-2 h-8 w-8 text-muted-foreground" />
                        <p className="text-sm text-muted-foreground">Nenhum agendamento no período</p>
                        <p className="text-xs text-muted-foreground">Todos os agendamentos estão concluídos ou cancelados</p>
                    </div>
                )}
            </CardContent>
        </Card>
    );
}

function statusColor(status: string): string {
    switch (status) {
        case 'SCHEDULED':
            return 'bg-amber-500';
        case 'COMPLETED':
            return 'bg-green-500';
        case 'CANCELLED':
            return 'bg-red-500';
        default:
            return 'bg-gray-500';
    }
}

function statusText(status: string): string {
    switch (status) {
        case 'SCHEDULED':
            return 'Agendado';
        case 'COMPLETED':
            return 'Concluído';
        case 'CANCELLED':
            return 'Cancelado';
        default:
            return 'Desconhecido';
    }
}
