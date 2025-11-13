export type User = {
    id: number;
    id_role: number;
    name: string;
    email: string;
    phone: string;
    email_verified_at: string | null;
    image: string | null;
    image_url: string;
    created_at: string;
    updated_at: string;
};

export type Role = {
    id: number;
    name: string;
};


export type UserProps = {
    data: User[];
    roles: Role[];
    currentPage: number;
    lastPage: number;
    total: number;
    links: { url: string | null; label: string; active: boolean }[];
    filters?: {
        page?: number;
        search?: string;
        per_page?: number;
        order?: string;
    };
};
