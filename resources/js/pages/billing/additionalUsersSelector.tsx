import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Minus, Plus } from 'lucide-react';
import { useState } from 'react';

interface AdditionalUsersSelectorProps {
    currentCount: number;
    pricePerUser: string;
    includedUsers: number;
    currentUsersCount: number;
    onChange: (count: number) => void;
}

export function AdditionalUsersSelector({ currentCount, pricePerUser, includedUsers, currentUsersCount, onChange }: AdditionalUsersSelectorProps) {
    const [count, setCount] = useState(currentCount);

    const handleIncrement = () => {
        const newCount = count + 1;
        setCount(newCount);
        onChange(newCount);
    };

    const handleDecrement = () => {
        if (count > 0) {
            const newCount = count - 1;
            setCount(newCount);
            onChange(newCount);
        }
    };

    const totalAdditionalCost = count * parseFloat(pricePerUser);
    const totalUsers = includedUsers + count;

    return (
        <Card>
            <CardHeader>
                <CardTitle>Usuários Adicionais</CardTitle>
                <CardDescription>
                    Seu plano inclui {includedUsers} usuário(s). Atualmente você tem {currentUsersCount} usuário(s) cadastrado(s).
                </CardDescription>
            </CardHeader>
            <CardContent className="space-y-4">
                <div className="flex items-center justify-between">
                    <div>
                        <p className="font-medium">Quantidade de usuários adicionais</p>
                        <p className="text-sm text-muted-foreground">R$ {pricePerUser} por usuário/mês</p>
                    </div>
                    <div className="flex items-center space-x-2">
                        <Button type="button" variant="outline" size="icon" onClick={handleDecrement} disabled={count === 0}>
                            <Minus className="h-4 w-4" />
                        </Button>
                        <span className="w-8 text-center font-medium">{count}</span>
                        <Button type="button" variant="outline" size="icon" onClick={handleIncrement}>
                            <Plus className="h-4 w-4" />
                        </Button>
                    </div>
                </div>

                {count > 0 && (
                    <div className="rounded-lg bg-muted p-3">
                        <div className="flex justify-between text-sm">
                            <span>Usuários incluídos:</span>
                            <span>{includedUsers}</span>
                        </div>
                        <div className="flex justify-between text-sm">
                            <span>Usuários adicionais:</span>
                            <span>{count}</span>
                        </div>
                        <div className="flex justify-between text-sm">
                            <span>Total de usuários:</span>
                            <span className="font-medium">{totalUsers}</span>
                        </div>
                        <div className="mt-2 flex justify-between border-t pt-2 text-sm font-medium">
                            <span>Custo adicional:</span>
                            <span>R$ {totalAdditionalCost.toFixed(2)}/mês</span>
                        </div>
                    </div>
                )}
            </CardContent>
        </Card>
    );
}
