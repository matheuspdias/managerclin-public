import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Switch } from '@/components/ui/switch';
import AppLayout from '@/layouts/app-layout';
import SettingsLayout from '@/layouts/settings/layout';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm, usePage } from '@inertiajs/react';
import { Calendar, Clock, Plus, Save, Trash2 } from 'lucide-react';
import { toast } from 'sonner';

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Horários', href: '/settings/timezone' }];

const daysOfWeek = [
    { id: 0, name: 'Domingo' },
    { id: 1, name: 'Segunda-feira' },
    { id: 2, name: 'Terça-feira' },
    { id: 3, name: 'Quarta-feira' },
    { id: 4, name: 'Quinta-feira' },
    { id: 5, name: 'Sexta-feira' },
    { id: 6, name: 'Sábado' },
];

type Schedule = {
    id?: number;
    day_of_week: number;
    start_time: string;
    end_time: string;
    is_work: boolean;
    created_at?: string;
};

export default function Timezone() {
    const { schedules } = usePage<{ schedules: Schedule[] }>().props;

    // Inicializar com os horários do banco
    const initialSchedules =
        schedules.length > 0
            ? schedules.map((s) => ({
                  id: s.id,
                  day_of_week: s.day_of_week,
                  start_time: s.start_time,
                  end_time: s.end_time,
                  is_work: s.is_work,
              }))
            : daysOfWeek.flatMap((day) => [
                  {
                      day_of_week: day.id,
                      start_time: '08:00:00',
                      end_time: '17:00:00',
                      is_work: false,
                  },
              ]);

    const { data, setData, patch, processing, errors } = useForm({
        schedules: initialSchedules,
    });

    // Função para mudar um campo de um registro
    const handleChange = (index: number, field: 'start_time' | 'end_time' | 'day_of_week' | 'is_work', value: string | number | boolean) => {
        const updated: Schedule[] = [...data.schedules];
        updated[index][field] = value as never;
        setData('schedules', updated);
    };

    // Função para adicionar um novo horário
    const addSlot = (day_of_week: number) => {
        const updated = [...data.schedules];
        updated.push({
            day_of_week,
            start_time: '08:00:00',
            end_time: '17:00:00',
            is_work: true,
        });
        setData('schedules', updated);
    };

    // Função para remover um horário pelo índice - COM VALIDAÇÃO
    const removeSlot = (index: number, day_of_week: number) => {
        // Contar quantos horários existem para este dia
        const daySlotsCount = data.schedules.filter((slot) => slot.day_of_week === day_of_week && slot.is_work).length;

        // Não permitir remover se for o único horário do dia
        if (daySlotsCount <= 1) {
            toast.error('Não é possível remover o único horário do dia');
            return;
        }

        const updated = [...data.schedules];
        updated.splice(index, 1);
        setData('schedules', updated);
    };

    // Submissão - filtrar apenas dias com is_work true
    function submit(e: React.FormEvent) {
        e.preventDefault();

        // VALIDAÇÃO FRONTEND: Verificar se há dias com is_work true mas sem horários válidos
        const daysWithErrors = data.schedules
            .filter((schedule) => schedule.is_work)
            .filter(
                (schedule) => !schedule.start_time || !schedule.end_time || schedule.start_time === '00:00:00' || schedule.end_time === '00:00:00',
            );

        if (daysWithErrors.length > 0) {
            toast.error('Existem dias ativos sem horários configurados. Configure os horários ou desative o dia.');
            return;
        }

        // Enviar apenas os horários que têm is_work true
        const schedulesToSubmit = data.schedules.filter((schedule) => schedule.is_work);

        patch(route('profile.updateTimezone'), {
            schedules: schedulesToSubmit,
        });
    }

    // Verificar se um dia tem horários configurados e está ativo
    const getDaySchedules = (dayId: number) => {
        return data.schedules.map((slot, index) => ({ slot, index })).filter(({ slot }) => slot.day_of_week === dayId && slot.is_work);
    };

    // Encontrar o registro principal do dia
    const getDayRecord = (dayId: number) => {
        return data.schedules.find((slot) => slot.day_of_week === dayId);
    };

    // Contar quantos horários existem para um dia
    const getDaySlotsCount = (dayId: number) => {
        return data.schedules.filter((slot) => slot.day_of_week === dayId && slot.is_work).length;
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Configurações de Horários" />
            <SettingsLayout>
                <div className="space-y-6">
                    <div className="flex items-center gap-3">
                        <div className="rounded-lg bg-primary/10 p-2">
                            <Clock className="h-6 w-6 text-primary" />
                        </div>
                        <div>
                            <h2 className="text-2xl font-bold tracking-tight">Horários de Atendimento</h2>
                            <p className="text-muted-foreground">Configure os horários de funcionamento da sua clínica</p>
                        </div>
                    </div>

                    <form onSubmit={submit} className="space-y-6">
                        <div className="grid gap-4">
                            {daysOfWeek.map((day) => {
                                const dayRecord = getDayRecord(day.id);
                                const daySlots = getDaySchedules(day.id);
                                const daySlotsCount = getDaySlotsCount(day.id);
                                const isWorking = dayRecord?.is_work || false;

                                return (
                                    <div key={day.id} className="rounded-lg border bg-card p-6">
                                        <div className="mb-4 flex items-center justify-between">
                                            <div className="flex items-center gap-3">
                                                <h3 className="flex items-center gap-2 text-lg font-semibold">
                                                    <Calendar className="h-5 w-5 text-muted-foreground" />
                                                    {day.name}
                                                </h3>
                                                <Switch
                                                    checked={isWorking}
                                                    onCheckedChange={(checked) => {
                                                        // Encontrar ou criar registro para este dia
                                                        const dayIndex = data.schedules.findIndex((s) => s.day_of_week === day.id);
                                                        if (dayIndex >= 0) {
                                                            handleChange(dayIndex, 'is_work', checked);
                                                        } else {
                                                            const updated = [...data.schedules];
                                                            updated.push({
                                                                day_of_week: day.id,
                                                                start_time: '08:00:00',
                                                                end_time: '17:00:00',
                                                                is_work: checked,
                                                            });
                                                            setData('schedules', updated);
                                                        }
                                                    }}
                                                />
                                            </div>
                                            <span className={`text-sm ${isWorking ? 'text-green-600' : 'text-muted-foreground'}`}>
                                                {isWorking ? 'Atendimento ativo' : 'Sem atendimento'}
                                            </span>
                                        </div>

                                        {isWorking && (
                                            <>
                                                <div className="space-y-4">
                                                    {daySlots.map(({ slot, index }) => (
                                                        <div
                                                            key={index}
                                                            className="grid grid-cols-1 items-end gap-4 rounded-lg bg-muted/50 p-4 md:grid-cols-4"
                                                        >
                                                            <div className="md:col-span-2">
                                                                <label className="mb-2 block flex items-center gap-2 text-sm font-medium">
                                                                    <Clock className="h-4 w-4" />
                                                                    Período de Atendimento
                                                                </label>
                                                                <div className="grid grid-cols-2 gap-3">
                                                                    <div>
                                                                        <Input
                                                                            type="time"
                                                                            value={slot.start_time.slice(0, 5)}
                                                                            onChange={(e) =>
                                                                                handleChange(index, 'start_time', e.target.value + ':00')
                                                                            }
                                                                            className="w-full"
                                                                        />
                                                                        <p className="mt-1 text-xs text-muted-foreground">Início</p>
                                                                    </div>
                                                                    <div>
                                                                        <Input
                                                                            type="time"
                                                                            value={slot.end_time.slice(0, 5)}
                                                                            onChange={(e) => handleChange(index, 'end_time', e.target.value + ':00')}
                                                                            className="w-full"
                                                                        />
                                                                        <p className="mt-1 text-xs text-muted-foreground">Término</p>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <div className="flex items-end gap-2 md:col-span-2">
                                                                <Button
                                                                    type="button"
                                                                    variant="outline"
                                                                    size="icon"
                                                                    onClick={() => removeSlot(index, day.id)}
                                                                    className="h-10 w-10 flex-shrink-0"
                                                                    title="Remover horário"
                                                                    disabled={daySlotsCount <= 1} // Desabilita se for o único horário
                                                                >
                                                                    <Trash2 className="h-4 w-4" />
                                                                </Button>
                                                                <div className="flex-1 text-xs text-muted-foreground">
                                                                    Das {slot.start_time.slice(0, 5)} às {slot.end_time.slice(0, 5)}
                                                                </div>
                                                            </div>
                                                        </div>
                                                    ))}
                                                </div>

                                                <Button type="button" onClick={() => addSlot(day.id)} variant="outline" className="mt-4 w-full gap-2">
                                                    <Plus className="h-4 w-4" />
                                                    Adicionar Outro Período
                                                </Button>
                                            </>
                                        )}
                                    </div>
                                );
                            })}
                        </div>

                        {errors.schedules && (
                            <div className="rounded-lg border border-destructive bg-destructive/10 p-4">
                                <p className="text-sm text-destructive">{errors.schedules}</p>
                            </div>
                        )}

                        <div className="flex justify-end border-t pt-4">
                            <Button type="submit" disabled={processing} className="gap-2" size="lg">
                                {processing ? (
                                    <>
                                        <div className="h-4 w-4 animate-spin rounded-full border-2 border-current border-t-transparent" />
                                        Salvando...
                                    </>
                                ) : (
                                    <>
                                        <Save className="h-4 w-4" />
                                        Salvar Configurações
                                    </>
                                )}
                            </Button>
                        </div>
                    </form>

                    {/* Informações de ajuda */}
                    <div className="rounded-lg border bg-muted/50 p-6">
                        <h4 className="mb-3 flex items-center gap-2 font-semibold">
                            <Clock className="h-4 w-4" />
                            Como configurar os horários
                        </h4>
                        <ul className="space-y-2 text-sm text-muted-foreground">
                            <li>• Use o switch para ativar/desativar o atendimento em cada dia</li>
                            <li>• Configure os horários de início e término do atendimento</li>
                            <li>• Você pode adicionar múltiplos períodos por dia (ex: manhã e tarde)</li>
                            <li>• Cada dia deve ter pelo menos um horário configurado quando ativo</li>
                            <li>• Dias desativados não permitirão agendamentos</li>
                            <li>• Os horários serão usados para bloquear agendamentos fora do expediente</li>
                        </ul>
                    </div>
                </div>
            </SettingsLayout>
        </AppLayout>
    );
}
