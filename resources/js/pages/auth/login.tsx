import { Head, useForm } from '@inertiajs/react';
import { Eye, EyeOff, LoaderCircle, Lock, Mail } from 'lucide-react';
import { FormEventHandler, useState } from 'react';

import InputError from '@/components/input-error';
import TextLink from '@/components/text-link';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthLayout from '@/layouts/auth-layout';

type LoginForm = {
    email: string;
    password: string;
    remember: boolean;
};

interface LoginProps {
    status?: string;
    canResetPassword: boolean;
}

export default function Login({ status, canResetPassword }: LoginProps) {
    const { data, setData, post, processing, errors, reset } = useForm<Required<LoginForm>>({
        email: '',
        password: '',
        remember: false,
    });

    const [showPassword, setShowPassword] = useState(false);

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        post(route('login'), {
            onFinish: () => reset('password'),
        });
    };

    const togglePasswordVisibility = () => setShowPassword(!showPassword);

    return (
        <AuthLayout
            title="Acesse sua conta"
            description="Bem-vindo de volta! Digite suas credenciais para acessar o sistema."
            showIllustration={true}
        >
            <Head title="Login" />

            <Card className="w-full max-w-md overflow-hidden rounded-lg border shadow-lg">
                <CardHeader className="space-y-1 border-b bg-muted/50 pb-6">
                    <div className="mb-2 flex justify-center">
                        <div className="rounded-full bg-primary/10 p-3">
                            <Lock className="h-8 w-8 text-primary" />
                        </div>
                    </div>
                    <CardTitle className="text-center text-2xl font-bold">Acesse sua conta</CardTitle>
                    <CardDescription className="text-center">Digite suas credenciais para continuar</CardDescription>
                </CardHeader>

                <CardContent className="pt-6">
                    <form className="flex flex-col gap-5" onSubmit={submit}>
                        {status && (
                            <div className="mb-4 rounded-md border border-green-200 bg-green-100 p-3 text-center text-sm text-green-700">
                                {status}
                            </div>
                        )}

                        <div className="grid gap-5">
                            <div className="grid gap-2">
                                <Label htmlFor="email" className="text-sm font-medium">
                                    E-mail
                                </Label>
                                <div className="relative">
                                    <div className="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                        <Mail className="h-5 w-5 text-muted-foreground" />
                                    </div>
                                    <Input
                                        id="email"
                                        type="email"
                                        required
                                        autoFocus
                                        tabIndex={1}
                                        autoComplete="email"
                                        value={data.email}
                                        onChange={(e) => setData('email', e.target.value)}
                                        placeholder="seu.email@exemplo.com"
                                        className="py-5 pl-10"
                                    />
                                </div>
                                <InputError message={errors.email} className="mt-1" />
                            </div>

                            <div className="grid gap-2">
                                <div className="flex items-center justify-between">
                                    <Label htmlFor="password" className="text-sm font-medium">
                                        Senha
                                    </Label>
                                    {canResetPassword && (
                                        <TextLink
                                            href={route('password.request')}
                                            className="text-xs transition-colors hover:text-foreground"
                                            tabIndex={4}
                                        >
                                            Esqueceu sua senha?
                                        </TextLink>
                                    )}
                                </div>
                                <div className="relative">
                                    <div className="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                        <Lock className="h-5 w-5 text-muted-foreground" />
                                    </div>
                                    <Input
                                        id="password"
                                        type={showPassword ? 'text' : 'password'}
                                        required
                                        tabIndex={2}
                                        autoComplete="current-password"
                                        value={data.password}
                                        onChange={(e) => setData('password', e.target.value)}
                                        placeholder="Digite sua senha"
                                        className="py-5 pr-10 pl-10"
                                    />
                                    <button
                                        type="button"
                                        className="absolute inset-y-0 right-0 flex items-center pr-3"
                                        onClick={togglePasswordVisibility}
                                        tabIndex={3}
                                    >
                                        {showPassword ? (
                                            <EyeOff className="h-5 w-5 text-muted-foreground transition-colors hover:text-foreground" />
                                        ) : (
                                            <Eye className="h-5 w-5 text-muted-foreground transition-colors hover:text-foreground" />
                                        )}
                                    </button>
                                </div>
                                <InputError message={errors.password} />
                            </div>

                            <div className="flex items-center space-x-3 pt-2">
                                <Checkbox
                                    id="remember"
                                    name="remember"
                                    checked={data.remember}
                                    onClick={() => setData('remember', !data.remember)}
                                    tabIndex={4}
                                />
                                <Label htmlFor="remember" className="cursor-pointer text-sm">
                                    Lembrar-me
                                </Label>
                            </div>

                            <Button type="submit" className="mt-2 w-full py-5 text-base font-medium" tabIndex={5} disabled={processing}>
                                {processing ? (
                                    <>
                                        <LoaderCircle className="mr-2 h-5 w-5 animate-spin" />
                                        Entrando...
                                    </>
                                ) : (
                                    'Entrar na conta'
                                )}
                            </Button>
                        </div>

                        <div className="border-t border-border pt-4 text-center text-sm text-muted-foreground">
                            <p>
                                Ainda não tem uma conta?{' '}
                                <TextLink href={route('register')} tabIndex={6} className="font-medium transition-colors hover:text-foreground">
                                    Cadastre-se agora!
                                </TextLink>
                            </p>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </AuthLayout>
    );
}
