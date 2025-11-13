import { Patient } from './patient';
import { Room } from './room';
import { Service } from './service';
import { User } from './user';

export type Appointment = {
    id: number;
    id_user: number;
    id_customer: number;
    id_service: number;
    id_room: number;
    date: string;
    start_time: string;
    end_time: string;
    customer: Patient;
    user: User;
    service: Service;
    room: Room;
    status: 'SCHEDULED' | 'IN_PROGRESS' | 'COMPLETED' | 'CANCELLED';
    notes?: string;
    created_at: string;
    updated_at: string;
};

export type AppointmentProps = {
    data: Appointment[];
    services: Service[];
    users: User[];
    customers: Patient[];
    rooms: Room[];
    currentPage: number;
    lastPage: number;
    total: number;
    period: { start_date: string; end_date: string };
    links: { url: string | null; label: string; active: boolean }[];
    filters?: {
        search?: string;
        per_page?: number;
        order?: string;
        page?: number;
    };
};
