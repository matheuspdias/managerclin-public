export type Subscription = {
    id: string;
    stripe_id: string;
    stripe_price: string | null;
    quantity: number;
    trial_ends_at: string | null;
    ends_at: string | null;
    created_at: string;
    updated_at: string;
};
