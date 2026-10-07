import { Form, Head } from '@inertiajs/react';
import { Pencil, Plus, Trash2 } from 'lucide-react';
import { useState } from 'react';
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
import { destroy, store, update } from '@/routes/items';
import type { Item } from '@/types';

type Props = {
    team: { name: string; slug: string };
    items: Item[];
};

const itemTypes: Item['type'][] = ['product', 'service'];

export default function ItemsIndex({ team, items }: Props) {
    const [isCreating, setIsCreating] = useState(false);
    const [itemEditing, setItemEditing] = useState<Item | null>(null);
    const [itemDeleting, setItemDeleting] = useState<Item | null>(null);

    return (
        <AppLayout showHeader={false}>
            <Head title="Items" />

            <div className="p-4 md:p-6">
                <div className="flex flex-col gap-6">
                    <header className="flex flex-col gap-4 border-b pb-5 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <p className="text-sm text-muted-foreground">
                                {team.name} / Sales
                            </p>
                            <h1 className="mt-1 text-2xl font-semibold tracking-tight">
                                Products & services
                            </h1>
                            <p className="mt-1 text-sm text-muted-foreground">
                                Manage the items you sell to customers.
                            </p>
                        </div>
                        <Button onClick={() => setIsCreating(true)}>
                            <Plus /> New item
                        </Button>
                    </header>

                    <section className="overflow-hidden rounded-lg border bg-background">
                        <div className="border-b px-5 py-4 text-sm text-muted-foreground">
                            <span className="font-medium text-foreground">
                                {items.length}
                            </span>{' '}
                            total items
                        </div>
                        <div className="overflow-x-auto">
                            <table className="w-full min-w-[48rem] text-sm">
                                <thead className="bg-muted/40 text-left text-xs font-medium tracking-wider text-muted-foreground uppercase">
                                    <tr>
                                        <th className="px-5 py-3 font-medium">
                                            Name
                                        </th>
                                        <th className="px-5 py-3 font-medium">
                                            Type
                                        </th>
                                        <th className="px-5 py-3 font-medium">
                                            SKU
                                        </th>
                                        <th className="px-5 py-3 text-right font-medium">
                                            Unit price
                                        </th>
                                        <th className="px-5 py-3 text-right font-medium">
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y">
                                    {items.map((item) => (
                                        <tr
                                            key={item.id}
                                            className="transition-colors hover:bg-muted/30"
                                        >
                                            <td className="px-5 py-4">
                                                <p className="font-medium">
                                                    {item.name}
                                                </p>
                                                {item.description ? (
                                                    <p className="mt-1 max-w-md truncate text-xs text-muted-foreground">
                                                        {item.description}
                                                    </p>
                                                ) : null}
                                            </td>
                                            <td className="px-5 py-4">
                                                <Badge variant="outline">
                                                    {capitalize(item.type)}
                                                </Badge>
                                            </td>
                                            <td className="px-5 py-4 font-mono text-xs text-muted-foreground">
                                                {item.sku ?? '—'}
                                            </td>
                                            <td className="px-5 py-4 text-right font-medium">
                                                {formatPrice(item.unit_price)}
                                            </td>
                                            <td className="px-5 py-4">
                                                <div className="flex justify-end gap-1">
                                                    <Button
                                                        type="button"
                                                        variant="ghost"
                                                        size="icon"
                                                        aria-label={`Edit ${item.name}`}
                                                        onClick={() =>
                                                            setItemEditing(item)
                                                        }
                                                    >
                                                        <Pencil />
                                                    </Button>
                                                    <Button
                                                        type="button"
                                                        variant="ghost"
                                                        size="icon"
                                                        className="text-destructive hover:text-destructive"
                                                        aria-label={`Delete ${item.name}`}
                                                        onClick={() =>
                                                            setItemDeleting(
                                                                item,
                                                            )
                                                        }
                                                    >
                                                        <Trash2 />
                                                    </Button>
                                                </div>
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                        {items.length === 0 ? (
                            <div className="px-5 py-16 text-center">
                                <p className="font-medium">No items yet</p>
                                <p className="mt-1 text-sm text-muted-foreground">
                                    Add a product or service to get started.
                                </p>
                            </div>
                        ) : null}
                    </section>
                </div>
            </div>

            <ItemFormDialog
                team={team}
                open={isCreating}
                onOpenChange={setIsCreating}
            />
            <ItemFormDialog
                team={team}
                item={itemEditing}
                open={itemEditing !== null}
                onOpenChange={(open) => {
                    if (!open) {
                        setItemEditing(null);
                    }
                }}
            />
            <DeleteItemDialog
                team={team}
                item={itemDeleting}
                onOpenChange={(open) => {
                    if (!open) {
                        setItemDeleting(null);
                    }
                }}
            />
        </AppLayout>
    );
}

function ItemFormDialog({
    team,
    item,
    open,
    onOpenChange,
}: {
    team: Props['team'];
    item?: Item | null;
    open: boolean;
    onOpenChange: (open: boolean) => void;
}) {
    const isEditing = item !== undefined && item !== null;
    const form = isEditing
        ? update.form([team.slug, item.id])
        : store.form(team.slug);

    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent>
                <Form
                    key={item?.id ?? 'new'}
                    {...form}
                    className="grid gap-5"
                    onSuccess={() => onOpenChange(false)}
                >
                    {({ errors, processing }) => (
                        <>
                            <DialogHeader>
                                <DialogTitle>
                                    {isEditing ? 'Edit item' : 'New item'}
                                </DialogTitle>
                                <DialogDescription>
                                    {isEditing
                                        ? 'Update this product or service.'
                                        : 'Add a product or service that your team sells.'}
                                </DialogDescription>
                            </DialogHeader>
                            <ItemFields item={item} errors={errors} />
                            <DialogFooter>
                                <DialogClose asChild>
                                    <Button type="button" variant="secondary">
                                        Cancel
                                    </Button>
                                </DialogClose>
                                <Button type="submit" disabled={processing}>
                                    {isEditing ? 'Save changes' : 'Create item'}
                                </Button>
                            </DialogFooter>
                        </>
                    )}
                </Form>
            </DialogContent>
        </Dialog>
    );
}

function ItemFields({
    item,
    errors,
}: {
    item?: Item | null;
    errors: Record<string, string>;
}) {
    return (
        <div className="grid gap-4">
            <div className="grid gap-2">
                <Label htmlFor="item-name">Name</Label>
                <Input
                    id="item-name"
                    name="name"
                    defaultValue={item?.name}
                    placeholder="e.g. Website design"
                    required
                />
                <InputError message={errors.name} />
            </div>
            <div className="grid gap-2 sm:grid-cols-2">
                <div className="grid gap-2">
                    <Label htmlFor="item-type">Type</Label>
                    <select
                        id="item-type"
                        name="type"
                        defaultValue={item?.type ?? 'product'}
                        className="h-9 rounded-md border border-input bg-background px-3 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50"
                    >
                        {itemTypes.map((type) => (
                            <option key={type} value={type}>
                                {capitalize(type)}
                            </option>
                        ))}
                    </select>
                    <InputError message={errors.type} />
                </div>
                <div className="grid gap-2">
                    <Label htmlFor="item-price">Unit price</Label>
                    <Input
                        id="item-price"
                        name="unit_price"
                        type="number"
                        min="0"
                        step="0.01"
                        defaultValue={item?.unit_price}
                        placeholder="0.00"
                        required
                    />
                    <InputError message={errors.unit_price} />
                </div>
            </div>
            <div className="grid gap-2">
                <Label htmlFor="item-sku">SKU (optional)</Label>
                <Input
                    id="item-sku"
                    name="sku"
                    defaultValue={item?.sku ?? ''}
                    placeholder="e.g. SVC-001"
                />
                <InputError message={errors.sku} />
            </div>
            <div className="grid gap-2">
                <Label htmlFor="item-description">Description (optional)</Label>
                <textarea
                    id="item-description"
                    name="description"
                    defaultValue={item?.description ?? ''}
                    rows={3}
                    className="rounded-md border border-input bg-background px-3 py-2 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50"
                />
                <InputError message={errors.description} />
            </div>
        </div>
    );
}

function DeleteItemDialog({
    team,
    item,
    onOpenChange,
}: {
    team: Props['team'];
    item: Item | null;
    onOpenChange: (open: boolean) => void;
}) {
    if (item === null) {
        return null;
    }

    return (
        <Dialog open onOpenChange={onOpenChange}>
            <DialogContent>
                <Form
                    {...destroy.form([team.slug, item.id])}
                    className="grid gap-5"
                    onSuccess={() => onOpenChange(false)}
                >
                    {({ processing }) => (
                        <>
                            <DialogHeader>
                                <DialogTitle>Delete item?</DialogTitle>
                                <DialogDescription>
                                    This permanently removes{' '}
                                    <strong>{item.name}</strong>.
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
                                    Delete item
                                </Button>
                            </DialogFooter>
                        </>
                    )}
                </Form>
            </DialogContent>
        </Dialog>
    );
}

function capitalize(value: string): string {
    return value[0].toUpperCase() + value.slice(1);
}

function formatPrice(value: string): string {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
    }).format(Number(value));
}
