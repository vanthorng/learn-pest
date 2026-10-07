import { Head } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';

type Customer = {
    id: number;
    name: string;
    email: string | null;
    phone: string | null;
    address: string | null;
};

type Props = {
    team: { name: string; slug: string };
    customers: Customer[];
};

export default function CustomersIndex({ team, customers }: Props) {
    return (
        <AppLayout showHeader={false}>
            <Head title="Customers" />

            <div className="p-4 md:p-6">
                <header className="border-b pb-5">
                    <p className="text-sm text-muted-foreground">
                        {team.name} / Contacts
                    </p>
                    <h1 className="mt-1 text-2xl font-semibold tracking-tight">
                        Customers
                    </h1>
                    <p className="mt-1 text-sm text-muted-foreground">
                        Customer records for your team.
                    </p>
                </header>

                <section className="mt-6 overflow-hidden rounded-lg border bg-background">
                    <div className="border-b px-5 py-4 text-sm text-muted-foreground">
                        {customers.length} customer{customers.length === 1 ? '' : 's'}
                    </div>
                    <div className="overflow-x-auto">
                        <table className="w-full min-w-[42rem] text-sm">
                            <thead className="bg-muted/40 text-left text-xs font-medium uppercase tracking-wider text-muted-foreground">
                                <tr>
                                    <th className="px-5 py-3 font-medium">Name</th>
                                    <th className="px-5 py-3 font-medium">Email</th>
                                    <th className="px-5 py-3 font-medium">Phone</th>
                                    <th className="px-5 py-3 font-medium">Address</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y">
                                {customers.map((customer) => (
                                    <tr key={customer.id}>
                                        <td className="px-5 py-4 font-medium">
                                            {customer.name}
                                        </td>
                                        <td className="px-5 py-4 text-muted-foreground">
                                            {customer.email ?? '—'}
                                        </td>
                                        <td className="px-5 py-4 text-muted-foreground">
                                            {customer.phone ?? '—'}
                                        </td>
                                        <td className="px-5 py-4 text-muted-foreground">
                                            {customer.address ?? '—'}
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>

                    {customers.length === 0 ? (
                        <p className="px-5 py-14 text-center text-sm text-muted-foreground">
                            No customers yet.
                        </p>
                    ) : null}
                </section>
            </div>
        </AppLayout>
    );
}
