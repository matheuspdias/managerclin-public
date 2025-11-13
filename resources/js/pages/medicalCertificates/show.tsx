import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';
import { CalendarDays, Download } from 'lucide-react';
import { toast } from 'sonner';

interface MedicalCertificate {
    id: number;
    content: string;
    days_off: number;
    issue_date: string;
    valid_until: string;
    digital_signature: string;
    validation_hash: string;
    user: {
        name: string;
        doctor_registration_number: string;
    };
    customer: {
        name: string;
    };
}

interface ShowMedicalCertificateProps {
    certificate: MedicalCertificate;
    qrCode: string;
    verificationUrl: string;
}

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Atestados',
        href: '/medical-certificates',
    },
    {
        title: 'Visualizar Atestado',
        href: '#',
    },
];

export default function Show({ certificate, qrCode, verificationUrl }: ShowMedicalCertificateProps) {
    const isValid = new Date(certificate.valid_until) > new Date();

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Visualizar Atestado Médico" />

            <div className="space-y-6 p-6">
                <div className="flex items-center justify-between">
                    <div>
                        <h2 className="text-3xl font-bold tracking-tight">Atestado Médico</h2>
                        <p className="text-muted-foreground">Emitido para {certificate.customer.name}</p>
                    </div>
                    <Button variant="outline" size="sm" asChild>
                        <a href={route('medical-certificates.download', certificate.id)} target="_blank" rel="noopener noreferrer">
                            <Download className="h-4 w-4" />
                        </a>
                    </Button>
                </div>
                <Card>
                    <CardHeader>
                        <div className="flex items-center justify-between">
                            <div>
                                <CardTitle>Atestado Médico #{certificate.id}</CardTitle>
                                <CardDescription>Emitido em {new Date(certificate.issue_date).toLocaleDateString('pt-BR')}</CardDescription>
                            </div>
                            <Badge variant={isValid ? 'default' : 'secondary'}>{isValid ? 'Válido' : 'Expirado'}</Badge>
                        </div>
                    </CardHeader>
                    <CardContent className="space-y-6">
                        <div className="rounded-lg border bg-muted/50 p-6">
                            <div className="mb-8 text-center">
                                <h3 className="text-2xl font-bold uppercase">Atestado Médico</h3>
                            </div>

                            <div className="mb-8 text-justify leading-relaxed">
                                <p className="whitespace-pre-line">{certificate.content}</p>
                            </div>

                            <div className="mb-8 grid grid-cols-1 gap-4 md:grid-cols-2">
                                <div className="flex items-center">
                                    <CalendarDays className="mr-2 h-4 w-4" />
                                    <span>Data de Emissão: {new Date(certificate.issue_date).toLocaleDateString('pt-BR')}</span>
                                </div>
                                <div className="flex items-center">
                                    <CalendarDays className="mr-2 h-4 w-4" />
                                    <span>Válido até: {new Date(certificate.valid_until).toLocaleDateString('pt-BR')}</span>
                                </div>
                            </div>

                            <div className="mt-12 text-center">
                                <div className="mx-auto mb-4 w-64 border-t-2 border-foreground"></div>
                                <p className="text-lg font-bold">{certificate.user.name}</p>
                                <p className="text-muted-foreground">CRM: {certificate.user.doctor_registration_number}</p>
                                <p className="mt-2 text-xs text-muted-foreground">
                                    Assinatura Digital: {certificate.digital_signature.substring(0, 16)}...
                                </p>
                                <p className="text-xs text-muted-foreground">Código de Validação: {certificate.validation_hash}</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>Verificação de Autenticidade</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div className="space-y-2">
                            <Label>Link Público de Verificação:</Label>
                            <div className="flex items-center space-x-2">
                                <Input value={`${window.location.origin}/medical-certificates/verify/${certificate.validation_hash}`} readOnly />
                                <Button
                                    onClick={() => {
                                        navigator.clipboard.writeText(verificationUrl);
                                        toast.success('Link copiado!');
                                    }}
                                >
                                    Copiar
                                </Button>
                            </div>
                            <p className="text-sm text-muted-foreground">
                                Compartilhe este link para que terceiros possam verificar a autenticidade deste atestado
                            </p>
                        </div>

                        {/* QR Code para verificação */}
                        <div className="mt-4 text-center">
                            <img src={`data:image/png;base64,${qrCode}`} alt="QR Code para verificação" className="mx-auto" />
                            <p className="mt-2 text-sm text-muted-foreground">Escaneie o QR Code para verificar</p>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
