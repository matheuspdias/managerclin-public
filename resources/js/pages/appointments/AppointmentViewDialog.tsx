import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Separator } from '@/components/ui/separator';
import {
    Tooltip,
    TooltipContent,
    TooltipProvider,
    TooltipTrigger,
} from '@/components/ui/tooltip';
import { Appointment } from '@/types/appointment';
import { router } from '@inertiajs/react';
import { format, parseISO } from 'date-fns';
import { ptBR } from 'date-fns/locale';
import {
    Calendar,
    CheckCircle2,
    Clock,
    Edit,
    FileText,
    MapPin,
    Plus,
    PlayCircle,
    User,
    Video,
    XCircle,
    Stethoscope,
    DollarSign,
    Send,
    Info,
} from 'lucide-react';
import { useState } from 'react';
import { toast } from 'sonner';
import axios from 'axios';

interface AppointmentViewDialogProps {
    open: boolean;
    onClose: () => void;
    appointment: Appointment | null;
    onEdit: (appointment: Appointment) => void;
    onStatusUpdate: (id: number, status: string, callback?: () => void) => void;
}

export function AppointmentViewDialog({
    open,
    onClose,
    appointment,
    onEdit,
    onStatusUpdate,
}: AppointmentViewDialogProps) {
    const [isCreatingSession, setIsCreatingSession] = useState(false);
    const [isSendingNotification, setIsSendingNotification] = useState(false);
    const [currentSessionId, setCurrentSessionId] = useState<number | null>(null);

    if (!appointment) return null;

    const handleSendNotification = async () => {
        if (!currentSessionId) {
            toast.error('Nenhuma sessão ativa encontrada');
            return;
        }

        try {
            setIsSendingNotification(true);
            await axios.post(`/telemedicine/sessions/${currentSessionId}/notify`);
            toast.success('Notificação reenviada ao paciente via WhatsApp!', {
                duration: 4000
            });
            console.log('Notificação enviada para o paciente via WhatsApp');
        } catch (error: any) {
            console.error('Erro ao enviar notificação:', error);
            toast.error(error.response?.data?.message || 'Erro ao enviar notificação');
        } finally {
            setIsSendingNotification(false);
        }
    };

    const handleStartTelemedicine = async () => {
        console.log('🚀 handleStartTelemedicine iniciado');
        try {
            setIsCreatingSession(true);

            // Primeiro, verifica se já existe uma sessão para este agendamento
            console.log('🔍 Verificando se existe sessão para appointment_id:', appointment.id);
            const checkResponse = await axios.get(`/telemedicine/sessions/appointment/${appointment.id}`);

            if (checkResponse.data.success) {
                // Sessão já existe, abre diretamente
                console.log('✅ Sessão encontrada:', checkResponse.data.data);
                const session = checkResponse.data.data;
                setCurrentSessionId(session.session_id);

                // Calcular tempo decorrido se a sessão já foi iniciada
                let message = 'Reconectando à sala de telemedicina...';
                const isFirstAccess = session.status === 'WAITING' || !session.started_at;

                if (session.started_at) {
                    const startedAt = new Date(session.started_at);
                    const now = new Date();
                    const minutesElapsed = Math.floor((now.getTime() - startedAt.getTime()) / (1000 * 60));
                    const creditsUsed = session.credits_consumed || 1;

                    message = `Reconectando... (${minutesElapsed} min decorridos, ${creditsUsed} crédito${creditsUsed > 1 ? 's' : ''} consumido${creditsUsed > 1 ? 's' : ''})`;
                }

                window.open(session.join_url, '_blank');
                toast.success(message, { duration: 5000 });

                // Se for o primeiro acesso (status WAITING), enviar notificação automática
                if (isFirstAccess) {
                    console.log('📤 Primeira abertura da sessão - Enviando notificação para session_id:', session.session_id);
                    try {
                        const notifyResponse = await axios.post(`/telemedicine/sessions/${session.session_id}/notify`);
                        console.log('✅ Resposta da notificação:', notifyResponse.data);
                        toast.success('Notificação enviada ao paciente via WhatsApp!', {
                            duration: 4000
                        });
                    } catch (notifyError: any) {
                        console.error('❌ Erro ao notificar paciente:', notifyError);
                        console.error('Detalhes do erro:', notifyError.response?.data);
                        toast.warning('Não foi possível enviar a notificação ao paciente', {
                            duration: 4000
                        });
                    }
                }

                onClose();
                return;
            }
        } catch (error: any) {
            // Se não encontrar sessão (404), cria uma nova
            console.log('❌ Erro ao verificar sessão, status:', error.response?.status);
            if (error.response?.status === 404) {
                try {
                    // Cria nova sessão
                    console.log('📝 Criando nova sessão para appointment_id:', appointment.id);
                    const createResponse = await axios.post('/telemedicine/sessions', {
                        appointment_id: appointment.id,
                    });

                    console.log('✅ Sessão criada:', createResponse.data);

                    if (createResponse.data.success) {
                        const session = createResponse.data.data;
                        setCurrentSessionId(session.session_id);

                        window.open(session.join_url, '_blank');
                        toast.success('Sessão de telemedicina criada com sucesso!');

                        // Atualiza status do agendamento para IN_PROGRESS se ainda estiver SCHEDULED
                        if (appointment.status === 'SCHEDULED') {
                            onStatusUpdate(appointment.id, 'IN_PROGRESS');
                        }

                        // Enviar notificação automática para o paciente via WhatsApp
                        console.log('📤 Iniciando envio de notificação para session_id:', session.session_id);
                        try {
                            const notifyResponse = await axios.post(`/telemedicine/sessions/${session.session_id}/notify`);
                            console.log('✅ Resposta da notificação:', notifyResponse.data);
                            toast.success('Notificação enviada ao paciente via WhatsApp!', {
                                duration: 4000
                            });
                        } catch (notifyError: any) {
                            console.error('❌ Erro ao notificar paciente:', notifyError);
                            console.error('Detalhes do erro:', notifyError.response?.data);
                            // Não bloqueia o fluxo se a notificação falhar
                            toast.warning('Sessão criada, mas não foi possível enviar a notificação ao paciente', {
                                duration: 4000
                            });
                        } finally {
                            // Garante que o diálogo só fecha após tentar enviar a notificação
                            console.log('🚪 Fechando diálogo');
                            onClose();
                        }
                        return;
                    }
                } catch (createError: any) {
                    console.error('❌ Erro ao criar sessão:', createError);
                    toast.error(createError.response?.data?.message || 'Erro ao criar sessão de telemedicina');
                }
            } else {
                console.error('❌ Erro ao verificar sessão:', error);
                toast.error(error.response?.data?.message || 'Erro ao verificar sessão de telemedicina');
            }
        } finally {
            console.log('🏁 Finalizando handleStartTelemedicine');
            setIsCreatingSession(false);
        }
    };

    const statusConfig = {
        SCHEDULED: {
            label: 'Agendado',
            color: 'bg-blue-100 text-blue-800 border-blue-200',
            icon: Calendar,
        },
        IN_PROGRESS: {
            label: 'Em Andamento',
            color: 'bg-amber-100 text-amber-800 border-amber-200',
            icon: Clock,
        },
        COMPLETED: {
            label: 'Concluído',
            color: 'bg-green-100 text-green-800 border-green-200',
            icon: CheckCircle2,
        },
        CANCELLED: {
            label: 'Cancelado',
            color: 'bg-red-100 text-red-800 border-red-200',
            icon: XCircle,
        },
    };

    const currentStatus = statusConfig[appointment.status as keyof typeof statusConfig];
    const StatusIcon = currentStatus.icon;

    const appointmentDate = parseISO(appointment.date);
    const formattedDate = format(appointmentDate, "dd 'de' MMMM 'de' yyyy", { locale: ptBR });
    const formattedDay = format(appointmentDate, 'EEEE', { locale: ptBR });

    return (
        <Dialog open={open} onOpenChange={onClose}>
            <DialogContent className="max-w-2xl max-h-[90vh] overflow-y-auto">
                <DialogHeader>
                    <div className="flex items-start justify-between">
                        <div className="space-y-1">
                            <DialogTitle className="text-2xl">Detalhes do Agendamento</DialogTitle>
                            <DialogDescription>
                                Visualize e gerencie as informações do agendamento
                            </DialogDescription>
                        </div>
                        <Badge className={`${currentStatus.color} border flex items-center gap-1.5 px-3 py-1`}>
                            <StatusIcon className="h-3.5 w-3.5" />
                            {currentStatus.label}
                        </Badge>
                    </div>
                </DialogHeader>

                <div className="space-y-6 mt-4">
                    {/* Informações do Paciente */}
                    <div className="space-y-3">
                        <h3 className="font-semibold text-lg flex items-center gap-2">
                            <User className="h-5 w-5 text-blue-600" />
                            Paciente
                        </h3>
                        <div className="bg-muted/50 rounded-lg p-4">
                            <p className="font-medium text-lg">{appointment.customer.name}</p>
                            {appointment.customer.email && (
                                <p className="text-sm text-muted-foreground mt-1">
                                    {appointment.customer.email}
                                </p>
                            )}
                            {appointment.customer.phone && (
                                <p className="text-sm text-muted-foreground">
                                    {appointment.customer.phone}
                                </p>
                            )}
                        </div>
                    </div>

                    {/* Data e Horário */}
                    <div className="space-y-3">
                        <h3 className="font-semibold text-lg flex items-center gap-2">
                            <Calendar className="h-5 w-5 text-blue-600" />
                            Data e Horário
                        </h3>
                        <div className="bg-muted/50 rounded-lg p-4 space-y-2">
                            <div className="flex items-center justify-between">
                                <span className="text-muted-foreground">Data</span>
                                <span className="font-medium capitalize">
                                    {formattedDay}, {formattedDate}
                                </span>
                            </div>
                            <Separator />
                            <div className="flex items-center justify-between">
                                <span className="text-muted-foreground">Horário</span>
                                <span className="font-medium flex items-center gap-2">
                                    <Clock className="h-4 w-4" />
                                    {appointment.start_time.slice(0, 5)} - {appointment.end_time.slice(0, 5)}
                                </span>
                            </div>
                        </div>
                    </div>

                    {/* Serviço e Profissional */}
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                        {/* Serviço */}
                        {appointment.service && (
                            <div className="space-y-3">
                                <h3 className="font-semibold flex items-center gap-2">
                                    <Stethoscope className="h-4 w-4 text-blue-600" />
                                    Serviço
                                </h3>
                                <div className="bg-muted/50 rounded-lg p-3">
                                    <p className="font-medium">{appointment.service.name}</p>
                                    {appointment.service.duration && (
                                        <p className="text-sm text-muted-foreground mt-1">
                                            Duração: {appointment.service.duration} min
                                        </p>
                                    )}
                                    {appointment.service.price && (
                                        <p className="text-sm text-muted-foreground flex items-center gap-1">
                                            <DollarSign className="h-3 w-3" />
                                            R$ {appointment.service.price}
                                        </p>
                                    )}
                                </div>
                            </div>
                        )}

                        {/* Profissional */}
                        {appointment.user && (
                            <div className="space-y-3">
                                <h3 className="font-semibold flex items-center gap-2">
                                    <User className="h-4 w-4 text-blue-600" />
                                    Profissional
                                </h3>
                                <div className="bg-muted/50 rounded-lg p-3">
                                    <p className="font-medium">{appointment.user.name}</p>
                                    {appointment.user.email && (
                                        <p className="text-sm text-muted-foreground mt-1">
                                            {appointment.user.email}
                                        </p>
                                    )}
                                </div>
                            </div>
                        )}
                    </div>

                    {/* Sala */}
                    {appointment.room && (
                        <div className="space-y-3">
                            <h3 className="font-semibold flex items-center gap-2">
                                <MapPin className="h-4 w-4 text-blue-600" />
                                Sala
                            </h3>
                            <div className="bg-muted/50 rounded-lg p-3">
                                <p className="font-medium">{appointment.room.name}</p>
                                {appointment.room.description && (
                                    <p className="text-sm text-muted-foreground mt-1">
                                        {appointment.room.description}
                                    </p>
                                )}
                            </div>
                        </div>
                    )}

                    {/* Observações */}
                    {appointment.notes && (
                        <div className="space-y-3">
                            <h3 className="font-semibold flex items-center gap-2">
                                <FileText className="h-4 w-4 text-blue-600" />
                                Observações
                            </h3>
                            <div className="bg-muted/50 rounded-lg p-4">
                                <p className="text-sm whitespace-pre-wrap">{appointment.notes}</p>
                            </div>
                        </div>
                    )}

                    {/* Ações */}
                    <Separator />

                    <div className="space-y-4">
                        <h3 className="font-semibold text-lg">Ações</h3>

                        {/* Telemedicina */}
                        <div className="space-y-3">
                            {/* Título com ícone de informação */}
                            <div className="flex items-center gap-2 mb-2">
                                <h4 className="text-sm font-medium text-muted-foreground">Telemedicina</h4>
                                <TooltipProvider>
                                    <Tooltip>
                                        <TooltipTrigger asChild>
                                            <button className="inline-flex items-center justify-center rounded-full p-0.5 hover:bg-muted transition-colors">
                                                <Info className="h-4 w-4 text-muted-foreground" />
                                            </button>
                                        </TooltipTrigger>
                                        <TooltipContent side="right" className="max-w-sm p-4">
                                            <div className="space-y-3 text-sm">
                                                <div>
                                                    <p className="font-semibold mb-1">Como funciona:</p>
                                                    <ul className="space-y-1 text-xs text-muted-foreground">
                                                        <li>• Ao iniciar, uma sala de videochamada é criada</li>
                                                        <li>• O paciente recebe o link automaticamente via WhatsApp</li>
                                                        <li>• Consome 1 crédito inicial (30 minutos)</li>
                                                    </ul>
                                                </div>
                                                <div>
                                                    <p className="font-semibold mb-1">Durante a consulta:</p>
                                                    <ul className="space-y-1 text-xs text-muted-foreground">
                                                        <li>• Pode fechar e reconectar na mesma sala</li>
                                                        <li>• Créditos extras consumidos a cada 30 minutos</li>
                                                        <li>• Sistema monitora automaticamente a cada 5 min</li>
                                                    </ul>
                                                </div>
                                                <div>
                                                    <p className="font-semibold mb-1">Finalização:</p>
                                                    <ul className="space-y-1 text-xs text-muted-foreground">
                                                        <li>• Clique em "Finalizar Atendimento" quando concluir</li>
                                                        <li>• Sessão fecha automaticamente se créditos acabarem</li>
                                                        <li>• Você pode reenviar o link ao paciente a qualquer momento</li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </TooltipContent>
                                    </Tooltip>
                                </TooltipProvider>
                            </div>

                            <Button
                                variant="outline"
                                onClick={handleStartTelemedicine}
                                disabled={isCreatingSession || appointment.status === 'COMPLETED' || appointment.status === 'CANCELLED'}
                                className="w-full justify-start gap-2 h-12 border-2 border-purple-300 text-purple-700 hover:bg-purple-50 hover:text-purple-800 hover:border-purple-400 disabled:opacity-50"
                            >
                                <Video className="h-5 w-5" />
                                <div className="text-left">
                                    <p className="font-semibold">
                                        {isCreatingSession ? 'Iniciando...' : 'Iniciar Telemedicina'}
                                    </p>
                                    <p className="text-xs opacity-75">
                                        {appointment.status === 'COMPLETED' || appointment.status === 'CANCELLED'
                                            ? 'Disponível apenas para consultas ativas'
                                            : 'Abrir sala de videochamada'}
                                    </p>
                                </div>
                            </Button>

                            {/* Botão para reenviar notificação (aparece apenas se houver sessão ativa) */}
                            {currentSessionId && appointment.status !== 'COMPLETED' && appointment.status !== 'CANCELLED' && (
                                <Button
                                    variant="outline"
                                    onClick={handleSendNotification}
                                    disabled={isSendingNotification}
                                    className="w-full justify-start gap-2 border-green-300 text-green-700 hover:bg-green-50 hover:text-green-800 hover:border-green-400"
                                >
                                    <Send className="h-5 w-5" />
                                    <div className="text-left">
                                        <p className="font-semibold">
                                            {isSendingNotification ? 'Enviando...' : 'Notificar Paciente'}
                                        </p>
                                        <p className="text-xs opacity-75">
                                            Enviar link via WhatsApp
                                        </p>
                                    </div>
                                </Button>
                            )}
                        </div>

                        {/* Ação de Prontuário */}
                        <Button
                            variant="outline"
                            onClick={() => {
                                router.visit(`/medical-records/patient/${appointment.customer.id}`);
                                onClose();
                            }}
                            className="w-full justify-start gap-2"
                        >
                            <FileText className="h-4 w-4" />
                            Ver Prontuário
                        </Button>

                        {/* Ações de Status */}
                        <div className="grid grid-cols-1 md:grid-cols-2 gap-3">
                            {appointment.status === 'SCHEDULED' && (
                                <>
                                    <Button
                                        onClick={() => {
                                            onStatusUpdate(appointment.id, 'IN_PROGRESS', onClose);
                                        }}
                                        variant="default"
                                        className="justify-start gap-2 bg-amber-600 hover:bg-amber-700"
                                    >
                                        <PlayCircle className="h-4 w-4" />
                                        Iniciar Atendimento
                                    </Button>
                                    <Button
                                        onClick={() => {
                                            onStatusUpdate(appointment.id, 'COMPLETED', onClose);
                                        }}
                                        variant="outline"
                                        className="justify-start gap-2"
                                    >
                                        <CheckCircle2 className="h-4 w-4" />
                                        Marcar como Concluído
                                    </Button>
                                </>
                            )}

                            {appointment.status === 'IN_PROGRESS' && (
                                <Button
                                    onClick={() => {
                                        onStatusUpdate(appointment.id, 'COMPLETED', onClose);
                                    }}
                                    variant="default"
                                    className="justify-start gap-2 bg-green-600 hover:bg-green-700 md:col-span-2"
                                >
                                    <CheckCircle2 className="h-4 w-4" />
                                    Finalizar Atendimento
                                </Button>
                            )}

                            {(appointment.status === 'SCHEDULED' || appointment.status === 'IN_PROGRESS') && (
                                <Button
                                    onClick={() => {
                                        onStatusUpdate(appointment.id, 'CANCELLED', onClose);
                                    }}
                                    variant="outline"
                                    className="justify-start gap-2 text-red-600 hover:text-red-700 hover:bg-red-50 md:col-span-2"
                                >
                                    <XCircle className="h-4 w-4" />
                                    Cancelar Agendamento
                                </Button>
                            )}
                        </div>

                        {/* Botão de Editar */}
                        <Separator />
                        <Button
                            onClick={() => {
                                onEdit(appointment);
                                onClose();
                            }}
                            variant="outline"
                            className="w-full justify-center gap-2 border-blue-200 text-blue-700 hover:bg-blue-50 hover:text-blue-800"
                        >
                            <Edit className="h-4 w-4" />
                            Editar Agendamento
                        </Button>
                    </div>
                </div>
            </DialogContent>
        </Dialog>
    );
}
