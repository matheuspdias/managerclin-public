import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';

export type EditPatient = {
    id: number;
    name: string;
    phone?: string;
    birthdate?: string;
    notes?: string;
};

type ConfirmDeleteDialogProps = {
    open: boolean;
    onClose: () => void;
    onConfirm: () => void;
    description?: string;
    confirmText?: string;
};

export default function ConfirmDeleteDialog({ open, onClose, onConfirm, description, confirmText }: ConfirmDeleteDialogProps) {
    return (
        <Dialog open={open} onOpenChange={onClose}>
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Confirmar exclusão</DialogTitle>
                    <DialogDescription>{description}</DialogDescription>
                </DialogHeader>
                <p>{confirmText}</p>
                <DialogFooter>
                    <Button variant="outline" onClick={onClose}>
                        Cancelar
                    </Button>
                    <Button variant="destructive" onClick={onConfirm}>
                        Excluir
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    );
}
