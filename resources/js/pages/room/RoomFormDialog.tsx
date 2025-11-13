import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Room } from '@/types/room';
import { router } from '@inertiajs/react';
import { Dispatch, SetStateAction } from 'react';
import { toast } from 'sonner';

type RoomFormDialogProps = {
    open: boolean;
    room: Room | null;
    setRoom: Dispatch<SetStateAction<Room | null>>;
    onClose: () => void;
    mode: 'edit' | 'create';
};

export function RoomFormDialog({ open, room, setRoom, onClose, mode }: RoomFormDialogProps) {
    if (!room) return null;

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        const method = mode === 'edit' ? 'patch' : 'post';
        const url = mode === 'edit' ? `/rooms/${room.id}` : '/rooms';

        router[method](url, room, {
            onSuccess: () => {
                toast.success(mode === 'edit' ? 'Consultório atualizado com sucesso!' : 'Consultório criado com sucesso!');
                onClose();
            },
        });
    };

    return (
        <Dialog open={open} onOpenChange={onClose}>
            <DialogContent className="sm:max-w-[450px]">
                <DialogHeader>
                    <DialogTitle className="text-xl">{mode === 'edit' ? 'Editar Consultório' : 'Novo Consultório'}</DialogTitle>
                    <DialogDescription>
                        {mode === 'edit' ? 'Atualize os dados do consultório' : 'Preencha os dados para cadastrar um novo consultório'}
                    </DialogDescription>
                </DialogHeader>
                <form onSubmit={handleSubmit} className="space-y-4 py-4">
                    <div className="space-y-2">
                        <Label htmlFor="name">Nome do consultório *</Label>
                        <Input
                            id="name"
                            value={room.name}
                            onChange={(e) => setRoom({ ...room, name: e.target.value })}
                            required
                            placeholder="Ex: Consultório 1, Sala de Exames"
                        />
                    </div>

                    <div className="space-y-2">
                        <Label htmlFor="location">Localização</Label>
                        <Input
                            id="location"
                            value={room.location || ''}
                            onChange={(e) => setRoom({ ...room, location: e.target.value })}
                            placeholder="Ex: 2º Andar, Ala B, Bloco C"
                        />
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
