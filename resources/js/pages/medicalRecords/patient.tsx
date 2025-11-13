import { Head, usePage, Link } from '@inertiajs/react';
import { FileText, Calendar, User, Clock, ArrowLeft, Plus, Edit } from 'lucide-react';
import { useEffect } from 'react';

import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { type BreadcrumbItem } from '@/types';
import { format, parseISO } from 'date-fns';
import { ptBR } from 'date-fns/locale';
import { toast } from 'sonner';

interface Customer {
    id: number;
    name: string;
    email?: string;
    phone?: string;
    birthdate?: string;
}

interface MedicalRecord {
    id: number;
    chief_complaint?: string;
    history_present_illness?: string;
    physical_examination?: string;
    diagnosis?: string;
    treatment_plan?: string;
    prescriptions?: string;
    notes?: string;
    created_at: string;
    updated_at: string;
    user?: {
        id: number;
        name: string;
    };
    appointment?: {
        id: number;
        date: string;
        start_time: string;
    };
}

interface PatientMedicalRecordsProps {
    customer: Customer;
    medicalRecords: {
        data: MedicalRecord[];
        total: number;
        current_page: number;
        per_page: number;
        last_page: number;
        prev_page_url: string | null;
        next_page_url: string | null;
    };
}

export default function PatientMedicalRecords() {
    const page = usePage();
    const { customer, medicalRecords } = page.props as any;
    const flashError = (page.props as any).flash?.error;
    const flashSuccess = (page.props as any).flash?.success;

    // Exibir mensagens flash
    useEffect(() => {
        if (flashError) {
            toast.error(flashError);
        }
        if (flashSuccess) {
            toast.success(flashSuccess);
        }
    }, [flashError, flashSuccess]);

    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: 'Prontuários',
            href: '/medical-records',
        },
        {
            title: customer.name,
            href: `/medical-records/patient/${customer.id}`,
        },
    ];

    const formatDate = (dateString: string) => {
        return format(parseISO(dateString), "dd 'de' MMMM 'de' yyyy", { locale: ptBR });
    };

    const formatDateTime = (dateString: string) => {
        return format(parseISO(dateString), "dd/MM/yyyy 'às' HH:mm", { locale: ptBR });
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Prontuários - ${customer.name}`} />

            <div className="space-y-6">
                {/* Patient Info Card */}
                <Card>
                    <CardHeader>
                        <div className="flex items-center justify-between">
                            <div className="flex items-center gap-4">
                                <div className="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center">
                                    <User className="h-6 w-6 text-blue-600" />
                                </div>
                                <div>
                                    <CardTitle className="text-xl">{customer.name}</CardTitle>
                                    <div className="flex items-center gap-4 text-sm text-muted-foreground">
                                        {customer.phone && <span>{customer.phone}</span>}
                                        {customer.email && <span>{customer.email}</span>}
                                        {customer.birthdate && (
                                            <span>Nascimento: {format(parseISO(customer.birthdate), 'dd/MM/yyyy')}</span>
                                        )}
                                    </div>
                                </div>
                            </div>
                            <div className="flex gap-2">
                                <Button asChild>
                                    <Link href={`/medical-records/patient/${customer.id}/create`}>
                                        <Plus className="mr-2 h-4 w-4" />
                                        Novo Prontuário
                                    </Link>
                                </Button>
                                <Button variant="outline" onClick={() => window.history.back()}>
                                    <ArrowLeft className="mr-2 h-4 w-4" />
                                    Voltar
                                </Button>
                            </div>
                        </div>
                    </CardHeader>
                </Card>

                {/* Medical Records */}
                <Card>
                    <CardHeader>
                        <CardTitle className="flex items-center gap-2">
                            <FileText className="h-5 w-5" />
                            Histórico de Prontuários
                            <Badge variant="secondary">{medicalRecords.total}</Badge>
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        {medicalRecords.data.length === 0 ? (
                            <div className="text-center py-12">
                                <FileText className="mx-auto h-12 w-12 text-muted-foreground" />
                                <h3 className="mt-4 text-lg font-medium">Nenhum prontuário encontrado</h3>
                                <p className="text-muted-foreground">
                                    Este paciente ainda não possui prontuários médicos registrados.
                                </p>
                            </div>
                        ) : (
                            <div className="space-y-6">
                                {medicalRecords.data.map((record) => (
                                    <Card key={record.id} className="border-l-4 border-l-blue-500">
                                        <CardHeader>
                                            <div className="flex items-center justify-between">
                                                <div className="flex items-center gap-4">
                                                    <div>
                                                        <div className="flex items-center gap-2">
                                                            <h3 className="font-semibold">
                                                                Consulta {record.appointment ? 'Agendada' : 'Avulsa'}
                                                            </h3>
                                                            {record.appointment && (
                                                                <Badge variant="outline">
                                                                    <Calendar className="mr-1 h-3 w-3" />
                                                                    {format(parseISO(record.appointment.date), 'dd/MM/yyyy')}
                                                                    <Clock className="ml-2 mr-1 h-3 w-3" />
                                                                    {record.appointment.start_time.slice(0, 5)}
                                                                </Badge>
                                                            )}
                                                        </div>
                                                        <div className="flex items-center gap-2 text-sm text-muted-foreground">
                                                            <User className="h-4 w-4" />
                                                            <span>Dr(a). {record.user?.name}</span>
                                                            <span>•</span>
                                                            <span>{formatDateTime(record.created_at)}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <Link href={`/medical-records/${record.id}/edit`}>
                                                    <Button variant="outline" size="sm">
                                                        <Edit className="h-4 w-4 mr-2" />
                                                        Editar
                                                    </Button>
                                                </Link>
                                            </div>
                                        </CardHeader>
                                        <CardContent className="space-y-4">
                                            {record.chief_complaint && (
                                                <div>
                                                    <h4 className="font-medium text-sm text-muted-foreground mb-1">
                                                        Queixa Principal
                                                    </h4>
                                                    <p className="text-sm">{record.chief_complaint}</p>
                                                </div>
                                            )}

                                            {record.history_present_illness && (
                                                <div>
                                                    <h4 className="font-medium text-sm text-muted-foreground mb-1">
                                                        História da Doença Atual
                                                    </h4>
                                                    <p className="text-sm whitespace-pre-wrap">{record.history_present_illness}</p>
                                                </div>
                                            )}

                                            {record.physical_examination && (
                                                <div>
                                                    <h4 className="font-medium text-sm text-muted-foreground mb-1">
                                                        Exame Físico
                                                    </h4>
                                                    <p className="text-sm whitespace-pre-wrap">{record.physical_examination}</p>
                                                </div>
                                            )}

                                            {record.diagnosis && (
                                                <div>
                                                    <h4 className="font-medium text-sm text-muted-foreground mb-1">
                                                        Diagnóstico
                                                    </h4>
                                                    <p className="text-sm whitespace-pre-wrap">{record.diagnosis}</p>
                                                </div>
                                            )}

                                            {record.treatment_plan && (
                                                <div>
                                                    <h4 className="font-medium text-sm text-muted-foreground mb-1">
                                                        Plano de Tratamento
                                                    </h4>
                                                    <p className="text-sm whitespace-pre-wrap">{record.treatment_plan}</p>
                                                </div>
                                            )}

                                            {record.prescriptions && (
                                                <div>
                                                    <h4 className="font-medium text-sm text-muted-foreground mb-1">
                                                        Prescrições
                                                    </h4>
                                                    <p className="text-sm whitespace-pre-wrap">{record.prescriptions}</p>
                                                </div>
                                            )}

                                            {record.notes && (
                                                <div>
                                                    <h4 className="font-medium text-sm text-muted-foreground mb-1">
                                                        Observações
                                                    </h4>
                                                    <p className="text-sm whitespace-pre-wrap">{record.notes}</p>
                                                </div>
                                            )}
                                        </CardContent>
                                    </Card>
                                ))}
                            </div>
                        )}

                        {/* Pagination */}
                        {medicalRecords.last_page > 1 && (
                            <div className="flex items-center justify-between px-2 py-4 mt-6">
                                <div className="text-sm text-muted-foreground">
                                    Mostrando {medicalRecords.data.length} de {medicalRecords.total} prontuários
                                </div>
                                <div className="flex gap-2">
                                    {medicalRecords.prev_page_url && (
                                        <Button
                                            variant="outline"
                                            size="sm"
                                            onClick={() => window.location.href = medicalRecords.prev_page_url!}
                                        >
                                            Anterior
                                        </Button>
                                    )}
                                    {medicalRecords.next_page_url && (
                                        <Button
                                            variant="outline"
                                            size="sm"
                                            onClick={() => window.location.href = medicalRecords.next_page_url!}
                                        >
                                            Próximo
                                        </Button>
                                    )}
                                </div>
                            </div>
                        )}
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}