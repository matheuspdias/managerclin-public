import { NavMain } from '@/components/nav-main';
import { NavUser } from '@/components/nav-user';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/react';
import { BriefcaseMedical, CalendarClock, DoorOpen, LayoutGrid, Users, CreditCard, Brain, CircleDollarSign, Package, FileText, FileCheck, UserCog, Megaphone } from 'lucide-react';
import AppLogo from './app-logo';
import { useMenuPermissions } from '@/hooks/use-menu-permissions';

// Mapeamento dos ícones para os componentes Lucide
const iconMap = {
    LayoutGrid,
    CalendarClock,
    Users,
    FileText,
    FileCheck,
    BriefcaseMedical,
    DoorOpen,
    UserCog,
    Package,
    CircleDollarSign,
    Megaphone,
    CreditCard,
    Brain,
};

export function AppSidebar() {
    const { menuItems } = useMenuPermissions();

    // Converte os itens do menu para o formato NavItem
    const navItems: NavItem[] = menuItems.map(item => ({
        title: item.display_name,
        href: item.route_prefix,
        icon: iconMap[item.icon as keyof typeof iconMap] || LayoutGrid,
    }));

    return (
        <Sidebar collapsible="icon" variant="inset">
            <SidebarHeader>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton size="lg" asChild>
                            <Link href="/dashboard" prefetch>
                                <AppLogo />
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarHeader>

            <SidebarContent>
                <NavMain items={navItems} />
            </SidebarContent>

            <SidebarFooter>
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}
