/**
 * Sistema de Design para Agendamentos
 *
 * Centraliza todas as configurações visuais relacionadas a agendamentos
 * para garantir consistência em toda a aplicação.
 */

import { CheckCircle, Clock, PlayCircle, XCircle } from 'lucide-react';

/**
 * Cores de status para badges e highlights
 * Suporta dark mode automaticamente
 */
export const appointmentStatusColors = {
    SCHEDULED: {
        badge: 'bg-blue-500 hover:bg-blue-600 text-white dark:bg-blue-600 dark:hover:bg-blue-700',
        card: 'bg-blue-50 text-blue-800 border-blue-200 dark:bg-blue-950/40 dark:text-blue-200 dark:border-blue-800',
        cardHover: 'hover:bg-blue-100 dark:hover:bg-blue-950/60',
        text: 'text-blue-600 dark:text-blue-400',
        border: 'border-blue-200 dark:border-blue-800',
        bg: 'bg-blue-50 dark:bg-blue-950/20',
    },
    IN_PROGRESS: {
        badge: 'bg-amber-500 hover:bg-amber-600 text-white dark:bg-amber-600 dark:hover:bg-amber-700',
        card: 'bg-amber-50 text-amber-800 border-amber-200 dark:bg-amber-950/40 dark:text-amber-200 dark:border-amber-800',
        cardHover: 'hover:bg-amber-100 dark:hover:bg-amber-950/60',
        text: 'text-amber-600 dark:text-amber-400',
        border: 'border-amber-200 dark:border-amber-800',
        bg: 'bg-amber-50 dark:bg-amber-950/20',
    },
    COMPLETED: {
        badge: 'bg-green-500 hover:bg-green-600 text-white dark:bg-green-600 dark:hover:bg-green-700',
        card: 'bg-green-50 text-green-800 border-green-200 dark:bg-green-950/40 dark:text-green-200 dark:border-green-800',
        cardHover: 'hover:bg-green-100 dark:hover:bg-green-950/60',
        text: 'text-green-600 dark:text-green-400',
        border: 'border-green-200 dark:border-green-800',
        bg: 'bg-green-50 dark:bg-green-950/20',
    },
    CANCELLED: {
        badge: 'bg-red-500 hover:bg-red-600 text-white dark:bg-red-600 dark:hover:bg-red-700',
        card: 'bg-red-50 text-red-800 border-red-200 dark:bg-red-950/40 dark:text-red-200 dark:border-red-800',
        cardHover: 'hover:bg-red-100 dark:hover:bg-red-950/60',
        text: 'text-red-600 dark:text-red-400',
        border: 'border-red-200 dark:border-red-800',
        bg: 'bg-red-50 dark:bg-red-950/20',
    },
} as const;

/**
 * Configuração de ícones e labels por status
 */
export const appointmentStatusConfig = {
    SCHEDULED: {
        label: 'Agendado',
        icon: Clock,
    },
    IN_PROGRESS: {
        label: 'Em Andamento',
        icon: PlayCircle,
    },
    COMPLETED: {
        label: 'Concluído',
        icon: CheckCircle,
    },
    CANCELLED: {
        label: 'Cancelado',
        icon: XCircle,
    },
} as const;

/**
 * Estilos comuns para cards de agendamentos
 */
export const appointmentCardStyles = {
    base: 'rounded-lg border transition-all duration-200',
    interactive: 'cursor-pointer hover:shadow-md hover:scale-[1.02]',
    elevated: 'shadow-sm hover:shadow-lg',
    compact: 'p-3',
    comfortable: 'p-4',
    spacious: 'p-5',
} as const;

/**
 * Estilos para grids e layouts
 */
export const appointmentLayoutStyles = {
    statsGrid: 'grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6',
    filterGrid: 'grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6',
    formGrid: 'grid grid-cols-1 gap-4 md:grid-cols-2',
    weekGrid: 'grid grid-cols-8 gap-2',
} as const;

/**
 * Tamanhos de ícones consistentes
 */
export const appointmentIconSizes = {
    xs: 'h-3 w-3',
    sm: 'h-4 w-4',
    md: 'h-5 w-5',
    lg: 'h-6 w-6',
} as const;

/**
 * Espaçamentos padronizados
 */
export const appointmentSpacing = {
    sectionGap: 'space-y-6',
    cardGap: 'space-y-4',
    itemGap: 'gap-4',
    compactGap: 'gap-2',
} as const;

/**
 * Animações e transições
 */
export const appointmentAnimations = {
    fadeIn: 'animate-in fade-in duration-200',
    slideIn: 'animate-in slide-in-from-bottom-2 duration-300',
    scaleIn: 'animate-in zoom-in-95 duration-200',
    transition: 'transition-all duration-200',
} as const;

/**
 * Tipografia para agendamentos
 */
export const appointmentTypography = {
    title: 'text-2xl font-bold tracking-tight',
    subtitle: 'text-muted-foreground',
    cardTitle: 'text-lg font-semibold',
    label: 'text-sm font-medium',
    value: 'text-sm',
    caption: 'text-xs text-muted-foreground',
} as const;

/**
 * Breakpoints para responsividade
 */
export const appointmentBreakpoints = {
    mobile: 'sm',
    tablet: 'md',
    desktop: 'lg',
    wide: 'xl',
} as const;
