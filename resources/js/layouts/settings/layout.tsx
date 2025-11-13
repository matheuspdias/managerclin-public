import Heading from '@/components/heading';
import { Button } from '@/components/ui/button';
import { Separator } from '@/components/ui/separator';
import { cn } from '@/lib/utils';
import { SharedData, type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/react';
import { Building, Lock, Palette, Timer, UserRoundPen } from 'lucide-react';
import { type PropsWithChildren } from 'react';

const sidebarNavItems: NavItem[] = [
    {
        title: 'Perfil',
        href: '/settings/profile',
        icon: UserRoundPen,
    },
    {
        title: 'Configuração de senha',
        href: '/settings/password',
        icon: Lock,
    },
    {
        title: 'Preferências de horário',
        href: '/settings/timezone',
        icon: Timer,
    },
    {
        title: 'Tema',
        href: '/settings/appearance',
        icon: Palette,
    },
    {
        title: 'Configurações de WhatsApp',
        href: '/settings/whatsapp',
        icon: Building,
        isAdminOnly: true,
    },
];

export default function SettingsLayout({ children }: PropsWithChildren) {
    const page = usePage<SharedData>();

    if (typeof window === 'undefined') {
        return null;
    }

    const currentPath = window.location.pathname;
    const { auth } = page.props;

    return (
        <div className="px-4 py-6">
            <Heading title="Configurações" description="Gerencie seu perfil e as configurações da conta" />

            <div className="flex flex-col space-y-8 lg:flex-row lg:space-y-0 lg:space-x-12">
                <aside className="w-full max-w-xl lg:w-48">
                    <nav className="flex flex-col space-y-1 space-x-0">
                        {sidebarNavItems
                            .filter((item) => {
                                return !item.isAdminOnly || auth.user.is_admin;
                            })
                            .map((item, index) => (
                                <Button
                                    key={`${item.href}-${index}`}
                                    size="sm"
                                    variant="ghost"
                                    asChild
                                    className={cn('w-full justify-start', {
                                        'bg-muted': currentPath === item.href,
                                    })}
                                >
                                    <Link href={item.href} prefetch>
                                        {item.icon && <item.icon />}
                                        {item.title}
                                    </Link>
                                </Button>
                            ))}
                    </nav>
                </aside>

                <Separator className="my-6 md:hidden" />

                <div className="flex-1">
                    <section className="space-y-12">{children}</section>
                </div>
            </div>
        </div>
    );
}
