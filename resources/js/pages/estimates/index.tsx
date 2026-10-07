import { Form, Head, useForm } from '@inertiajs/react';
import { Check, Pencil, Plus, Send, Trash2, X } from 'lucide-react';
import { useMemo, useState } from 'react';
import InputError from '@/components/input-error';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/app-layout';
import { destroy, store, update } from '@/routes/estimates';
import { update as updateStatus } from '@/routes/estimates/status';
import type {
    Estimate,
    EstimateCustomer,
    EstimateItem,
    EstimateStatus,
} from '@/types';

type Props = {
    team: { name: string; slug: string };
    customers: EstimateCustomer[];
    items: EstimateItem[];
    estimates: Estimate[];
};

type EstimateFormData = {
    customer_id: string;
    issue_date: string;
    valid_until: string;
    discount_amount: string;
    tax_rate: string;
    notes: string;
    items: Array<{ item_id: string; quantity: string }>;
};

const statusStyles: Record<EstimateStatus, string> = {
    draft: 'border-slate-200 bg-slate-50 text-slate-700 dark:border-slate-800 dark:bg-slate-950/40 dark:text-slate-300',
    sent: 'border-blue-200 bg-blue-50 text-blue-700 dark:border-blue-900 dark:bg-blue-950/40 dark:text-blue-300',
    accepted:
        'border-green-200 bg-green-50 text-green-700 dark:border-green-900 dark:bg-green-950/40 dark:text-green-300',
    declined:
        'border-red-200 bg-red-50 text-red-700 dark:border-red-900 dark:bg-red-950/40 dark:text-red-300',
};

export default function EstimatesIndex({
    team,
    customers,
    items,
    estimates,
}: Props) {
    const [isCreating, setIsCreating] = useState(false);
    const [estimateEditing, setEstimateEditing] = useState<Estimate | null>(
        null,
    );
    const [estimateDeleting, setEstimateDeleting] = useState<Estimate | null>(
        null,
    );

    return (
        <AppLayout showHeader={false}>
            <Head title="Estimates" />

            <div className="p-4 md:p-6">
                <div className="flex flex-col gap-6">
                    <header className="flex flex-col gap-4 border-b pb-5 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <p className="text-sm text-muted-foreground">
                                {team.name} / Sales
                            </p>
                            <h1 className="mt-1 text-2xl font-semibold tracking-tight">
                                Estimates
                            </h1>
                            <p className="mt-1 text-sm text-muted-foreground">
                                Build clear proposals from your products and
                                services.
                            </p>
                        </div>
                        <Button
                            onClick={() => setIsCreating(true)}
                            disabled={
                                customers.length === 0 || items.length === 0
                            }
                        >
                            <Plus /> New estimate
                        </Button>
                    </header>

                    {customers.length === 0 || items.length === 0 ? (
                        <div className="rounded-lg border border-dashed p-4 text-sm text-muted-foreground">
                            Add at least one customer and one item before
                            creating an estimate.
                        </div>
                    ) : null}

                    <section className="overflow-hidden rounded-lg border bg-background">
                        <div className="border-b px-5 py-4 text-sm text-muted-foreground">
                            <span className="font-medium text-foreground">
                                {estimates.length}
                            </span>{' '}
                            total estimates
                        </div>
                        <div className="overflow-x-auto">
                            <table className="w-full min-w-[52rem] text-sm">
                                <thead className="bg-muted/40 text-left text-xs font-medium tracking-wider text-muted-foreground uppercase">
                                    <tr>
                                        <th className="px-5 py-3 font-medium">
                                            Estimate
                                        </th>
                                        <th className="px-5 py-3 font-medium">
                                            Customer
                                        </th>
                                        <th className="px-5 py-3 font-medium">
                                            Status
                                        </th>
                                        <th className="px-5 py-3 font-medium">
                                            Valid until
                                        </th>
                                        <th className="px-5 py-3 text-right font-medium">
                                            Total
                                        </th>
                                        <th className="px-5 py-3 text-right font-medium">
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y">
                                    {estimates.map((estimate) => (
                                        <EstimateRow
                                            key={estimate.id}
                                            team={team}
                                            estimate={estimate}
                                            onEdit={setEstimateEditing}
                                            onDelete={setEstimateDeleting}
                                        />
                                    ))}
                                </tbody>
                            </table>
                        </div>
                        {estimates.length === 0 ? (
                            <div className="px-5 py-16 text-center">
                                <p className="font-medium">No estimates yet</p>
                                <p className="mt-1 text-sm text-muted-foreground">
                                    Create your first proposal when you are
                                    ready.
                                </p>
                            </div>
                        ) : null}
                    </section>
                </div>
            </div>

            <EstimateEditorDialog
                team={team}
                customers={customers}
                items={items}
                open={isCreating}
                onOpenChange={setIsCreating}
            />
            {estimateEditing ? (
                <EstimateEditorDialog
                    key={estimateEditing.id}
                    team={team}
                    customers={customers}
                    items={items}
                    estimate={estimateEditing}
                    open
                    onOpenChange={(open) => {
                        if (!open) {
                            setEstimateEditing(null);
                        }
                    }}
                />
            ) : null}
            <DeleteEstimateDialog
                team={team}
                estimate={estimateDeleting}
                onOpenChange={(open) => {
                    if (!open) {
                        setEstimateDeleting(null);
                    }
                }}
            />
        </AppLayout>
    );
}

