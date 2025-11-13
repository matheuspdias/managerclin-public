import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem, type PageProps } from '@/types';
import { Head, router } from '@inertiajs/react';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import {
    Calendar,
    Users,
    Send,
    XCircle,
    CheckCircle2,
    AlertCircle,
    Clock,
    Megaphone,
    ArrowLeft,
    TrendingUp,
    MessageSquare,
} from 'lucide-react';
import { toast } from 'sonner';

interface Recipient {
    id: number;
    status: 'pending' | 'sent' | 'failed';
    error_message: string | null;
    sent_at: string | null;
    customer: {
        id: number;
        name: string;
        phone: string;
    };
}

interface Campaign {
    id: number;
    name: string;
    message: string;
    status: 'draft' | 'scheduled' | 'sending' | 'sent' | 'failed' | 'cancelled';
    target_audience: 'all' | 'with_appointments' | 'without_appointments' | 'custom';
    scheduled_at: string | null;
    sent_at: string | null;
    total_recipients: number;
    sent_count: number;
    failed_count: number;
    created_at: string;
    created_by?: {
        id: number;
        name: string;
    };
    createdBy?: {
        id: number;
        name: string;
    };
    recipients: Recipient[];
}

interface CampaignShowProps extends PageProps {
    campaign: Campaign;
    statistics: {
        success_rate: number;
        pending_count: number;
        sent_count: number;
        failed_count: number;
    };
}

