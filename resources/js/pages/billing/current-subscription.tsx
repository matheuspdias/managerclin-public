import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { CreditCard, ExternalLink, CheckCircle, AlertCircle, Clock, DollarSign } from 'lucide-react';

interface CurrentSubscriptionProps {
    company: any;
    subscription: any;
    currentPlan: any;
    billingPortalUrl: string | null;
}

export function CurrentSubscription({
    company,
    subscription,
    currentPlan,
    billingPortalUrl,
}: CurrentSubscriptionProps) {
    // Acesse os dados do método de pagamento diretamente do objeto
    const defaultPaymentMethod = company.default_payment_method;

    // Verifica o status da assinatura
    const getSubscriptionStatus = () => {
        if (!subscription) return 'inactive';
        if (subscription.ends_at) return 'cancelling';
        if (subscription.active) return 'active';
        return 'inactive';
    };

    const status = getSubscriptionStatus();
    const statusLabels = {
        active: 'Ativo',
        cancelling: 'Cancelando',
        inactive: 'Inativo',
    };

    return (
        <Card className="bg-card shadow-sm">
            <CardHeader>
                <CardTitle className="flex items-center text-foreground">
                    <CheckCircle className="mr-2 h-5 w-5 text-muted-foreground" />
                    Assinatura Atual
                </CardTitle>
                <CardDescription>
                    Informações da sua assinatura atual e método de pagamento
                </CardDescription>
            </CardHeader>

            <CardContent className="p-6 space-y-6">
                {/* Current Plan */}
                <div className="space-y-3">
                    <h3 className="flex items-center text-lg font-semibold text-foreground">
                        <DollarSign className="mr-2 h-5 w-5 text-muted-foreground" />
                        Plano Atual
                    </h3>
                    {subscription ? (
                        <div className="flex items-center justify-between rounded-lg bg-muted/50 p-4 border border-border">
                            <div className="flex items-center space-x-3">
                                <div className="flex h-10 w-10 items-center justify-center rounded-full bg-muted">
                                    <DollarSign className="h-5 w-5 text-muted-foreground" />
                                </div>
                                <div>
                                    <span className="block font-semibold text-foreground">
                                        {currentPlan?.name || 'Plano Principal'}
                                    </span>
                                    {currentPlan?.price && (
                                        <span className="text-sm text-muted-foreground">
                                            {currentPlan.price}
                                        </span>
                                    )}
                                    {subscription.ends_at && (
                                        <span className="flex items-center text-sm text-amber-600 dark:text-amber-500">
                                            <Clock className="mr-1 h-3 w-3" />
                                            Expira em: {new Date(subscription.ends_at).toLocaleDateString()}
                                        </span>
                                    )}
                                </div>
                            </div>
                            <Badge
                                className={`${status === 'active'
                                    ? 'bg-green-500 text-white dark:bg-green-600'
                                    : status === 'cancelling'
                                        ? 'bg-yellow-500 text-white dark:bg-yellow-600'
                                        : 'bg-muted text-muted-foreground'
                                    }`}
                            >
                                {statusLabels[status]}
                            </Badge>
                        </div>
                    ) : (
                        <div className="rounded-lg bg-muted/50 p-4 border border-border">
                            <p className="flex items-center text-muted-foreground">
                                <AlertCircle className="mr-2 h-4 w-4" />
                                Nenhum plano ativo
                            </p>
                        </div>
                    )}
                </div>

                {/* Payment Method */}
                <div className="space-y-3">
                    <h3 className="flex items-center text-lg font-semibold text-foreground">
                        <CreditCard className="mr-2 h-5 w-5 text-muted-foreground" />
                        Método de Pagamento
                    </h3>
                    {defaultPaymentMethod ? (
                        <div className="flex items-center justify-between rounded-lg bg-muted/50 p-4 border border-border">
                            <div className="flex items-center space-x-3">
                                <div className="flex h-10 w-10 items-center justify-center rounded-full bg-muted">
                                    <CreditCard className="h-5 w-5 text-muted-foreground" />
                                </div>
                                <span className="font-medium text-foreground">
                                    {defaultPaymentMethod.card?.brand?.toUpperCase()} •••• {defaultPaymentMethod.card?.last4}
                                </span>
                            </div>
                            <Badge variant="outline">
                                Padrão
                            </Badge>
                        </div>
                    ) : (
                        <div className="rounded-lg bg-muted/50 p-4 border border-border">
                            <p className="flex items-center text-muted-foreground">
                                <AlertCircle className="mr-2 h-4 w-4" />
                                Nenhum método de pagamento cadastrado
                            </p>
                        </div>
                    )}
                </div>

                {/* Manage Subscription Button */}
                {billingPortalUrl && subscription && (
                    <Button
                        variant="outline"
                        className="w-full"
                        asChild
                    >
                        <a href={billingPortalUrl} target="_blank" rel="noopener noreferrer">
                            <ExternalLink className="mr-2 h-4 w-4" />
                            Gerenciar Assinatura
                        </a>
                    </Button>
                )}
            </CardContent>
        </Card>
    );
}