function EstimateRow({
    team,
    estimate,
    onEdit,
    onDelete,
}: {
    team: Props['team'];
    estimate: Estimate;
    onEdit: (estimate: Estimate) => void;
    onDelete: (estimate: Estimate) => void;
}) {
    return (
        <tr className="transition-colors hover:bg-muted/30">
            <td className="px-5 py-4">
                <p className="font-mono text-xs text-muted-foreground">
                    {estimate.number}
                </p>
                <p className="mt-1 font-medium">
                    {estimate.lines.length}{' '}
                    {estimate.lines.length === 1 ? 'line' : 'lines'}
                </p>
            </td>
            <td className="px-5 py-4 font-medium">{estimate.customer.name}</td>
            <td className="px-5 py-4">
                <Badge
                    variant="outline"
                    className={statusStyles[estimate.status]}
                >
                    {capitalize(estimate.status)}
                </Badge>
            </td>
            <td className="px-5 py-4 text-muted-foreground">
                {estimate.valid_until ?? '—'}
            </td>
            <td className="px-5 py-4 text-right font-medium">
                {formatPrice(estimate.total)}
            </td>
            <td className="px-5 py-4">
                <div className="flex justify-end gap-1">
                    {estimate.status === 'draft' ? (
                        <>
                            <Button
                                type="button"
                                variant="ghost"
                                size="icon"
                                aria-label={`Edit ${estimate.number}`}
                                onClick={() => onEdit(estimate)}
                            >
                                <Pencil />
                            </Button>
                            <StatusButton
                                team={team}
                                estimate={estimate}
                                status="sent"
                                label="Send"
                                icon={<Send />}
                            />
                            <Button
                                type="button"
                                variant="ghost"
                                size="icon"
                                className="text-destructive hover:text-destructive"
                                aria-label={`Delete ${estimate.number}`}
                                onClick={() => onDelete(estimate)}
                            >
                                <Trash2 />
                            </Button>
                        </>
                    ) : null}
                    {estimate.status === 'sent' ? (
                        <>
                            <StatusButton
                                team={team}
                                estimate={estimate}
                                status="accepted"
                                label="Accept"
                                icon={<Check />}
                            />
                            <StatusButton
                                team={team}
                                estimate={estimate}
                                status="declined"
                                label="Decline"
                                icon={<X />}
                            />
                        </>
                    ) : null}
                </div>
            </td>
        </tr>
    );
}

