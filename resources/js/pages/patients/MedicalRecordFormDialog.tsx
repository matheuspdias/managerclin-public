import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { MedicalRecord } from '@/types/medicalRecord';
import { router } from '@inertiajs/react';
import { Dispatch, SetStateAction } from 'react';
import { toast } from 'sonner';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';

type MedicalRecordFormDialogProps = {
    open: boolean;
    medicalRecord: MedicalRecord | null;
    setMedicalRecord: Dispatch<SetStateAction<MedicalRecord | null>>;
    onClose: () => void;
    mode: 'edit' | 'create';
};

export function MedicalRecordFormDialog({ open, medicalRecord, setMedicalRecord, onClose, mode }: MedicalRecordFormDialogProps) {
    if (!medicalRecord) return null;

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        const method = mode === 'edit' ? 'patch' : 'post';
        const url = mode === 'edit' ? `/medical-records/${medicalRecord.id}` : '/medical-records';

        router[method](url, medicalRecord, {
            onSuccess: () => {
                toast.success(mode === 'edit' ? 'Prontuário atualizado com sucesso!' : 'Prontuário criado com sucesso!');
                handleClose();
                // Recarrega a página para buscar os dados atualizados
                router.reload({ only: ['patients'] });
            },
        });
    };

    const handleClose = () => {
        onClose();
        // Reseta o estado do medical record para garantir dados frescos
        if (mode === 'edit') {
            setMedicalRecord(null);
        }
    };

    return (
        <Dialog open={open} onOpenChange={handleClose}>
            <DialogContent className="sm:max-w-[600px]">
                <DialogHeader>
                    <DialogTitle className="text-xl">{mode === 'edit' ? 'Editar Prontuário' : 'Novo Prontuário'}</DialogTitle>
                    <DialogDescription>
                        {mode === 'edit' ? 'Atualize as informações do prontuário' : 'Preencha as informações médicas do paciente'}
                    </DialogDescription>
                </DialogHeader>
                <form onSubmit={handleSubmit} className="space-y-4 py-4">
                    <Tabs defaultValue="consultation" className="w-full">
                        <TabsList className="grid grid-cols-3 w-full mb-4">
                            <TabsTrigger value="consultation">Consulta</TabsTrigger>
                            <TabsTrigger value="history">Histórico</TabsTrigger>
                            <TabsTrigger value="followup">Acompanhamento</TabsTrigger>
                        </TabsList>

                        <TabsContent value="consultation" className="space-y-4">
                            <div className="space-y-2">
                                <Label htmlFor="chief_complaint">Queixa Principal *</Label>
                                <Textarea
                                    id="chief_complaint"
                                    value={medicalRecord.chief_complaint || ''}
                                    onChange={(e) => setMedicalRecord({ ...medicalRecord, chief_complaint: e.target.value })}
                                    placeholder="Descreva a queixa principal do paciente"
                                    rows={3}
                                    className="resize-none"
                                    required
                                />
                            </div>

                            <div className="space-y-2">
                                <Label htmlFor="physical_exam">Exame Físico *</Label>
                                <Textarea
                                    id="physical_exam"
                                    value={medicalRecord.physical_exam || ''}
                                    onChange={(e) => setMedicalRecord({ ...medicalRecord, physical_exam: e.target.value })}
                                    placeholder="Descrição do exame físico realizado"
                                    rows={4}
                                    className="resize-none"
                                    required
                                />
                            </div>

                            <div className="grid grid-cols-2 gap-4">
                                <div className="space-y-2">
                                    <Label htmlFor="diagnosis">Diagnóstico *</Label>
                                    <Textarea
                                        id="diagnosis"
                                        value={medicalRecord.diagnosis || ''}
                                        onChange={(e) => setMedicalRecord({ ...medicalRecord, diagnosis: e.target.value })}
                                        placeholder="Diagnóstico médico"
                                        rows={3}
                                        className="resize-none"
                                        required
                                    />
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="treatment_plan">Plano de Tratamento *</Label>
                                    <Textarea
                                        id="treatment_plan"
                                        value={medicalRecord.treatment_plan || ''}
                                        onChange={(e) => setMedicalRecord({ ...medicalRecord, treatment_plan: e.target.value })}
                                        placeholder="Plano terapêutico proposto"
                                        rows={3}
                                        className="resize-none"
                                        required
                                    />
                                </div>
                            </div>
                        </TabsContent>

                        <TabsContent value="history" className="space-y-4">
                            <div className="space-y-2">
                                <Label htmlFor="medical_history">Histórico Médico</Label>
                                <Textarea
                                    id="medical_history"
                                    value={medicalRecord.medical_history || ''}
                                    onChange={(e) => setMedicalRecord({ ...medicalRecord, medical_history: e.target.value })}
                                    placeholder="Histórico médico relevante do paciente"
                                    rows={4}
                                    className="resize-none"
                                />
                            </div>

                            <div className="grid grid-cols-2 gap-4">
                                <div className="space-y-2">
                                    <Label htmlFor="medications">Medicações Atuais</Label>
                                    <Textarea
                                        id="medications"
                                        value={medicalRecord.medications || ''}
                                        onChange={(e) => setMedicalRecord({ ...medicalRecord, medications: e.target.value })}
                                        placeholder="Medicamentos em uso contínuo"
                                        rows={3}
                                        className="resize-none"
                                    />
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="allergies">Alergias</Label>
                                    <Textarea
                                        id="allergies"
                                        value={medicalRecord.allergies || ''}
                                        onChange={(e) => setMedicalRecord({ ...medicalRecord, allergies: e.target.value })}
                                        placeholder="Alergias conhecidas"
                                        rows={3}
                                        className="resize-none"
                                    />
                                </div>
                            </div>
                        </TabsContent>

                        <TabsContent value="followup" className="space-y-4">
                            <div className="space-y-2">
                                <Label htmlFor="prescriptions">Prescrições</Label>
                                <Textarea
                                    id="prescriptions"
                                    value={medicalRecord.prescriptions || ''}
                                    onChange={(e) => setMedicalRecord({ ...medicalRecord, prescriptions: e.target.value })}
                                    placeholder="Medicamentos prescritos na consulta"
                                    rows={4}
                                    className="resize-none"
                                />
                            </div>

                            <div className="space-y-2">
                                <Label htmlFor="observations">Observações</Label>
                                <Textarea
                                    id="observations"
                                    value={medicalRecord.observations || ''}
                                    onChange={(e) => setMedicalRecord({ ...medicalRecord, observations: e.target.value })}
                                    placeholder="Observações e orientações adicionais"
                                    rows={3}
                                    className="resize-none"
                                />
                            </div>

                            <div className="space-y-2">
                                <Label htmlFor="follow_up_date">Data de Retorno</Label>
                                <Input
                                    id="follow_up_date"
                                    type="date"
                                    value={medicalRecord.follow_up_date || ''}
                                    onChange={(e) => setMedicalRecord({ ...medicalRecord, follow_up_date: e.target.value })}
                                    min={new Date().toISOString().split('T')[0]}
                                />
                            </div>
                        </TabsContent>
                    </Tabs>

                    <DialogFooter>
                        <Button type="button" variant="outline" onClick={handleClose}>
                            Cancelar
                        </Button>
                        <Button type="submit">{mode === 'edit' ? 'Atualizar' : 'Criar'} Prontuário</Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}
