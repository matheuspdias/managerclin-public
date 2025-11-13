import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Download, ExternalLink, FileText } from 'lucide-react';

interface InvoicesListProps {
    invoices: any[];
}

export function InvoicesList({ invoices }: InvoicesListProps) {
    const formatCurrency = (amount: number) => {
        return new Intl.NumberFormat('pt-BR', {
            style: 'currency',
            currency: 'BRL',
        }).format(amount);
    };

    const formatDate = (dateString: string) => {
        return new Date(dateString).toLocaleDateString('pt-BR');
    };

    const getStatusBadge = (invoice: any) => {
        if (invoice.paid) {
            return <Badge className="bg-green-500">Pago</Badge>;
        }
        if (invoice.attempted && !invoice.paid) {
            return <Badge className="bg-red-500">Falhou</Badge>;
        }
        return <Badge variant="outline">Pendente</Badge>;
    };

    const getStatusColor = (invoice: any) => {
        if (invoice.paid) return 'text-green-600 bg-green-100';
        if (invoice.attempted && !invoice.paid) return 'text-red-600 bg-red-100';
        return 'text-yellow-600 bg-yellow-100';
    };

    if (invoices.length === 0) {
        return (
            <Card>
                <CardHeader>
                    <CardTitle>Faturas</CardTitle>
                    <CardDescription>Histórico de cobranças</CardDescription>
                </CardHeader>
                <CardContent>
                    <div className="flex flex-col items-center justify-center py-8 text-center">
                        <FileText className="mb-4 h-12 w-12 text-muted-foreground" />
                        <p className="text-muted-foreground">Nenhuma fatura encontrada.</p>
                    </div>
                </CardContent>
            </Card>
        );
    }

    return (
        <Card>
            <CardHeader>
                <CardTitle>Faturas</CardTitle>
                <CardDescription>Histórico de cobranças</CardDescription>
            </CardHeader>
            <CardContent>
                <div className="space-y-4">
                    {invoices.map((invoice) => (
                        <div key={invoice.id} className="flex items-center justify-between rounded-lg border p-4">
                            <div className="flex-1">
                                <span className={`rounded px-2 py-1 text-xs ${getStatusColor(invoice)}`}>
                                    {invoice.paid ? 'Pago' : invoice.attempted ? 'Falhou' : 'Pendente'}
                                </span>

                                <div className="flex items-center space-x-2">
                                    <span className="font-medium">Fatura #{invoice.number}</span>
                                    {getStatusBadge(invoice)}
                                </div>
                                <p className="text-sm text-muted-foreground">Emitida em: {formatDate(invoice.date)}</p>
                                {invoice.due_date && <p className="text-sm text-muted-foreground">Vencimento: {formatDate(invoice.due_date)}</p>}
                                <p className="mt-1 text-sm font-medium">Valor: {formatCurrency(invoice.amount_due)}</p>
                            </div>
                            <div className="flex space-x-2">
                                {invoice.invoice_pdf && (
                                    <Button variant="outline" size="sm" asChild>
                                        <a href={invoice.invoice_pdf} target="_blank" rel="noopener noreferrer" download>
                                            <Download className="mr-1 h-4 w-4" />
                                            PDF
                                        </a>
                                    </Button>
                                )}
                                {invoice.hosted_invoice_url && (
                                    <Button variant="outline" size="sm" asChild>
                                        <a href={invoice.hosted_invoice_url} target="_blank" rel="noopener noreferrer">
                                            <ExternalLink className="mr-1 h-4 w-4" />
                                            Ver
                                        </a>
                                    </Button>
                                )}
                            </div>
                        </div>
                    ))}
                </div>
            </CardContent>
        </Card>
    );
}