function StatusButton({
    team,
    estimate,
    status,
    label,
    icon,
}: {
    team: Props['team'];
    estimate: Estimate;
    status: 'sent' | 'accepted' | 'declined';
    label: string;
    icon: React.ReactNode;
}) {
    return (
        <Form {...updateStatus.form([team.slug, estimate.id])}>
            {({ processing }) => (
                <>
                    <input type="hidden" name="status" value={status} />
                    <Button
                        type="submit"
                        variant="ghost"
                        size="icon"
                        disabled={processing}
                        aria-label={`${label} ${estimate.number}`}
                    >
                        {icon}
                    </Button>
                </>
            )}
        </Form>
    );
}

function EstimateEditorDialog({
    team,
    customers,
    items,
    estimate,
    open,
    onOpenChange,
}: {
    team: Props['team'];
    customers: EstimateCustomer[];
    items: EstimateItem[];
    estimate?: Estimate;
    open: boolean;
    onOpenChange: (open: boolean) => void;
}) {
    const isEditing = estimate !== undefined;
    const form = useForm<EstimateFormData>(initialFormData(estimate));
    const totals = useMemo(
        () =>
            calculateTotals(
                form.data.items,
                items,
                form.data.discount_amount,
                form.data.tax_rate,
            ),
        [form.data.items, items, form.data.discount_amount, form.data.tax_rate],
    );

    function submit(event: React.FormEvent<HTMLFormElement>): void {
        event.preventDefault();
        const options = { onSuccess: () => onOpenChange(false) };
        if (isEditing) {
            form.patch(update.url([team.slug, estimate.id]), options);
        } else {
            form.post(store.url(team.slug), options);
        }
    }

    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent className="max-h-[90vh] overflow-y-auto sm:max-w-3xl">
                <form onSubmit={submit} className="grid gap-5">
                    <DialogHeader>
                        <DialogTitle>
                            {isEditing
                                ? `Edit ${estimate.number}`
                                : 'New estimate'}
                        </DialogTitle>
                        <DialogDescription>
                            Prices and totals are saved as a snapshot when you
                            create or update this draft.
                        </DialogDescription>
                    </DialogHeader>
                    <div className="grid gap-4 sm:grid-cols-2">
                        <Field label="Customer" error={form.errors.customer_id}>
                            <select
                                value={form.data.customer_id}
                                onChange={(event) =>
                                    form.setData(
                                        'customer_id',
                                        event.target.value,
                                    )
                                }
                                className={selectClassName}
                            >
                                <option value="">Select customer</option>
                                {customers.map((customer) => (
                                    <option
                                        key={customer.id}
                                        value={customer.id}
                                    >
                                        {customer.name}
                                    </option>
                                ))}
                            </select>
                        </Field>
                        <Field
                            label="Issue date"
                            error={form.errors.issue_date}
                        >
                            <Input
                                type="date"
                                value={form.data.issue_date}
                                onChange={(event) =>
                                    form.setData(
                                        'issue_date',
                                        event.target.value,
                                    )
                                }
                            />
                        </Field>
                        <Field
                            label="Valid until"
                            error={form.errors.valid_until}
                        >
                            <Input
                                type="date"
                                value={form.data.valid_until}
                                onChange={(event) =>
                                    form.setData(
                                        'valid_until',
                                        event.target.value,
                                    )
                                }
                            />
                        </Field>
                        <Field
                            label="Discount amount"
                            error={form.errors.discount_amount}
                        >
                            <Input
                                type="number"
                                min="0"
                                step="0.01"
                                value={form.data.discount_amount}
                                onChange={(event) =>
                                    form.setData(
                                        'discount_amount',
                                        event.target.value,
                                    )
                                }
                            />
                        </Field>
                        <Field
                            label="Tax rate (%)"
                            error={form.errors.tax_rate}
                        >
                            <Input
                                type="number"
                                min="0"
                                max="100"
                                step="0.01"
                                value={form.data.tax_rate}
                                onChange={(event) =>
                                    form.setData('tax_rate', event.target.value)
                                }
                            />
                        </Field>
                    </div>
                    <div className="grid gap-3">
                        <div className="flex items-center justify-between">
                            <Label>Line items</Label>
                            <Button
                                type="button"
                                size="sm"
                                variant="secondary"
                                onClick={() =>
                                    form.setData('items', [
                                        ...form.data.items,
                                        { item_id: '', quantity: '1' },
                                    ])
                                }
                            >
                                <Plus /> Add line
                            </Button>
                        </div>
                        {form.data.items.map((line, index) => (
                            <EstimateLineEditor
                                key={index}
                                line={line}
                                index={index}
                                items={items}
                                error={form.errors[`items.${index}.item_id`]}
                                quantityError={
                                    form.errors[`items.${index}.quantity`]
                                }
                                onChange={(key, value) =>
                                    form.setData(
                                        'items',
                                        form.data.items.map(
                                            (current, currentIndex) =>
                                                currentIndex === index
                                                    ? {
                                                          ...current,
                                                          [key]: value,
                                                      }
                                                    : current,
                                        ),
                                    )
                                }
                                onRemove={() =>
                                    form.setData(
                                        'items',
                                        form.data.items.filter(
                                            (_, currentIndex) =>
                                                currentIndex !== index,
                                        ),
                                    )
                                }
                                canRemove={form.data.items.length > 1}
                            />
                        ))}
                        <InputError message={form.errors.items} />
                    </div>
                    <div className="grid gap-2 rounded-lg bg-muted/50 p-4 text-sm sm:grid-cols-3">
                        <Summary
                            label="Subtotal"
                            value={formatPrice(totals.subtotal)}
                        />
                        <Summary label="Tax" value={formatPrice(totals.tax)} />
                        <Summary
                            label="Total"
                            value={formatPrice(totals.total)}
                            strong
                        />
                    </div>
                    <div className="grid gap-2">
                        <Label htmlFor="estimate-notes">Notes</Label>
                        <textarea
                            id="estimate-notes"
                            value={form.data.notes}
                            onChange={(event) =>
                                form.setData('notes', event.target.value)
                            }
                            rows={3}
                            className={textareaClassName}
                        />
                        <InputError message={form.errors.notes} />
                    </div>
                    <DialogFooter>
                        <DialogClose asChild>
                            <Button type="button" variant="secondary">
                                Cancel
                            </Button>
                        </DialogClose>
                        <Button type="submit" disabled={form.processing}>
                            {isEditing ? 'Save draft' : 'Create estimate'}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}

