import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { User } from '@/types/user';
import { formatPhoneBR, onlyDigits } from '@/utils/formatPhone';
import { router } from '@inertiajs/react';
import { Dispatch, SetStateAction } from 'react';
import { toast } from 'sonner';

type UserFormDialogProps = {
    open: boolean;
    onClose: () => void;
    mode: 'create' | 'edit';
    user: User | null;
    setUser: Dispatch<SetStateAction<User | null>>;
    roles: { id: number; name: string }[];
};

export function UserFormDialog({ open, onClose, mode, user, setUser, roles }: UserFormDialogProps) {
    if (!user) return null;

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();

        // Desformatar telefone antes de enviar
        const userData = {
            ...user,
            phone: onlyDigits(user.phone || ''),
        };

        const method = mode === 'edit' ? 'patch' : 'post';
        const url = mode === 'edit' ? `/users/${user.id}` : '/users';

        router[method](url, userData, {
            onSuccess: () => {
                toast.success(mode === 'edit' ? 'Colaborador atualizado com sucesso!' : 'Colaborador criado com sucesso!');
                onClose();
            },
        });
    };

    return (
        <Dialog open={open} onOpenChange={onClose}>
            <DialogContent className="sm:max-w-[550px]">
                <DialogHeader>
                    <DialogTitle className="text-xl">{mode === 'edit' ? 'Editar Colaborador' : 'Novo Colaborador'}</DialogTitle>
                    <DialogDescription>
                        {mode === 'edit' ?
                            'Atualize os dados do colaborador' :
                            'Preencha os dados para cadastrar um novo colaborador. Um e-mail com instruções para criar a senha será enviado automaticamente.'
                        }
                    </DialogDescription>
                </DialogHeader>

                <form onSubmit={handleSubmit} className="space-y-4 py-4">
                    <div className="space-y-2">
                        <Label htmlFor="name">Nome completo *</Label>
                        <Input
                            id="name"
                            value={user.name}
                            onChange={(e) => setUser({ ...user, name: e.target.value })}
                            required
                            placeholder="Digite o nome completo"
                        />
                    </div>

                    <div className="grid grid-cols-2 gap-4">
                        <div className="space-y-2">
                            <Label htmlFor="email">Email *</Label>
                            <Input
                                id="email"
                                type="email"
                                value={user.email}
                                onChange={(e) => setUser({ ...user, email: e.target.value })}
                                required
                                placeholder="email@clinica.com"
                            />
                            {mode === 'create' && (
                                <p className="text-xs text-muted-foreground">
                                    Será enviado um e-mail para criar a senha
                                </p>
                            )}
                        </div>

                        <div className="space-y-2">
                            <Label htmlFor="phone">Telefone</Label>
                            <Input
                                id="phone"
                                value={user.phone ? formatPhoneBR(user.phone) : ''}
                                onChange={(e) => {
                                    const rawValue = e.target.value;
                                    const formatted = formatPhoneBR(rawValue);
                                    setUser({ ...user, phone: formatted });
                                }}
                                placeholder="(00) 00000-0000"
                            />
                        </div>
                    </div>

                    <div className="space-y-2">
                        <Label htmlFor="role">Função *</Label>
                        <Select value={String(user.id_role)} onValueChange={(value) => setUser({ ...user, id_role: Number(value) })}>
                            <SelectTrigger>
                                <SelectValue placeholder="Selecione a função" />
                            </SelectTrigger>
                            <SelectContent>
                                {roles.map((role) => (
                                    <SelectItem key={role.id} value={String(role.id)}>
                                        {role.name}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
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
