import { Form, Head } from '@inertiajs/react';
import { Pencil, Plus, Search, Trash2 } from 'lucide-react';
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
import { destroy, store, update } from '@/routes/chart-of-accounts';
import type { ChartOfAccount } from '@/types';

type Props = {
    team: { name: string; slug: string };
    accounts: ChartOfAccount[];
};

const accountTypes: ChartOfAccount['type'][] = [
    'asset',
    'liability',
    'equity',
    'revenue',
    'expense',
];

const accountTypeStyles: Record<ChartOfAccount['type'], string> = {
    asset: 'border-blue-200 bg-blue-50 text-blue-700 dark:border-blue-900 dark:bg-blue-950/40 dark:text-blue-300',
    liability: 'border-orange-200 bg-orange-50 text-orange-700 dark:border-orange-900 dark:bg-orange-950/40 dark:text-orange-300',
    equity: 'border-purple-200 bg-purple-50 text-purple-700 dark:border-purple-900 dark:bg-purple-950/40 dark:text-purple-300',
    revenue: 'border-green-200 bg-green-50 text-green-700 dark:border-green-900 dark:bg-green-950/40 dark:text-green-300',
    expense: 'border-red-200 bg-red-50 text-red-700 dark:border-red-900 dark:bg-red-950/40 dark:text-red-300',
};

