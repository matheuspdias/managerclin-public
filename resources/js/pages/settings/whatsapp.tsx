import { Alert, AlertDescription } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import HeadingSmall from '@/components/heading-small';
import AppLayout from '@/layouts/app-layout';
import SettingsLayout from '@/layouts/settings/layout';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/react';
import axios from 'axios';
import { AlertCircle, CheckCircle2, Loader2 } from 'lucide-react';
import { useEffect, useRef, useState } from 'react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Configurações de WhatsApp',
        href: '/settings/company',
    },
];

type WhatsAppStatus = {
    instance?: {
        instanceName: string;
        state: 'open' | 'connecting' | 'close';
    };
    base64?: string;
};

type Props = {
    messageTemplates?: {
        day_before: string;
        '3hours_before': string;
    };
    availableVariables?: Record<string, string>;
};

export default function Whatsapp({ messageTemplates, availableVariables }: Props) {
    const [whatsappData, setWhatsappData] = useState<WhatsAppStatus | null>(null);
    const [isLoading, setIsLoading] = useState(true);
    const [error, setError] = useState<string | null>(null);
    const [nextCheck, setNextCheck] = useState<number | null>(null);
    const pollingInterval = useRef<NodeJS.Timeout | null>(null);
    const countdownInterval = useRef<NodeJS.Timeout | null>(null);

    const { data, setData, patch, processing, errors, reset } = useForm({
        whatsapp_message_day_before: messageTemplates?.day_before || '',
        whatsapp_message_3hours_before: messageTemplates?.['3hours_before'] || '',
    });

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        patch(route('whatsapp.updateMessages'), {
            preserveScroll: true,
            onSuccess: () => {
                // Sucesso
            },
        });
    };

    const fetchWhatsAppStatus = async () => {
        try {
            const response = await axios.get('/settings/whatsapp/qrcode');
            setWhatsappData(response.data);
            return response.data;
        } catch (err) {
            setError('Erro ao verificar status do WhatsApp');
            console.error(err);
            return null;
        } finally {
            setIsLoading(false);
        }
    };

    const fetchQrCode = async () => {
        setIsLoading(true);
        try {
            const response = await axios.get('/settings/whatsapp/qrcode');
            setWhatsappData((prev) => ({ ...prev, base64: response.data.base64 }));

            // Inicia o polling quando o QR Code é gerado
            if (!pollingInterval.current) {
                startPolling();
            }
        } catch (err) {
            setError('Erro ao gerar QR Code');
            console.error(err);
        } finally {
            setIsLoading(false);
        }
    };

    const startPolling = () => {
        // Para qualquer intervalo existente
        stopPolling();

        // Configura novo intervalo (verifica a cada 5 segundos)
        pollingInterval.current = setInterval(async () => {
            // Mostra contagem regressiva
            let count = 5;
            setNextCheck(count);

            countdownInterval.current = setInterval(() => {
                count--;
                setNextCheck(count);
                if (count <= 0) {
                    if (countdownInterval.current) {
                        clearInterval(countdownInterval.current);
                    }
                }
            }, 1000);

            const data = await fetchWhatsAppStatus();
            if (data?.instance?.state === 'open') {
                // Conexão estabelecida, para o polling
                stopPolling();
                // Atualiza os dados removendo o QR Code
                setWhatsappData((prev) => ({
                    instance: data.instance,
                    base64: undefined,
                }));
            }
        }, 10000); // 10 segundos entre as verificações
    };

    const stopPolling = () => {
        if (pollingInterval.current) {
            clearInterval(pollingInterval.current);
            pollingInterval.current = null;
        }
        if (countdownInterval.current) {
            clearInterval(countdownInterval.current);
            countdownInterval.current = null;
        }
        setNextCheck(null);
    };

    useEffect(() => {
        // Verifica o status inicial
        fetchWhatsAppStatus().then((data) => {
            if (!data?.instance || data.instance.state !== 'open') {
                fetchQrCode();
            }
        });

        // Limpa o intervalo quando o componente é desmontado
        return () => {
            stopPolling();
        };
    }, []);

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Configurações de WhatsApp" />

            <SettingsLayout>
                <div className="space-y-6">
                    <HeadingSmall title="WhatsApp" description="Gerencia o numero que irá interagir com seus clientes no WhatsApp" />

                    <Card>
                        <CardHeader>
                            <CardTitle>Conexão com WhatsApp</CardTitle>
                        </CardHeader>
                        <CardContent>
                            {error && (
                                <Alert variant="destructive" className="mb-4">
                                    <AlertCircle className="h-4 w-4" />
                                    <AlertDescription>{error}</AlertDescription>
                                </Alert>
                            )}

                            {isLoading ? (
                                <div className="flex items-center justify-center py-8">
                                    <Loader2 className="h-12 w-12 animate-spin text-primary" />
                                </div>
                            ) : whatsappData?.instance?.state === 'open' ? (
                                <div className="flex flex-col items-center">
                                    <Alert className="mb-4 w-full border-green-500 bg-green-50 text-green-900 dark:bg-green-950 dark:text-green-100">
                                        <CheckCircle2 className="h-4 w-4" />
                                        <AlertDescription>
                                            WhatsApp conectado com sucesso! (Instância: {whatsappData.instance.instanceName})
                                        </AlertDescription>
                                    </Alert>
                                </div>
                            ) : whatsappData?.base64 ? (
                                <div className="flex flex-col items-center">
                                    <img src={whatsappData.base64} alt="QR Code para conexão do WhatsApp" className="mb-4" />
                                    <p className="mb-4 text-sm text-muted-foreground">
                                        Escaneie este QR Code com o WhatsApp no seu celular para conectar
                                    </p>

                                    {nextCheck !== null && (
                                        <div className="flex items-center gap-2">
                                            <Loader2 className="h-4 w-4 animate-spin" />
                                            <p className="text-sm text-muted-foreground">Verificando conexão</p>
                                        </div>
                                    )}
                                </div>
                            ) : (
                                <div className="flex flex-col items-center">
                                    <p className="mb-4 text-sm text-muted-foreground">
                                        Clique no botão abaixo para gerar o QR Code de conexão
                                    </p>
                                    <Button onClick={fetchQrCode} disabled={isLoading}>
                                        {isLoading ? 'Gerando...' : 'Gerar QR Code'}
                                    </Button>
                                </div>
                            )}
                        </CardContent>
                    </Card>

                    {/* Mensagens Personalizadas */}
                    <Card>
                        <CardHeader>
                            <CardTitle>Mensagens Personalizadas</CardTitle>
                            <CardDescription>Configure as mensagens que serão enviadas automaticamente aos seus clientes.</CardDescription>
                        </CardHeader>
                        <CardContent>
                            {/* Variáveis disponíveis */}
                            <Alert className="mb-6">
                                <AlertDescription>
                                    <h4 className="mb-2 font-medium">Variáveis disponíveis:</h4>
                                    <div className="space-y-1">
                                        {availableVariables &&
                                            Object.entries(availableVariables).map(([key, description]) => (
                                                <div key={key} className="text-sm">
                                                    <code className="rounded bg-muted px-1.5 py-0.5 font-mono text-xs">{`{{${key}}}`}</code>
                                                    <span className="ml-2">{description}</span>
                                                </div>
                                            ))}
                                    </div>
                                </AlertDescription>
                            </Alert>

                            <form onSubmit={handleSubmit} className="space-y-6">
                                {/* Mensagem 1 dia antes */}
                                <div className="space-y-2">
                                    <Label htmlFor="day_before">Mensagem enviada 1 dia antes do agendamento</Label>
                                    <Textarea
                                        id="day_before"
                                        rows={4}
                                        value={data.whatsapp_message_day_before}
                                        onChange={(e) => setData('whatsapp_message_day_before', e.target.value)}
                                        placeholder="Ex: Olá {{nome_usuario}}, lembrando que você tem um agendamento amanhã às {{inicio_atendimento}} com {{profissional}}."
                                    />
                                    {errors.whatsapp_message_day_before && (
                                        <p className="text-sm text-destructive">{errors.whatsapp_message_day_before}</p>
                                    )}
                                </div>

                                {/* Mensagem 3 horas antes */}
                                <div className="space-y-2">
                                    <Label htmlFor="3hours_before">Mensagem enviada 3 horas antes do agendamento</Label>
                                    <Textarea
                                        id="3hours_before"
                                        rows={4}
                                        value={data.whatsapp_message_3hours_before}
                                        onChange={(e) => setData('whatsapp_message_3hours_before', e.target.value)}
                                        placeholder="Ex: Olá {{nome_usuario}}, lembrando que você tem um agendamento hoje às {{inicio_atendimento}} com {{profissional}}."
                                    />
                                    {errors.whatsapp_message_3hours_before && (
                                        <p className="text-sm text-destructive">{errors.whatsapp_message_3hours_before}</p>
                                    )}
                                </div>

                                <div className="flex items-center justify-between">
                                    <Button type="button" variant="outline" onClick={() => reset()}>
                                        Restaurar padrão
                                    </Button>
                                    <Button type="submit" disabled={processing}>
                                        {processing ? (
                                            <>
                                                <Loader2 className="mr-2 h-4 w-4 animate-spin" />
                                                Salvando...
                                            </>
                                        ) : (
                                            'Salvar mensagens'
                                        )}
                                    </Button>
                                </div>
                            </form>
                        </CardContent>
                    </Card>
                </div>
            </SettingsLayout>
        </AppLayout>
    );
}