function EstimateLineEditor({
    line,
    index,
    items,
    error,
    quantityError,
    onChange,
    onRemove,
    canRemove,
}: {
    line: EstimateFormData['items'][number];
    index: number;
    items: EstimateItem[];
    error?: string;
    quantityError?: string;
    onChange: (key: 'item_id' | 'quantity', value: string) => void;
    onRemove: () => void;
    canRemove: boolean;
}) {
    const item = items.find(
        (candidate) => candidate.id === Number(line.item_id),
    );
    return (
        <div className="grid gap-2 rounded-lg border p-3 sm:grid-cols-[1fr_8rem_7rem_auto]">
            <div className="grid gap-1">
                <select
                    value={line.item_id}
                    onChange={(event) =>
                        onChange('item_id', event.target.value)
                    }
                    className={selectClassName}
                >
                    <option value="">Select item</option>
                    {items.map((candidate) => (
                        <option key={candidate.id} value={candidate.id}>
                            {candidate.name} —{' '}
                            {formatPrice(candidate.unit_price)}
                        </option>
                    ))}
                </select>
                <InputError message={error} />
            </div>
            <div className="grid gap-1">
                <Input
                    type="number"
                    min="0.01"
                    step="0.01"
                    value={line.quantity}
                    onChange={(event) =>
                        onChange('quantity', event.target.value)
                    }
                />
                <InputError message={quantityError} />
            </div>
            <div className="flex items-center justify-end font-medium">
                {formatPrice(
                    String(
                        (
                            Number(item?.unit_price ?? 0) *
                            Number(line.quantity || 0)
                        ).toFixed(2),
                    ),
                )}
            </div>
            <Button
                type="button"
                variant="ghost"
                size="icon"
                disabled={!canRemove}
                aria-label={`Remove line ${index + 1}`}
                onClick={onRemove}
            >
                <Trash2 />
            </Button>
        </div>
    );
}