export default function ChartOfAccountsIndex({ team, accounts }: Props) {
    const [query, setQuery] = useState('');
    const [activeType, setActiveType] = useState<ChartOfAccount['type'] | 'all'>(
        'all',
    );
    const [isCreating, setIsCreating] = useState(false);
    const [accountEditing, setAccountEditing] =
        useState<ChartOfAccount | null>(null);
    const [accountDeleting, setAccountDeleting] =
        useState<ChartOfAccount | null>(null);

    const visibleAccounts = useMemo(() => {
        const normalizedQuery = query.trim().toLowerCase();

        return accounts.filter((account) => {
            const matchesType =
                activeType === 'all' || account.type === activeType;
            const matchesQuery =
                normalizedQuery === '' ||
                account.code.toLowerCase().includes(normalizedQuery) ||
                account.name.toLowerCase().includes(normalizedQuery);

            return matchesType && matchesQuery;
        });
    }, [accounts, activeType, query]);

    return (
        <AppLayout showHeader={false}>
            <Head title="Chart of accounts" />

            <div className="p-4 md:p-6">
                <div className="flex flex-col gap-6">
                    <header className="flex flex-col gap-4 border-b pb-5 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <p className="text-sm text-muted-foreground">
                                {team.name} / Accounting
                            </p>
                            <h1 className="mt-1 text-2xl font-semibold tracking-tight">
                                Chart of accounts
                            </h1>
                            <p className="mt-1 text-sm text-muted-foreground">
                                Define the accounts used across your general ledger.
                            </p>
                        </div>
                        <Button onClick={() => setIsCreating(true)}>
                            <Plus /> New account
                        </Button>
                    </header>

                    <section className="overflow-hidden rounded-lg border bg-background">
                        <div className="flex flex-col gap-4 border-b p-4 lg:flex-row lg:items-center lg:justify-between">
                            <div className="flex items-center gap-2 text-sm text-muted-foreground">
                                <span className="font-medium text-foreground">
                                    {accounts.length}
                                </span>
                                total accounts
                            </div>
                            <div className="relative w-full lg:w-72">
                                <Search className="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-muted-foreground" />
                                <Input
                                    value={query}
                                    onChange={(event) =>
                                        setQuery(event.target.value)
                                    }
                                    placeholder="Search code or name"
                                    className="pl-9"
                                />
                            </div>
                        </div>

                        <div className="flex gap-1 overflow-x-auto border-b px-4 py-3">
                            <FilterButton
                                active={activeType === 'all'}
                                onClick={() => setActiveType('all')}
                            >
                                All
                            </FilterButton>
                            {accountTypes.map((type) => (
                                <FilterButton
                                    key={type}
                                    active={activeType === type}
                                    onClick={() => setActiveType(type)}
                                >
                                    {capitalize(type)}
                                </FilterButton>
                            ))}
                        </div>

                        <div className="overflow-x-auto">
                            <table className="w-full min-w-[44rem] text-sm">
                                <thead className="bg-muted/40 text-left text-xs font-medium uppercase tracking-wider text-muted-foreground">
                                    <tr>
                                        <th className="px-5 py-3 font-medium">
                                            Account code
                                        </th>
                                        <th className="px-5 py-3 font-medium">
                                            Account name
                                        </th>
                                        <th className="px-5 py-3 font-medium">
                                            Type
                                        </th>
                                        <th className="px-5 py-3 text-right font-medium">
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y">
                                    {visibleAccounts.map((account) => (
                                        <tr
                                            key={account.id}
                                            className="transition-colors hover:bg-muted/30"
                                        >
                                            <td className="px-5 py-4 font-mono text-xs text-muted-foreground">
                                                {account.code}
                                            </td>
                                            <td className="px-5 py-4 font-medium">
                                                {account.name}
                                            </td>
                                            <td className="px-5 py-4">
                                                <Badge
                                                    variant="outline"
                                                    className={
                                                        accountTypeStyles[
                                                            account.type
                                                        ]
                                                    }
                                                >
                                                    {capitalize(account.type)}
                                                </Badge>
                                            </td>
                                            <td className="px-5 py-4">
                                                <div className="flex justify-end gap-1">
                                                    <Button
                                                        type="button"
                                                        variant="ghost"
                                                        size="icon"
                                                        aria-label={`Edit ${account.name}`}
                                                        onClick={() =>
                                                            setAccountEditing(
                                                                account,
                                                            )
                                                        }
                                                    >
                                                        <Pencil />
                                                    </Button>
                                                    <Button
                                                        type="button"
                                                        variant="ghost"
                                                        size="icon"
                                                        className="text-destructive hover:text-destructive"
                                                        aria-label={`Delete ${account.name}`}
                                                        onClick={() =>
                                                            setAccountDeleting(
                                                                account,
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

                        {visibleAccounts.length === 0 ? (
                            <div className="px-5 py-16 text-center">
                                <p className="font-medium">
                                    No accounts to display
                                </p>
                                <p className="mt-1 text-sm text-muted-foreground">
                                    Create an account or change your search and
                                    filter.
                                </p>
                            </div>
                        ) : null}
                    </section>
                </div>
            </div>

            <AccountFormDialog
                team={team}
                open={isCreating}
                onOpenChange={setIsCreating}
            />
            <AccountFormDialog
                team={team}
                account={accountEditing}
                open={accountEditing !== null}
                onOpenChange={(open) => {
                    if (!open) {
                        setAccountEditing(null);
                    }
                }}
            />
            <DeleteAccountDialog
                team={team}
                account={accountDeleting}
                onOpenChange={(open) => {
                    if (!open) {
                        setAccountDeleting(null);
                    }
                }}
            />
        </AppLayout>
    );
}

function AccountFormDialog({
    team,
    account,
    open,
    onOpenChange,
}: {
    team: Props['team'];
    account?: ChartOfAccount | null;
    open: boolean;
    onOpenChange: (open: boolean) => void;
}) {
    const isEditing = account !== undefined && account !== null;
    const form = isEditing
        ? update.form([team.slug, account.id])
        : store.form(team.slug);

    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent>
                <Form
                    key={account?.id ?? 'new'}
                    {...form}
                    className="grid gap-5"
                    onSuccess={() => onOpenChange(false)}
                >
                    {({ errors, processing }) => (
                        <>
                            <DialogHeader>
                                <DialogTitle>
                                    {isEditing
                                        ? 'Edit account'
                                        : 'New account'}
                                </DialogTitle>
                                <DialogDescription>
                                    {isEditing
                                        ? 'Update this account’s details.'
                                        : 'Add an account to the general ledger.'}
                                </DialogDescription>
                            </DialogHeader>
                            <AccountFields account={account} errors={errors} />
                            <DialogFooter>
                                <DialogClose asChild>
                                    <Button type="button" variant="secondary">
                                        Cancel
                                    </Button>
                                </DialogClose>
                                <Button type="submit" disabled={processing}>
                                    {isEditing ? 'Save changes' : 'Create account'}
                                </Button>
                            </DialogFooter>
                        </>
                    )}
                </Form>
            </DialogContent>
        </Dialog>
    );
}

function AccountFields({
    account,
    errors,
}: {
    account?: ChartOfAccount | null;
    errors: Record<string, string>;
}) {
    return (
        <div className="grid gap-4">
            <div className="grid gap-2">
                <Label htmlFor="account-code">Account code</Label>
                <Input
                    id="account-code"
                    name="code"
                    defaultValue={account?.code}
                    placeholder="e.g. 1000"
                    required
                />
                <InputError message={errors.code} />
            </div>
            <div className="grid gap-2">
                <Label htmlFor="account-name">Account name</Label>
                <Input
                    id="account-name"
                    name="name"
                    defaultValue={account?.name}
                    placeholder="e.g. Cash"
                    required
                />
                <InputError message={errors.name} />
            </div>
            <div className="grid gap-2">
                <Label htmlFor="account-type">Account type</Label>
                <select
                    id="account-type"
                    name="type"
                    defaultValue={account?.type ?? 'asset'}
                    className="border-input bg-background focus-visible:border-ring focus-visible:ring-ring/50 h-9 rounded-md border px-3 text-sm shadow-xs outline-none focus-visible:ring-[3px]"
                >
                    {accountTypes.map((type) => (
                        <option key={type} value={type}>
                            {capitalize(type)}
                        </option>
                    ))}
                </select>
                <InputError message={errors.type} />
            </div>
        </div>
    );
}

function DeleteAccountDialog({
    team,
    account,
    onOpenChange,
}: {
    team: Props['team'];
    account: ChartOfAccount | null;
    onOpenChange: (open: boolean) => void;
}) {
    if (account === null) {
        return null;
    }

    return (
        <Dialog open onOpenChange={onOpenChange}>
            <DialogContent>
                <Form
                    {...destroy.form([team.slug, account.id])}
                    className="grid gap-5"
                    onSuccess={() => onOpenChange(false)}
                >
                    {({ processing }) => (
                        <>
                            <DialogHeader>
                                <DialogTitle>Delete account?</DialogTitle>
                                <DialogDescription>
                                    This permanently removes{' '}
                                    <strong>{account.name}</strong> from the
                                    chart of accounts.
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
                                    Delete account
                                </Button>
                            </DialogFooter>
                        </>
                    )}
                </Form>
            </DialogContent>
        </Dialog>
    );
}

function FilterButton({
    active,
    onClick,
    children,
}: {
    active: boolean;
    onClick: () => void;
    children: React.ReactNode;
}) {
    return (
        <button
            type="button"
            onClick={onClick}
            className={
                active
                    ? 'rounded-md bg-secondary px-3 py-1.5 text-xs font-medium text-secondary-foreground'
                    : 'rounded-md px-3 py-1.5 text-xs font-medium text-muted-foreground hover:bg-muted hover:text-foreground'
            }
        >
            {children}
        </button>
    );
}

function capitalize(value: string): string {
    return value[0].toUpperCase() + value.slice(1);
}
