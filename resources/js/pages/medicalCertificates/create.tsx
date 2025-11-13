import { Button } from '@/components/ui/button';
import { Calendar } from '@/components/ui/calendar';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/app-layout';
import { cn } from '@/lib/utils';
import { type BreadcrumbItem } from '@/types';
import { Patient } from '@/types/patient';
import { Head, useForm } from '@inertiajs/react';
import { format } from 'date-fns';
import { ptBR } from 'date-fns/locale';
import { CalendarIcon } from 'lucide-react';
import { useState } from 'react';

interface CreateMedicalCertificateProps {
    customers: Patient[];
}

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Atestados Médicos',
        href: '/medical-certificates',
    },
    {
        title: 'Novo Atestado',
        href: '#',
    },
];

export default function Create({ customers }: CreateMedicalCertificateProps) {
    const [date, setDate] = useState<Date | undefined>(new Date(Date.now() + 86400000));
    const [selectedCustomer, setSelectedCustomer] = useState<string>('');

    const { data, setData, errors, post, processing } = useForm({
        id_customer: '',
        content: '',
        days_off: 1,
        valid_until: date?.toISOString().split('T')[0] || '',
    });

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        data.content = data.content.replace('[DIAS]', data.days_off.toString());
        post(route('medical-certificates.store'));
    };

    const handleCustomerChange = (value: string) => {
        setSelectedCustomer(value);
        setData('id_customer', value);

        const customer = customers.find((c) => c.id.toString() === value);
        if (customer) {
            setData(
                'content',
                `Atesto que o(a) paciente ${customer.name} necessita de afastamento das suas atividades por período de [DIAS] dias, a partir de ${new Date().toLocaleDateString('pt-BR')}.`,
            );
        }
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Emitir Atestado Médico" />

            <div className="space-y-6 p-6">
                <div className="flex items-center justify-between">
                    <div>
                        <h2 className="text-3xl font-bold tracking-tight">Emitir Atestado Médico</h2>
                        <p className="text-muted-foreground">Preencha os dados para emitir um novo atestado</p>
                    </div>
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle>Dados do Atestado</CardTitle>
                        <CardDescription>Preencha as informações necessárias para emitir o atestado médico</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <form onSubmit={handleSubmit} className="space-y-6">
                            <div className="grid grid-cols-1 gap-6 md:grid-cols-2">
                                <div className="space-y-2">
                                    <Label htmlFor="customer">Paciente *</Label>
                                    <Select value={selectedCustomer} onValueChange={handleCustomerChange}>
                                        <SelectTrigger className={errors.id_customer ? 'border-destructive' : ''}>
                                            <SelectValue placeholder="Selecione um paciente" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            {customers.map((customer) => (
                                                <SelectItem key={customer.id} value={customer.id.toString()}>
                                                    {customer.name}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                    {errors.id_customer && <p className="text-sm text-destructive">{errors.id_customer}</p>}
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="days_off">Dias de Afastamento *</Label>
                                    <Input
                                        id="days_off"
                                        type="number"
                                        min="1"
                                        value={data.days_off}
                                        onChange={(e) => setData('days_off', parseInt(e.target.value))}
                                        className={errors.days_off ? 'border-destructive' : ''}
                                    />
                                    {errors.days_off && <p className="text-sm text-destructive">{errors.days_off}</p>}
                                </div>
                            </div>

                            <div className="space-y-2">
                                <Label htmlFor="valid_until">Válido até *</Label>
                                <Popover>
                                    <PopoverTrigger asChild>
                                        <Button
                                            variant={'outline'}
                                            className={cn(
                                                'w-full justify-start text-left font-normal',
                                                !date && 'text-muted-foreground',
                                                errors.valid_until && 'border-destructive',
                                            )}
                                        >
                                            <CalendarIcon className="mr-2 h-4 w-4" />
                                            {date ? format(date, 'PPP', { locale: ptBR }) : <span>Selecione uma data</span>}
                                        </Button>
                                    </PopoverTrigger>
                                    <PopoverContent className="w-auto p-0">
                                        <Calendar
                                            mode="single"
                                            selected={date}
                                            onSelect={(selectedDate) => {
                                                setDate(selectedDate);
                                                setData('valid_until', selectedDate?.toISOString().split('T')[0] || '');
                                            }}
                                            initialFocus
                                            disabled={(date) => date <= new Date()}
                                        />
                                    </PopoverContent>
                                </Popover>
                                {errors.valid_until && <p className="text-sm text-destructive">{errors.valid_until}</p>}
                            </div>

                            <div className="space-y-2">
                                <Label htmlFor="content">Conteúdo do Atestado *</Label>
                                <Textarea
                                    id="content"
                                    rows={6}
                                    value={data.content.replace('[DIAS]', data.days_off.toString())}
                                    onChange={(e) => setData('content', e.target.value)}
                                    className={errors.content ? 'border-destructive' : ''}
                                    placeholder="Digite o conteúdo do atestado..."
                                />
                                {errors.content && <p className="text-sm text-destructive">{errors.content}</p>}
                            </div>

                            <div className="flex justify-end space-x-4">
                                <Button type="button" variant="outline" asChild>
                                    <a href={route('medical-certificates.index')}>Cancelar</a>
                                </Button>
                                <Button type="submit" disabled={processing}>
                                    {processing ? 'Emitindo...' : 'Emitir Atestado'}
                                </Button>
                            </div>
                        </form>
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