export default function MarketingCampaignShow({ campaign, statistics }: CampaignShowProps) {
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Marketing', href: '/marketing/campaigns' },
        { title: 'Campanhas', href: '/marketing/campaigns' },
        { title: campaign.name, href: `/marketing/campaigns/${campaign.id}` },
    ];

    function handleSendNow() {
        router.post(
            `/marketing/campaigns/${campaign.id}/send-now`,
            {},
            {
                onSuccess: () => {
                    toast.success('Campanha adicionada à fila de envio!');
                },
                onError: () => {
                    toast.error('Erro ao enviar campanha');
                },
            },
        );
    }

    function handleCancel() {
        router.post(
            `/marketing/campaigns/${campaign.id}/cancel`,
            {},
            {
                onSuccess: () => {
                    toast.success('Campanha cancelada com sucesso!');
                },
                onError: () => {
                    toast.error('Erro ao cancelar campanha');
                },
            },
        );
    }

    const getStatusBadge = (status: Campaign['status']) => {
        const statusConfig = {
            draft: { color: 'bg-gray-100 text-gray-800 border-gray-200', label: 'Rascunho', icon: MessageSquare },
            scheduled: { color: 'bg-blue-100 text-blue-800 border-blue-200', label: 'Agendada', icon: Clock },
            sending: { color: 'bg-yellow-100 text-yellow-800 border-yellow-200', label: 'Enviando', icon: Send },
            sent: { color: 'bg-green-100 text-green-800 border-green-200', label: 'Enviada', icon: CheckCircle2 },
            failed: { color: 'bg-red-100 text-red-800 border-red-200', label: 'Falhou', icon: AlertCircle },
            cancelled: { color: 'bg-gray-100 text-gray-800 border-gray-200', label: 'Cancelada', icon: XCircle },
        };

        const config = statusConfig[status];
        const Icon = config.icon;

        return (
            <Badge className={`${config.color} gap-1`}>
                <Icon className="h-3 w-3" />
                {config.label}
            </Badge>
        );
    };

    const getRecipientStatusBadge = (status: Recipient['status']) => {
        const statusConfig = {
            pending: { color: 'bg-yellow-100 text-yellow-800 border-yellow-200', label: 'Pendente' },
            sent: { color: 'bg-green-100 text-green-800 border-green-200', label: 'Enviado' },
            failed: { color: 'bg-red-100 text-red-800 border-red-200', label: 'Falhou' },
        };

        const config = statusConfig[status];

        return <Badge className={config.color}>{config.label}</Badge>;
    };

    const getTargetAudienceLabel = (audience: Campaign['target_audience']) => {
        const labels = {
            all: 'Todos os Pacientes',
            with_appointments: 'Com Agendamentos',
            without_appointments: 'Sem Agendamentos',
            custom: 'Personalizado',
        };

        return labels[audience] || audience;
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Campanha: ${campaign.name}`} />

            <div className="space-y-6 p-6">
                {/* Header */}
                <div className="flex flex-col justify-between gap-4 md:flex-row md:items-start">
                    <div className="space-y-1">
                        <div className="flex items-center gap-2">
                            <Button
                                variant="ghost"
                                size="sm"
                                onClick={() => router.get('/marketing/campaigns')}
                            >
                                <ArrowLeft className="h-4 w-4" />
                            </Button>
                            <Megaphone className="h-6 w-6 text-muted-foreground" />
                            <h1 className="text-2xl font-bold tracking-tight">{campaign.name}</h1>
                        </div>
                        <p className="text-muted-foreground ml-12">Detalhes da campanha de marketing</p>
                    </div>

                    <div className="flex gap-2">
                        {(campaign.status === 'draft' || campaign.status === 'scheduled') && (
                            <Button onClick={handleSendNow} className="gap-2">
                                <Send className="h-4 w-4" />
                                Enviar Agora
                            </Button>
                        )}
                        {(campaign.status === 'scheduled' || campaign.status === 'sending') && (
                            <Button onClick={handleCancel} variant="outline" className="gap-2">
                                <XCircle className="h-4 w-4" />
                                Cancelar
                            </Button>
                        )}
                    </div>
                </div>

                {/* Estatísticas */}
                <div className="grid gap-4 md:grid-cols-4">
                    <Card>
                        <CardHeader className="pb-3">
                            <CardTitle className="text-sm font-medium">Total de Destinatários</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="flex items-center gap-2">
                                <Users className="h-4 w-4 text-muted-foreground" />
                                <span className="text-2xl font-bold">{campaign.total_recipients}</span>
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="pb-3">
                            <CardTitle className="text-sm font-medium">Enviados</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="flex items-center gap-2">
                                <CheckCircle2 className="h-4 w-4 text-green-600" />
                                <span className="text-2xl font-bold text-green-600">{statistics.sent_count}</span>
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="pb-3">
                            <CardTitle className="text-sm font-medium">Falharam</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="flex items-center gap-2">
                                <AlertCircle className="h-4 w-4 text-red-600" />
                                <span className="text-2xl font-bold text-red-600">{statistics.failed_count}</span>
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="pb-3">
                            <CardTitle className="text-sm font-medium">Taxa de Sucesso</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="flex items-center gap-2">
                                <TrendingUp className="h-4 w-4 text-blue-600" />
                                <span className="text-2xl font-bold text-blue-600">{statistics.success_rate}%</span>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                {/* Informações da Campanha */}
                <div className="grid gap-4 md:grid-cols-2">
                    <Card>
                        <CardHeader>
                            <CardTitle>Informações</CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div>
                                <p className="text-sm font-medium text-muted-foreground">Status</p>
                                <div className="mt-1">{getStatusBadge(campaign.status)}</div>
                            </div>

                            <div>
                                <p className="text-sm font-medium text-muted-foreground">Público-Alvo</p>
                                <p className="mt-1">{getTargetAudienceLabel(campaign.target_audience)}</p>
                            </div>

                            {campaign.scheduled_at && (
                                <div>
                                    <p className="text-sm font-medium text-muted-foreground">Agendado para</p>
                                    <div className="mt-1 flex items-center gap-2">
                                        <Calendar className="h-4 w-4 text-muted-foreground" />
                                        <span>
                                            {new Date(campaign.scheduled_at).toLocaleDateString('pt-BR')} às{' '}
                                            {new Date(campaign.scheduled_at).toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' })}
                                        </span>
                                    </div>
                                </div>
                            )}

                            {campaign.sent_at && (
                                <div>
                                    <p className="text-sm font-medium text-muted-foreground">Enviado em</p>
                                    <div className="mt-1 flex items-center gap-2">
                                        <Calendar className="h-4 w-4 text-muted-foreground" />
                                        <span>
                                            {new Date(campaign.sent_at).toLocaleDateString('pt-BR')} às{' '}
                                            {new Date(campaign.sent_at).toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' })}
                                        </span>
                                    </div>
                                </div>
                            )}

                            <div>
                                <p className="text-sm font-medium text-muted-foreground">Criado em</p>
                                <p className="mt-1">{new Date(campaign.created_at).toLocaleDateString('pt-BR')}</p>
                            </div>

                            {(campaign.created_by || campaign.createdBy) && (
                                <div>
                                    <p className="text-sm font-medium text-muted-foreground">Criado por</p>
                                    <p className="mt-1">{campaign.created_by?.name || campaign.createdBy?.name}</p>
                                </div>
                            )}
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle>Mensagem</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="rounded-lg bg-muted p-4">
                                <p className="whitespace-pre-wrap">{campaign.message}</p>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                {/* Lista de Destinatários */}
                <Card>
                    <CardHeader>
                        <CardTitle>Destinatários ({campaign.recipients.length})</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div className="rounded-md border">
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>Paciente</TableHead>
                                        <TableHead>Telefone</TableHead>
                                        <TableHead>Status</TableHead>
                                        <TableHead>Enviado em</TableHead>
                                        <TableHead>Erro</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {campaign.recipients.map((recipient) => (
                                        <TableRow key={recipient.id}>
                                            <TableCell>
                                                <p className="font-medium">{recipient.customer.name}</p>
                                            </TableCell>
                                            <TableCell>{recipient.customer.phone}</TableCell>
                                            <TableCell>{getRecipientStatusBadge(recipient.status)}</TableCell>
                                            <TableCell>
                                                {recipient.sent_at ? (
                                                    <span className="text-sm">
                                                        {new Date(recipient.sent_at).toLocaleDateString('pt-BR')} às{' '}
                                                        {new Date(recipient.sent_at).toLocaleTimeString('pt-BR', {
                                                            hour: '2-digit',
                                                            minute: '2-digit',
                                                        })}
                                                    </span>
                                                ) : (
                                                    <span className="text-sm text-muted-foreground">-</span>
                                                )}
                                            </TableCell>
                                            <TableCell>
                                                {recipient.error_message ? (
                                                    <span className="text-sm text-red-600">{recipient.error_message}</span>
                                                ) : (
                                                    <span className="text-sm text-muted-foreground">-</span>
                                                )}
                                            </TableCell>
                                        </TableRow>
                                    ))}
                                </TableBody>
                            </Table>
                        </div>

                        {campaign.recipients.length === 0 && (
                            <div className="py-12 text-center">
                                <Users className="mx-auto h-12 w-12 text-muted-foreground" />
                                <h3 className="mt-4 text-lg font-medium">Nenhum destinatário</h3>
                                <p className="text-muted-foreground">Esta campanha ainda não possui destinatários.</p>
                            </div>
                        )}
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
