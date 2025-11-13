// resources/js/types/dashboard.ts

import { Appointment } from './appointment';

export type RankingItem = {
    id: number;
    name: string;
    image: string | null;
    position: string;
    appointments_count: number;
    ranking: string;
};

export type MostPopularService = {
    name: string;
    totalAppointments: number;
};

export type DashboardProps = {
    ranking: RankingItem[];
    totalUsers: {
        total: number;
        total_registered_today: number;
    };
    totalCustomers: {
        total: number;
        total_registered_today: number;
    };
    appointmentsChart: {
        count: number;
        completedCount: number;
        cancelledCount: number;
        pendingCount: number;
        completedPercent: number;
    };
    appointments: Appointment[];
    mostPopularServices: MostPopularService[];
    period: { start_date: string; end_date: string };
    userName: string;
};
