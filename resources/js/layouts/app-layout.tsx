import ChatFloatingButton from '@/components/ChatFloatingButton';
import { Toaster } from '@/components/ui/sonner';
import AppLayoutTemplate from '@/layouts/app/app-sidebar-layout';
import { type BreadcrumbItem } from '@/types';
import { usePage } from '@inertiajs/react';
import { formatDistanceToNow } from 'date-fns';
import { ptBR } from 'date-fns/locale';
import { type ReactNode } from 'react';

interface AppLayoutProps {
    children: ReactNode;
    breadcrumbs?: BreadcrumbItem[];
}

export default ({ children, breadcrumbs, ...props }: AppLayoutProps) => {
    const { auth } = usePage().props as any;
    const trialEndsAt = auth?.company?.trial_ends_at;

    const showTrialBanner = trialEndsAt && new Date(trialEndsAt) > new Date(); // ainda em trial

    return (
        <AppLayoutTemplate breadcrumbs={breadcrumbs} {...props}>
            {showTrialBanner && (
                <div className="bg-yellow-100 p-2 text-center text-yellow-800">
                    Seu período de teste expira em {formatDistanceToNow(new Date(trialEndsAt), { addSuffix: true, locale: ptBR })}.{' '}
                    <a href="/billing" className="underline">
                        Clique aqui para assinar
                    </a>
                </div>
            )}
            {children}
            <Toaster richColors />
            <ChatFloatingButton />
        </AppLayoutTemplate>
    );
};
