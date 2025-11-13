export type Room = {
    id: number;
    name: string;
    location: string;
    created_at: string;
};

export type RoomProps = {
    data: Room[];
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
