import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { PieChartIcon } from 'lucide-react';
import { Cell, Pie, PieChart, ResponsiveContainer, Tooltip } from 'recharts';

interface AppointmentStatusChartProps {
    data: {
        count: number;
        completedCount: number;
        cancelledCount: number;
        pendingCount: number;
        completedPercent: number;
    };
}

const COLORS = ['#10B981', '#F59E0B', '#EF4444'];

export default function AppointmentStatusChart({ data }: AppointmentStatusChartProps) {
    const chartData = [
        {
            name: 'Concluídos',
            value: data.completedCount,
        },
        {
            name: 'Pendentes',
            value: data.pendingCount,
        },
        {
            name: 'Cancelados',
            value: data.cancelledCount,
        },
    ].filter(item => item.value > 0); // Remove categorias com valor 0

    const CustomTooltip = ({ active, payload }: any) => {
        if (active && payload && payload.length) {
            return (
                <div className="rounded-lg border bg-background p-3 shadow-sm">
                    <p className="font-medium">{payload[0].name}</p>
                    <p className="text-sm text-muted-foreground">{payload[0].value} agendamentos</p>
                </div>
            );
        }
        return null;
    };

    return (
        <Card className="border-border bg-card transition-colors hover:shadow-md">
            <CardHeader>
                <CardTitle className="flex items-center gap-2 text-lg font-semibold">
                    <PieChartIcon className="h-5 w-5 text-green-500" />
                    Status dos Agendamentos
                </CardTitle>
                <CardDescription>Distribuição por status</CardDescription>
            </CardHeader>
            <CardContent>
                {data.count > 0 ? (
                    <div className="space-y-4">
                        <ResponsiveContainer width="100%" height={200}>
                            <PieChart>
                                <Pie
                                    data={chartData}
                                    cx="50%"
                                    cy="50%"
                                    outerRadius={80}
                                    innerRadius={60}
                                    fill="#8884d8"
                                    dataKey="value"
                                    label={({ name, percent }) => `${(percent * 100).toFixed(0)}%`}
                                    labelLine={false}
                                >
                                    {chartData.map((_, index) => (
                                        <Cell key={`cell-${index}`} fill={COLORS[index % COLORS.length]} />
                                    ))}
                                </Pie>
                                <Tooltip content={<CustomTooltip />} />
                            </PieChart>
                        </ResponsiveContainer>
                        <div className="flex justify-center gap-4">
                            {chartData.map((item, index) => (
                                <div key={item.name} className="flex items-center gap-2">
                                    <div className="h-3 w-3 rounded-full" style={{ backgroundColor: COLORS[index] }} />
                                    <span className="text-xs text-muted-foreground">{item.name}</span>
                                </div>
                            ))}
                        </div>
                    </div>
                ) : (
                    <div className="flex h-[200px] flex-col items-center justify-center text-center">
                        <PieChartIcon className="mb-2 h-8 w-8 text-muted-foreground" />
                        <p className="text-sm text-muted-foreground">Nenhum agendamento</p>
                        <p className="text-xs text-muted-foreground">Não há dados para exibir no gráfico</p>
                    </div>
                )}
            </CardContent>
        </Card>
    );
}
