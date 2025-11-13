import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem, type PageProps } from '@/types';
import { Head, router, useForm, Link } from '@inertiajs/react';
import { useState, useEffect } from 'react';
import { toast } from 'sonner';

import ConfirmDeleteDialog from '@/components/ConfirmDeleteDialog';
import { MarketingCampaignFormDialog } from '@/components/marketing/MarketingCampaignFormDialog';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuSeparator, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { Input } from '@/components/ui/input';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import {
    Calendar,
    Edit,
    Eye,
    Megaphone,
    MoreHorizontal,
    Plus,
    Search,
    Send,
    Trash2,
    Users,
    XCircle,
    Clock,
    CheckCircle2,
    AlertCircle,
    ChevronLeft,
    ChevronRight,
} from 'lucide-react';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Marketing', href: '/marketing/campaigns' },
    { title: 'Campanhas', href: '/marketing/campaigns' },
];

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
    updated_at: string;
    media_type: 'image' | 'video' | 'document' | 'audio' | null;
    media_url: string | null;
    media_filename: string | null;
    created_by?: {
        id: number;
        name: string;
    };
}

interface CampaignsPageProps extends PageProps {
    campaigns: {
        data: Campaign[];
        total: number;
        current_page: number;
        per_page: number;
        last_page: number;
    };
    filters: {
        search?: string;
        order?: string;
        page: number;
        per_page: number;
    };
}

