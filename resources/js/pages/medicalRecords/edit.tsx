import { Head, router, useForm, usePage } from '@inertiajs/react';
import { ArrowLeft, FileText, Save, User } from 'lucide-react';
import { useEffect } from 'react';

import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { format, parseISO } from 'date-fns';
import { toast } from 'sonner';

interface Customer {
    id: number;
    name: string;
    email?: string;
    phone?: string;
    birthdate?: string;
}

interface Appointment {
    id: number;
    date: string;
    start_time: string;
    end_time: string;
    status: string;
    user?: {
        id: number;
        name: string;
    };
    service?: {
        id: number;
        name: string;
    };
    room?: {
        id: number;
        name: string;
    };
}

interface MedicalRecord {
    id: number;
    id_customer: number;
    id_appointment: number | null;
    chief_complaint: string;
    medical_history?: string;
    physical_exam: string;
    diagnosis: string;
    treatment_plan: string;
    prescriptions?: string;
    observations?: string;
    created_at: string;
    updated_at: string;
}

interface EditMedicalRecordProps {
    customer: Customer;
    appointments: Appointment[];
    medicalRecord: MedicalRecord;
}

export default function EditMedicalRecord() {
    const page = usePage();
    const { customer, appointments, medicalRecord } = page.props as any;
    const flashError = (page.props as any).flash?.error;
    const flashSuccess = (page.props as any).flash?.success;

    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: 'Prontuários',
            href: '/medical-records',
        },
        {
            title: customer.name,
            href: `/medical-records/patient/${customer.id}`,
        },
        {
            title: 'Editar Prontuário',
            href: `/medical-records/${medicalRecord.id}/edit`,
        },
    ];

    const { data, setData, patch, processing, errors, transform } = useForm({
        id_customer: medicalRecord.id_customer,
        id_appointment: medicalRecord.id_appointment?.toString() || 'none',
        chief_complaint: medicalRecord.chief_complaint || '',
        medical_history: medicalRecord.medical_history || '',
        physical_exam: medicalRecord.physical_exam || '',
        diagnosis: medicalRecord.diagnosis || '',
        treatment_plan: medicalRecord.treatment_plan || '',
        prescriptions: medicalRecord.prescriptions || '',
        observations: medicalRecord.observations || '',
    });

    // Exibir mensagens flash do backend
    useEffect(() => {
        if (flashError) {
            toast.error(flashError);
        }
        if (flashSuccess) {
            toast.success(flashSuccess);
        }
    }, [flashError, flashSuccess]);

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();

        // Transform id_appointment before submitting
        transform((data) => ({
            ...data,
            id_appointment: data.id_appointment === 'none' ? null : data.id_appointment,
        }));

        patch(`/medical-records/${medicalRecord.id}`, {
            onSuccess: () => {
                toast.success('Prontuário atualizado com sucesso!');
                router.visit(`/medical-records/patient/${customer.id}`);
            },
            onError: () => {
                toast.error('Erro ao atualizar prontuário');
            },
            preserveScroll: true,
        });
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Editar Prontuário - ${customer.name}`} />

            <div className="space-y-6">
                {/* Patient Info Card */}
                <Card>
                    <CardHeader>
                        <div className="flex items-center justify-between">
                            <div className="flex items-center gap-4">
                                <div className="flex h-12 w-12 items-center justify-center rounded-full bg-blue-100">
                                    <User className="h-6 w-6 text-blue-600" />
                                </div>
                                <div>
                                    <CardTitle className="text-xl">{customer.name}</CardTitle>
                                    <div className="flex items-center gap-4 text-sm text-muted-foreground">
                                        {customer.phone && <span>{customer.phone}</span>}
                                        {customer.email && <span>{customer.email}</span>}
                                        {customer.birthdate && <span>Nascimento: {format(parseISO(customer.birthdate), 'dd/MM/yyyy')}</span>}
                                    </div>
                                </div>
                            </div>
                            <Button variant="outline" onClick={() => router.visit(`/medical-records/patient/${customer.id}`)}>
                                <ArrowLeft className="mr-2 h-4 w-4" />
                                Voltar
                            </Button>
                        </div>
                    </CardHeader>
                </Card>

                {/* Medical Record Form */}
                <Card>
                    <CardHeader>
                        <CardTitle className="flex items-center gap-2">
                            <FileText className="h-5 w-5" />
                            Editar Prontuário Médico
                        </CardTitle>
                        <p className="text-sm text-muted-foreground">
                            Criado em: {format(parseISO(medicalRecord.created_at), "dd/MM/yyyy 'às' HH:mm")}
                        </p>
                    </CardHeader>
                    <CardContent>
                        <form onSubmit={handleSubmit} className="space-y-6">
                            {/* Appointment Selection */}
                            <div className="space-y-2">
                                <Label htmlFor="id_appointment">Vincular a Agendamento (Opcional)</Label>
                                <Select value={data.id_appointment} onValueChange={(value) => setData('id_appointment', value)}>
                                    <SelectTrigger className={errors.id_appointment ? 'border-red-500' : ''}>
                                        <SelectValue placeholder="Selecione um agendamento ou deixe em branco" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="none">Nenhum agendamento (consulta avulsa)</SelectItem>
                                        {appointments.map((appointment) => (
                                            <SelectItem key={appointment.id} value={appointment.id.toString()}>
                                                <div className="flex flex-col">
                                                    <span>
                                                        {format(parseISO(appointment.date), 'dd/MM/yyyy')} às {appointment.start_time.slice(0, 5)}
                                                    </span>
                                                    {appointment.service && (
                                                        <span className="text-xs text-muted-foreground">
                                                            {appointment.service.name}
                                                            {appointment.user && ` - Dr(a). ${appointment.user.name}`}
                                                        </span>
                                                    )}
                                                </div>
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                                {errors.id_appointment && <p className="text-sm text-red-500">{errors.id_appointment}</p>}
                            </div>

                            {/* Chief Complaint */}
                            <div className="space-y-2">
                                <Label htmlFor="chief_complaint">Queixa Principal *</Label>
                                <Input
                                    id="chief_complaint"
                                    value={data.chief_complaint}
                                    onChange={(e) => setData('chief_complaint', e.target.value)}
                                    placeholder="Descreva a principal queixa do paciente..."
                                    className={errors.chief_complaint ? 'border-red-500' : ''}
                                />
                                {errors.chief_complaint && <p className="text-sm text-red-500">{errors.chief_complaint}</p>}
                            </div>

                            {/* Medical History */}
                            <div className="space-y-2">
                                <Label htmlFor="medical_history">Histórico Médico</Label>
                                <Textarea
                                    id="medical_history"
                                    value={data.medical_history}
                                    onChange={(e) => setData('medical_history', e.target.value)}
                                    placeholder="Descreva o histórico médico do paciente..."
                                    rows={4}
                                    className={errors.medical_history ? 'border-red-500' : ''}
                                />
                                {errors.medical_history && <p className="text-sm text-red-500">{errors.medical_history}</p>}
                            </div>

                            {/* Physical Examination */}
                            <div className="space-y-2">
                                <Label htmlFor="physical_exam">Exame Físico *</Label>
                                <Textarea
                                    id="physical_exam"
                                    value={data.physical_exam}
                                    onChange={(e) => setData('physical_exam', e.target.value)}
                                    placeholder="Descreva os achados do exame físico..."
                                    rows={4}
                                    className={errors.physical_exam ? 'border-red-500' : ''}
                                />
                                {errors.physical_exam && <p className="text-sm text-red-500">{errors.physical_exam}</p>}
                            </div>

                            {/* Diagnosis */}
                            <div className="space-y-2">
                                <Label htmlFor="diagnosis">Diagnóstico *</Label>
                                <Textarea
                                    id="diagnosis"
                                    value={data.diagnosis}
                                    onChange={(e) => setData('diagnosis', e.target.value)}
                                    placeholder="Descreva o diagnóstico..."
                                    rows={3}
                                    className={errors.diagnosis ? 'border-red-500' : ''}
                                />
                                {errors.diagnosis && <p className="text-sm text-red-500">{errors.diagnosis}</p>}
                            </div>

                            {/* Treatment Plan */}
                            <div className="space-y-2">
                                <Label htmlFor="treatment_plan">Plano de Tratamento *</Label>
                                <Textarea
                                    id="treatment_plan"
                                    value={data.treatment_plan}
                                    onChange={(e) => setData('treatment_plan', e.target.value)}
                                    placeholder="Descreva o plano de tratamento..."
                                    rows={4}
                                    className={errors.treatment_plan ? 'border-red-500' : ''}
                                />
                                {errors.treatment_plan && <p className="text-sm text-red-500">{errors.treatment_plan}</p>}
                            </div>

                            {/* Prescriptions */}
                            <div className="space-y-2">
                                <Label htmlFor="prescriptions">Prescrições</Label>
                                <Textarea
                                    id="prescriptions"
                                    value={data.prescriptions}
                                    onChange={(e) => setData('prescriptions', e.target.value)}
                                    placeholder="Liste as medicações prescritas..."
                                    rows={4}
                                    className={errors.prescriptions ? 'border-red-500' : ''}
                                />
                                {errors.prescriptions && <p className="text-sm text-red-500">{errors.prescriptions}</p>}
                            </div>

                            {/* Observations */}
                            <div className="space-y-2">
                                <Label htmlFor="observations">Observações Adicionais</Label>
                                <Textarea
                                    id="observations"
                                    value={data.observations}
                                    onChange={(e) => setData('observations', e.target.value)}
                                    placeholder="Observações gerais..."
                                    rows={3}
                                    className={errors.observations ? 'border-red-500' : ''}
                                />
                                {errors.observations && <p className="text-sm text-red-500">{errors.observations}</p>}
                            </div>

                            {/* Submit Button */}
                            <div className="flex justify-end gap-2 pt-6">
                                <Button
                                    type="button"
                                    variant="outline"
                                    onClick={() => router.visit(`/medical-records/patient/${customer.id}`)}
                                >
                                    Cancelar
                                </Button>
                                <Button type="submit" disabled={processing}>
                                    <Save className="mr-2 h-4 w-4" />
                                    {processing ? 'Salvando...' : 'Atualizar Prontuário'}
                                </Button>
                            </div>
                        </form>
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
