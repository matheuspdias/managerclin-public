import { MedicalRecord } from './medicalRecord';

export type Patient = {
    id: number;
    name: string;
    phone: string;
    birthdate: string;
    notes?: string;
    medical_record: MedicalRecord | null;
    created_at: string;
    updated_at: string;
};

export type PatientProps = {
    data: Patient[];
    currentPage: number;
    lastPage: number;
    total: number;
    links: { url: string | null; label: string; active: boolean }[];
    filters?: {
        search?: string;
        per_page?: number;
        order?: string;
        page?: number;
    };
};