function DeleteEstimateDialog({
    team,
    estimate,
    onOpenChange,
}: {
    team: Props['team'];
    estimate: Estimate | null;
    onOpenChange: (open: boolean) => void;
}) {
    if (!estimate) {
        return null;
    }
    return (
        <Dialog open onOpenChange={onOpenChange}>
            <DialogContent>
                <Form
                    {...destroy.form([team.slug, estimate.id])}
                    className="grid gap-5"
                    onSuccess={() => onOpenChange(false)}
                >
                    {({ processing }) => (
                        <>
                            <DialogHeader>
                                <DialogTitle>Delete estimate?</DialogTitle>
                                <DialogDescription>
                                    This permanently removes{' '}
                                    <strong>{estimate.number}</strong> and its
                                    line items.
                                </DialogDescription>
                            </DialogHeader>
                            <DialogFooter>
                                <DialogClose asChild>
                                    <Button type="button" variant="secondary">
                                        Cancel
                                    </Button>
                                </DialogClose>
                                <Button
                                    variant="destructive"
                                    type="submit"
                                    disabled={processing}
                                >
                                    Delete estimate
                                </Button>
                            </DialogFooter>
                        </>
                    )}
                </Form>
            </DialogContent>
        </Dialog>
    );
}

function Field({
    label,
    error,
    children,
}: {
    label: string;
    error?: string;
    children: React.ReactNode;
}) {
    return (
        <div className="grid gap-2">
            <Label>{label}</Label>
            {children}
            <InputError message={error} />
        </div>
    );
}
function Summary({
    label,
    value,
    strong = false,
}: {
    label: string;
    value: string;
    strong?: boolean;
}) {
    return (
        <div>
            <p className="text-xs text-muted-foreground">{label}</p>
            <p className={strong ? 'text-base font-semibold' : 'font-medium'}>
                {value}
            </p>
        </div>
    );
}
function initialFormData(estimate?: Estimate): EstimateFormData {
    return {
        customer_id: estimate ? String(estimate.customer.id) : '',
        issue_date: estimate?.issue_date ?? today(),
        valid_until: estimate?.valid_until ?? '',
        discount_amount: estimate?.discount_amount ?? '0',
        tax_rate: estimate?.tax_rate ?? '0',
        notes: estimate?.notes ?? '',
        items: estimate?.lines.map((line) => ({
            item_id: String(line.item_id ?? ''),
            quantity: line.quantity,
        })) ?? [{ item_id: '', quantity: '1' }],
    };
}
function calculateTotals(
    lines: EstimateFormData['items'],
    items: EstimateItem[],
    discountAmount: string,
    taxRate: string,
): { subtotal: number; tax: number; total: number } {
    const subtotal = lines.reduce(
        (sum, line) =>
            sum +
            Number(
                items.find((item) => item.id === Number(line.item_id))
                    ?.unit_price ?? 0,
            ) *
                Number(line.quantity || 0),
        0,
    );
    const discount = Math.min(Number(discountAmount || 0), subtotal);
    const tax = (subtotal - discount) * (Number(taxRate || 0) / 100);
    return { subtotal, tax, total: subtotal - discount + tax };
}
function today(): string {
    return new Date().toISOString().slice(0, 10);
}
function capitalize(value: string): string {
    return value[0].toUpperCase() + value.slice(1);
}
function formatPrice(value: string | number): string {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
    }).format(Number(value));
}
const selectClassName =
    'h-9 rounded-md border border-input bg-background px-3 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50';
const textareaClassName =
    'rounded-md border border-input bg-background px-3 py-2 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50';
