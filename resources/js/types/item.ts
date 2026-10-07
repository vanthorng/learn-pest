export type Item = {
    id: number;
    name: string;
    type: 'product' | 'service';
    sku: string | null;
    unit_price: string;
    description: string | null;
};