export default function MarketingCampaignsIndex({ campaigns, filters }: CampaignsPageProps) {
    const [openDeleteModal, setOpenDeleteModal] = useState(false);
    const [openFormModal, setOpenFormModal] = useState(false);
    const [selectedCampaignId, setSelectedCampaignId] = useState<number | null>(null);
    const [editingCampaign, setEditingCampaign] = useState<Campaign | null>(null);
    const [formMode, setFormMode] = useState<'create' | 'edit'>('create');

    const { data, setData } = useForm({
        search: filters.search || '',
    });

    // Debounce automático para busca
    useEffect(() => {
        const timer = setTimeout(() => {
            router.get(
                '/marketing/campaigns',
                {
                    search: data.search,
                    page: 1 // Resetar para primeira página ao buscar
                },
                {
                    preserveScroll: true,
                    preserveState: true,
                    only: ['campaigns'],
                },
            );
        }, 500); // Aguarda 500ms após parar de digitar

        return () => clearTimeout(timer);
    }, [data.search]);

    function handlePageChange(page: number) {
        router.get(
            '/marketing/campaigns',
            {
                search: data.search,
                page
            },
            {
                preserveScroll: true,
                preserveState: true,
                only: ['campaigns'],
            },
        );
    }

    function handleDelete(campaignId: number) {
        router.delete(`/marketing/campaigns/${campaignId}`, {
            onSuccess: () => {
                toast.success('Campanha excluída com sucesso!');
            },
            onError: () => {
                toast.error('Erro ao excluir campanha');
            },
        });
    }

    function handleSendNow(campaignId: number) {
        router.post(
            `/marketing/campaigns/${campaignId}/send-now`,
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

    function handleCancel(campaignId: number) {
        router.post(
            `/marketing/campaigns/${campaignId}/cancel`,
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
            draft: { color: 'bg-gray-100 text-gray-800 border-gray-200', label: 'Rascunho', icon: Edit },
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

    const getTargetAudienceLabel = (audience: Campaign['target_audience']) => {
        const labels = {
            all: 'Todos os Pacientes',
            with_appointments: 'Com Agendamentos',
            without_appointments: 'Sem Agendamentos',
            custom: 'Personalizado',
        };

        return labels[audience] || audience;
    };

    const getSuccessRate = (campaign: Campaign) => {
        if (campaign.total_recipients === 0) return 0;
        return Math.round((campaign.sent_count / campaign.total_recipients) * 100);
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Campanhas de Marketing" />

            <div className="space-y-6 p-6">
                {/* Header */}
                <div className="flex flex-col justify-between gap-4 md:flex-row md:items-center">
                    <div>
                        <h1 className="text-2xl font-bold tracking-tight">Campanhas de Marketing</h1>
                        <p className="text-muted-foreground">Gerencie suas campanhas de WhatsApp para pacientes</p>
                    </div>
                    <Button
                        className="gap-2"
                        onClick={() => {
                            setEditingCampaign(null);
                            setFormMode('create');
                            setOpenFormModal(true);
                        }}
                    >
                        <Plus className="h-4 w-4" />
                        Nova Campanha
                    </Button>
                </div>

                {/* Search */}
                <Card>
                    <CardContent className="pt-6">
                        <div className="relative flex-1">
                            <Search className="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                            <Input
                                placeholder="Buscar campanhas... (busca automática)"
                                value={data.search}
                                onChange={(e) => setData('search', e.target.value)}
                                className="pl-10"
                            />
                        </div>
                    </CardContent>
                </Card>

                {/* Table */}
                <Card>
                    <CardHeader>
                        <CardTitle className="flex items-center justify-between">
                            <div className="flex items-center gap-2">
                                <Megaphone className="h-5 w-5" />
                                <span>Campanhas</span>
                            </div>
                            <span className="text-sm font-normal text-muted-foreground">{campaigns.total} campanha(s) encontrada(s)</span>
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div className="rounded-md border">
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>Nome</TableHead>
                                        <TableHead>Público</TableHead>
                                        <TableHead>Destinatários</TableHead>
                                        <TableHead>Enviados</TableHead>
                                        <TableHead>Taxa de Sucesso</TableHead>
                                        <TableHead>Agendamento</TableHead>
                                        <TableHead>Status</TableHead>
                                        <TableHead className="w-16">Ações</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {campaigns.data.map((campaign) => (
                                        <TableRow key={campaign.id} className="group hover:bg-muted/50">
                                            <TableCell>
                                                <div>
                                                    <p className="font-medium">{campaign.name}</p>
                                                    <p className="text-xs text-muted-foreground line-clamp-1">{campaign.message}</p>
                                                </div>
                                            </TableCell>
                                            <TableCell>
                                                <div className="flex items-center gap-2">
                                                    <Users className="h-4 w-4 text-muted-foreground" />
                                                    <span className="text-sm">{getTargetAudienceLabel(campaign.target_audience)}</span>
                                                </div>
                                            </TableCell>
                                            <TableCell>
                                                <div className="text-center">
                                                    <p className="font-medium">{campaign.total_recipients}</p>
                                                </div>
                                            </TableCell>
                                            <TableCell>
                                                <div className="space-y-1">
                                                    <p className="text-sm">
                                                        <span className="font-medium text-green-600">{campaign.sent_count}</span> enviado(s)
                                                    </p>
                                                    {campaign.failed_count > 0 && (
                                                        <p className="text-sm">
                                                            <span className="font-medium text-red-600">{campaign.failed_count}</span> falhou(ram)
                                                        </p>
                                                    )}
                                                </div>
                                            </TableCell>
                                            <TableCell>
                                                <div className="text-center">
                                                    <p className="font-medium">{getSuccessRate(campaign)}%</p>
                                                </div>
                                            </TableCell>
                                            <TableCell>
                                                {campaign.scheduled_at ? (
                                                    <div className="flex items-center gap-2">
                                                        <Calendar className="h-4 w-4 text-muted-foreground" />
                                                        <div className="text-sm">
                                                            <p>{new Date(campaign.scheduled_at).toLocaleDateString('pt-BR')}</p>
                                                            <p className="text-muted-foreground">{new Date(campaign.scheduled_at).toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' })}</p>
                                                        </div>
                                                    </div>
                                                ) : (
                                                    <span className="text-sm text-muted-foreground">-</span>
                                                )}
                                            </TableCell>
                                            <TableCell>{getStatusBadge(campaign.status)}</TableCell>
                                            <TableCell>
                                                <DropdownMenu modal={false}>
                                                    <DropdownMenuTrigger asChild>
                                                        <Button variant="ghost" size="sm" className="h-8 w-8 p-0">
                                                            <MoreHorizontal className="h-4 w-4" />
                                                        </Button>
                                                    </DropdownMenuTrigger>
                                                    <DropdownMenuContent align="end">
                                                        <DropdownMenuItem asChild>
                                                            <Link href={`/marketing/campaigns/${campaign.id}`}>
                                                                <Eye className="mr-2 h-4 w-4" />
                                                                Visualizar
                                                            </Link>
                                                        </DropdownMenuItem>

                                                        {(campaign.status === 'draft' || campaign.status === 'scheduled') && (
                                                            <DropdownMenuItem
                                                                onClick={(e) => {
                                                                    e.preventDefault();
                                                                    e.stopPropagation();
                                                                    setEditingCampaign(campaign);
                                                                    setFormMode('edit');
                                                                    setOpenFormModal(true);
                                                                }}
                                                            >
                                                                <Edit className="mr-2 h-4 w-4" />
                                                                Editar
                                                            </DropdownMenuItem>
                                                        )}

                                                        {(campaign.status === 'draft' || campaign.status === 'scheduled') && (
                                                            <>
                                                                <DropdownMenuItem
                                                                    onClick={(e) => {
                                                                        e.preventDefault();
                                                                        e.stopPropagation();
                                                                        handleSendNow(campaign.id);
                                                                    }}
                                                                >
                                                                    <Send className="mr-2 h-4 w-4" />
                                                                    Enviar Agora
                                                                </DropdownMenuItem>
                                                            </>
                                                        )}

                                                        {(campaign.status === 'scheduled' || campaign.status === 'sending') && (
                                                            <>
                                                                <DropdownMenuSeparator />
                                                                <DropdownMenuItem
                                                                    onClick={(e) => {
                                                                        e.preventDefault();
                                                                        e.stopPropagation();
                                                                        handleCancel(campaign.id);
                                                                    }}
                                                                    className="text-orange-600"
                                                                >
                                                                    <XCircle className="mr-2 h-4 w-4" />
                                                                    Cancelar
                                                                </DropdownMenuItem>
                                                            </>
                                                        )}

                                                        {(campaign.status === 'draft' || campaign.status === 'cancelled') && (
                                                            <>
                                                                <DropdownMenuSeparator />
                                                                <DropdownMenuItem
                                                                    onClick={(e) => {
                                                                        e.preventDefault();
                                                                        e.stopPropagation();
                                                                        setSelectedCampaignId(campaign.id);
                                                                        setOpenDeleteModal(true);
                                                                    }}
                                                                    className="text-red-600"
                                                                >
                                                                    <Trash2 className="mr-2 h-4 w-4" />
                                                                    Excluir
                                                                </DropdownMenuItem>
                                                            </>
                                                        )}
                                                    </DropdownMenuContent>
                                                </DropdownMenu>
                                            </TableCell>
                                        </TableRow>
                                    ))}
                                </TableBody>
                            </Table>
                        </div>

                        {campaigns.data.length === 0 && (
                            <div className="py-12 text-center">
                                <Megaphone className="mx-auto h-12 w-12 text-muted-foreground" />
                                <h3 className="mt-4 text-lg font-medium">Nenhuma campanha encontrada</h3>
                                <p className="text-muted-foreground">Comece criando sua primeira campanha de marketing.</p>
                                <Button className="mt-4 gap-2" onClick={() => {
                                    setEditingCampaign(null);
                                    setFormMode('create');
                                    setOpenFormModal(true);
                                }}>
                                    <Plus className="h-4 w-4" />
                                    Nova Campanha
                                </Button>
                            </div>
                        )}

                        {/* Pagination */}
                        {campaigns.last_page > 1 && (
                            <div className="flex items-center justify-between px-2 py-4 border-t">
                                <div className="text-sm text-muted-foreground">
                                    Mostrando {(campaigns.current_page - 1) * campaigns.per_page + 1} a{' '}
                                    {Math.min(campaigns.current_page * campaigns.per_page, campaigns.total)} de {campaigns.total}{' '}
                                    campanhas
                                </div>
                                <div className="flex items-center gap-2">
                                    <Button
                                        variant="outline"
                                        size="sm"
                                        onClick={() => handlePageChange(campaigns.current_page - 1)}
                                        disabled={campaigns.current_page === 1}
                                    >
                                        <ChevronLeft className="h-4 w-4" />
                                        Anterior
                                    </Button>
                                    <div className="text-sm text-muted-foreground">
                                        Página {campaigns.current_page} de {campaigns.last_page}
                                    </div>
                                    <Button
                                        variant="outline"
                                        size="sm"
                                        onClick={() => handlePageChange(campaigns.current_page + 1)}
                                        disabled={campaigns.current_page === campaigns.last_page}
                                    >
                                        Próxima
                                        <ChevronRight className="h-4 w-4" />
                                    </Button>
                                </div>
                            </div>
                        )}
                    </CardContent>
                </Card>
            </div>

            {/* Campaign Form Dialog */}
            <MarketingCampaignFormDialog
                open={openFormModal}
                onClose={() => {
                    setOpenFormModal(false);
                    setEditingCampaign(null);
                }}
                campaign={editingCampaign}
                mode={formMode}
            />

            {/* Delete Dialog */}
            <ConfirmDeleteDialog
                confirmText="Tem certeza que deseja excluir esta campanha?"
                description="Esta ação não pode ser desfeita. A campanha será removida permanentemente."
                open={openDeleteModal}
                onClose={() => setOpenDeleteModal(false)}
                onConfirm={() => {
                    if (selectedCampaignId !== null) {
                        handleDelete(selectedCampaignId);
                        setOpenDeleteModal(false);
                    }
                }}
            />
        </AppLayout>
    );
}
