export type EstimateStatus = 'draft' | 'sent' | 'accepted' | 'declined';

export type EstimateLine = {
    id: number;
    item_id: number | null;
    name: string;
    quantity: string;
    unit_price: string;
    line_total: string;
};

export type Estimate = {
    id: number;
    number: string;
    status: EstimateStatus;
    issue_date: string;
    valid_until: string | null;
    discount_amount: string;
    tax_rate: string;
    subtotal: string;
    tax_amount: string;
    total: string;
    notes: string | null;
    customer: { id: number; name: string };
    lines: EstimateLine[];
};

export type EstimateCustomer = {
    id: number;
    name: string;
};

export type EstimateItem = {
    id: number;
    name: string;
    type: 'product' | 'service';
    sku: string | null;
    unit_price: string;
    description: string | null;
};
