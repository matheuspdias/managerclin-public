import { Head, router, usePage } from '@inertiajs/react';
import { format } from 'date-fns';
import { ptBR } from 'date-fns/locale';
import { useEffect, useState } from 'react';
import { DateRange } from 'react-day-picker';

import { Button } from '@/components/ui/button';
import { Calendar } from '@/components/ui/calendar';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { type DashboardProps } from '@/types/dashboard';

import { Activity, CalendarIcon, CheckCircle, UserPlus, Users } from 'lucide-react';
import AppointmentStatusChart from './appointmentStatusChart';
import MostPopularServices from './mostPopularService';
import TopProfessionalsRanking from './topProfessionalsRanking';
import UpcomingAppointments from './upcomingAppointsments';

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Dashboard', href: '/dashboard' }];

export default function Dashboard() {
    const { totalUsers, totalCustomers, appointmentsChart, ranking, appointments, mostPopularServices, period, userName } =
        usePage<DashboardProps>().props;

    const [date, setDate] = useState<DateRange | undefined>(() => {
        const fromDate = new Date(period.start_date + 'T00:00:00');
        const toDate = new Date(period.end_date + 'T00:00:00');

        return {
            from: fromDate,
            to: toDate,
        };
    });
    const [isInitialized, setIsInitialized] = useState(false);

    useEffect(() => {
        // Marca como inicializado na primeira renderização
        setIsInitialized(true);
    }, []);

    useEffect(() => {
        // Só faz a requisição se já foi inicializado e não é a primeira renderização
        if (isInitialized && date?.from && date?.to) {
            const start_date = format(date.from, 'yyyy-MM-dd');
            const end_date = format(date.to, 'yyyy-MM-dd');

            // Só faz a requisição se as datas mudaram do valor inicial
            if (start_date !== period.start_date || end_date !== period.end_date) {
                router.get('/dashboard', { start_date, end_date }, { preserveScroll: true, preserveState: true });
            }
        }
    }, [date, isInitialized]);

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Dashboard" />
            <div className="space-y-6 p-6">
                {/* Header Section */}
                <div className="flex flex-col justify-between gap-4 md:flex-row md:items-center">
                    <div>
                        <h1 className="text-3xl font-bold tracking-tight">Painel de Controle</h1>
                        <p className="text-muted-foreground">Visão geral das atividades da clínica</p>
                    </div>
                    <div className="flex items-center gap-2">
                        <CalendarIcon className="h-5 w-5 text-muted-foreground" />
                        <Popover>
                            <PopoverTrigger asChild>
                                <Button variant="outline" className="w-[280px] justify-start text-left font-normal">
                                    {date?.from ? (
                                        date.to ? (
                                            `${format(date.from, 'dd/MM/yyyy')} - ${format(date.to, 'dd/MM/yyyy')}`
                                        ) : (
                                            format(date.from, 'dd/MM/yyyy')
                                        )
                                    ) : (
                                        <span>Selecionar intervalo</span>
                                    )}
                                </Button>
                            </PopoverTrigger>
                            <PopoverContent className="w-auto p-0">
                                <Calendar
                                    autoFocus
                                    mode="range"
                                    defaultMonth={date?.from}
                                    selected={date}
                                    onSelect={setDate}
                                    numberOfMonths={2}
                                    locale={ptBR}
                                />
                            </PopoverContent>
                        </Popover>
                    </div>
                </div>

                {/* Métricas Principais */}
                <div className="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-4">
                    <Card className="border-border bg-card transition-all duration-200 hover:shadow-lg">
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-3">
                            <CardTitle className="text-sm font-medium text-muted-foreground">Total de Consultas</CardTitle>
                            <Activity className="h-5 w-5 text-blue-600" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-3xl font-bold text-blue-600">{appointmentsChart.count}</div>
                            <p className="mt-1 text-xs text-muted-foreground">No período selecionado</p>
                        </CardContent>
                    </Card>

                    <Card className="border-border bg-card transition-all duration-200 hover:shadow-lg">
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-3">
                            <CardTitle className="text-sm font-medium text-muted-foreground">Consultas Realizadas</CardTitle>
                            <CheckCircle className="h-5 w-5 text-green-600" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-3xl font-bold text-green-600">{appointmentsChart.completedCount}</div>
                            <p className="mt-1 text-xs text-muted-foreground">{appointmentsChart.completedPercent}% de conclusão</p>
                        </CardContent>
                    </Card>

                    <Card className="border-border bg-card transition-all duration-200 hover:shadow-lg">
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-3">
                            <CardTitle className="text-sm font-medium text-muted-foreground">Novos Pacientes</CardTitle>
                            <Users className="h-5 w-5 text-purple-600" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-3xl font-bold text-purple-600">{totalCustomers.total_registered_today}</div>
                            <p className="mt-1 text-xs text-muted-foreground">Total cadastrados: {totalCustomers.total}</p>
                        </CardContent>
                    </Card>

                    <Card className="border-border bg-card transition-all duration-200 hover:shadow-lg">
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-3">
                            <CardTitle className="text-sm font-medium text-muted-foreground">Novos Profissionais</CardTitle>
                            <UserPlus className="h-5 w-5 text-orange-600" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-3xl font-bold text-orange-600">{totalUsers.total_registered_today}</div>
                            <p className="mt-1 text-xs text-muted-foreground">Total ativos: {totalUsers.total}</p>
                        </CardContent>
                    </Card>
                </div>

                {/* Seção Principal - Análises e Relatórios */}
                <div className="grid grid-cols-1 gap-6 xl:grid-cols-3">
                    <div className="space-y-6 xl:col-span-2">
                        <UpcomingAppointments appointments={appointments} />
                        <TopProfessionalsRanking ranking={ranking} />
                    </div>
                    <div className="space-y-6 xl:col-span-1">
                        <AppointmentStatusChart data={appointmentsChart} />
                        <MostPopularServices services={mostPopularServices} />
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
