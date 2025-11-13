import { type BreadcrumbItem, type SharedData } from '@/types';
import { Transition } from '@headlessui/react';
import { Head, Link, router, useForm, usePage } from '@inertiajs/react';
import { FormEventHandler, useState } from 'react';

import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { useInitials } from '@/hooks/use-initials';
import AppLayout from '@/layouts/app-layout';
import SettingsLayout from '@/layouts/settings/layout';
import { ProfilePage } from '@/types/profilePage';
import { Camera, CheckCircle, FileText, Mail, Phone, Save, User } from 'lucide-react';
import { toast } from 'sonner';

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Configurações', href: '/settings/profile' }];

export default function Profile({ mustVerifyEmail, status }: ProfilePage) {
    const { auth } = usePage<SharedData>().props;
    const getInitials = useInitials();

    const [previewUrl, setPreviewUrl] = useState<string | null>(auth.user.image ?? null);

    const { data, setData, errors, processing, recentlySuccessful } = useForm({
        name: auth.user.name ?? '',
        email: auth.user.email ?? '',
        phone: auth.user.phone ?? '',
        crm: auth.user.crm || '',
        image: null as File | null,
        id_role: auth.user.id_role ?? '',
    });

    const handleImageChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        const file = e.target.files?.[0];
        if (file) {
            setData('image', file);
            setPreviewUrl(URL.createObjectURL(file));
        } else {
            setData('image', null);
            setPreviewUrl(null);
        }
    };

    const submit: FormEventHandler = (e) => {
        e.preventDefault();

        router.post(
            'profile',
            {
                _method: 'patch',
                name: data.name,
                email: data.email,
                phone: data.phone,
                crm: data.crm,
                image: data.image,
            },
            {
                onSuccess: () => {
                    setData({
                        ...data,
                    });

                    if (data.image) {
                        setPreviewUrl(URL.createObjectURL(data.image));
                    } else {
                        setPreviewUrl(auth.user.image ?? null);
                    }
                    toast.success('Perfil atualizado com sucesso!');
                },
                onError: () => {
                    toast.error('Erro ao atualizar perfil. Verifique os dados e tente novamente.');
                },
            },
        );
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Editar Perfil" />
            <SettingsLayout>
                <div className="space-y-8">
                    {/* Header Section */}
                    <div className="flex items-center gap-4">
                        <div className="rounded-lg bg-primary/10 p-3">
                            <User className="h-7 w-7 text-primary" />
                        </div>
                        <div>
                            <h1 className="text-2xl font-bold tracking-tight">Editar Perfil</h1>
                            <p className="text-muted-foreground">Atualize suas informações pessoais e preferências</p>
                        </div>
                    </div>

                    <form onSubmit={submit} className="space-y-8">
                        {/* Photo Section */}
                        <div className="rounded-lg border bg-card p-6">
                            <h2 className="mb-4 flex items-center gap-2 text-lg font-semibold">
                                <Camera className="h-5 w-5 text-muted-foreground" />
                                Foto de Perfil
                            </h2>

                            <div className="flex items-center gap-6">
                                <div className="relative">
                                    {previewUrl ? (
                                        <img
                                            src={previewUrl}
                                            alt="Preview"
                                            className="h-24 w-24 rounded-full border-2 border-muted object-cover shadow-sm"
                                        />
                                    ) : (
                                        <div className="flex h-24 w-24 items-center justify-center rounded-full bg-gradient-to-br from-primary/20 to-primary/10 text-lg font-semibold text-primary">
                                            {getInitials(auth.user.name)}
                                        </div>
                                    )}
                                    <label
                                        htmlFor="image"
                                        className="absolute right-0 bottom-0 flex h-8 w-8 cursor-pointer items-center justify-center rounded-full bg-primary text-white shadow-md transition-colors hover:bg-primary/90"
                                    >
                                        <Camera className="h-4 w-4" />
                                        <input id="image" type="file" accept="image/*" onChange={handleImageChange} className="hidden" />
                                    </label>
                                </div>

                                <div className="flex-1 space-y-2">
                                    <p className="text-sm text-muted-foreground">Formatos suportados: JPG, PNG, GIF. Tamanho máximo: 2MB.</p>
                                    <InputError message={errors.image} />
                                </div>
                            </div>
                        </div>

                        {/* Personal Information Section */}
                        <div className="rounded-lg border bg-card p-6">
                            <h2 className="mb-6 flex items-center gap-2 text-lg font-semibold">
                                <User className="h-5 w-5 text-muted-foreground" />
                                Informações Pessoais
                            </h2>

                            <div className="grid grid-cols-1 gap-6 md:grid-cols-2">
                                <div className="space-y-3">
                                    <Label htmlFor="name" className="flex items-center gap-2">
                                        <User className="h-4 w-4" />
                                        Nome Completo *
                                    </Label>
                                    <Input id="name" value={data.name} onChange={(e) => setData('name', e.target.value)} required className="h-11" />
                                    <InputError message={errors.name} />
                                </div>

                                <div className="space-y-3">
                                    <Label htmlFor="email" className="flex items-center gap-2">
                                        <Mail className="h-4 w-4" />
                                        Email *
                                    </Label>
                                    <Input
                                        id="email"
                                        type="email"
                                        value={data.email}
                                        onChange={(e) => setData('email', e.target.value)}
                                        required
                                        className="h-11"
                                    />
                                    <InputError message={errors.email} />
                                </div>

                                <div className="space-y-3">
                                    <Label htmlFor="phone" className="flex items-center gap-2">
                                        <Phone className="h-4 w-4" />
                                        Telefone
                                    </Label>
                                    <Input
                                        id="phone"
                                        value={data.phone}
                                        onChange={(e) => setData('phone', e.target.value)}
                                        placeholder="(00) 00000-0000"
                                        className="h-11"
                                    />
                                    <InputError message={errors.phone} />
                                </div>

                                <div className="space-y-3">
                                    <Label htmlFor="crm" className="flex items-center gap-2">
                                        <FileText className="h-4 w-4" />
                                        CRM
                                    </Label>
                                    <Input
                                        id="crm"
                                        value={data.crm}
                                        onChange={(e) => setData('crm', e.target.value)}
                                        placeholder="CRM/UF 123456"
                                        className="h-11"
                                    />
                                    <InputError message={errors.crm} />
                                </div>

                            </div>
                        </div>

                        {/* Email Verification Section */}
                        {mustVerifyEmail && auth.user.email_verified_at === null && (
                            <div className="rounded-lg border border-amber-200 bg-amber-50 p-6">
                                <div className="flex items-start gap-4">
                                    <div className="rounded-full bg-amber-100 p-2">
                                        <Mail className="h-5 w-5 text-amber-600" />
                                    </div>
                                    <div className="flex-1">
                                        <h3 className="font-semibold text-amber-900">Verificação de Email Pendente</h3>
                                        <p className="mt-1 text-sm text-amber-700">
                                            Seu e-mail ainda não foi verificado. Para acessar todos os recursos do sistema, verifique seu email.
                                        </p>
                                        <Link
                                            href={route('verification.send')}
                                            method="post"
                                            as="button"
                                            className="mt-3 text-sm font-medium text-amber-600 underline hover:text-amber-700"
                                        >
                                            Clique aqui para reenviar o link de verificação
                                        </Link>
                                        {status === 'verification-link-sent' && (
                                            <p className="mt-2 text-sm font-medium text-green-600">
                                                <CheckCircle className="mr-1 inline h-4 w-4" />
                                                Link de verificação enviado!
                                            </p>
                                        )}
                                    </div>
                                </div>
                            </div>
                        )}

                        {/* Submit Section */}
                        <div className="rounded-lg border bg-card p-6">
                            <div className="flex items-center justify-between">
                                <div>
                                    <h3 className="font-semibold">Salvar Alterações</h3>
                                    <p className="text-sm text-muted-foreground">Revise suas informações antes de salvar</p>
                                </div>

                                <div className="flex items-center gap-4">
                                    <Transition
                                        show={recentlySuccessful}
                                        enter="transition-opacity duration-300"
                                        enterFrom="opacity-0"
                                        leave="transition-opacity duration-300"
                                        leaveTo="opacity-0"
                                    >
                                        <span className="flex items-center gap-2 text-sm font-medium text-green-600">
                                            <CheckCircle className="h-4 w-4" />
                                            Atualizado com sucesso
                                        </span>
                                    </Transition>

                                    <Button type="submit" disabled={processing} className="gap-2 px-6" size="lg">
                                        {processing ? (
                                            <>
                                                <div className="h-4 w-4 animate-spin rounded-full border-2 border-current border-t-transparent" />
                                                Salvando...
                                            </>
                                        ) : (
                                            <>
                                                <Save className="h-4 w-4" />
                                                Salvar Alterações
                                            </>
                                        )}
                                    </Button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </SettingsLayout>
        </AppLayout>
    );
}
