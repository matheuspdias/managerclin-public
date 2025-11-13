import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/react';
import { CalendarClock, Download, Eye, Plus, Search, Trash2 } from 'lucide-react';

interface User {
    id: number;
    name: string;
}

interface MedicalCertificate {
    id: number;
    content: string;
    days_off: number;
    issue_date: string;
    valid_until: string;
    digital_signature: string;
    validation_hash: string;
    user: User;
    customer: User;
}

interface PaginatedResponse {
    data: MedicalCertificate[];
    links?: any[];
    meta?: {
        total: number;
        current_page: number;
        last_page: number;
        per_page: number;
    };
}

interface IndexMedicalCertificatesProps {
    certificates: PaginatedResponse;
    customers: User[];
    filters: {
        search?: string;
        customer?: string;
        status?: string;
    };
}

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Atestados Médicos',
        href: '/medical-certificates',
    },
];

export default function Index({ certificates, customers, filters }: IndexMedicalCertificatesProps) {
    const { data, setData, get } = useForm({
        search: filters.search || '',
        customer: filters.customer || 'all',
        status: filters.status || 'all',
    });

    const handleFilter = () => {
        const params: any = {
            search: data.search || '',
            status: data.status === 'all' ? '' : data.status,
            customer: data.customer === 'all' ? '' : data.customer,
        };

        // Remover parâmetros vazios
        Object.keys(params).forEach((key) => {
            if (params[key] === '') {
                delete params[key];
            }
        });

        get(route('medical-certificates.index', params), {
            preserveState: true,
            preserveScroll: true,
        });
    };

    const handleReset = () => {
        setData({
            search: '',
            customer: 'all',
            status: 'all',
        });

        get(route('medical-certificates.index'), {
            preserveState: true,
            preserveScroll: true,
        });
    };

    const isValid = (validUntil: string) => new Date(validUntil) > new Date();

    // Contagem segura de certificados
    const totalCertificates = certificates?.meta?.total || certificates?.data?.length || 0;

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Atestados Médicos" />
            <div className="space-y-6 p-6">
                <div className="flex items-center justify-between">
                    <div className="flex items-center space-x-2">
                        <CalendarClock className="h-8 w-8" />
                        <div>
                            <h2 className="text-3xl font-bold tracking-tight">Atestados Médicos</h2>
                            <p className="text-muted-foreground">Gerencie todos os atestados médicos do sistema</p>
                        </div>
                    </div>
                    <Button asChild>
                        <Link href={route('medical-certificates.create')}>
                            <Plus className="mr-2 h-4 w-4" />
                            Novo Atestado
                        </Link>
                    </Button>
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle>Filtros</CardTitle>
                        <CardDescription>Filtre os atestados por paciente ou status</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div className="grid grid-cols-1 gap-4 md:grid-cols-4">
                            <div className="space-y-2">
                                <label className="text-sm font-medium">Buscar</label>
                                <Input
                                    placeholder="Pesquisar..."
                                    value={data.search}
                                    onChange={(e) => setData('search', e.target.value)}
                                    onKeyDown={(e) => e.key === 'Enter' && handleFilter()}
                                />
                            </div>
                            <div className="space-y-2">
                                <label className="text-sm font-medium">Paciente</label>
                                <Select value={data.customer} onValueChange={(value) => setData('customer', value)}>
                                    <SelectTrigger>
                                        <SelectValue placeholder="Todos os pacientes" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="all">Todos os pacientes</SelectItem>
                                        {customers.map((customer) => (
                                            <SelectItem key={customer.id} value={customer.id.toString()}>
                                                {customer.name}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </div>
                            <div className="space-y-2">
                                <label className="text-sm font-medium">Status</label>
                                <Select value={data.status} onValueChange={(value) => setData('status', value)}>
                                    <SelectTrigger>
                                        <SelectValue placeholder="Todos os status" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="all">Todos os status</SelectItem>
                                        <SelectItem value="valid">Válidos</SelectItem>
                                        <SelectItem value="expired">Expirados</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                            <div className="flex items-end space-y-2">
                                <div className="flex space-x-2">
                                    <Button onClick={handleFilter}>
                                        <Search className="mr-2 h-4 w-4" />
                                        Filtrar
                                    </Button>
                                    <Button variant="outline" onClick={handleReset}>
                                        Limpar
                                    </Button>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>Lista de Atestados</CardTitle>
                        <CardDescription>{totalCertificates} atestado(s) encontrado(s)</CardDescription>
                    </CardHeader>
                    <CardContent>
                        {!certificates?.data || certificates.data.length === 0 ? (
                            <div className="py-12 text-center">
                                <CalendarClock className="mx-auto h-12 w-12 text-muted-foreground" />
                                <p className="mt-4 text-muted-foreground">Nenhum atestado encontrado.</p>
                                <Button asChild className="mt-4">
                                    <Link href={route('medical-certificates.create')}>Criar Primeiro Atestado</Link>
                                </Button>
                            </div>
                        ) : (
                            <div className="rounded-md border">
                                <Table>
                                    <TableHeader>
                                        <TableRow>
                                            <TableHead>Paciente</TableHead>
                                            <TableHead>Médico</TableHead>
                                            <TableHead>Emissão</TableHead>
                                            <TableHead>Validade</TableHead>
                                            <TableHead>Dias</TableHead>
                                            <TableHead>Status</TableHead>
                                            <TableHead className="text-right">Ações</TableHead>
                                        </TableRow>
                                    </TableHeader>
                                    <TableBody>
                                        {certificates.data.map((certificate) => (
                                            <TableRow key={certificate.id}>
                                                <TableCell className="font-medium">{certificate.customer.name}</TableCell>
                                                <TableCell>{certificate.user.name}</TableCell>
                                                <TableCell>{new Date(certificate.issue_date).toLocaleDateString('pt-BR')}</TableCell>
                                                <TableCell>{new Date(certificate.valid_until).toLocaleDateString('pt-BR')}</TableCell>
                                                <TableCell>{certificate.days_off} dias</TableCell>
                                                <TableCell>
                                                    <Badge variant={isValid(certificate.valid_until) ? 'default' : 'secondary'}>
                                                        {isValid(certificate.valid_until) ? 'Válido' : 'Expirado'}
                                                    </Badge>
                                                </TableCell>
                                                <TableCell className="text-right">
                                                    <div className="flex justify-end space-x-2">
                                                        <Button variant="outline" size="sm" asChild>
                                                            <Link href={route('medical-certificates.show', certificate.id)}>
                                                                <Eye className="h-4 w-4" />
                                                            </Link>
                                                        </Button>
                                                        <Button variant="outline" size="sm" asChild>
                                                            <a
                                                                href={route('medical-certificates.download', certificate.id)}
                                                                target="_blank"
                                                                rel="noopener noreferrer"
                                                            >
                                                                <Download className="h-4 w-4" />
                                                            </a>
                                                        </Button>

                                                        <Button variant="outline" size="sm" className="text-destructive" asChild>
                                                            <Link
                                                                href={route('medical-certificates.destroy', certificate.id)}
                                                                method="delete"
                                                                as="button"
                                                                onBefore={() => confirm('Tem certeza que deseja excluir este atestado?')}
                                                            >
                                                                <Trash2 className="h-4 w-4" />
                                                            </Link>
                                                        </Button>
                                                    </div>
                                                </TableCell>
                                            </TableRow>
                                        ))}
                                    </TableBody>
                                </Table>
                            </div>
                        )}
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
