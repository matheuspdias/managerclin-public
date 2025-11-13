export type MedicalRecord = {
    id: number;
    id_customer: number;
    id_user: number;
    id_appointment?: number;
    chief_complaint?: string;
    physical_exam?: string;
    diagnosis?: string;
    treatment_plan?: string;
    prescriptions?: string;
    observations?: string;
    follow_up_date?: string;
    medical_history?: string;
    allergies?: string;
    medications?: string;
    created_at: string;
    updated_at: string;
};
