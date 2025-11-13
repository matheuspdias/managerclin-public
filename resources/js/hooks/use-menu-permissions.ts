import { usePage } from '@inertiajs/react';
import { useMemo } from 'react';

interface MenuItem {
    name: string;
    display_name: string;
    icon: string;
    route_prefix: string;
}

// Todos os itens de menu disponíveis
// Ordenados por contexto de uso: Dashboard > Atendimento > Gestão > Financeiro > Sistema
const ALL_MENU_ITEMS: MenuItem[] = [
    // 1. Dashboard - Visão geral
    {
        name: 'dashboard',
        display_name: 'Dashboard',
        icon: 'LayoutGrid',
        route_prefix: '/dashboard',
    },
    // 2. Área de Atendimento
    {
        name: 'appointments',
        display_name: 'Agendamentos',
        icon: 'CalendarClock',
        route_prefix: '/appointments',
    },
    {
        name: 'patients',
        display_name: 'Pacientes',
        icon: 'Users',
        route_prefix: '/patients',
    },
    {
        name: 'medical-records',
        display_name: 'Prontuários',
        icon: 'FileText',
        route_prefix: '/medical-records',
    },
    {
        name: 'medical-certificates',
        display_name: 'Atestados Médicos',
        icon: 'FileCheck',
        route_prefix: '/medical-certificates',
    },
    // 3. Gestão e Configuração
    {
        name: 'services',
        display_name: 'Serviços',
        icon: 'BriefcaseMedical',
        route_prefix: '/services',
    },
    {
        name: 'rooms',
        display_name: 'Consultórios',
        icon: 'DoorOpen',
        route_prefix: '/rooms',
    },
    {
        name: 'users',
        display_name: 'Colaboradores',
        icon: 'UserCog',
        route_prefix: '/users',
    },
    {
        name: 'inventory',
        display_name: 'Estoque',
        icon: 'Package',
        route_prefix: '/inventory',
    },
    // 4. Financeiro e Marketing
    {
        name: 'financial',
        display_name: 'Financeiro',
        icon: 'CircleDollarSign',
        route_prefix: '/financial',
    },
    {
        name: 'marketing',
        display_name: 'Marketing',
        icon: 'Megaphone',
        route_prefix: '/marketing/campaigns',
    },
    {
        name: 'billing',
        display_name: 'Faturamento',
        icon: 'CreditCard',
        route_prefix: '/billing',
    },
    {
        name: 'ai-credits',
        display_name: 'Créditos de IA',
        icon: 'Brain',
        route_prefix: '/ai-credits',
    },
];

// Regras de acesso por tipo de role
const ROLE_ACCESS_RULES = {
    ADMIN: [
        'dashboard',
        'financial',
        'marketing',
        'patients',
        'users',
        'services',
        'rooms',
        'appointments',
        'medical-records',
        'inventory',
        'medical-certificates',
        'billing',
        'ai-credits',
    ],
    RECEPTIONIST: ['dashboard', 'patients', 'services', 'rooms', 'appointments'],
    DOCTOR: ['dashboard', 'patients', 'services', 'rooms', 'appointments', 'medical-records', 'medical-certificates'],
    FINANCE: ['dashboard', 'financial', 'inventory', 'billing', 'ai-credits'],
};

// Recursos que exigem plano Pro ou Premium
const PRO_PREMIUM_REQUIRED_RESOURCES = ['financial', 'inventory'];

// Recursos que exigem plano Premium (não disponível no Pro)
const PREMIUM_ONLY_RESOURCES = ['marketing'];

export function useMenuPermissions() {
    const { auth } = usePage<{ auth: { user: any; company: any } }>().props;

    const userRole = auth.user?.role?.type || 'GUEST';
    const companyPlan = auth.company?.plan;
    const isOnTrial = auth.company?.is_on_trial || false;

    const allowedResources = useMemo(() => {
        return ROLE_ACCESS_RULES[userRole as keyof typeof ROLE_ACCESS_RULES] || ['dashboard'];
    }, [userRole]);

    const hasProOrPremiumPlan = useMemo(() => {
        return companyPlan === 'pro' || companyPlan === 'premium';
    }, [companyPlan]);

    const hasPremiumPlan = useMemo(() => {
        return companyPlan === 'premium';
    }, [companyPlan]);

    const menuItems = useMemo(() => {
        return ALL_MENU_ITEMS.filter((item) => {
            // First check if the user's role allows access to this resource
            if (!allowedResources.includes(item.name)) {
                return false;
            }

            // Se a empresa está em trial, permite acesso a tudo
            if (isOnTrial) {
                return true;
            }

            // Verifica se o recurso requer plano Premium
            if (PREMIUM_ONLY_RESOURCES.includes(item.name)) {
                return hasPremiumPlan;
            }

            // Verifica se o recurso requer plano Pro ou Premium
            if (PRO_PREMIUM_REQUIRED_RESOURCES.includes(item.name)) {
                return hasProOrPremiumPlan;
            }

            return true;
        });
    }, [allowedResources, hasProOrPremiumPlan, hasPremiumPlan, isOnTrial]);

    const canAccess = (resourceName: string): boolean => {
        // Check role permission
        if (!allowedResources.includes(resourceName)) {
            return false;
        }

        // Se a empresa está em trial, permite acesso a tudo
        if (isOnTrial) {
            return true;
        }

        // Verifica se o recurso requer plano Premium
        if (PREMIUM_ONLY_RESOURCES.includes(resourceName)) {
            return hasPremiumPlan;
        }

        // Verifica se o recurso requer plano Pro ou Premium
        if (PRO_PREMIUM_REQUIRED_RESOURCES.includes(resourceName)) {
            return hasProOrPremiumPlan;
        }

        return true;
    };

    const isAdmin = userRole === 'ADMIN';

    return {
        menuItems,
        canAccess,
        isAdmin,
        userRole,
        companyPlan,
        hasProOrPremiumPlan,
        hasPremiumPlan,
        isOnTrial,
    };
}
