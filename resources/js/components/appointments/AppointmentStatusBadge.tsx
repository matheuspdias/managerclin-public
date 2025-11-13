import { Badge } from '@/components/ui/badge';
import { type Appointment } from '@/types/appointment';
import { appointmentStatusConfig, appointmentStatusColors, appointmentIconSizes } from '@/config/appointment-design-system';

interface AppointmentStatusBadgeProps {
    status: Appointment['status'];
    className?: string;
    size?: 'xs' | 'sm' | 'md' | 'lg';
}

export function AppointmentStatusBadge({ status, className = '', size = 'sm' }: AppointmentStatusBadgeProps) {
    const config = appointmentStatusConfig[status];
    const colors = appointmentStatusColors[status];
    const Icon = config.icon;

    return (
        <Badge className={`${colors.badge} ${className} gap-1 font-medium`}>
            <Icon className={appointmentIconSizes[size]} />
            {config.label}
        </Badge>
    );
}
