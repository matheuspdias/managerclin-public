import { Head, useForm } from '@inertiajs/react';
import { LoaderCircle } from 'lucide-react';
import { FormEventHandler } from 'react';

import InputError from '@/components/input-error';
import TextLink from '@/components/text-link';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import AuthLayout from '@/layouts/auth-layout';
import { Building2, Eye, EyeOff, Lock, Mail, Phone, User } from 'lucide-react';
import { useState } from 'react';

type RegisterForm = {
    name: string;
    company_name: string;
    email: string;
    phone: string;
    password: string;
    password_confirmation: string;
};

export default function Register() {
    const { data, setData, post, processing, errors, reset } = useForm<Required<RegisterForm>>({
        name: '',
        company_name: '',
        email: '',
        phone: '',
        password: '',
        password_confirmation: '',
    });

    const [showPassword, setShowPassword] = useState(false);
    const [showConfirmPassword, setShowConfirmPassword] = useState(false);

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        post(route('register'), {
            onFinish: () => reset('password', 'password_confirmation'),
        });
    };

    const togglePasswordVisibility = () => setShowPassword(!showPassword);
    const toggleConfirmPasswordVisibility = () => setShowConfirmPassword(!showConfirmPassword);

    return (
        <AuthLayout
            title="Teste Gratuito"
            description="Experimente nosso sistema por 14 dias sem compromisso. Sem necessidade de cartão de crédito."
            showIllustration={true}
        >
            <Head title="Teste Gratuito" />

            <Card className="w-full max-w-md overflow-hidden rounded-lg border shadow-lg">
                <CardHeader className="space-y-1 border-b bg-muted/50 pb-6">
                    <div className="mb-2 flex justify-center">
                        <div className="rounded-full bg-primary/10 p-3">
                            <Lock className="h-8 w-8 text-primary" />
                        </div>
                    </div>
                    <CardTitle className="text-center text-2xl font-bold">Crie sua conta</CardTitle>
                    <CardDescription className="text-center">Preencha os dados abaixo para começar seu teste gratuito</CardDescription>
                </CardHeader>

                <CardContent className="pt-6">
                    <form className="flex flex-col gap-5" onSubmit={submit}>
                        <div className="grid gap-5">
                            <div className="grid gap-2">
                                <div className="relative">
                                    <div className="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                        <User className="h-5 w-5 text-muted-foreground" />
                                    </div>
                                    <Input
                                        id="name"
                                        type="text"
                                        required
                                        autoFocus
                                        tabIndex={1}
                                        autoComplete="name"
                                        value={data.name}
                                        onChange={(e) => setData('name', e.target.value)}
                                        disabled={processing}
                                        placeholder="Digite seu nome completo"
                                        className="py-5 pl-10"
                                    />
                                </div>
                                <InputError message={errors.name} className="mt-1" />
                            </div>

                            <div className="grid gap-2">
                                <div className="relative">
                                    <div className="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                        <Building2 className="h-5 w-5 text-muted-foreground" />
                                    </div>
                                    <Input
                                        id="company_name"
                                        type="text"
                                        required
                                        tabIndex={2}
                                        autoComplete="organization"
                                        value={data.company_name}
                                        onChange={(e) => setData('company_name', e.target.value)}
                                        disabled={processing}
                                        placeholder="Nome da sua empresa"
                                        className="py-5 pl-10"
                                    />
                                </div>
                                <InputError message={errors.company_name} />
                            </div>

                            <div className="grid gap-2">
                                <div className="relative">
                                    <div className="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                        <Mail className="h-5 w-5 text-muted-foreground" />
                                    </div>
                                    <Input
                                        id="email"
                                        type="email"
                                        required
                                        tabIndex={3}
                                        autoComplete="email"
                                        value={data.email}
                                        onChange={(e) => setData('email', e.target.value)}
                                        disabled={processing}
                                        placeholder="email@empresa.com"
                                        className="py-5 pl-10"
                                    />
                                </div>
                                <InputError message={errors.email} />
                            </div>

                            <div className="grid gap-2">
                                <div className="relative">
                                    <div className="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                        <Phone className="h-5 w-5 text-muted-foreground" />
                                    </div>
                                    <Input
                                        id="phone"
                                        type="tel"
                                        required
                                        tabIndex={4}
                                        autoComplete="tel"
                                        value={data.phone}
                                        onChange={(e) => setData('phone', e.target.value)}
                                        disabled={processing}
                                        placeholder="(00) 00000-0000"
                                        className="py-5 pl-10"
                                    />
                                </div>
                                <InputError message={errors.phone} />
                            </div>

                            <div className="grid gap-2">
                                <div className="relative">
                                    <div className="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                        <Lock className="h-5 w-5 text-muted-foreground" />
                                    </div>
                                    <Input
                                        id="password"
                                        type={showPassword ? 'text' : 'password'}
                                        required
                                        tabIndex={5}
                                        autoComplete="new-password"
                                        value={data.password}
                                        onChange={(e) => setData('password', e.target.value)}
                                        disabled={processing}
                                        placeholder="Crie uma senha segura"
                                        className="py-5 pr-10 pl-10"
                                    />
                                    <button
                                        type="button"
                                        className="absolute inset-y-0 right-0 flex items-center pr-3"
                                        onClick={togglePasswordVisibility}
                                    >
                                        {showPassword ? (
                                            <EyeOff className="h-5 w-5 text-muted-foreground hover:text-foreground" />
                                        ) : (
                                            <Eye className="h-5 w-5 text-muted-foreground hover:text-foreground" />
                                        )}
                                    </button>
                                </div>
                                <InputError message={errors.password} />
                            </div>

                            <div className="grid gap-2">
                                <div className="relative">
                                    <div className="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                        <Lock className="h-5 w-5 text-muted-foreground" />
                                    </div>
                                    <Input
                                        id="password_confirmation"
                                        type={showConfirmPassword ? 'text' : 'password'}
                                        required
                                        tabIndex={6}
                                        autoComplete="new-password"
                                        value={data.password_confirmation}
                                        onChange={(e) => setData('password_confirmation', e.target.value)}
                                        disabled={processing}
                                        placeholder="Confirme sua senha"
                                        className="py-5 pr-10 pl-10"
                                    />
                                    <button
                                        type="button"
                                        className="absolute inset-y-0 right-0 flex items-center pr-3"
                                        onClick={toggleConfirmPasswordVisibility}
                                    >
                                        {showConfirmPassword ? (
                                            <EyeOff className="h-5 w-5 text-muted-foreground hover:text-foreground" />
                                        ) : (
                                            <Eye className="h-5 w-5 text-muted-foreground hover:text-foreground" />
                                        )}
                                    </button>
                                </div>
                                <InputError message={errors.password_confirmation} />
                            </div>

                            <Button type="submit" className="mt-2 w-full py-5 text-base font-medium" tabIndex={7} disabled={processing}>
                                {processing && <LoaderCircle className="mr-2 h-5 w-5 animate-spin" />}
                                Começar teste gratuito
                            </Button>
                        </div>

                        <div className="pt-2 text-center text-sm text-muted-foreground">
                            <p>
                                Já tem uma conta?{' '}
                                <TextLink href={route('login')} tabIndex={8} className="font-medium">
                                    Faça login
                                </TextLink>
                            </p>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </AuthLayout>
    );
}
