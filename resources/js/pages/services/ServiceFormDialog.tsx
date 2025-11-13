import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Service } from '@/types/service';
import { router } from '@inertiajs/react';
import { Dispatch, SetStateAction } from 'react';
import { toast } from 'sonner';

type ServiceFormDialogProps = {
    open: boolean;
    service: Service | null;
    setService: Dispatch<SetStateAction<Service | null>>;
    onClose: () => void;
    mode: 'edit' | 'create';
};

export function ServiceFormDialog({ open, service, setService, onClose, mode }: ServiceFormDialogProps) {
    if (!service) return null;

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        const method = mode === 'edit' ? 'patch' : 'post';
        const url = mode === 'edit' ? `/services/${service.id}` : '/services';

        router[method](url, service, {
            onSuccess: () => {
                toast.success(mode === 'edit' ? 'Serviço atualizado com sucesso!' : 'Serviço criado com sucesso!');
                onClose();
            },
        });
    };

    return (
        <Dialog open={open} onOpenChange={onClose}>
            <DialogContent className="sm:max-w-[500px]">
                <DialogHeader>
                    <DialogTitle className="text-xl">{mode === 'edit' ? 'Editar Serviço' : 'Novo Serviço'}</DialogTitle>
                    <DialogDescription>
                        {mode === 'edit' ? 'Atualize os dados do serviço' : 'Preencha os dados para cadastrar um novo serviço'}
                    </DialogDescription>
                </DialogHeader>
                <form onSubmit={handleSubmit} className="space-y-4 py-4">
                    <div className="space-y-2">
                        <Label htmlFor="name">Nome do serviço *</Label>
                        <Input
                            id="name"
                            value={service.name}
                            onChange={(e) => setService({ ...service, name: e.target.value })}
                            required
                            placeholder="Ex: Consulta médica, Exame laboratorial"
                        />
                    </div>

                    <div className="space-y-2">
                        <Label htmlFor="description">Descrição</Label>
                        <Textarea
                            id="description"
                            value={service.description || ''}
                            onChange={(e) => setService({ ...service, description: e.target.value })}
                            placeholder="Descreva detalhes sobre o serviço"
                            rows={3}
                        />
                    </div>

                    <div className="space-y-2">
                        <Label htmlFor="price">Preço (R$)</Label>
                        <Input
                            id="price"
                            value={service.price != null ? service.price.toString() : ''}
                            onChange={(e) => setService({ ...service, price: e.target.value ? parseFloat(e.target.value) : 0 })}
                            type="number"
                            step="0.01"
                            min="0"
                            placeholder="0.00"
                            className="[appearance:textfield] [&::-webkit-inner-spin-button]:appearance-none [&::-webkit-outer-spin-button]:appearance-none"
                        />
                        <p className="text-xs text-muted-foreground">Deixe em branco ou zero para serviços gratuitos</p>
                    </div>

                    <DialogFooter>
                        <Button type="button" variant="outline" onClick={onClose}>
                            Cancelar
                        </Button>
                        <Button type="submit">{mode === 'edit' ? 'Atualizar' : 'Cadastrar'}</Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}
