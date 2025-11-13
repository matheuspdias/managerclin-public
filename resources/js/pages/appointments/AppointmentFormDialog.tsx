import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Appointment } from '@/types/appointment';
import { Patient } from '@/types/patient';
import { Room } from '@/types/room';
import { Service } from '@/types/service';
import { User } from '@/types/user';
import { router, usePage } from '@inertiajs/react';
import { Calendar, Clock, FileText, MapPin, Stethoscope, User as UserIcon, Loader2, AlertCircle } from 'lucide-react';
import { Dispatch, SetStateAction, useEffect, useRef, useState } from 'react';
import { toast } from 'sonner';
import { appointmentStatusColors, appointmentIconSizes } from '@/config/appointment-design-system';

type AppointmentFormDialogProps = {
    open: boolean;
    appointment: Appointment | null;
    setAppointment: Dispatch<SetStateAction<Appointment | null>>;
    services: Service[];
    users: User[];
    customers: Patient[];
    rooms: Room[];
    onClose: () => void;
    mode: 'edit' | 'create';
    currentUser: {
        id: number;
        is_admin: boolean;
    };
};

export function AppointmentFormDialog({
    open,
    appointment,
    services,
    users,
    customers,
    rooms,
    setAppointment,
    onClose,
    mode,
    currentUser,
}: AppointmentFormDialogProps) {
    const { errors } = usePage().props as any;
    const previouslyFocusedElement = useRef<HTMLElement | null>(null);
    const [isSubmitting, setIsSubmitting] = useState(false);
    const [isCheckingConflicts, setIsCheckingConflicts] = useState(false);
    const [conflicts, setConflicts] = useState<any[]>([]);
    const conflictCheckTimeout = useRef<NodeJS.Timeout | null>(null);
    const [isLoadingSlots, setIsLoadingSlots] = useState(false);
    const [availableSlots, setAvailableSlots] = useState<any[]>([]);
    const [showAvailableSlots, setShowAvailableSlots] = useState(false);
    const [showTimeInputs, setShowTimeInputs] = useState(false);
    const [originalTime, setOriginalTime] = useState<{ start: string; end: string } | null>(null);

    // Store the previously focused element when modal opens
    useEffect(() => {
        if (open) {
            previouslyFocusedElement.current = document.activeElement as HTMLElement;

            // Se for modo edição e tiver horários, guardar os originais
            if (mode === 'edit' && appointment?.start_time && appointment?.end_time) {
                setOriginalTime({
                    start: appointment.start_time,
                    end: appointment.end_time,
                });
            } else {
                setOriginalTime(null);
            }

            // Resetar estados ao abrir o modal
            setShowTimeInputs(false);
            setShowAvailableSlots(false);
        } else if (previouslyFocusedElement.current) {
            // Restore focus when modal closes
            setTimeout(() => {
                previouslyFocusedElement.current?.focus();
            }, 100);
        }
    }, [open, mode, appointment]);

    // Capturar erros do backend e exibir como toast
    useEffect(() => {
        if (errors?.error) {
            toast.error(errors.error);
        }
    }, [errors]);

    // Função para verificar conflitos usando fetch com Accept: application/json
    const checkConflicts = async () => {
        // Validar se todos os campos necessários estão preenchidos
        if (!appointment?.date || !appointment?.start_time || !appointment?.end_time ||
            !appointment?.id_user || !appointment?.id_room) {
            setConflicts([]);
            return;
        }

        // Validar formato dos horários (HH:MM)
        const timeRegex = /^([0-1][0-9]|2[0-3]):[0-5][0-9]$/;
        if (!timeRegex.test(appointment.start_time) || !timeRegex.test(appointment.end_time)) {
            setConflicts([]);
            return;
        }

        setIsCheckingConflicts(true);
        try {
            // Montar o payload apenas com campos válidos
            const payload: any = {
                date: appointment.date,
                start_time: appointment.start_time,
                end_time: appointment.end_time,
                user_id: appointment.id_user,
                room_id: appointment.id_room,
            };

            // Só incluir appointment_id se existir (modo edição)
            if (appointment.id) {
                payload.appointment_id = appointment.id;
            }

            const response = await fetch('/appointments/check-conflicts', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                },
                body: JSON.stringify(payload),
            });

            const data = await response.json();

            if (data.has_conflicts) {
                setConflicts(data.conflicts);
            } else {
                setConflicts([]);
            }
        } catch (error) {
            console.error('Erro ao verificar conflitos:', error);
        } finally {
            setIsCheckingConflicts(false);
        }
    };

    // Função para buscar horários disponíveis
    const loadAvailableSlots = async () => {
        if (!appointment?.date || !appointment?.id_user) {
            return;
        }

        setIsLoadingSlots(true);
        try {
            const params = new URLSearchParams({
                date: appointment.date,
                user_id: appointment.id_user.toString(),
                duration: '60', // 60 minutos por padrão
            });

            const response = await fetch(`/appointments/available-slots?${params}`, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                },
            });

            const data = await response.json();
            setAvailableSlots(data.available_slots || []);
        } catch (error) {
            console.error('Erro ao buscar horários disponíveis:', error);
            setAvailableSlots([]);
        } finally {
            setIsLoadingSlots(false);
        }
    };

    // Selecionar um horário disponível
    const selectSlot = (slot: any) => {
        setAppointment({
            ...appointment!,
            start_time: slot.start_time,
            end_time: slot.end_time,
        });
        setShowTimeInputs(true); // Mostrar os inputs após selecionar
        toast.success('Horário selecionado!');
    };

    // Verificar conflitos quando os campos relevantes mudarem (com debounce)
    useEffect(() => {
        if (!open) return;

        // Limpar timeout anterior
        if (conflictCheckTimeout.current) {
            clearTimeout(conflictCheckTimeout.current);
        }

        // Aguardar 500ms após a última alteração para verificar
        conflictCheckTimeout.current = setTimeout(() => {
            checkConflicts();
        }, 500);

        return () => {
            if (conflictCheckTimeout.current) {
                clearTimeout(conflictCheckTimeout.current);
            }
        };
    }, [appointment?.date, appointment?.start_time, appointment?.end_time, appointment?.id_user, appointment?.id_room, open]);

    if (!appointment) return null;

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        setIsSubmitting(true);

        const method = mode === 'edit' ? 'patch' : 'post';
        const url = mode === 'edit' ? `/appointments/${appointment.id}` : '/appointments';

        router[method](url, appointment, {
            onSuccess: () => {
                toast.success(mode === 'edit' ? 'Agendamento atualizado com sucesso!' : 'Agendamento criado com sucesso!');
                setIsSubmitting(false);
                onClose();
            },
            onError: () => {
                setIsSubmitting(false);
                // Não fechar o modal em caso de erro para o usuário ver a mensagem
            },
        });
    };

    return (
        <Dialog open={open} onOpenChange={(isOpen) => {
            if (!isOpen) {
                // Clear any potential overlay issues
                setTimeout(() => {
                    const overlays = document.querySelectorAll('[data-slot="dialog-overlay"]');
                    overlays.forEach(overlay => {
                        if (overlay.parentNode) {
                            overlay.parentNode.removeChild(overlay);
                        }
                    });
                }, 200);
                onClose();
            }
        }}>
            <DialogContent className="sm:max-w-[600px]">
                <DialogHeader>
                    <DialogTitle className="flex items-center gap-2 text-xl">
                        <Calendar className="h-5 w-5" />
                        {mode === 'edit' ? 'Editar Agendamento' : 'Novo Agendamento'}
                    </DialogTitle>
                    <DialogDescription>
                        {mode === 'edit' ? 'Atualize os detalhes do agendamento' : 'Preencha os dados para criar um novo agendamento'}
                    </DialogDescription>
                </DialogHeader>

                {/* Alerta de Conflitos */}
                {conflicts.length > 0 && (
                    <div className="bg-red-50 dark:bg-red-950 border border-red-200 dark:border-red-800 rounded-lg p-4">
                        <div className="flex items-start gap-3">
                            <AlertCircle className="h-5 w-5 text-red-600 dark:text-red-400 mt-0.5 flex-shrink-0" />
                            <div className="flex-1">
                                <h4 className="text-sm font-semibold text-red-800 dark:text-red-300 mb-2">
                                    Conflito de Horário Detectado
                                </h4>
                                <div className="space-y-2">
                                    {conflicts.map((conflict, index) => (
                                        <div key={index} className="text-sm text-red-700 dark:text-red-400">
                                            <p className="font-medium">Profissional já possui agendamento:</p>
                                            <p className="ml-2">
                                                {conflict.customer_name} - {conflict.start_time.slice(0, 5)} às {conflict.end_time.slice(0, 5)}
                                            </p>
                                        </div>
                                    ))}
                                </div>
                            </div>
                        </div>
                    </div>
                )}

                {/* Indicador de Verificação */}
                {isCheckingConflicts && (
                    <div className="flex items-center gap-2 text-sm text-muted-foreground">
                        <Loader2 className="h-4 w-4 animate-spin" />
                        <span>Verificando disponibilidade...</span>
                    </div>
                )}

                <form onSubmit={handleSubmit} className="space-y-6 py-4">
                    {/* Seção: Informações do Atendimento */}
                    <div className="space-y-4">
                        <div className="flex items-center gap-2 pb-2">
                            <Stethoscope className={`${appointmentIconSizes.md} text-primary`} />
                            <h3 className="text-base font-semibold">Informações do Atendimento</h3>
                        </div>

                        <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                            {/* Médico */}
                            <div className="space-y-2">
                                <Label htmlFor="user" className="flex items-center gap-2">
                                    <Stethoscope className={appointmentIconSizes.sm} />
                                    Profissional *
                                </Label>
                            {currentUser.is_admin ? (
                                <Select
                                    value={appointment.id_user?.toString()}
                                    onValueChange={(value) => setAppointment({ ...appointment, id_user: Number(value) })}
                                    disabled={isSubmitting}
                                >
                                    <SelectTrigger disabled={isSubmitting}>
                                        <SelectValue placeholder="Selecione um profissional" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {users.map((user) => (
                                            <SelectItem key={user.id} value={user.id.toString()}>
                                                {user.name}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            ) : (
                                <Input
                                    value={users.find(u => u.id === currentUser.id)?.name || 'Usuário não encontrado'}
                                    disabled
                                    className="bg-muted"
                                />
                            )}
                        </div>

                            {/* Paciente */}
                            <div className="space-y-2">
                                <Label htmlFor="customer" className="flex items-center gap-2">
                                    <UserIcon className={appointmentIconSizes.sm} />
                                    Paciente *
                                </Label>
                                <Select
                                    value={appointment.id_customer?.toString()}
                                    onValueChange={(value) => setAppointment({ ...appointment, id_customer: Number(value) })}
                                    disabled={isSubmitting}
                                >
                                    <SelectTrigger disabled={isSubmitting}>
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
                            </div>

                            {/* Serviço */}
                            <div className="space-y-2">
                                <Label htmlFor="service" className="flex items-center gap-2">
                                    <Stethoscope className={appointmentIconSizes.sm} />
                                    Serviço *
                                </Label>
                                <Select
                                    value={appointment.id_service?.toString()}
                                    onValueChange={(value) => setAppointment({ ...appointment, id_service: Number(value) })}
                                    disabled={isSubmitting}
                                >
                                    <SelectTrigger disabled={isSubmitting}>
                                        <SelectValue placeholder="Selecione um serviço" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {services.map((service) => (
                                            <SelectItem key={service.id} value={service.id.toString()}>
                                                {service.name}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </div>

                            {/* Sala */}
                            <div className="space-y-2">
                                <Label htmlFor="room" className="flex items-center gap-2">
                                    <MapPin className={appointmentIconSizes.sm} />
                                    Consultório *
                                </Label>
                                <Select
                                    value={appointment.id_room?.toString()}
                                    onValueChange={(value) => setAppointment({ ...appointment, id_room: Number(value) })}
                                    disabled={isSubmitting}
                                >
                                    <SelectTrigger disabled={isSubmitting}>
                                        <SelectValue placeholder="Selecione um consultório" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {rooms.map((room) => (
                                            <SelectItem key={room.id} value={room.id.toString()}>
                                                {room.name}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </div>

                            {/* Status */}
                            <div className="space-y-2">
                                <Label htmlFor="status" className="flex items-center gap-2">
                                    <AlertCircle className={appointmentIconSizes.sm} />
                                    Status *
                                </Label>
                                <Select
                                    value={appointment.status}
                                    onValueChange={(value) =>
                                        setAppointment({ ...appointment, status: value as 'SCHEDULED' | 'IN_PROGRESS' | 'COMPLETED' | 'CANCELLED' })
                                    }
                                    disabled={isSubmitting}
                                >
                                    <SelectTrigger disabled={isSubmitting}>
                                        <SelectValue placeholder="Selecione um status" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="SCHEDULED">Agendado</SelectItem>
                                        <SelectItem value="IN_PROGRESS">Em Andamento</SelectItem>
                                        <SelectItem value="COMPLETED">Concluído</SelectItem>
                                        <SelectItem value="CANCELLED">Cancelado</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                        </div>
                    </div>

                    {/* Seção: Data e Horário */}
                    <div className="space-y-4">
                        <div className="flex items-center gap-2 pb-2 border-t pt-4">
                            <Clock className={`${appointmentIconSizes.md} text-primary`} />
                            <h3 className="text-base font-semibold">Data e Horário</h3>
                        </div>

                        <div className="space-y-2">
                            <Label htmlFor="date" className="flex items-center gap-2">
                                <Calendar className={appointmentIconSizes.sm} />
                                Data *
                            </Label>
                            <Input
                                type="date"
                                value={appointment.date}
                                onChange={(e) => setAppointment({ ...appointment, date: e.target.value })}
                                required
                                disabled={isSubmitting}
                            />
                        </div>
                    </div>

                    {/* Botão para mostrar horários disponíveis */}
                    {appointment.date && appointment.id_user && (
                        <div className="flex items-center gap-2">
                            <Button
                                type="button"
                                variant="outline"
                                size="sm"
                                onClick={() => {
                                    setShowAvailableSlots(!showAvailableSlots);
                                    if (!showAvailableSlots) {
                                        loadAvailableSlots();
                                    }
                                }}
                                disabled={isSubmitting}
                                className="w-full"
                            >
                                <Clock className="mr-2 h-4 w-4" />
                                {showAvailableSlots ? 'Ocultar' : 'Ver'} Horários Disponíveis
                            </Button>
                        </div>
                    )}

                    {/* Grid de horários disponíveis */}
                    {showAvailableSlots && (
                        <div className="space-y-2">
                            <Label className="text-sm font-medium">Horários Disponíveis</Label>
                            {isLoadingSlots ? (
                                <div className="flex items-center justify-center py-8">
                                    <Loader2 className="h-6 w-6 animate-spin text-muted-foreground" />
                                </div>
                            ) : availableSlots.length > 0 ? (
                                <div className="grid grid-cols-3 gap-2 max-h-48 overflow-y-auto p-2 border rounded-md">
                                    {availableSlots.map((slot: { start_time: string; end_time: string }, index: number) => (
                                        <Button
                                            key={index}
                                            type="button"
                                            variant="outline"
                                            size="sm"
                                            onClick={() => selectSlot(slot)}
                                            className="text-xs hover:bg-green-50 hover:border-green-300 dark:hover:bg-green-950"
                                        >
                                            {slot.start_time} - {slot.end_time}
                                        </Button>
                                    ))}
                                </div>
                            ) : (
                                <p className="text-sm text-muted-foreground text-center py-4 border rounded-md">
                                    Nenhum horário disponível para esta data
                                </p>
                            )}
                        </div>
                    )}

                    {/* Indicador de horário atual (modo edição) */}
                    {originalTime && !showTimeInputs && (
                        <div className="p-3 border rounded-md bg-blue-50 dark:bg-blue-950 border-blue-200 dark:border-blue-800">
                            <div className="flex items-center gap-2">
                                <Clock className="h-4 w-4 text-blue-600 dark:text-blue-400" />
                                <div className="flex-1">
                                    <p className="text-xs text-blue-600 dark:text-blue-400 font-medium">Horário Atual</p>
                                    <p className="text-sm font-semibold text-blue-700 dark:text-blue-300">
                                        {originalTime.start} às {originalTime.end}
                                    </p>
                                </div>
                            </div>
                        </div>
                    )}

                    {/* Inputs de horário - só aparecem após selecionar um slot */}
                    {showTimeInputs && (
                        <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                            {/* Hora de Início */}
                            <div className="space-y-2">
                                <Label htmlFor="start_time" className="flex items-center gap-2">
                                    <Clock className="h-4 w-4" />
                                    Hora de Início *
                                </Label>
                                <Input
                                    type="time"
                                    value={appointment.start_time}
                                    onChange={(e) => setAppointment({ ...appointment, start_time: e.target.value })}
                                    required
                                    disabled={isSubmitting}
                                />
                            </div>

                            {/* Hora de Término */}
                            <div className="space-y-2">
                                <Label htmlFor="end_time" className="flex items-center gap-2">
                                    <Clock className="h-4 w-4" />
                                    Hora de Término *
                                </Label>
                                <Input
                                    type="time"
                                    value={appointment.end_time}
                                    onChange={(e) => setAppointment({ ...appointment, end_time: e.target.value })}
                                    required
                                    disabled={isSubmitting}
                                />
                            </div>
                        </div>
                    )}

                    {/* Notas */}
                    <div className="space-y-2">
                        <Label htmlFor="notes" className="flex items-center gap-2">
                            <FileText className="h-4 w-4" />
                            Observações
                        </Label>
                        <textarea
                            value={appointment.notes || ''}
                            onChange={(e) => setAppointment({ ...appointment, notes: e.target.value })}
                            className="w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-50"
                            rows={3}
                            placeholder="Observações importantes sobre o agendamento"
                            disabled={isSubmitting}
                        />
                    </div>

                    <DialogFooter>
                        <Button
                            type="button"
                            variant="outline"
                            onClick={(e) => {
                                e.preventDefault();
                                e.stopPropagation();
                                // Force focus restoration
                                if (previouslyFocusedElement.current) {
                                    previouslyFocusedElement.current.focus();
                                }
                                onClose();
                            }}
                            disabled={isSubmitting}
                        >
                            Cancelar
                        </Button>
                        <Button type="submit" disabled={isSubmitting || conflicts.length > 0}>
                            {isSubmitting ? (
                                <>
                                    <Loader2 className="mr-2 h-4 w-4 animate-spin" />
                                    {mode === 'edit' ? 'Atualizando...' : 'Agendando...'}
                                </>
                            ) : conflicts.length > 0 ? (
                                <>
                                    <AlertCircle className="mr-2 h-4 w-4" />
                                    Conflito Detectado
                                </>
                            ) : (
                                mode === 'edit' ? 'Atualizar' : 'Agendar'
                            )}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}
