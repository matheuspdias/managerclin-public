// resources/js/Pages/MedicalCertificates/Verify.tsx
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Head } from '@inertiajs/react';
import { AlertCircle, Calendar, CheckCircle, Clock, FileText, User, XCircle } from 'lucide-react';

interface VerifyMedicalCertificateProps {
    certificate: any;
    isValid: boolean;
    isExpired: boolean;
    verificationDate: string;
    error?: string;
}

export default function Verify({ certificate, isValid, isExpired, error }: VerifyMedicalCertificateProps) {
    // Se não há certificado (hash inválido)
    if (!certificate || error) {
        return (
            <div className="min-h-screen bg-gradient-to-br from-blue-50 to-gray-100 px-4 py-12">
                <Head title="Atestado Não Encontrado" />
                
                <div className="mx-auto max-w-2xl">
                    <Card className="w-full shadow-lg">
                        <CardHeader className="space-y-4 text-center">
                            <div className="flex justify-center">
                                <XCircle className="h-16 w-16 text-red-500" />
                            </div>
                            <CardTitle className="text-2xl">Atestado Não Encontrado</CardTitle>
                            <CardDescription>Verificação de autenticidade</CardDescription>
                        </CardHeader>
                        
                        <CardContent className="space-y-6 text-center">
                            <Badge variant="destructive" className="px-4 py-2 text-lg">
                                ATESTADO INVÁLIDO
                            </Badge>
                            
                            <div className="rounded-lg bg-red-50 p-4">
                                <AlertCircle className="mx-auto h-8 w-8 text-red-500" />
                                <p className="mt-2 text-red-700">
                                    {error || 'O atestado não foi encontrado em nosso sistema.'}
                                </p>
                                <p className="text-sm text-red-600 mt-2">
                                    Possíveis motivos:
                                </p>
                                <ul className="text-sm text-red-600 text-left mt-1 space-y-1">
                                    <li>• Código de verificação incorreto</li>
                                    <li>• Atestado foi excluído</li>
                                    <li>• Tentativa de fraude</li>
                                </ul>
                            </div>
                            
                            <div className="text-center text-sm text-muted-foreground">
                                <p>Verificado em: {new Date().toLocaleString('pt-BR')}</p>
                                <p className="mt-2">Entre em contato com a clínica para mais informações</p>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        );
    }

    // Se o certificado existe mas é inválido (hash não corresponde)
    if (!isValid) {
        return (
            <div className="min-h-screen bg-gradient-to-br from-blue-50 to-gray-100 px-4 py-12">
                <Head title="Atestado Inválido" />
                
                <div className="mx-auto max-w-2xl">
                    <Card className="w-full shadow-lg">
                        <CardHeader className="space-y-4 text-center">
                            <div className="flex justify-center">
                                <XCircle className="h-16 w-16 text-red-500" />
                            </div>
                            <CardTitle className="text-2xl">Atestado Inválido</CardTitle>
                            <CardDescription>Código: {certificate.validation_hash}</CardDescription>
                        </CardHeader>
                        
                        <CardContent className="space-y-6">
                            <div className="text-center">
                                <Badge variant="destructive" className="px-4 py-2 text-lg">
                                    ATESTADO INVÁLIDO
                                </Badge>
                                
                                <div className="mt-3 rounded-lg bg-red-50 p-4">
                                    <AlertCircle className="mx-auto h-8 w-8 text-red-500" />
                                    <p className="mt-2 text-red-700 font-semibold">
                                        Este atestado foi adulterado ou modificado
                                    </p>
                                    <p className="text-sm text-red-600 mt-2">
                                        O código de verificação não corresponde aos dados do atestado.
                                    </p>
                                </div>
                            </div>

                            {/* Mostrar informações do certificado mesmo sendo inválido (para transparência) */}
                            <div className="grid grid-cols-1 gap-4 rounded-lg bg-muted p-4 md:grid-cols-2">
                                <div className="flex items-center">
                                    <User className="mr-2 h-4 w-4" />
                                    <span><strong>Paciente:</strong> {certificate.customer.name}</span>
                                </div>
                                <div className="flex items-center">
                                    <User className="mr-2 h-4 w-4" />
                                    <span><strong>Médico:</strong> {certificate.user.name}</span>
                                </div>
                                <div className="flex items-center">
                                    <Calendar className="mr-2 h-4 w-4" />
                                    <span><strong>Emissão:</strong> {new Date(certificate.issue_date).toLocaleDateString('pt-BR')}</span>
                                </div>
                                <div className="flex items-center">
                                    <Calendar className="mr-2 h-4 w-4" />
                                    <span><strong>Válido até:</strong> {new Date(certificate.valid_until).toLocaleDateString('pt-BR')}</span>
                                </div>
                            </div>

                            <div className="text-center text-sm text-muted-foreground">
                                <p>Verificado em: {new Date().toLocaleString('pt-BR')}</p>
                                <p className="mt-2">Entre em contato com a clínica para verificar a autenticidade</p>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        );
    }

    // Certificado válido (fluxo normal)
    return (
        <div className="min-h-screen bg-gradient-to-br from-blue-50 to-gray-100 px-4 py-12">
            <Head title="Verificar Atestado Médico" />

            <div className="mx-auto max-w-2xl">
                <Card className="w-full shadow-lg">
                    <CardHeader className="space-y-4 text-center">
                        <div className="flex justify-center">
                            {isValid ? <CheckCircle className="h-16 w-16 text-green-500" /> : <XCircle className="h-16 w-16 text-red-500" />}
                        </div>

                        <CardTitle className="text-2xl">Verificação de Atestado Médico</CardTitle>

                        <CardDescription>Código: {certificate.validation_hash}</CardDescription>
                    </CardHeader>

                    <CardContent className="space-y-6">
                        {/* Status */}
                        <div className="text-center">
                            <Badge variant={isValid ? 'default' : 'destructive'} className="px-4 py-2 text-lg">
                                {isValid ? 'ATESTADO VÁLIDO' : 'ATESTADO INVÁLIDO'}
                            </Badge>

                            {isExpired && isValid && (
                                <Badge variant="secondary" className="ml-2 px-4 py-2 text-lg">
                                    <Clock className="mr-1 h-4 w-4" />
                                    EXPIRADO
                                </Badge>
                            )}
                        </div>

                        {/* Informações */}
                        <div className="grid grid-cols-1 gap-4 rounded-lg bg-muted p-4 md:grid-cols-2">
                            <div className="flex items-center">
                                <User className="mr-2 h-4 w-4" />
                                <span>
                                    <strong>Paciente:</strong> {certificate.customer.name}
                                </span>
                            </div>
                            <div className="flex items-center">
                                <User className="mr-2 h-4 w-4" />
                                <span>
                                    <strong>Médico:</strong> {certificate.user.name}
                                </span>
                            </div>
                            <div className="flex items-center">
                                <Calendar className="mr-2 h-4 w-4" />
                                <span>
                                    <strong>Emissão:</strong> {new Date(certificate.issue_date).toLocaleDateString('pt-BR')}
                                </span>
                            </div>
                            <div className="flex items-center">
                                <Calendar className="mr-2 h-4 w-4" />
                                <span>
                                    <strong>Válido até:</strong> {new Date(certificate.valid_until).toLocaleDateString('pt-BR')}
                                </span>
                            </div>
                        </div>

                        {/* Conteúdo do Atestado */}
                        <div className="rounded-lg border p-4">
                            <div className="mb-3 flex items-center">
                                <FileText className="mr-2 h-5 w-5" />
                                <strong className="text-lg">Conteúdo do Atestado</strong>
                            </div>
                            <p className="whitespace-pre-line text-gray-700">{certificate.content}</p>
                        </div>

                        {/* Informações de Verificação */}
                        <div className="rounded-lg bg-gray-50 p-3 text-center text-sm text-muted-foreground">
                            <p>Verificado em: {new Date().toLocaleString('pt-BR')}</p>
                            <p className="mt-1">
                                Emitido por:{' '}
                                {certificate.user.doctor_registration_number
                                    ? `CRM: ${certificate.user.doctor_registration_number}`
                                    : 'Sistema Médico'}
                            </p>
                        </div>

                        {/* Aviso Legal */}
                        <div className="text-center text-xs text-muted-foreground">
                            <p>Esta verificação foi realizada através do sistema {window.location.hostname}</p>
                            <p>Em caso de dúvidas, entre em contato com a clínica</p>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    );
}