import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { ArrowRight, CheckCircle, CreditCard, Users } from 'lucide-react';

interface PlanConfirmationModalProps {
    isOpen: boolean;
    onClose: () => void;
    onConfirm: () => void;
    currentPlan: any;
    selectedPlan: any;
    additionalUsers: number;
    additionalUsersPrice: number;
}

export function PlanConfirmationModal({
    isOpen,
    onClose,
    onConfirm,
    currentPlan,
    selectedPlan,
    additionalUsers,
    additionalUsersPrice,
}: PlanConfirmationModalProps) {
    if (!selectedPlan) return null;

    const currentTotal = currentPlan ? currentPlan.base_price + additionalUsers * additionalUsersPrice : 0;
    const newTotal = selectedPlan.base_price + additionalUsers * additionalUsersPrice;
    const difference = newTotal - currentTotal;
    const isUpgrade = difference > 0;

    return (
        <Dialog open={isOpen} onOpenChange={onClose}>
            <DialogContent className="sm:max-w-md">
                <DialogHeader>
                    <DialogTitle className="flex items-center">
                        <CreditCard className="mr-2 h-5 w-5" />
                        Confirmar Alteração de Plano
                    </DialogTitle>
                    <DialogDescription>Revise as informações antes de confirmar a mudança</DialogDescription>
                </DialogHeader>

                <div className="space-y-4">
                    {/* Plan Change Summary */}
                    <div className="rounded-lg border p-4">
                        <div className="flex items-center justify-between">
                            <div className="text-center">
                                <p className="text-sm text-gray-500">Plano Atual</p>
                                <p className="font-semibold text-gray-900">{currentPlan ? currentPlan.name : 'Nenhum'}</p>
                                {currentPlan && <p className="text-sm text-gray-600">R$ {currentPlan.base_price.toFixed(2)}/mês</p>}
                            </div>

                            <ArrowRight className="h-5 w-5 text-gray-400" />

                            <div className="text-center">
                                <p className="text-sm text-gray-500">Novo Plano</p>
                                <p className="font-semibold text-gray-900">{selectedPlan.name}</p>
                                <p className="text-sm text-gray-600">R$ {selectedPlan.base_price.toFixed(2)}/mês</p>
                            </div>
                        </div>
                    </div>

                    {/* Additional Users */}
                    {additionalUsers > 0 && (
                        <div className="rounded-lg bg-gray-50 p-3">
                            <div className="flex items-center">
                                <Users className="mr-2 h-4 w-4 text-gray-500" />
                                <span className="text-sm text-gray-700">
                                    {additionalUsers} usuário(s) adicional(is): R$ {(additionalUsers * additionalUsersPrice).toFixed(2)}/mês
                                </span>
                            </div>
                        </div>
                    )}

                    {/* Total Calculation */}
                    <div className="space-y-2 border-t pt-4">
                        <div className="flex justify-between text-sm">
                            <span className="text-gray-600">Plano base:</span>
                            <span>R$ {selectedPlan.base_price.toFixed(2)}</span>
                        </div>
                        {additionalUsers > 0 && (
                            <div className="flex justify-between text-sm">
                                <span className="text-gray-600">Usuários adicionais:</span>
                                <span>R$ {(additionalUsers * additionalUsersPrice).toFixed(2)}</span>
                            </div>
                        )}
                        <div className="flex justify-between border-t pt-2 text-lg font-bold">
                            <span>Total mensal:</span>
                            <span>R$ {newTotal.toFixed(2)}</span>
                        </div>
                    </div>

                    {/* Pricing Change Information */}
                    {currentPlan && (
                        <div className={`rounded-lg p-3 ${isUpgrade ? 'border border-blue-200 bg-blue-50' : 'border border-green-200 bg-green-50'}`}>
                            <div className="flex items-center">
                                <CheckCircle className={`mr-2 h-4 w-4 ${isUpgrade ? 'text-blue-600' : 'text-green-600'}`} />
                                <div className="text-sm">
                                    {isUpgrade ? (
                                        <p className="text-blue-800">
                                            <strong>Upgrade:</strong> Você será cobrado R$ {difference.toFixed(2)} a mais na próxima fatura.
                                        </p>
                                    ) : difference < 0 ? (
                                        <p className="text-green-800">
                                            <strong>Downgrade:</strong> Você economizará R$ {Math.abs(difference).toFixed(2)} na próxima fatura.
                                        </p>
                                    ) : (
                                        <p className="text-gray-800">O valor permanecerá o mesmo.</p>
                                    )}
                                </div>
                            </div>
                        </div>
                    )}

                    {/* AI Credits Information */}
                    <div className="rounded-lg border border-purple-200 bg-purple-50 p-3">
                        <div className="flex items-center">
                            <CheckCircle className="mr-2 h-4 w-4 text-purple-600" />
                            <div className="text-sm text-purple-800">
                                <p>
                                    <strong>Créditos de IA inclusos:</strong> {selectedPlan.id === 'essencial' && '100 créditos'}
                                    {selectedPlan.id === 'pro' && '400 créditos'}
                                    {selectedPlan.id === 'premium' && '2.000 créditos'} mensais
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <DialogFooter className="gap-2">
                    <Button variant="outline" onClick={onClose}>
                        Cancelar
                    </Button>
                    <Button onClick={onConfirm} className="bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700">
                        Confirmar Alteração
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    );
}
