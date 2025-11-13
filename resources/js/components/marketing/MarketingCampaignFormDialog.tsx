import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogFooter,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import { useForm } from '@inertiajs/react';
import { toast } from 'sonner';
import { useEffect, useState } from 'react';
import { Calendar, Users, Image, FileText, Video, Music, X, Upload } from 'lucide-react';

interface Campaign {
    id: number;
    name: string;
    message: string;
    status: string;
    target_audience: 'all' | 'with_appointments' | 'without_appointments' | 'custom';
    scheduled_at: string | null;
    media_type: 'image' | 'video' | 'document' | 'audio' | null;
    media_url: string | null;
    media_filename: string | null;
}

interface MarketingCampaignFormDialogProps {
    open: boolean;
    onClose: () => void;
    campaign?: Campaign | null;
    mode: 'create' | 'edit';
}

export function MarketingCampaignFormDialog({
    open,
    onClose,
    campaign,
    mode
}: MarketingCampaignFormDialogProps) {
    // Helper function to format datetime for datetime-local input
    const formatDateTimeLocal = (dateString: string | null) => {
        if (!dateString) return '';
        const date = new Date(dateString);
        // Get local date components
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        const hours = String(date.getHours()).padStart(2, '0');
        const minutes = String(date.getMinutes()).padStart(2, '0');
        return `${year}-${month}-${day}T${hours}:${minutes}`;
    };

    const { data, setData, post, patch, processing, errors, reset } = useForm({
        name: campaign?.name || '',
        message: campaign?.message || '',
        target_audience: campaign?.target_audience || 'all',
        scheduled_at: formatDateTimeLocal(campaign?.scheduled_at || null),
        media_type: campaign?.media_type || null,
        media_url: campaign?.media_url || '',
        media_filename: campaign?.media_filename || '',
        media_file: null as File | null,
    });

    const [previewUrl, setPreviewUrl] = useState<string | null>(null);
    const [uploadMode, setUploadMode] = useState<'file' | 'url'>('file');

    // Update form data when campaign prop changes
    useEffect(() => {
        if (campaign && mode === 'edit') {
            setData({
                name: campaign.name,
                message: campaign.message,
                target_audience: campaign.target_audience,
                scheduled_at: formatDateTimeLocal(campaign.scheduled_at),
                media_type: campaign.media_type || null,
                media_url: campaign.media_url || '',
                media_filename: campaign.media_filename || '',
            });
        } else if (mode === 'create') {
            setData({
                name: '',
                message: '',
                target_audience: 'all',
                scheduled_at: '',
                media_type: null,
                media_url: '',
                media_filename: '',
            });
        }
    }, [campaign, mode, setData]);

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();

        const url = mode === 'edit' ? `/marketing/campaigns/${campaign?.id}` : '/marketing/campaigns';
        const method = mode === 'edit' ? patch : post;

        method(url, {
            onSuccess: () => {
                toast.success(mode === 'edit' ? 'Campanha atualizada com sucesso!' : 'Campanha criada com sucesso!');
                handleClose();
            },
            onError: (errors) => {
                console.error('Errors:', errors);
                toast.error('Erro ao salvar campanha');
            },
        });
    };

    const handleClose = () => {
        reset();
        onClose();
    };

    const targetAudienceOptions = [
        { value: 'all', label: 'Todos os Pacientes', description: 'Envia para todos os pacientes cadastrados' },
        { value: 'with_appointments', label: 'Com Agendamentos', description: 'Apenas pacientes que já têm consultas' },
        { value: 'without_appointments', label: 'Sem Agendamentos', description: 'Apenas pacientes sem consultas agendadas' },
    ];

    const mediaTypeOptions = [
        { value: 'image', label: 'Imagem', icon: Image, description: 'JPG, PNG, GIF' },
        { value: 'video', label: 'Vídeo', icon: Video, description: 'MP4, AVI' },
        { value: 'document', label: 'Documento', icon: FileText, description: 'PDF, DOC, XLS' },
        { value: 'audio', label: 'Áudio', icon: Music, description: 'MP3, WAV' },
    ];

    const messageCharCount = data.message.length;
    const messageLimit = 1000;

    const handleFileChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        const file = e.target.files?.[0];
        if (file) {
            setData('media_file', file);

            // Gerar preview para imagens
            if (file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onloadend = () => {
                    setPreviewUrl(reader.result as string);
                };
                reader.readAsDataURL(file);
            } else {
                setPreviewUrl(null);
            }

            // Definir filename automaticamente
            if (!data.media_filename) {
                setData('media_filename', file.name);
            }
        }
    };

    const clearMedia = () => {
        setData({
            ...data,
            media_type: null,
            media_url: '',
            media_filename: '',
            media_file: null,
        });
        setPreviewUrl(null);
    };

    return (
        <Dialog open={open} onOpenChange={handleClose}>
            <DialogContent className="sm:max-w-[600px] max-h-[90vh] flex flex-col">
                <DialogHeader>
                    <DialogTitle>
                        {mode === 'edit' ? 'Editar Campanha' : 'Nova Campanha de Marketing'}
                    </DialogTitle>
                </DialogHeader>

                <form onSubmit={handleSubmit} className="flex flex-col flex-1 overflow-hidden">
                    <div className="space-y-4 overflow-y-auto pr-2 flex-1">
                    {/* Nome da Campanha */}
                    <div className="space-y-2">
                        <Label htmlFor="name">Nome da Campanha *</Label>
                        <Input
                            id="name"
                            value={data.name}
                            onChange={(e) => setData('name', e.target.value)}
                            placeholder="Ex: Campanha de Retorno"
                            required
                        />
                        {errors.name && (
                            <p className="text-sm text-red-600">{errors.name}</p>
                        )}
                    </div>

                    {/* Público-Alvo */}
                    <div className="space-y-2">
                        <Label htmlFor="target_audience">Público-Alvo *</Label>
                        <Select
                            value={data.target_audience}
                            onValueChange={(value) => setData('target_audience', value as typeof data.target_audience)}
                        >
                            <SelectTrigger>
                                <SelectValue placeholder="Selecione o público" />
                            </SelectTrigger>
                            <SelectContent>
                                {targetAudienceOptions.map((option) => (
                                    <SelectItem key={option.value} value={option.value}>
                                        <div className="flex items-start gap-2">
                                            <Users className="h-4 w-4 mt-0.5" />
                                            <div>
                                                <p className="font-medium">{option.label}</p>
                                                <p className="text-xs text-muted-foreground">{option.description}</p>
                                            </div>
                                        </div>
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                        {errors.target_audience && (
                            <p className="text-sm text-red-600">{errors.target_audience}</p>
                        )}
                    </div>

                    {/* Mensagem */}
                    <div className="space-y-2">
                        <Label htmlFor="message">
                            {data.media_type ? 'Legenda da Mídia *' : 'Mensagem do WhatsApp *'}
                        </Label>
                        <Textarea
                            id="message"
                            value={data.message}
                            onChange={(e) => setData('message', e.target.value)}
                            placeholder={data.media_type ? 'Digite a legenda que acompanhará a mídia...' : 'Digite a mensagem que será enviada aos pacientes...'}
                            rows={4}
                            maxLength={messageLimit}
                            required
                        />
                        <div className="flex items-center justify-between text-sm">
                            <p className="text-muted-foreground">
                                {data.media_type && data.media_type !== 'audio'
                                    ? 'A legenda será exibida junto com a mídia'
                                    : 'A mensagem será enviada via WhatsApp para os pacientes selecionados'}
                            </p>
                            <span className={`${messageCharCount > messageLimit * 0.9 ? 'text-orange-600' : 'text-muted-foreground'}`}>
                                {messageCharCount}/{messageLimit}
                            </span>
                        </div>
                        {errors.message && (
                            <p className="text-sm text-red-600">{errors.message}</p>
                        )}
                    </div>

                    {/* Mídia (opcional) */}
                    <div className="space-y-3 border-t pt-4">
                        <div className="flex items-center justify-between">
                            <Label>Mídia (opcional)</Label>
                            {data.media_type && (
                                <Button
                                    type="button"
                                    variant="ghost"
                                    size="sm"
                                    onClick={clearMedia}
                                >
                                    <X className="h-4 w-4 mr-1" />
                                    Remover Mídia
                                </Button>
                            )}
                        </div>

                        {!data.media_type ? (
                            <div className="grid grid-cols-2 gap-2">
                                {mediaTypeOptions.map((option) => {
                                    const Icon = option.icon;
                                    return (
                                        <Button
                                            key={option.value}
                                            type="button"
                                            variant="outline"
                                            className="h-auto p-3 flex-col items-start"
                                            onClick={() => setData('media_type', option.value as 'image' | 'video' | 'document' | 'audio')}
                                        >
                                            <div className="flex items-center gap-2 w-full">
                                                <Icon className="h-4 w-4" />
                                                <span className="font-medium">{option.label}</span>
                                            </div>
                                            <span className="text-xs text-muted-foreground mt-1">
                                                {option.description}
                                            </span>
                                        </Button>
                                    );
                                })}
                            </div>
                        ) : (
                            <div className="space-y-3 border rounded-lg p-4 bg-muted/30">
                                <div className="flex items-center gap-2 text-sm font-medium">
                                    {(() => {
                                        const option = mediaTypeOptions.find(o => o.value === data.media_type);
                                        const Icon = option?.icon || Image;
                                        return (
                                            <>
                                                <Icon className="h-4 w-4" />
                                                {option?.label}
                                            </>
                                        );
                                    })()}
                                </div>

                                {/* Toggle entre Upload e URL */}
                                <div className="flex gap-2 mb-3">
                                    <Button
                                        type="button"
                                        variant={uploadMode === 'file' ? 'default' : 'outline'}
                                        size="sm"
                                        onClick={() => setUploadMode('file')}
                                    >
                                        <Upload className="h-3 w-3 mr-1" />
                                        Upload de Arquivo
                                    </Button>
                                    <Button
                                        type="button"
                                        variant={uploadMode === 'url' ? 'default' : 'outline'}
                                        size="sm"
                                        onClick={() => setUploadMode('url')}
                                    >
                                        URL/Base64
                                    </Button>
                                </div>

                                {uploadMode === 'file' ? (
                                    <div className="space-y-3">
                                        <div className="space-y-2">
                                            <Label htmlFor="media_file">Selecionar Arquivo *</Label>
                                            <Input
                                                id="media_file"
                                                type="file"
                                                onChange={handleFileChange}
                                                accept={
                                                    data.media_type === 'image' ? 'image/*' :
                                                    data.media_type === 'video' ? 'video/*' :
                                                    data.media_type === 'audio' ? 'audio/*' :
                                                    'application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document'
                                                }
                                            />
                                            <p className="text-xs text-muted-foreground">
                                                Tamanho máximo: 20MB
                                            </p>
                                            {errors.media_file && (
                                                <p className="text-sm text-red-600">{errors.media_file}</p>
                                            )}
                                        </div>

                                        {/* Preview da imagem */}
                                        {previewUrl && data.media_type === 'image' && (
                                            <div className="border rounded-lg p-2">
                                                <img
                                                    src={previewUrl}
                                                    alt="Preview"
                                                    className="max-h-48 mx-auto rounded"
                                                />
                                            </div>
                                        )}

                                        {/* Nome do arquivo selecionado */}
                                        {data.media_file && (
                                            <div className="text-sm text-muted-foreground">
                                                📎 {data.media_file.name} ({(data.media_file.size / 1024 / 1024).toFixed(2)} MB)
                                            </div>
                                        )}
                                    </div>
                                ) : (
                                    <div className="space-y-2">
                                        <Label htmlFor="media_url">URL da Mídia *</Label>
                                        <Input
                                            id="media_url"
                                            value={data.media_url}
                                            onChange={(e) => setData('media_url', e.target.value)}
                                            placeholder="https://exemplo.com/imagem.jpg ou dados em base64"
                                        />
                                        <p className="text-xs text-muted-foreground">
                                            Cole a URL da mídia ou dados em base64
                                        </p>
                                        {errors.media_url && (
                                            <p className="text-sm text-red-600">{errors.media_url}</p>
                                        )}
                                    </div>
                                )}

                                {data.media_type === 'document' && (
                                    <div className="space-y-2">
                                        <Label htmlFor="media_filename">Nome do Arquivo</Label>
                                        <Input
                                            id="media_filename"
                                            value={data.media_filename}
                                            onChange={(e) => setData('media_filename', e.target.value)}
                                            placeholder="documento.pdf"
                                        />
                                        <p className="text-xs text-muted-foreground">
                                            Nome que aparecerá no arquivo enviado {uploadMode === 'file' && '(preenchido automaticamente)'}
                                        </p>
                                        {errors.media_filename && (
                                            <p className="text-sm text-red-600">{errors.media_filename}</p>
                                        )}
                                    </div>
                                )}

                                {data.media_type === 'audio' && (
                                    <div className="rounded bg-yellow-50 dark:bg-yellow-950 p-3 border border-yellow-200 dark:border-yellow-800">
                                        <p className="text-xs text-yellow-800 dark:text-yellow-200">
                                            ⚠️ Áudios não suportam legenda. A mensagem acima não será enviada junto com o áudio.
                                        </p>
                                    </div>
                                )}
                            </div>
                        )}
                    </div>

                    {/* Agendamento */}
                    <div className="space-y-2">
                        <Label htmlFor="scheduled_at" className="flex items-center gap-2">
                            <Calendar className="h-4 w-4" />
                            Agendar Envio (opcional)
                        </Label>
                        <Input
                            id="scheduled_at"
                            type="datetime-local"
                            value={data.scheduled_at}
                            onChange={(e) => setData('scheduled_at', e.target.value)}
                        />
                        <p className="text-sm text-muted-foreground">
                            {data.scheduled_at
                                ? 'A campanha será enviada automaticamente na data e hora selecionadas'
                                : 'Deixe em branco para salvar como rascunho'}
                        </p>
                        {errors.scheduled_at && (
                            <p className="text-sm text-red-600">{errors.scheduled_at}</p>
                        )}
                    </div>

                    {/* Info Box */}
                    <div className="rounded-lg bg-blue-50 dark:bg-blue-950 p-4 border border-blue-200 dark:border-blue-800">
                        <h4 className="text-sm font-medium text-blue-900 dark:text-blue-100 mb-2">
                            📌 Dicas para uma boa campanha
                        </h4>
                        <ul className="text-sm text-blue-800 dark:text-blue-200 space-y-1 list-disc list-inside">
                            <li>Use uma mensagem clara e objetiva</li>
                            <li>Personalize quando possível</li>
                            <li>Evite enviar em horários inadequados</li>
                            <li>Teste com um pequeno grupo primeiro</li>
                        </ul>
                    </div>
                    </div>

                    {/* Footer fixo */}
                    <DialogFooter className="mt-4 pt-4 border-t">
                        <Button
                            type="button"
                            variant="outline"
                            onClick={handleClose}
                            disabled={processing}
                        >
                            Cancelar
                        </Button>
                        <Button type="submit" disabled={processing}>
                            {processing ? 'Salvando...' : mode === 'edit' ? 'Atualizar' : 'Criar Campanha'}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}
