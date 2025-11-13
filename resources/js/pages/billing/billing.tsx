import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/react';
import { CheckCircle, DollarSign, Users } from 'lucide-react';
import { useState } from 'react';
import axios from 'axios';

// Components
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
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { CurrentSubscription } from './current-subscription';
import { InvoicesList } from './invoices-list';
import { PlanCards } from './plan-cards';
import { PlanSelector } from './plan-selector';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';

// Types
interface BillingPageProps {
    company: {
        id: number;
        name: string;
        has_default_payment_method: boolean;
        default_payment_method?: any;
        current_users_count: number;
    };
    subscription: any;
    currentPlan: any;
    totalMonthly: string;
    invoices: Invoice[];
    billingPortalUrl: string | null;
}

interface Invoice {
    id: string;
    number: string;
    date: string;
    due_date: string;
    amount_due: number;
    amount_paid: number;
    status: string;
    paid: boolean;
    attempted: boolean;
    invoice_pdf: string;
    hosted_invoice_url: string;
    lines: InvoiceLine[];
}

interface InvoiceLine {
    description: string;
    amount: number;
    quantity: number;
    period: {
        start: string;
        end: string;
    };
}

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Faturamento', href: '/billing' }];

export default function BillingPage({
    company,
    subscription,
    currentPlan,
    totalMonthly,
    invoices,
    billingPortalUrl,
}: BillingPageProps) {
    // Hooks must come before any returns
    const [showChangePlanDialog, setShowChangePlanDialog] = useState(false);
    const [showConfirmPlanDialog, setShowConfirmPlanDialog] = useState(false);
    const [selectedPlan, setSelectedPlan] = useState<string | null>(null);
    const [isChangingPlan, setIsChangingPlan] = useState(false);

    // Verificação de segurança para company
    if (!company) {
        return (
            <AppLayout breadcrumbs={breadcrumbs}>
                <Head title="Faturamento" />
                <div className="min-h-screen bg-background">
                    <div className="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
                        <div className="text-center">
                            <p className="text-lg text-muted-foreground">Erro ao carregar dados da empresa. Tente novamente.</p>
                        </div>
                    </div>
                </div>
            </AppLayout>
        );
    }

    // Available plans
    const availablePlans = [
        {
            id: 'essencial',
            name: 'Essencial',
            base_price: 79.90,
            description: 'Reduza a papelada e organize suas consultas',
            features: [
                'Dashboard Completo',
                'Controle de pacientes e agendamentos',
                'Prontuario digital',
                'Controle de serviços e salas',
                'Gerador de atestados e receitas',
                'Preferência de horário de atendimento',
                '100 Créditos de IA mensais',
                'Até 2 usuários',
            ],
        },
        {
            id: 'pro',
            name: 'Pro',
            base_price: 149.00,
            description: 'Elimine as faltas com lembretes automáticos',
            features: [
                'Todos os recursos do plano Essencial',
                'Envio de WhatsApp automático',
                'Módulo financeiro completo',
                'Controle de estoque',
                'Relatórios avançados',
                '400 Créditos de IA mensais',
                'Até 5 usuários',
            ],
        },
        {
            id: 'premium',
            name: 'Premium',
            base_price: 249.00,
            description: 'Máximo de tecnologia e IA médica avançada',
            features: [
                'Todos os recursos do plano Pro',
                'Campanhas de Marketing para WhatsApp',
                'Suporte prioritário 24/7',
                '2000 Créditos de IA mensais',
                'Até 10 usuários',
            ],
        },
    ];

    const handleOpenChangePlan = () => {
        setShowChangePlanDialog(true);
    };

    const handleSelectPlan = (planId: string) => {
        setSelectedPlan(planId);
        setShowChangePlanDialog(false);
        setShowConfirmPlanDialog(true);
    };

    const handleConfirmChangePlan = async (planId: string) => {
        setIsChangingPlan(true);

        try {
            const response = await axios.post('/billing/change-plan', {
                plan: planId,
            });

            if (response.data.url) {
                // Se precisa confirmar pagamento no Stripe
                window.location.href = response.data.url;
            } else {
                // Sucesso, recarregar página
                router.reload();
            }
        } catch (error: any) {
            console.error('Erro ao trocar plano:', error);
            const errorMessage = error.response?.data?.error || 'Erro ao trocar plano. Tente novamente.';
            alert(errorMessage);
        } finally {
            setIsChangingPlan(false);
        }
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Faturamento" />
            <div className="min-h-screen bg-background relative">
                {/* Loading Overlay */}
                {isChangingPlan && (
                    <div className="fixed inset-0 bg-background/80 backdrop-blur-sm z-50 flex items-center justify-center">
                        <div className="bg-card p-8 rounded-lg shadow-lg flex flex-col items-center gap-4">
                            <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-primary"></div>
                            <p className="text-lg font-medium text-foreground">Alterando plano...</p>
                        </div>
                    </div>
                )}

                <div className="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
                    <div className="space-y-8">
                        {/* Header */}
                        <div className="flex items-center justify-between">
                            <div>
                                <h1 className="text-3xl font-bold text-foreground">Faturamento</h1>
                                <p className="mt-2 text-muted-foreground">Gerencie sua assinatura e visualize suas faturas</p>
                            </div>
                            {subscription?.active && (
                                <Button
                                    onClick={handleOpenChangePlan}
                                    variant="outline"
                                    disabled={isChangingPlan}
                                >
                                    {isChangingPlan ? 'Processando...' : 'Trocar Plano'}
                                </Button>
                            )}
                        </div>

                        {/* Show plan cards if no active subscription */}
                        {!subscription?.active ? (
                            <PlanCards />
                        ) : (
                            <>
                                {/* Status Cards */}
                                <div className="grid gap-6 md:grid-cols-3">
                                    <Card className="group bg-card shadow-sm transition-all duration-300 hover:shadow-md">
                                        <CardContent className="p-6">
                                            <div className="flex items-center">
                                                <div className="flex-shrink-0">
                                                    <div className="flex h-12 w-12 items-center justify-center rounded-lg bg-muted transition-all duration-300 group-hover:bg-muted/80">
                                                        <CheckCircle className="h-6 w-6 text-muted-foreground" />
                                                    </div>
                                                </div>
                                                <div className="ml-4">
                                                    <p className="text-sm font-medium text-muted-foreground">Status</p>
                                                    <p className="text-2xl font-semibold text-foreground">
                                                        {subscription?.active ? 'Ativo' : 'Inativo'}
                                                    </p>
                                                </div>
                                            </div>
                                        </CardContent>
                                    </Card>

                                    <Card className="group bg-card shadow-sm transition-all duration-300 hover:shadow-md">
                                        <CardContent className="p-6">
                                            <div className="flex items-center">
                                                <div className="flex-shrink-0">
                                                    <div className="flex h-12 w-12 items-center justify-center rounded-lg bg-muted transition-all duration-300 group-hover:bg-muted/80">
                                                        <Users className="h-6 w-6 text-muted-foreground" />
                                                    </div>
                                                </div>
                                                <div className="ml-4">
                                                    <p className="text-sm font-medium text-muted-foreground">Usuários</p>
                                                    <p className="text-2xl font-semibold text-foreground">{company?.current_users_count ?? 0}</p>
                                                </div>
                                            </div>
                                        </CardContent>
                                    </Card>

                                    <Card className="group bg-card shadow-sm transition-all duration-300 hover:shadow-md">
                                        <CardContent className="p-6">
                                            <div className="flex items-center">
                                                <div className="flex-shrink-0">
                                                    <div className="flex h-12 w-12 items-center justify-center rounded-lg bg-muted transition-all duration-300 group-hover:bg-muted/80">
                                                        <DollarSign className="h-6 w-6 text-muted-foreground" />
                                                    </div>
                                                </div>
                                                <div className="ml-4">
                                                    <p className="text-sm font-medium text-muted-foreground">Total Mensal</p>
                                                    <p className="text-2xl font-semibold text-foreground">R$ {totalMonthly}</p>
                                                </div>
                                            </div>
                                        </CardContent>
                                    </Card>
                                </div>

                                {/* Current Subscription */}
                                <CurrentSubscription
                                    company={company}
                                    subscription={subscription}
                                    currentPlan={currentPlan}
                                    billingPortalUrl={billingPortalUrl}
                                />
                            </>
                        )}

                        {/* Plan Details Card */}
                        {currentPlan && subscription?.active && (
                            <Card className="bg-card shadow-sm">
                                <CardHeader>
                                    <CardTitle className="text-foreground">Detalhes do Plano</CardTitle>
                                </CardHeader>
                                <CardContent className="space-y-4">
                                    <div className="flex items-center justify-between border-b border-border py-3">
                                        <span className="text-muted-foreground">Plano:</span>
                                        <span className="font-semibold text-foreground">{currentPlan.name}</span>
                                    </div>
                                    <div className="flex items-center justify-between border-b border-border py-3">
                                        <span className="text-muted-foreground">Usuários:</span>
                                        <span className="font-semibold text-foreground">
                                            {company.current_users_count} / {currentPlan.id === 'essencial' ? '2' : currentPlan.id === 'pro' ? '5' : '10'} usuários
                                        </span>
                                    </div>

                                    <div className="flex items-center justify-between border-t-2 border-border py-3 pt-6">
                                        <span className="text-lg font-bold text-foreground">Total Mensal:</span>
                                        <span className="text-lg font-bold text-foreground">
                                            R$ {currentPlan.base_price.toFixed(2)}
                                        </span>
                                    </div>
                                </CardContent>
                            </Card>
                        )}

                        {/* Invoices */}
                        <InvoicesList invoices={invoices} />
                    </div>
                </div>
            </div>

            {/* Change Plan Dialog */}
            <Dialog open={showChangePlanDialog} onOpenChange={setShowChangePlanDialog}>
                <DialogContent className="!max-w-[1400px] w-[95vw] max-h-[95vh] overflow-y-auto p-6">
                    <DialogHeader>
                        <DialogTitle>Trocar Plano</DialogTitle>
                        <DialogDescription>
                            Escolha o plano ideal para sua clínica.
                        </DialogDescription>
                    </DialogHeader>
                    <PlanSelector
                        currentPlan={currentPlan}
                        availablePlans={availablePlans}
                        onSelectPlan={handleSelectPlan}
                        isUpdating={isChangingPlan}
                    />
                </DialogContent>
            </Dialog>

            {/* Confirm Plan Change Dialog */}
            <AlertDialog open={showConfirmPlanDialog} onOpenChange={setShowConfirmPlanDialog}>
                <AlertDialogContent>
                    <AlertDialogHeader>
                        <AlertDialogTitle>Confirmar Troca de Plano</AlertDialogTitle>
                        <AlertDialogDescription className="space-y-4">
                            {selectedPlan && (() => {
                                const newPlan = availablePlans.find(p => p.id === selectedPlan);
                                const oldTotal = currentPlan ? currentPlan.base_price : 0;
                                const newTotal = newPlan ? newPlan.base_price : 0;
                                const difference = newTotal - oldTotal;

                                return (
                                    <>
                                        <div>
                                            Você está trocando do plano <strong>{currentPlan?.name}</strong> para <strong>{newPlan?.name}</strong>.
                                        </div>

                                        <div className="rounded-lg bg-muted p-4 space-y-2">
                                            <div className="flex justify-between text-sm">
                                                <span className="text-muted-foreground">Plano {newPlan?.name}:</span>
                                                <span className="font-medium">R$ {newPlan?.base_price.toFixed(2)}</span>
                                            </div>
                                            <div className="flex justify-between text-base font-bold border-t border-border pt-2 mt-2">
                                                <span>Novo Total Mensal:</span>
                                                <span className="text-primary">R$ {newTotal.toFixed(2)}</span>
                                            </div>
                                            {difference !== 0 && (
                                                <div className="text-xs text-muted-foreground text-center pt-1">
                                                    {difference > 0 ? (
                                                        <span className="text-orange-600">
                                                            ⬆ Aumento de R$ {difference.toFixed(2)}/mês
                                                        </span>
                                                    ) : (
                                                        <span className="text-green-600">
                                                            ⬇ Economia de R$ {Math.abs(difference).toFixed(2)}/mês
                                                        </span>
                                                    )}
                                                </div>
                                            )}
                                        </div>

                                        <p className="text-sm text-muted-foreground">
                                            A alteração será refletida imediatamente com ajuste proporcional (proration) na próxima fatura.
                                        </p>
                                    </>
                                );
                            })()}
                        </AlertDialogDescription>
                    </AlertDialogHeader>
                    <AlertDialogFooter>
                        <AlertDialogCancel>Cancelar</AlertDialogCancel>
                        <AlertDialogAction
                            onClick={() => {
                                if (selectedPlan) {
                                    setShowConfirmPlanDialog(false);
                                    handleConfirmChangePlan(selectedPlan);
                                }
                            }}
                            className="bg-gradient-to-r from-purple-500 to-pink-500 hover:from-purple-600 hover:to-pink-600"
                        >
                            Confirmar Troca
                        </AlertDialogAction>
                    </AlertDialogFooter>
                </AlertDialogContent>
            </AlertDialog>

        </AppLayout>
    );
}
