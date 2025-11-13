import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/react';
import { useState } from 'react';
import { Brain, Zap, Star, Crown, ShoppingCart } from 'lucide-react';
import axios from 'axios';

// Components
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
} from '@/components/ui/alert-dialog';

// Types
interface AICreditsPageProps {
    credits: {
        current_credits: number;
        additional_credits: number;
        total_credits: number;
        last_purchase: string | null;
    };
    packages: Array<{
        price_id: string;
        name: string;
        credits: number;
        price: number;
        price_formatted: string;
        description: string;
        popular: boolean;
    }>;
    company?: {
        id: number;
        name: string;
        has_default_payment_method: boolean;
        default_payment_method?: any;
    };
    error?: string;
    success_message?: string;
}

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Créditos de IA', href: '/ai-credits' },
];

const getPackageIcon = (index: number) => {
    const icons = [Zap, Star, Crown];
    return icons[index] || Brain;
};

const getPackageGradient = (index: number) => {
    const gradients = ['from-blue-500 to-cyan-500', 'from-purple-500 to-pink-500', 'from-amber-500 to-orange-500'];
    return gradients[index] || 'from-gray-500 to-gray-600';
};

export default function AICreditsPage({ credits, packages, company, error, success_message }: AICreditsPageProps) {
    const [selectedPackage, setSelectedPackage] = useState<string | null>(null);
    const [isLoading, setIsLoading] = useState(false);
    const [showConfirmDialog, setShowConfirmDialog] = useState(false);
    const [isPurchasing, setIsPurchasing] = useState(false);

    const handlePackageSelect = (priceId: string) => {
        setSelectedPackage(priceId);
        setShowConfirmDialog(true);
    };

    const handleConfirmPurchase = async () => {
        if (!selectedPackage) return;

        setShowConfirmDialog(false);
        setIsPurchasing(true);

        try {
            // Se tem cartão salvo, comprar direto
            if (company?.has_default_payment_method) {
                const response = await axios.post('/ai-credits/purchase-with-saved-card', {
                    price_id: selectedPackage,
                });

                if (response.data.success) {
                    // Recarregar página para atualizar créditos
                    router.reload();
                } else {
                    throw new Error(response.data.error || 'Erro ao processar pagamento');
                }
            } else {
                // Redirecionar para checkout do Stripe
                const response = await axios.post('/ai-credits/create-payment-intent', {
                    price_id: selectedPackage,
                });

                if (response.data.checkout_url) {
                    window.location.href = response.data.checkout_url;
                }
            }
        } catch (error: any) {
            console.error('Erro ao comprar créditos:', error);
            const errorMessage = error.response?.data?.error || error.message || 'Erro ao processar pagamento';
            alert(errorMessage);
        } finally {
            setIsPurchasing(false);
            setSelectedPackage(null);
        }
    };

    if (error) {
        return (
            <AppLayout breadcrumbs={breadcrumbs}>
                <Head title="Créditos de IA" />
                <div className="min-h-screen bg-background flex items-center justify-center">
                    <Card className="max-w-md">
                        <CardContent className="p-6 text-center">
                            <div className="text-red-500 mb-4">
                                <Brain className="h-12 w-12 mx-auto" />
                            </div>
                            <h2 className="text-xl font-semibold text-foreground mb-2">Erro</h2>
                            <p className="text-muted-foreground">{error}</p>
                        </CardContent>
                    </Card>
                </div>
            </AppLayout>
        );
    }

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Créditos de IA" />
            <div className="min-h-screen bg-background">
                <div className="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
                    <div className="space-y-8">
                        {/* Header */}
                        <div className="text-center">
                            <h1 className="text-3xl font-bold text-foreground">Créditos de IA</h1>
                            <p className="mt-2 text-lg text-muted-foreground">
                                Compre créditos para usar o assistente virtual com IA
                            </p>
                            {success_message && (
                                <div className="mt-4 p-4 bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 rounded-lg">
                                    {success_message}
                                </div>
                            )}
                        </div>

                        {/* Current Credits Status */}
                        <Card className="bg-card shadow-sm">
                            <CardHeader>
                                <CardTitle className="flex items-center text-foreground">
                                    <Brain className="mr-2 h-5 w-5 text-muted-foreground" />
                                    Seus Créditos
                                </CardTitle>
                            </CardHeader>
                            <CardContent>
                                <div className="grid gap-6 md:grid-cols-3">
                                    <div className="text-center">
                                        <p className="text-4xl font-bold text-foreground">{credits.total_credits}</p>
                                        <p className="text-sm text-muted-foreground">Créditos Totais</p>
                                    </div>
                                    <div className="text-center space-y-2">
                                        <div>
                                            <p className="text-2xl font-semibold text-blue-600">{credits.current_credits}</p>
                                            <p className="text-xs text-muted-foreground">Créditos do Plano</p>
                                        </div>
                                        <div>
                                            <p className="text-2xl font-semibold text-green-600">{credits.additional_credits}</p>
                                            <p className="text-xs text-muted-foreground">Créditos Adicionais</p>
                                        </div>
                                    </div>
                                    <div className="text-center">
                                        <p className="text-sm text-muted-foreground">
                                            {credits.last_purchase
                                                ? `Última compra: ${new Date(credits.last_purchase).toLocaleDateString()}`
                                                : 'Nenhuma compra realizada'
                                            }
                                        </p>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>

                        {/* Packages */}
                        <div className="space-y-6">
                            <div className="text-center">
                                <h2 className="text-2xl font-bold text-foreground">Escolha seu Pacote</h2>
                                <p className="mt-2 text-muted-foreground">
                                    Selecione o pacote ideal para suas necessidades
                                </p>
                            </div>

                            <div className="grid gap-6 md:grid-cols-3">
                                {packages.map((pkg, index) => {
                                    const IconComponent = getPackageIcon(index);
                                    const gradientClass = getPackageGradient(index);

                                    return (
                                        <Card
                                            key={pkg.price_id}
                                            className="group relative transition-all duration-300 hover:shadow-lg border border-border hover:border-purple-300"
                                        >
                                            {pkg.popular && (
                                                <div className="absolute -top-3 left-1/2 -translate-x-1/2">
                                                    <Badge className="bg-gradient-to-r from-purple-500 to-pink-500 text-white shadow-lg">
                                                        <Star className="mr-1 h-3 w-3" />
                                                        Mais Popular
                                                    </Badge>
                                                </div>
                                            )}

                                            {/* Gradient Header */}
                                            <div className={`h-2 bg-gradient-to-r ${gradientClass}`}></div>

                                            <CardHeader className="pb-4">
                                                <div className="flex items-center justify-between">
                                                    <div className="flex items-center space-x-3">
                                                        <div className={`flex h-12 w-12 items-center justify-center rounded-full bg-gradient-to-r ${gradientClass} text-white shadow-lg`}>
                                                            <IconComponent className="h-6 w-6" />
                                                        </div>
                                                        <div>
                                                            <CardTitle className="text-xl font-bold text-foreground">{pkg.name}</CardTitle>
                                                        </div>
                                                    </div>
                                                </div>
                                                <CardDescription className="text-3xl font-bold text-foreground mt-2">
                                                    {pkg.price_formatted}
                                                </CardDescription>
                                            </CardHeader>

                                            <CardContent className="pt-0">
                                                <div className="mb-6">
                                                    <p className="text-sm text-muted-foreground leading-relaxed">{pkg.description}</p>
                                                </div>

                                                <div className="mb-6 text-center">
                                                    <p className="text-2xl font-bold text-foreground">{pkg.credits}</p>
                                                    <p className="text-sm text-muted-foreground">Créditos</p>
                                                </div>

                                                <Button
                                                    variant="outline"
                                                    className="w-full border-border text-foreground hover:bg-muted transition-all duration-200"
                                                    disabled={isPurchasing}
                                                    onClick={(e) => {
                                                        e.stopPropagation();
                                                        handlePackageSelect(pkg.price_id);
                                                    }}
                                                >
                                                    <ShoppingCart className="mr-2 h-4 w-4" />
                                                    Comprar
                                                </Button>
                                            </CardContent>
                                        </Card>
                                    );
                                })}
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            {/* Loading Overlay */}
            {isPurchasing && (
                <div className="fixed inset-0 bg-background/80 backdrop-blur-sm z-50 flex items-center justify-center">
                    <div className="bg-card p-8 rounded-lg shadow-lg flex flex-col items-center gap-4">
                        <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-primary"></div>
                        <p className="text-lg font-medium text-foreground">Processando compra...</p>
                    </div>
                </div>
            )}

            {/* Confirmation Dialog */}
            <AlertDialog open={showConfirmDialog} onOpenChange={setShowConfirmDialog}>
                <AlertDialogContent>
                    <AlertDialogHeader>
                        <AlertDialogTitle>Confirmar Compra de Créditos</AlertDialogTitle>
                        <AlertDialogDescription className="space-y-4">
                            {selectedPackage && (() => {
                                const pkg = packages.find(p => p.price_id === selectedPackage);
                                if (!pkg) return null;

                                return (
                                    <>
                                        <div>
                                            Você está prestes a comprar o <strong>{pkg.name}</strong>.
                                        </div>

                                        <div className="rounded-lg bg-muted p-4 space-y-2">
                                            <div className="flex justify-between text-sm">
                                                <span className="text-muted-foreground">Pacote:</span>
                                                <span className="font-medium">{pkg.name}</span>
                                            </div>
                                            <div className="flex justify-between text-sm">
                                                <span className="text-muted-foreground">Créditos:</span>
                                                <span className="font-medium">{pkg.credits} créditos</span>
                                            </div>
                                            <div className="flex justify-between text-base font-bold border-t border-border pt-2 mt-2">
                                                <span>Total:</span>
                                                <span className="text-primary">{pkg.price_formatted}</span>
                                            </div>
                                        </div>

                                        {company?.has_default_payment_method ? (
                                            <div className="text-sm text-muted-foreground">
                                                <p className="flex items-center gap-2">
                                                    <span className="text-green-600">✓</span>
                                                    Será cobrado no cartão {company.default_payment_method?.card?.brand?.toUpperCase()} •••• {company.default_payment_method?.card?.last4}
                                                </p>
                                            </div>
                                        ) : (
                                            <p className="text-sm text-muted-foreground">
                                                Você será redirecionado para o checkout seguro do Stripe.
                                            </p>
                                        )}
                                    </>
                                );
                            })()}
                        </AlertDialogDescription>
                    </AlertDialogHeader>
                    <AlertDialogFooter>
                        <AlertDialogCancel>Cancelar</AlertDialogCancel>
                        <AlertDialogAction
                            onClick={handleConfirmPurchase}
                            className="bg-gradient-to-r from-purple-500 to-pink-500 hover:from-purple-600 hover:to-pink-600"
                        >
                            Confirmar Compra
                        </AlertDialogAction>
                    </AlertDialogFooter>
                </AlertDialogContent>
            </AlertDialog>
        </AppLayout>
    );
}
