import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { CardElement, useElements, useStripe } from '@stripe/react-stripe-js';
import { useState } from 'react';
import { toast } from 'sonner';

interface CheckoutFormProps {
    selectedPlan: string | null;
    additionalUsers: number;
    isChangingUsersOnly: boolean;
    hasExistingPaymentMethod: boolean;
    onLoadingChange: (loading: boolean) => void;
}

export function CheckoutForm({ selectedPlan, additionalUsers, isChangingUsersOnly, hasExistingPaymentMethod, onLoadingChange }: CheckoutFormProps) {
    const stripe = useStripe();
    const elements = useElements();
    const [loading, setLoading] = useState(false);

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault();
        if (!stripe || !elements) return;

        setLoading(true);
        onLoadingChange(true);

        try {
            // Se não tem método de pagamento salvo, precisa criar um novo
            if (!hasExistingPaymentMethod) {
                const { error, paymentMethod } = await stripe.createPaymentMethod({
                    type: 'card',
                    card: elements.getElement(CardElement)!,
                });

                if (error) {
                    throw new Error(error.message);
                }

                // Envia para o backend
                const response = await fetch('/billing/update-subscription', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content || '',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({
                        plan_id: selectedPlan,
                        additional_users: additionalUsers,
                        payment_method_id: paymentMethod.id,
                    }),
                });

                const data = await response.json();

                if (!response.ok) {
                    throw new Error(data.error || 'Erro ao atualizar assinatura');
                }
                toast.success(data.message || 'Assinatura atualizada com sucesso!');

                window.location.reload();
            } else {
                // Já tem método de pagamento
                const requestBody: any = {
                    additional_users: additionalUsers,
                };

                // Só envia plan_id se estiver mudando de plano, não apenas usuários
                if (selectedPlan && !isChangingUsersOnly) {
                    requestBody.plan_id = selectedPlan;
                }

                const response = await fetch('/billing/update-subscription', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content || '',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify(requestBody),
                });

                const data = await response.json();

                if (!response.ok) {
                    throw new Error(data.error || 'Erro ao atualizar assinatura');
                }

                toast.success(data.message || 'Assinatura atualizada com sucesso!');
                window.location.reload();
            }
        } catch (error: any) {
            toast.error(error.message || 'Erro ao processar o pagamento');
        } finally {
            setLoading(false);
            onLoadingChange(false);
        }
    };

    const getButtonText = () => {
        if (loading) return 'Processando...';

        if (!hasExistingPaymentMethod) {
            return 'Confirmar Assinatura';
        }

        if (isChangingUsersOnly) {
            return 'Atualizar Usuários Adicionais';
        }

        if (selectedPlan) {
            return 'Confirmar Alteração de Plano';
        }

        return 'Confirmar Alterações';
    };

    return (
        <form onSubmit={handleSubmit} className="space-y-4">
            {!hasExistingPaymentMethod && (
                <div className="space-y-3">
                    <Label>Informações do Cartão</Label>
                    <div className="rounded-lg border p-3">
                        <CardElement
                            options={{
                                style: {
                                    base: {
                                        fontSize: '16px',
                                        color: '#424770',
                                        '::placeholder': {
                                            color: '#aab7c4',
                                        },
                                    },
                                },
                            }}
                        />
                    </div>
                    <p className="text-sm text-muted-foreground">Seus dados de cartão serão salvos para pagamentos futuros</p>
                </div>
            )}

            <Button type="submit" disabled={!stripe || loading} className="w-full" size="lg">
                {getButtonText()}
            </Button>

            {hasExistingPaymentMethod && <p className="text-center text-sm text-muted-foreground">Será usado o método de pagamento já cadastrado</p>}
        </form>
    );
}
