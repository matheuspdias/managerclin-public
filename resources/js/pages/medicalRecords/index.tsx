import { Head, Link, router, usePage } from '@inertiajs/react';
import { useState, useEffect } from 'react';
import { FileText, Plus, Search, User, Calendar, Filter, Eye } from 'lucide-react';

import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Badge } from '@/components/ui/badge';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription } from '@/components/ui/dialog';
import { type BreadcrumbItem } from '@/types';
import { toast } from 'sonner';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Prontuários',
        href: '/medical-records',
    },
];

interface MedicalRecord {
    id: number;
    customer: {
        id: number;
        name: string;
        email: string;
        phone: string;
    };
    user: {
        id: number;
        name: string;
    };
    appointment?: {
        id: number;
        date: string;
        start_time: string;
    };
    chief_complaint?: string;
    created_at: string;
    updated_at: string;
}

interface Customer {
    id: number;
    name: string;
    email?: string;
    phone?: string;
}

interface MedicalRecordsPageProps {
    medicalRecords: {
        data: MedicalRecord[];
        total: number;
        current_page: number;
        per_page: number;
        last_page: number;
        prev_page_url: string | null;
        next_page_url: string | null;
    };
    filters: {
        search?: string;
        status?: string;
        date_from?: string;
        date_to?: string;
    };
    customers: Customer[];
    stats: {
        total: number;
        this_month: number;
        today: number;
    };
}

