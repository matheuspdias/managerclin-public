import { Card, CardContent } from '@/components/ui/card';
import {
    CalendarDays,
    CheckCircle2,
    Clock,
    XCircle,
    TrendingUp,
    PlayCircle,
} from 'lucide-react';

interface StatsProps {
    stats: {
        total: number;
        scheduled: number;
        in_progress?: number;
        completed: number;
        cancelled: number;
        today: number;
        week: number;
    };
    onFilterByStatus?: (status: string | null) => void;
}

export function AppointmentStatsCards({ stats, onFilterByStatus }: StatsProps) {
    const statsCards = [
        {
            title: 'Total de Agendamentos',
            value: stats.total,
            icon: CalendarDays,
            color: 'text-blue-600',
            bgColor: 'bg-blue-50',
            filterStatus: null,
            description: 'Clique para ver todos',
        },
        {
            title: 'Agendados',
            value: stats.scheduled,
            icon: Clock,
            color: 'text-blue-600',
            bgColor: 'bg-blue-50',
            filterStatus: 'SCHEDULED',
            description: 'Clique para filtrar',
        },
        {
            title: 'Em Andamento',
            value: stats.in_progress || 0,
            icon: PlayCircle,
            color: 'text-amber-600',
            bgColor: 'bg-amber-50',
            filterStatus: 'IN_PROGRESS',
            description: 'Clique para filtrar',
        },
        {
            title: 'Concluídos',
            value: stats.completed,
            icon: CheckCircle2,
            color: 'text-green-600',
            bgColor: 'bg-green-50',
            filterStatus: 'COMPLETED',
            description: 'Clique para filtrar',
        },
        {
            title: 'Cancelados',
            value: stats.cancelled,
            icon: XCircle,
            color: 'text-red-600',
            bgColor: 'bg-red-50',
            filterStatus: 'CANCELLED',
            description: 'Clique para filtrar',
        },
    ];

    return (
        <div className="grid grid-cols-1 gap-4 md:grid-cols-3 lg:grid-cols-5">
            {statsCards.map((stat, index) => {
                const Icon = stat.icon;
                return (
                    <Card
                        key={index}
                        className={`transition-all hover:shadow-md ${onFilterByStatus ? 'cursor-pointer hover:scale-105 hover:ring-2 hover:ring-offset-2 hover:ring-blue-500' : ''}`}
                        onClick={() => onFilterByStatus?.(stat.filterStatus)}
                        role={onFilterByStatus ? 'button' : undefined}
                        tabIndex={onFilterByStatus ? 0 : undefined}
                        onKeyDown={(e) => {
                            if (onFilterByStatus && (e.key === 'Enter' || e.key === ' ')) {
                                e.preventDefault();
                                onFilterByStatus(stat.filterStatus);
                            }
                        }}
                    >
                        <CardContent className="p-4">
                            <div className="flex items-center justify-between">
                                <div className="flex-1">
                                    <p className="text-xs font-medium text-muted-foreground uppercase tracking-wide">
                                        {stat.title}
                                    </p>
                                    <p className="text-2xl font-bold mt-1">
                                        {stat.value.toLocaleString()}
                                    </p>
                                    {onFilterByStatus && (
                                        <p className="text-[10px] text-muted-foreground mt-1">
                                            {stat.description}
                                        </p>
                                    )}
                                </div>
                                <div className={`p-2 rounded-lg ${stat.bgColor}`}>
                                    <Icon className={`h-5 w-5 ${stat.color}`} />
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                );
            })}
        </div>
    );
}