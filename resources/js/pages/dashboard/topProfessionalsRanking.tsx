import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Star, TrophyIcon } from 'lucide-react';

interface Professional {
    id: number;
    name: string;
    image: string | null;
    position: string;
    appointments_count: number;
    ranking: string;
}

interface TopProfessionalsRankingProps {
    ranking: Professional[];
}

export default function TopProfessionalsRanking({ ranking }: TopProfessionalsRankingProps) {
    const getRankingIcon = (index: number) => {
        switch (index) {
            case 0:
                return <TrophyIcon className="h-5 w-5 text-yellow-500" />;
            case 1:
                return <TrophyIcon className="h-5 w-5 text-gray-400" />;
            case 2:
                return <TrophyIcon className="h-5 w-5 text-amber-700" />;
            default:
                return <Star className="h-4 w-4 text-muted-foreground" />;
        }
    };

    return (
        <Card className="border-border bg-card transition-colors hover:shadow-md">
            <CardHeader>
                <CardTitle className="flex items-center gap-2 text-lg font-semibold">
                    <TrophyIcon className="h-6 w-6 text-yellow-500" />
                    Top Profissionais
                </CardTitle>
                <CardDescription>Ranking baseado em número de atendimentos</CardDescription>
            </CardHeader>
            <CardContent>
                <div className="space-y-4">
                    {ranking.map((professional, index) => (
                        <div key={professional.id} className="flex items-center gap-4 rounded-lg p-3 transition-colors hover:bg-muted/50">
                            <div className="flex items-center gap-3">
                                <div className="text-lg font-medium text-muted-foreground">#{index + 1}</div>
                                <Avatar className="h-12 w-12 border-2 border-muted">
                                    {professional.image ? (
                                        <AvatarImage src={professional.image} alt={professional.name} />
                                    ) : (
                                        <AvatarFallback className="bg-primary/10 text-primary">{professional.name[0].toUpperCase()}</AvatarFallback>
                                    )}
                                </Avatar>
                                <div>
                                    <p className="font-medium text-foreground">{professional.name}</p>
                                    <p className="text-sm text-muted-foreground">{professional.position}</p>
                                </div>
                            </div>
                            <div className="ml-auto text-right">
                                <Badge variant="secondary" className="mb-1">
                                    {getRankingIcon(index)}
                                    <span className="ml-1">{professional.ranking}</span>
                                </Badge>
                                <p className="text-sm font-semibold">{professional.appointments_count} atendimentos</p>
                            </div>
                        </div>
                    ))}
                </div>
            </CardContent>
        </Card>
    );
}