export default function MedicalRecordsIndex() {
    const page = usePage();
    const { medicalRecords, filters, stats, customers } = page.props as any;
    const [searchTerm, setSearchTerm] = useState(filters.search || '');
    const [isDialogOpen, setIsDialogOpen] = useState(false);
    const [patientSearch, setPatientSearch] = useState('');
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

    // Filtrar pacientes localmente baseado na busca
    // Limitar resultados: menos de 3 caracteres = 10 resultados, 3+ caracteres = 50 resultados
    const searchLength = patientSearch.length;
    const maxResults = searchLength < 3 ? 10 : 50;

    const filteredPatients = (customers || [])
        .filter((patient: Customer) => {
            if (!patientSearch) return true;
            const search = patientSearch.toLowerCase();
            return (
                patient.name?.toLowerCase().includes(search) ||
                patient.email?.toLowerCase().includes(search) ||
                patient.phone?.toLowerCase().includes(search)
            );
        })
        .slice(0, maxResults);

    const handleSelectPatient = (customerId: number) => {
        setIsDialogOpen(false);
        router.visit(`/medical-records/patient/${customerId}/create`);
    };

    const handleSearch = () => {
        router.get('/medical-records', { search: searchTerm }, { preserveState: true });
    };

    const handleKeyPress = (e: React.KeyboardEvent) => {
        if (e.key === 'Enter') {
            handleSearch();
        }
    };

    const formatDate = (dateString: string) => {
        return new Date(dateString).toLocaleDateString('pt-BR', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric',
        });
    };

    const formatDateTime = (dateString: string) => {
        return new Date(dateString).toLocaleDateString('pt-BR', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
        });
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Prontuários" />

            <div className="space-y-6">
                {/* Stats Cards */}
                <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Total de Prontuários</CardTitle>
                            <FileText className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{stats.total}</div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Este Mês</CardTitle>
                            <Calendar className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{stats.this_month}</div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Hoje</CardTitle>
                            <Calendar className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{stats.today}</div>
                        </CardContent>
                    </Card>
                </div>

                {/* Main Content */}
                <Card>
                    <CardHeader>
                        <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <CardTitle className="flex items-center gap-2">
                                    <FileText className="h-5 w-5" />
                                    Prontuários Médicos
                                </CardTitle>
                                <p className="text-sm text-muted-foreground">
                                    Gerencie e visualize os prontuários dos pacientes
                                </p>
                            </div>
                            <Button onClick={() => setIsDialogOpen(true)}>
                                <Plus className="mr-2 h-4 w-4" />
                                Novo Prontuário
                            </Button>
                        </div>

                        {/* Search and Filters */}
                        <div className="flex flex-col gap-4 sm:flex-row sm:items-center">
                            <div className="flex-1">
                                <div className="relative">
                                    <Search className="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 transform text-muted-foreground" />
                                    <Input
                                        placeholder="Buscar por paciente, médico ou queixa..."
                                        value={searchTerm}
                                        onChange={(e) => setSearchTerm(e.target.value)}
                                        onKeyPress={handleKeyPress}
                                        className="pl-10"
                                    />
                                </div>
                            </div>
                            <Button onClick={handleSearch} variant="outline">
                                <Search className="mr-2 h-4 w-4" />
                                Buscar
                            </Button>
                        </div>
                    </CardHeader>

                    <CardContent>
                        <div className="rounded-md border">
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>Paciente</TableHead>
                                        <TableHead>Médico</TableHead>
                                        <TableHead>Queixa Principal</TableHead>
                                        <TableHead>Data da Consulta</TableHead>
                                        <TableHead>Criado em</TableHead>
                                        <TableHead className="w-24">Ações</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {medicalRecords.data.length === 0 ? (
                                        <TableRow>
                                            <TableCell colSpan={6} className="text-center py-8">
                                                <div className="flex flex-col items-center gap-2">
                                                    <FileText className="h-8 w-8 text-muted-foreground" />
                                                    <p className="text-muted-foreground">Nenhum prontuário encontrado</p>
                                                </div>
                                            </TableCell>
                                        </TableRow>
                                    ) : (
                                        medicalRecords.data.map((record) => (
                                            <TableRow key={record.id} className="hover:bg-muted/50">
                                                <TableCell>
                                                    <div className="flex items-center gap-2">
                                                        <User className="h-4 w-4 text-muted-foreground" />
                                                        <div>
                                                            <p className="font-medium">{record.customer.name}</p>
                                                            <p className="text-xs text-muted-foreground">
                                                                {record.customer.phone}
                                                            </p>
                                                        </div>
                                                    </div>
                                                </TableCell>
                                                <TableCell>
                                                    <div className="flex items-center gap-2">
                                                        <div className="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center">
                                                            <span className="text-xs font-medium text-blue-700">
                                                                {record.user?.name?.charAt(0).toUpperCase()}
                                                            </span>
                                                        </div>
                                                        <span>{record.user?.name}</span>
                                                    </div>
                                                </TableCell>
                                                <TableCell>
                                                    <div className="max-w-xs">
                                                        <p className="truncate text-sm">
                                                            {record.chief_complaint || '-'}
                                                        </p>
                                                    </div>
                                                </TableCell>
                                                <TableCell>
                                                    {record.appointment ? (
                                                        <div className="flex items-center gap-2">
                                                            <Calendar className="h-4 w-4 text-muted-foreground" />
                                                            <div>
                                                                <p className="text-sm">
                                                                    {formatDate(record.appointment.date)}
                                                                </p>
                                                                <p className="text-xs text-muted-foreground">
                                                                    {record.appointment.start_time.slice(0, 5)}
                                                                </p>
                                                            </div>
                                                        </div>
                                                    ) : (
                                                        <span className="text-muted-foreground">-</span>
                                                    )}
                                                </TableCell>
                                                <TableCell>
                                                    <div className="text-sm text-muted-foreground">
                                                        {formatDateTime(record.created_at)}
                                                    </div>
                                                </TableCell>
                                                <TableCell>
                                                    <div className="flex gap-1">
                                                        <Button
                                                            variant="outline"
                                                            size="sm"
                                                            asChild
                                                        >
                                                            <Link href={`/medical-records/patient/${record.customer.id}`}>
                                                                <Eye className="h-4 w-4" />
                                                            </Link>
                                                        </Button>
                                                    </div>
                                                </TableCell>
                                            </TableRow>
                                        ))
                                    )}
                                </TableBody>
                            </Table>
                        </div>

                        {/* Pagination */}
                        {medicalRecords.last_page > 1 && (
                            <div className="flex items-center justify-between px-2 py-4">
                                <div className="text-sm text-muted-foreground">
                                    Mostrando {medicalRecords.data.length} de {medicalRecords.total} prontuários
                                </div>
                                <div className="flex gap-2">
                                    {medicalRecords.prev_page_url && (
                                        <Button
                                            variant="outline"
                                            size="sm"
                                            onClick={() => router.get(medicalRecords.prev_page_url!)}
                                        >
                                            Anterior
                                        </Button>
                                    )}
                                    {medicalRecords.next_page_url && (
                                        <Button
                                            variant="outline"
                                            size="sm"
                                            onClick={() => router.get(medicalRecords.next_page_url!)}
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

            {/* Dialog para selecionar paciente */}
            <Dialog open={isDialogOpen} onOpenChange={setIsDialogOpen}>
                <DialogContent className="max-w-2xl">
                    <DialogHeader>
                        <DialogTitle>Selecione um Paciente</DialogTitle>
                        <DialogDescription>
                            Busque e selecione o paciente para criar um novo prontuário médico
                        </DialogDescription>
                    </DialogHeader>

                    <div className="space-y-4">
                        {/* Search */}
                        <div className="space-y-2">
                            <div className="relative">
                                <Search className="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                                <Input
                                    placeholder="Buscar paciente por nome, email ou telefone..."
                                    value={patientSearch}
                                    onChange={(e) => setPatientSearch(e.target.value)}
                                    className="pl-10"
                                />
                            </div>
                            {patientSearch && filteredPatients.length > 0 && (
                                <p className="text-xs text-muted-foreground">
                                    Mostrando {filteredPatients.length} resultado(s)
                                    {searchLength < 3 && ' (digite mais para ver mais resultados)'}
                                </p>
                            )}
                        </div>

                        {/* Patients List */}
                        <div className="max-h-[400px] overflow-y-auto border rounded-md">
                            {filteredPatients.length === 0 ? (
                                <div className="p-8 text-center text-muted-foreground">
                                    <User className="mx-auto h-12 w-12 mb-2" />
                                    <p>Nenhum paciente encontrado</p>
                                </div>
                            ) : (
                                <div className="divide-y">
                                    {filteredPatients.map((patient: Customer) => (
                                        <button
                                            key={patient.id}
                                            onClick={() => handleSelectPatient(patient.id)}
                                            className="w-full p-4 hover:bg-muted/50 transition-colors text-left"
                                        >
                                            <div className="flex items-center gap-3">
                                                <div className="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center flex-shrink-0">
                                                    <User className="h-5 w-5 text-blue-600" />
                                                </div>
                                                <div className="flex-1 min-w-0">
                                                    <p className="font-medium truncate">{patient.name}</p>
                                                    <div className="flex items-center gap-3 text-sm text-muted-foreground">
                                                        {patient.phone && <span>{patient.phone}</span>}
                                                        {patient.email && <span className="truncate">{patient.email}</span>}
                                                    </div>
                                                </div>
                                            </div>
                                        </button>
                                    ))}
                                </div>
                            )}
                        </div>
                    </div>
                </DialogContent>
            </Dialog>
        </AppLayout>
    );
}