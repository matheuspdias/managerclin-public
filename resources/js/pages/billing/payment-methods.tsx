// components/billing/payment-methods.tsx
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { router } from '@inertiajs/react';
import { CreditCard, Plus, Star, Trash2 } from 'lucide-react';
import { useState } from 'react';

interface PaymentMethod {
    id: string;
    type: string;
    brand: string;
    last4: string;
    exp_month: number;
    exp_year: number;
    is_default: boolean;
}

interface PaymentMethodsProps {
    paymentMethods: PaymentMethod[];
}

export default function PaymentMethods({ paymentMethods }: PaymentMethodsProps) {
    const [showAddCard, setShowAddCard] = useState(false);

    const handleSetDefault = (methodId: string) => {
        router.post(
            route('billing.set-default-payment'),
            {
                payment_method_id: methodId,
            },
            {
                onSuccess: () => router.reload(),
            },
        );
    };

    const handleRemove = (methodId: string) => {
        if (confirm('Tem certeza que deseja remover este método de pagamento?')) {
            router.delete(route('billing.remove-payment-method', { paymentMethod: methodId }), {
                onSuccess: () => router.reload(),
            });
        }
    };

    return (
        <Card>
            <CardHeader>
                <CardTitle>Métodos de Pagamento</CardTitle>
                <CardDescription>Gerencie seus cartões de crédito</CardDescription>
            </CardHeader>
            <CardContent className="space-y-4">
                {paymentMethods?.length === 0 ? (
                    <div className="py-8 text-center">
                        <CreditCard className="mx-auto h-12 w-12 text-muted-foreground" />
                        <p className="mt-4 text-muted-foreground">Nenhum método de pagamento</p>
                    </div>
                ) : (
                    <div className="space-y-3">
                        {paymentMethods?.map((method) => (
                            <div key={method.id} className="flex items-center justify-between rounded-lg border p-3">
                                <div className="flex items-center space-x-3">
                                    <CreditCard className="h-6 w-6 text-muted-foreground" />
                                    <div>
                                        <p className="font-medium">
                                            {method.brand} •••• {method.last4}
                                        </p>
                                        <p className="text-sm text-muted-foreground">
                                            Expira em {method.exp_month.toString().padStart(2, '0')}/{method.exp_year}
                                        </p>
                                    </div>
                                </div>
                                <div className="flex items-center space-x-2">
                                    {method.is_default && <span className="rounded-full bg-green-100 px-2 py-1 text-xs text-green-800">Padrão</span>}
                                    {!method.is_default && (
                                        <Button variant="ghost" size="sm" onClick={() => handleSetDefault(method.id)}>
                                            <Star className="h-4 w-4" />
                                        </Button>
                                    )}
                                    <Button variant="ghost" size="sm" onClick={() => handleRemove(method.id)}>
                                        <Trash2 className="h-4 w-4 text-destructive" />
                                    </Button>
                                </div>
                            </div>
                        ))}
                    </div>
                )}

                <Dialog open={showAddCard} onOpenChange={setShowAddCard}>
                    <DialogTrigger asChild>
                        <Button className="w-full">
                            <Plus className="mr-2 h-4 w-4" />
                            Adicionar Cartão
                        </Button>
                    </DialogTrigger>
                    <DialogContent>
                        <DialogHeader>
                            <DialogTitle>Adicionar Novo Cartão</DialogTitle>
                        </DialogHeader>
                        <div className="py-4">
                            <p className="text-muted-foreground">Integre com Stripe Elements para capturar dados do cartão com segurança.</p>
                            <Button className="mt-4 w-full" onClick={() => setShowAddCard(false)}>
                                Fechar
                            </Button>
                        </div>
                    </DialogContent>
                </Dialog>
            </CardContent>
        </Card>
    );
}
