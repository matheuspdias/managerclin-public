import { Button } from '@/components/ui/button';
import { router } from '@inertiajs/react';
import { PaymentElement, useElements, useStripe } from '@stripe/react-stripe-js';
import { CreditCard, Loader2 } from 'lucide-react';
import { useState } from 'react';
import { toast } from 'sonner';

interface AICreditsCheckoutFormProps {
    packageId: number;
    package: {
        id: number;
        name: string;
        credits: number;
        price: number;
        price_formatted: string;
        description: string;
        popular: boolean;
    };
    company?: {
        id: number;
        name: string;
        has_default_payment_method: boolean;
        default_payment_method?: {
            id: string;
            type: string;
            card: {
                brand: string;
                last4: string;
                exp_month: number;
                exp_year: number;
            };
        };
    };
    clientSecret: string;
    onLoadingChange: (loading: boolean) => void;
}

export function AICreditsCheckoutForm({ packageId, package: pkg, company, onLoadingChange }: AICreditsCheckoutFormProps) {
    const stripe = useStripe();
    const elements = useElements();
    const [isLoading, setIsLoading] = useState(false);
    const [error, setError] = useState<string | null>(null);

    const handleSubmitWithStripe = async (event: React.FormEvent) => {
        event.preventDefault();

        if (!stripe || !elements) {
            return;
        }

        setIsLoading(true);
        onLoadingChange(true);
        setError(null);

        try {
            // Confirm payment
            const { error: stripeError, paymentIntent } = await stripe.confirmPayment({
                elements,
                confirmParams: {
                    return_url: window.location.origin + '/ai-credits',
                },
                redirect: 'if_required',
            });

            if (stripeError) {
                setError(stripeError.message || 'Erro no pagamento');
            } else if (paymentIntent && paymentIntent.status === 'succeeded') {
                // Payment successful
                toast.success('Compra realizada com sucesso! Os créditos serão adicionados em instantes.');

                // Redirect to credits page after a short delay to allow webhook processing
                setTimeout(() => {
                    router.visit('/ai-credits', {
                        preserveScroll: true,
                        onSuccess: () => {},
                    });
                }, 2000);
            }
        } catch (err) {
            console.error('Erro inesperado:', err);
            setError(err instanceof Error ? err.message : 'Erro inesperado');
        } finally {
            setIsLoading(false);
            onLoadingChange(false);
        }
    };

    const handlePurchaseWithSavedCard = async () => {
        setIsLoading(true);
        onLoadingChange(true);
        setError(null);

        try {
            const response = await fetch('/ai-credits/purchase-with-saved-card', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content || '',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({ package_id: packageId }),
            });

            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.error || 'Erro ao processar compra');
            }

            if (data.success) {
                toast.success(data.message || 'Compra realizada com sucesso!');

                // Redirect to credits page
                setTimeout(() => {
                    router.visit('/ai-credits', {
                        preserveScroll: true,
                        onSuccess: () => {},
                    });
                }, 1000);
            } else {
                setError(data.error || 'Erro ao processar pagamento');
            }
        } catch (err) {
            console.error('Erro ao comprar com cartão salvo:', err);
            setError(err instanceof Error ? err.message : 'Erro inesperado');
        } finally {
            setIsLoading(false);
            onLoadingChange(false);
        }
    };

    return (
        <div className="space-y-6">
            {/* Package Summary */}
            <div className="rounded-lg bg-gray-50 p-4">
                <h3 className="mb-2 font-semibold text-gray-900">Resumo da Compra</h3>
                <div className="flex items-center justify-between">
                    <div>
                        <p className="font-medium text-gray-900">{pkg.name}</p>
                        <p className="text-sm text-gray-600">{pkg.credits} créditos</p>
                    </div>
                    <p className="text-lg font-bold text-gray-900">{pkg.price_formatted}</p>
                </div>
            </div>

            {/* Saved Card Option */}
            {company?.has_default_payment_method && company.default_payment_method && (
                <div className="rounded-lg border border-gray-200 p-4">
                    <h3 className="mb-3 font-medium text-gray-900">Cartão Salvo</h3>
                    <div className="flex items-center justify-between">
                        <div className="flex items-center space-x-3">
                            <CreditCard className="h-5 w-5 text-gray-400" />
                            <div>
                                <p className="text-sm font-medium text-gray-900">**** **** **** {company.default_payment_method.card.last4}</p>
                                <p className="text-xs text-gray-500">
                                    {company.default_payment_method.card.brand.toUpperCase()} •
                                    {company.default_payment_method.card.exp_month.toString().padStart(2, '0')}/
                                    {company.default_payment_method.card.exp_year}
                                </p>
                            </div>
                        </div>
                        <Button
                            onClick={handlePurchaseWithSavedCard}
                            disabled={isLoading}
                            className="bg-gradient-to-r from-green-500 to-green-600 text-white hover:from-green-600 hover:to-green-700"
                        >
                            {isLoading ? (
                                <>
                                    <Loader2 className="mr-2 h-4 w-4 animate-spin" />
                                    Processando...
                                </>
                            ) : (
                                'Comprar com este cartão'
                            )}
                        </Button>
                    </div>
                    <p className="mt-2 text-xs text-gray-500">Será cobrado no cartão que você já tem cadastrado</p>
                </div>
            )}

            {/* Divider */}
            {company?.has_default_payment_method && (
                <div className="relative">
                    <div className="absolute inset-0 flex items-center">
                        <div className="w-full border-t border-gray-300" />
                    </div>
                    <div className="relative flex justify-center text-sm">
                        <span className="bg-white px-2 text-gray-500">ou use outro cartão</span>
                    </div>
                </div>
            )}

            {/* New Card Form */}
            <form onSubmit={handleSubmitWithStripe} className="space-y-4">
                {/* Payment Element */}
                <div className="space-y-2">
                    <label className="text-sm font-medium text-gray-700">Dados do Cartão</label>
                    <div className="rounded-lg border border-gray-200 p-4">
                        <PaymentElement />
                    </div>
                </div>

                {/* Error Message */}
                {error && (
                    <div className="rounded-lg border border-red-200 bg-red-50 p-3">
                        <p className="text-sm text-red-600">{error}</p>
                    </div>
                )}

                {/* Submit Button */}
                <Button
                    type="submit"
                    disabled={!stripe || !elements || isLoading}
                    className="w-full bg-gradient-to-r from-purple-500 to-pink-500 text-white hover:from-purple-600 hover:to-pink-600"
                >
                    {isLoading ? (
                        <>
                            <Loader2 className="mr-2 h-4 w-4 animate-spin" />
                            Processando...
                        </>
                    ) : (
                        <>
                            <CreditCard className="mr-2 h-4 w-4" />
                            {company?.has_default_payment_method ? 'Comprar com Novo Cartão' : 'Comprar Créditos'}
                        </>
                    )}
                </Button>

                <p className="text-center text-xs text-gray-500">Seu pagamento será processado de forma segura pelo Stripe</p>
            </form>
        </div>
    );
}
