import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { BarChart3 } from 'lucide-react';

interface Service {
    name: string;
    totalAppointments: number;
}

interface MostPopularServicesProps {
    services: Service[];
}

export default function MostPopularServices({ services }: MostPopularServicesProps) {
    const maxAppointments = Math.max(...services.map((s) => s.totalAppointments), 0);

    return (
        <Card className="border-border bg-card transition-colors hover:shadow-md">
            <CardHeader>
                <CardTitle className="flex items-center gap-2 text-lg font-semibold">
                    <BarChart3 className="h-5 w-5 text-blue-500" />
                    Serviços Populares
                </CardTitle>
                <CardDescription>Serviços mais solicitados pelos clientes</CardDescription>
            </CardHeader>
            <CardContent>
                {services.length > 0 ? (
                    <div className="space-y-4">
                        {services.map((service, index) => {
                            const percentage = maxAppointments > 0 ? (service.totalAppointments / maxAppointments) * 100 : 0;
                            return (
                                <div key={index} className="space-y-2">
                                    <div className="flex items-center justify-between">
                                        <span className="text-sm font-medium">{service.name}</span>
                                        <span className="text-sm font-semibold text-foreground">{service.totalAppointments}</span>
                                    </div>
                                    <div className="h-2 w-full overflow-hidden rounded-full bg-muted">
                                        <div
                                            className="h-full rounded-full bg-blue-500 transition-all duration-300"
                                            style={{ width: `${percentage}%` }}
                                        />
                                    </div>
                                </div>
                            );
                        })}
                    </div>
                ) : (
                    <div className="flex flex-col items-center justify-center py-8 text-center">
                        <BarChart3 className="mb-2 h-8 w-8 text-muted-foreground" />
                        <p className="text-sm text-muted-foreground">Nenhum serviço popular registrado</p>
                        <p className="text-xs text-muted-foreground">Os serviços aparecerão aqui quando houver atendimentos</p>
                    </div>
                )}
            </CardContent>
        </Card>
    );
}
