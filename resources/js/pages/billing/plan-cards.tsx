import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Check, Crown, Rocket, Sparkles } from 'lucide-react';
import React from 'react';
import axios from 'axios';

interface Plan {
    id: string;
    name: string;
    price: number;
    description: string;
    features: string[];
    aiCredits: number;
    maxUsers: number;
    popular?: boolean;
}

const plans: Plan[] = [
    {
        id: 'essencial',
        name: 'Essencial',
        price: 79.90,
        description: 'Perfeito para clínicas iniciantes que querem organizar e digitalizar seus processos básicos.',
        aiCredits: 100,
        maxUsers: 2,
        features: [
            'Dashboard completo',
            'Agenda digital avançada',
            'Prontuário eletrônico',
            'Controle de pacientes',
            'Controle de funcionários',
            'Controle de consultórios',
            'Múltiplos serviços',
            'Atestado com assinatura digital',
            'Até 2 usuários'
        ]
    },
    {
        id: 'pro',
        name: 'Pro',
        price: 149.00,
        description: 'Para clínicas em crescimento que precisam de automação e gestão financeira completa.',
        aiCredits: 400,
        maxUsers: 5,
        popular: true,
        features: [
            'Tudo do plano Essencial',
            'Lembretes automáticos WhatsApp',
            'Módulo financeiro completo',
            'Controle de estoque',
            'Relatórios avançados',
            'Integração bancária',
            'Suporte prioritário',
            'Até 5 usuários'
        ]
    },
    {
        id: 'premium',
        name: 'Premium',
        price: 249.00,
        description: 'Para clínicas inovadoras que querem o máximo de tecnologia, incluindo IA médica avançada.',
        aiCredits: 2000,
        maxUsers: 10,
        features: [
            'Tudo do plano Pro',
            'Campanhas de Marketing para WhatsApp',
            'Suporte prioritário 24/7',
            'Até 10 usuários'
        ]
    }
];

export function PlanCards() {
    const [isLoading, setIsLoading] = React.useState<string | null>(null);

    const handleSelectPlan = async (planId: string) => {
        setIsLoading(planId);

        try {
            const response = await axios.post('/billing/checkout', {
                plan: planId
            });

            // Redirecionar para o Stripe Checkout
            if (response.data.url) {
                window.location.href = response.data.url;
            }
        } catch (error: any) {
            console.error('Erro ao processar checkout:', error);
            const errorMessage = error.response?.data?.error || 'Erro ao criar checkout. Tente novamente.';
            alert(errorMessage);
            setIsLoading(null);
        }
    };

    return (
        <div className="space-y-6">
            <div className="text-center max-w-3xl mx-auto">
                <h2 className="text-3xl font-bold text-foreground mb-4">
                    Escolha o Plano Ideal para sua Clínica
                </h2>
                <p className="text-muted-foreground">
                    Você está em período de trial. Escolha um plano para continuar aproveitando todos os recursos do ManagerClin.
                </p>
            </div>

            <div className="grid gap-6 md:grid-cols-3">
                {plans.map((plan) => (
                    <Card
                        key={plan.id}
                        className={`relative transition-all duration-300 hover:shadow-lg ${
                            plan.popular
                                ? 'border-2 border-primary shadow-xl scale-105'
                                : 'border border-border'
                        }`}
                    >
                        {plan.popular && (
                            <div className="absolute -top-4 left-1/2 -translate-x-1/2 bg-gradient-to-r from-purple-500 to-pink-500 text-white px-4 py-1 rounded-full text-sm font-semibold flex items-center gap-1">
                                <Sparkles className="h-3 w-3" />
                                Mais Popular
                            </div>
                        )}

                        <CardHeader className="pb-4">
                            <div className="flex items-center justify-between mb-2">
                                <CardTitle className="text-2xl font-bold text-foreground">
                                    {plan.name}
                                </CardTitle>
                                {plan.id === 'essencial' && <Rocket className="h-6 w-6 text-primary" />}
                                {plan.id === 'pro' && <Sparkles className="h-6 w-6 text-primary" />}
                                {plan.id === 'premium' && <Crown className="h-6 w-6 text-primary" />}
                            </div>
                            <div className="flex items-baseline gap-1 mb-3">
                                <span className="text-4xl font-bold text-primary">
                                    R$ {plan.price}
                                </span>
                                <span className="text-muted-foreground">/mês</span>
                            </div>
                            <CardDescription className="text-sm">
                                {plan.description}
                            </CardDescription>
                        </CardHeader>

                        <CardContent className="space-y-6">
                            {/* AI Credits Badge */}
                            <div className="bg-gradient-to-r from-purple-50 to-pink-50 dark:from-purple-950/20 dark:to-pink-950/20 p-3 rounded-lg border border-purple-200 dark:border-purple-800">
                                <div className="flex items-center justify-between">
                                    <span className="text-sm font-semibold text-foreground">
                                        {plan.aiCredits.toLocaleString('pt-BR')} consultas IA/mês
                                    </span>
                                    <div className="bg-gradient-to-r from-purple-500 to-pink-500 text-white px-2 py-1 rounded-full text-xs font-bold">
                                        IA
                                    </div>
                                </div>
                            </div>

                            {/* Features List */}
                            <ul className="space-y-2.5">
                                {plan.features.map((feature, index) => (
                                    <li key={index} className="flex items-start gap-2">
                                        <Check className="h-5 w-5 text-green-500 flex-shrink-0 mt-0.5" />
                                        <span className="text-sm text-foreground">{feature}</span>
                                    </li>
                                ))}
                            </ul>

                            {/* Additional Info */}
                            <div className="pt-4 border-t border-border">
                                <div className="text-center text-sm text-muted-foreground">
                                    <div className="flex items-center justify-center gap-1">
                                        <span className="font-semibold text-foreground">Até {plan.maxUsers} usuário{plan.maxUsers > 1 ? 's' : ''}</span>
                                    </div>
                                </div>
                            </div>

                            {/* CTA Button */}
                            <Button
                                onClick={() => handleSelectPlan(plan.id)}
                                disabled={isLoading !== null}
                                className={`w-full ${
                                    plan.popular
                                        ? 'bg-gradient-to-r from-purple-500 to-pink-500 hover:from-purple-600 hover:to-pink-600'
                                        : ''
                                }`}
                                variant={plan.popular ? 'default' : 'outline'}
                            >
                                {isLoading === plan.id ? (
                                    'Processando...'
                                ) : plan.popular ? (
                                    <>
                                        <Sparkles className="mr-2 h-4 w-4" />
                                        Começar Agora
                                    </>
                                ) : (
                                    'Selecionar Plano'
                                )}
                            </Button>
                        </CardContent>
                    </Card>
                ))}
            </div>
        </div>
    );
}
