import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Patient } from '@/types/patient';
import { formatPhoneBR, onlyDigits } from '@/utils/formatPhone';
import { router } from '@inertiajs/react';
import { Dispatch, SetStateAction } from 'react';
import { toast } from 'sonner';

type PatientFormDialogProps = {
    open: boolean;
    patient: Patient | null;
    setPatient: Dispatch<SetStateAction<Patient | null>>;
    onClose: () => void;
    mode: 'edit' | 'create';
};

export function PatientFormDialog({ open, patient, setPatient, onClose, mode }: PatientFormDialogProps) {
    if (!patient) return null;

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();

        // Desformatar telefone antes de enviar
        const patientData = {
            ...patient,
            phone: onlyDigits(patient.phone || ''),
        };

        const method = mode === 'edit' ? 'patch' : 'post';
        const url = mode === 'edit' ? `/patients/${patient.id}` : '/patients';

        router[method](url, patientData, {
            onSuccess: () => {
                toast.success(mode === 'edit' ? 'Paciente atualizado com sucesso!' : 'Paciente criado com sucesso!');
                onClose();
            },
        });
    };

    return (
        <Dialog open={open} onOpenChange={onClose}>
            <DialogContent className="sm:max-w-[500px]">
                <DialogHeader>
                    <DialogTitle className="text-xl">{mode === 'edit' ? 'Editar Paciente' : 'Novo Paciente'}</DialogTitle>
                    <DialogDescription>
                        {mode === 'edit' ? 'Atualize os dados do paciente' : 'Preencha os dados para cadastrar um novo paciente'}
                    </DialogDescription>
                </DialogHeader>
                <form onSubmit={handleSubmit} className="space-y-4 py-4">
                    <div className="space-y-2">
                        <Label htmlFor="name">Nome completo *</Label>
                        <Input
                            id="name"
                            value={patient.name}
                            onChange={(e) => setPatient({ ...patient, name: e.target.value })}
                            required
                            placeholder="Digite o nome completo"
                        />
                    </div>

                    <div className="grid grid-cols-2 gap-4">
                        <div className="space-y-2">
                            <Label htmlFor="phone">Telefone</Label>
                            <Input
                                id="phone"
                                value={patient.phone ? formatPhoneBR(patient.phone) : ''}
                                onChange={(e) => {
                                    const rawValue = e.target.value;
                                    const formatted = formatPhoneBR(rawValue);
                                    setPatient({ ...patient, phone: formatted });
                                }}
                                placeholder="(00) 00000-0000"
                            />
                        </div>

                        <div className="space-y-2">
                            <Label htmlFor="birthdate">Data de Nascimento</Label>
                            <Input
                                id="birthdate"
                                type="date"
                                value={patient.birthdate?.split('T')[0] || ''}
                                onChange={(e) => setPatient({ ...patient, birthdate: e.target.value })}
                            />
                        </div>
                    </div>

                    <div className="space-y-2">
                        <Label htmlFor="notes">Anotações</Label>
                        <Textarea
                            id="notes"
                            value={patient.notes || ''}
                            onChange={(e) => setPatient({ ...patient, notes: e.target.value })}
                            placeholder="Observações importantes sobre o paciente"
                            rows={3}
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
