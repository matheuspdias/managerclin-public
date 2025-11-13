import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Check, Star, Zap, Crown, Sparkles } from 'lucide-react';

interface PlanCardProps {
    plan: any;
    isSelected: boolean;
    isCurrent: boolean;
    onSelect: () => void;
}

const planIcons = {
    'Essencial': Zap,
    'Pro': Star,
    'Premium': Crown,
};

const planGradients = {
    'Essencial': 'from-blue-500 to-cyan-500',
    'Pro': 'from-purple-500 to-pink-500',
    'Premium': 'from-amber-500 to-orange-500',
};

export function PlanCard({ plan, isSelected, isCurrent, onSelect }: PlanCardProps) {
    const IconComponent = planIcons[plan.name as keyof typeof planIcons] || Sparkles;
    const gradientClass = planGradients[plan.name as keyof typeof planGradients] || 'from-gray-500 to-gray-600';

    return (
        <Card
            className={`group relative cursor-pointer overflow-hidden transition-all duration-300 hover:shadow-2xl ${isSelected
                    ? 'border-2 border-purple-500 shadow-2xl scale-105'
                    : isCurrent
                        ? 'border-2 border-green-500 shadow-xl'
                        : 'border border-gray-200 hover:border-purple-300'
                } ${isCurrent ? 'bg-gradient-to-br from-green-50 to-emerald-50' : 'bg-white'}`}
        >
            {isCurrent && (
                <div className="absolute -top-2 -right-2 z-10">
                    <Badge className="bg-gradient-to-r from-green-500 to-emerald-500 text-white shadow-lg">
                        <Check className="mr-1 h-3 w-3" />
                        Plano Atual
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
                            <CardTitle className="text-xl font-bold text-gray-900">{plan.name}</CardTitle>
                            <Badge variant="outline" className="mt-1 text-xs">
                                {plan.cicle}
                            </Badge>
                        </div>
                    </div>
                </div>
                <CardDescription className="text-3xl font-bold text-gray-900 mt-2">
                    {plan.price}
                </CardDescription>
            </CardHeader>

            <CardContent className="pt-0">
                <div className="mb-6">
                    <p className="text-sm text-gray-600 leading-relaxed">{plan.description}</p>
                </div>

                <ul className="mb-6 space-y-3">
                    {plan.features.map((feature: string, index: number) => (
                        <li key={index} className="flex items-start text-sm">
                            <div className="flex-shrink-0">
                                <div className="flex h-5 w-5 items-center justify-center rounded-full bg-green-100">
                                    <Check className="h-3 w-3 text-green-600" />
                                </div>
                            </div>
                            <span className="ml-3 text-gray-700">{feature}</span>
                        </li>
                    ))}
                </ul>

                <Button
                    variant={isSelected ? 'default' : isCurrent ? 'outline' : 'default'}
                    className={`w-full transition-all duration-200 ${isSelected
                            ? 'bg-gradient-to-r from-purple-500 to-pink-500 hover:from-purple-600 hover:to-pink-600 text-white shadow-lg'
                            : isCurrent
                                ? 'border-green-500 text-green-600 hover:bg-green-50'
                                : 'bg-gradient-to-r from-gray-800 to-gray-900 hover:from-gray-700 hover:to-gray-800 text-white shadow-md'
                        }`}
                    onClick={onSelect}
                    disabled={isCurrent && !isSelected}
                >
                    {isCurrent && !isSelected ? (
                        <>
                            <Check className="mr-2 h-4 w-4" />
                            Plano Atual
                        </>
                    ) : isSelected ? (
                        <>
                            <Check className="mr-2 h-4 w-4" />
                            Selecionado
                        </>
                    ) : (
                        'Selecionar Plano'
                    )}
                </Button>
            </CardContent>

            {/* Hover Effect Overlay */}
            <div className="absolute inset-0 bg-gradient-to-r from-purple-500/5 to-pink-500/5 opacity-0 transition-opacity duration-300 group-hover:opacity-100 pointer-events-none"></div>
        </Card>
    );
}
