export type Service = {
    id: number;
    name: string;
    description: string | null;
    //price decimal
    price: number;
    created_at: string;
};

export type ServiceProps = {
    data: Service[];
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
