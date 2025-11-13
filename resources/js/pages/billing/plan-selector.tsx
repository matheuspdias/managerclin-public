import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Check, Crown, Rocket, Sparkles } from 'lucide-react';

interface Plan {
    id: string;
    name: string;
    base_price: number;
    description: string;
    features: string[];
}

interface PlanSelectorProps {
    currentPlan: Plan | null;
    availablePlans: Plan[];
    onSelectPlan: (planId: string) => void;
    isUpdating?: boolean;
}

const getPlanIcon = (planId: string) => {
    switch (planId) {
        case 'essencial':
            return <Rocket className="h-6 w-6 text-primary" />;
        case 'pro':
            return <Sparkles className="h-6 w-6 text-primary" />;
        case 'premium':
            return <Crown className="h-6 w-6 text-primary" />;
        default:
            return null;
    }
};

const getPlanAICredits = (planId: string): number => {
    switch (planId) {
        case 'essencial':
            return 100;
        case 'pro':
            return 400;
        case 'premium':
            return 2000;
        default:
            return 0;
    }
};

export function PlanSelector({ currentPlan, availablePlans, onSelectPlan, isUpdating = false }: PlanSelectorProps) {
    const isCurrentPlan = (planId: string) => currentPlan?.id === planId;

    return (
        <div className="space-y-6 py-2">
            <div className="grid gap-6 grid-cols-1 lg:grid-cols-3">
                {availablePlans.map((plan) => {
                    const isCurrent = isCurrentPlan(plan.id);
                    const isPro = plan.id === 'pro';

                    return (
                        <Card
                            key={plan.id}
                            className={`relative transition-all duration-200 h-full flex flex-col ${
                                isCurrent
                                    ? 'border-2 border-primary shadow-lg'
                                    : isPro
                                    ? 'border-2 border-primary/50 shadow-md'
                                    : 'border border-border hover:shadow-md'
                            }`}
                        >
                            {isCurrent && (
                                <div className="absolute -top-3 left-1/2 -translate-x-1/2">
                                    <Badge className="bg-primary text-primary-foreground">
                                        Plano Atual
                                    </Badge>
                                </div>
                            )}

                            {isPro && !isCurrent && (
                                <div className="absolute -top-3 left-1/2 -translate-x-1/2">
                                    <Badge className="bg-gradient-to-r from-purple-500 to-pink-500 text-white">
                                        Popular
                                    </Badge>
                                </div>
                            )}

                            <CardHeader className="pb-4">
                                <div className="flex items-center justify-between mb-2">
                                    <CardTitle className="text-xl text-foreground">
                                        {plan.name}
                                    </CardTitle>
                                    {getPlanIcon(plan.id)}
                                </div>
                                <div className="mb-2">
                                    <span className="text-3xl font-bold text-primary">
                                        R$ {plan.base_price.toFixed(2)}
                                    </span>
                                    <span className="text-muted-foreground text-sm">/mês</span>
                                </div>
                                <CardDescription className="text-xs">
                                    {plan.description}
                                </CardDescription>
                            </CardHeader>

                            <CardContent className="space-y-4 flex-1 flex flex-col">
                                {/* AI Credits Badge */}
                                <div className="bg-gradient-to-r from-purple-50 to-pink-50 dark:from-purple-950/20 dark:to-pink-950/20 p-3 rounded-lg border border-purple-200 dark:border-purple-800">
                                    <div className="flex items-center justify-between">
                                        <span className="text-sm font-semibold text-foreground">
                                            {getPlanAICredits(plan.id).toLocaleString('pt-BR')} consultas IA/mês
                                        </span>
                                        <div className="bg-gradient-to-r from-purple-500 to-pink-500 text-white px-2 py-1 rounded-full text-xs font-bold">
                                            IA
                                        </div>
                                    </div>
                                </div>

                                <ul className="space-y-2 flex-1">
                                    {plan.features.slice(0, 5).map((feature, index) => (
                                        <li key={index} className="flex items-start gap-2 text-sm">
                                            <Check className="h-4 w-4 text-green-500 flex-shrink-0 mt-0.5" />
                                            <span className="text-foreground">{feature}</span>
                                        </li>
                                    ))}
                                    {plan.features.length > 5 && (
                                        <li className="text-xs text-muted-foreground pl-6">
                                            +{plan.features.length - 5} recursos
                                        </li>
                                    )}
                                </ul>

                                <Button
                                    onClick={() => onSelectPlan(plan.id)}
                                    disabled={isCurrent || isUpdating}
                                    variant={isCurrent ? 'outline' : isPro ? 'default' : 'outline'}
                                    className={`w-full ${
                                        isPro && !isCurrent
                                            ? 'bg-gradient-to-r from-purple-500 to-pink-500 hover:from-purple-600 hover:to-pink-600'
                                            : ''
                                    }`}
                                >
                                    {isCurrent ? (
                                        'Plano Atual'
                                    ) : isUpdating ? (
                                        'Processando...'
                                    ) : (
                                        'Selecionar'
                                    )}
                                </Button>
                            </CardContent>
                        </Card>
                    );
                })}
            </div>
        </div>
    );
}
