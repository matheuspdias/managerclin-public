import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { router } from '@inertiajs/react';
import { AlertCircle, Play, Trash2 } from 'lucide-react';

interface SubscriptionManagementProps {
    subscription: any;
}

export default function SubscriptionManagement({ subscription }: SubscriptionManagementProps) {
    // Calcula os status diretamente no render
    const isActive = subscription && !subscription.ends_at;
    const isOnGracePeriod = subscription && subscription.ends_at && new Date(subscription.ends_at) > new Date();

    const handleCancel = () => {
        if (confirm('Tem certeza que deseja cancelar sua assinatura? Ela permanecerá ativa até o final do período.')) {
            router.post(
                route('billing.cancel-subscription'),
                {},
                {
                    onSuccess: () => router.reload(),
                },
            );
        }
    };

    const handleResume = () => {
        router.post(
            route('billing.resume-subscription'),
            {},
            {
                onSuccess: () => router.reload(),
            },
        );
    };

    if (isOnGracePeriod) {
        return (
            <Card>
                <CardHeader>
                    <CardTitle>Gerenciar Assinatura</CardTitle>
                    <CardDescription>Sua assinatura está cancelada</CardDescription>
                </CardHeader>
                <CardContent>
                    <div className="rounded-md border border-yellow-200 bg-yellow-50 p-4">
                        <div className="flex items-center">
                            <AlertCircle className="mr-2 h-5 w-5 text-yellow-400" />
                            <p className="text-yellow-800">Expira em {new Date(subscription.ends_at).toLocaleDateString('pt-BR')}</p>
                        </div>
                        <Button className="mt-3" onClick={handleResume}>
                            <Play className="mr-2 h-4 w-4" />
                            Reativar Assinatura
                        </Button>
                    </div>
                </CardContent>
            </Card>
        );
    }

    if (isActive) {
        return (
            <Card>
                <CardHeader>
                    <CardTitle>Gerenciar Assinatura</CardTitle>
                    <CardDescription>Assinatura ativa</CardDescription>
                </CardHeader>
                <CardContent>
                    <div className="rounded-md border border-red-200 bg-red-50 p-4">
                        <p className="mb-3 text-red-800">Cancelar sua assinatura?</p>
                        <Button variant="destructive" onClick={handleCancel}>
                            <Trash2 className="mr-2 h-4 w-4" />
                            Cancelar Assinatura
                        </Button>
                    </div>
                </CardContent>
            </Card>
        );
    }

    return (
        <Card>
            <CardHeader>
                <CardTitle>Gerenciar Assinatura</CardTitle>
                <CardDescription>Nenhuma assinatura ativa</CardDescription>
            </CardHeader>
            <CardContent>
                <p className="text-muted-foreground">Nenhuma assinatura ativa para gerenciar.</p>
            </CardContent>
        </Card>
    );
}
