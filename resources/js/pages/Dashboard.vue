<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    DollarSign,
    FileText,
    Users,
} from 'lucide-vue-next';
import { useDateTime } from '@/composables/useDateTime';
import { useGreeting } from '@/composables/useGreeting';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import inventory from '@/routes/inventory';
import { type BreadcrumbItem } from '@/types';

type Props = {
    user: { name: string };
    draft_invoices_count: number;
    overview: {
        invoices: number;
        customers: number;
        amount: number;
        quotations: number;
    };
    sales_analytics: {
        total_sales: number;
        purchase: number;
        expenses: number;
        credits: number;
    };
    invoice_statistics: {
        invoiced: number;
        received: number;
        outstanding: number;
        overdue: number;
    };
    total_products: number;
    total_sales_count: number;
    total_quotations: number;
    products_change_percent: number;
    sales_change_percent: number;
    quotations_change_percent: number;
    revenue_chart: Array<{ day: string; received: number; outstanding: number }>;
    recent_customers: Array<{
        id: number;
        name: string;
        email?: string;
        invoices_count: number;
        outstanding: number;
    }>;
    recent_invoices: Array<{
        id: string;
        customer_name: string;
        created_on: string;
        amount: number;
        paid: number;
        payment_mode: string;
        due_date: string;
    }>;
    recent_transactions: Array<{
        name: string;
        reference: string;
        amount: number;
        date: string;
    }>;
    recent_quotations: Array<{
        name: string;
        id: string;
        status: string;
        date: string;
    }>;
    total_income_on_invoice: number;
    total_income_change_percent: number;
    top_products: Array<{ name: string; value: number; color?: string }>;
};

const props = defineProps<Props>();
const { greeting } = useGreeting();
const { formatted: dateTime } = useDateTime();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Home', href: dashboard().url },
    { title: 'Dashboard', href: dashboard().url },
];

const formatCurrency = (n: number) =>
    `$${n.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;

const revenueMax = Math.max(
    ...(props.revenue_chart?.flatMap((r: { received: number; outstanding: number }) => [
        r.received,
        r.outstanding,
    ]) ?? [1]),
    1,
);
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-4"
        >
            <!-- Welcome banner with greeting & date/time -->
            <div
                class="flex flex-col gap-4 rounded-xl bg-primary p-6 text-primary-foreground md:flex-row md:items-center md:justify-between"
            >
                <div>
                    <h2 class="text-2xl font-semibold">
                        {{ greeting }}, {{ props.user?.name ?? 'User' }}
                    </h2>
                    <p class="mt-1 text-primary-foreground/90">
                        {{
                            props.draft_invoices_count > 0
                                ? `You have ${props.draft_invoices_count}+ invoices saved to draft that has to send to customers.`
                                : "Here's what's happening with your business today."
                        }}
                    </p>
                    <p class="mt-2 text-sm text-primary-foreground/80">
                        {{ dateTime }}
                    </p>
                </div>
            </div>

            <!-- Overview -->
            <div>
                <h3 class="mb-3 text-lg font-semibold">Overview</h3>
                <div class="grid gap-4 md:grid-cols-4">
                    <div
                        class="flex items-center gap-3 rounded-xl border border-sidebar-border/70 bg-card p-4 dark:border-sidebar-border"
                    >
                        <FileText class="size-8 text-muted-foreground" />
                        <div>
                            <p class="text-xs text-muted-foreground">
                                Invoices
                            </p>
                            <p class="text-lg font-bold">
                                {{ props.overview?.invoices ?? 0 }}
                            </p>
                        </div>
                    </div>
                    <div
                        class="flex items-center gap-3 rounded-xl border border-sidebar-border/70 bg-card p-4 dark:border-sidebar-border"
                    >
                        <Users class="size-8 text-muted-foreground" />
                        <div>
                            <p class="text-xs text-muted-foreground">
                                Customers
                            </p>
                            <p class="text-lg font-bold">
                                {{ props.overview?.customers ?? 0 }}
                            </p>
                        </div>
                    </div>
                    <div
                        class="flex items-center gap-3 rounded-xl border border-sidebar-border/70 bg-card p-4 dark:border-sidebar-border"
                    >
                        <DollarSign class="size-8 text-muted-foreground" />
                        <div>
                            <p class="text-xs text-muted-foreground">
                                Amount
                            </p>
                            <p class="text-lg font-bold">
                                {{ formatCurrency(props.overview?.amount ?? 0) }}
                            </p>
                        </div>
                    </div>
                    <div
                        class="flex items-center gap-3 rounded-xl border border-sidebar-border/70 bg-card p-4 dark:border-sidebar-border"
                    >
                        <FileText class="size-8 text-muted-foreground" />
                        <div>
                            <p class="text-xs text-muted-foreground">
                                Quotations
                            </p>
                            <p class="text-lg font-bold">
                                {{ props.overview?.quotations ?? 0 }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sales Analytics & Invoice Statistics -->
            <div class="grid gap-6 lg:grid-cols-2">
                <div>
                    <h3 class="mb-3 text-lg font-semibold">Sales Analytics</h3>
                    <div class="grid gap-3 md:grid-cols-2">
                        <div
                            class="flex flex-col gap-1 rounded-xl border border-sidebar-border/70 bg-card p-4 dark:border-sidebar-border"
                        >
                            <p class="text-xs text-muted-foreground">
                                Total Sales
                            </p>
                            <p class="text-xl font-bold">
                                {{
                                    formatCurrency(
                                        props.sales_analytics?.total_sales ?? 0,
                                    )
                                }}
                            </p>
                        </div>
                        <div
                            class="flex flex-col gap-1 rounded-xl border border-sidebar-border/70 bg-card p-4 dark:border-sidebar-border"
                        >
                            <p class="text-xs text-muted-foreground">
                                Purchase
                            </p>
                            <p class="text-xl font-bold">
                                {{
                                    formatCurrency(
                                        props.sales_analytics?.purchase ?? 0,
                                    )
                                }}
                            </p>
                        </div>
                        <div
                            class="flex flex-col gap-1 rounded-xl border border-sidebar-border/70 bg-card p-4 dark:border-sidebar-border"
                        >
                            <p class="text-xs text-muted-foreground">
                                Expenses
                            </p>
                            <p class="text-xl font-bold">
                                {{
                                    formatCurrency(
                                        props.sales_analytics?.expenses ?? 0,
                                    )
                                }}
                            </p>
                        </div>
                        <div
                            class="flex flex-col gap-1 rounded-xl border border-sidebar-border/70 bg-card p-4 dark:border-sidebar-border"
                        >
                            <p class="text-xs text-muted-foreground">Credits</p>
                            <p class="text-xl font-bold">
                                {{
                                    formatCurrency(
                                        props.sales_analytics?.credits ?? 0,
                                    )
                                }}
                            </p>
                        </div>
                    </div>
                </div>
                <div>
                    <h3 class="mb-3 text-lg font-semibold">
                        Invoice Statistics
                    </h3>
                    <div class="grid gap-3 md:grid-cols-2">
                        <div
                            class="flex flex-col gap-1 rounded-xl border border-sidebar-border/70 bg-card p-4 dark:border-sidebar-border"
                        >
                            <p class="text-xs text-muted-foreground">
                                Invoiced
                            </p>
                            <p class="text-xl font-bold">
                                {{
                                    formatCurrency(
                                        props.invoice_statistics?.invoiced ??
                                            0,
                                    )
                                }}
                            </p>
                        </div>
                        <div
                            class="flex flex-col gap-1 rounded-xl border border-sidebar-border/70 bg-card p-4 dark:border-sidebar-border"
                        >
                            <p class="text-xs text-muted-foreground">
                                Received
                            </p>
                            <p class="text-xl font-bold">
                                {{
                                    formatCurrency(
                                        props.invoice_statistics?.received ?? 0,
                                    )
                                }}
                            </p>
                        </div>
                        <div
                            class="flex flex-col gap-1 rounded-xl border border-sidebar-border/70 bg-card p-4 dark:border-sidebar-border"
                        >
                            <p class="text-xs text-muted-foreground">
                                Outstanding
                            </p>
                            <p class="text-xl font-bold">
                                {{
                                    formatCurrency(
                                        props.invoice_statistics
                                            ?.outstanding ?? 0,
                                    )
                                }}
                            </p>
                        </div>
                        <div
                            class="flex flex-col gap-1 rounded-xl border border-sidebar-border/70 bg-card p-4 dark:border-sidebar-border"
                        >
                            <p class="text-xs text-muted-foreground">
                                Overdue
                            </p>
                            <p class="text-xl font-bold">
                                {{
                                    formatCurrency(
                                        props.invoice_statistics?.overdue ?? 0,
                                    )
                                }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Products / Sales / Quotations with % change -->
            <div class="grid gap-4 md:grid-cols-3">
                <div
                    class="flex flex-col justify-between rounded-xl border border-sidebar-border/70 bg-card p-4 dark:border-sidebar-border"
                >
                    <div>
                        <p class="text-sm text-muted-foreground">
                            Total Products
                        </p>
                        <p class="flex items-center gap-2 text-xl font-bold">
                            {{ props.total_products ?? 0 }}
                            <span
                                v-if="
                                    (props.products_change_percent ?? 0) !== 0
                                "
                                :class="[
                                    'text-xs',
                                    (props.products_change_percent ?? 0) >= 0
                                        ? 'text-green-600'
                                        : 'text-red-600',
                                ]"
                            >
                                {{
                                    (props.products_change_percent ?? 0) >= 0
                                        ? '+'
                                        : ''
                                }}{{ props.products_change_percent }}%
                            </span>
                        </p>
                    </div>
                    <Link
                        :href="
                            inventory?.items?.url
                                ? inventory.items.url()
                                : '/inventory/items'
                        "
                        class="mt-2 text-sm text-primary hover:underline"
                    >
                        View Inventory
                    </Link>
                </div>
                <div
                    class="flex flex-col justify-between rounded-xl border border-sidebar-border/70 bg-card p-4 dark:border-sidebar-border"
                >
                    <div>
                        <p class="text-sm text-muted-foreground">
                            Total Sales
                        </p>
                        <p class="flex items-center gap-2 text-xl font-bold">
                            {{ props.total_sales_count ?? 0 }}
                            <span
                                v-if="
                                    (props.sales_change_percent ?? 0) !== 0
                                "
                                :class="[
                                    'text-xs',
                                    (props.sales_change_percent ?? 0) >= 0
                                        ? 'text-green-600'
                                        : 'text-red-600',
                                ]"
                            >
                                {{
                                    (props.sales_change_percent ?? 0) >= 0
                                        ? '+'
                                        : ''
                                }}{{ props.sales_change_percent }}%
                            </span>
                        </p>
                    </div>
                    <span class="mt-2 text-sm text-muted-foreground">
                        View Invoices
                    </span>
                </div>
                <div
                    class="flex flex-col justify-between rounded-xl border border-sidebar-border/70 bg-card p-4 dark:border-sidebar-border"
                >
                    <div>
                        <p class="text-sm text-muted-foreground">
                            Total Quotations
                        </p>
                        <p class="flex items-center gap-2 text-xl font-bold">
                            {{ props.total_quotations ?? 0 }}
                            <span
                                v-if="
                                    (props.quotations_change_percent ?? 0) !== 0
                                "
                                :class="[
                                    'text-xs',
                                    (props.quotations_change_percent ?? 0) >= 0
                                        ? 'text-green-600'
                                        : 'text-red-600',
                                ]"
                            >
                                {{
                                    (props.quotations_change_percent ?? 0) >= 0
                                        ? '+'
                                        : ''
                                }}{{ props.quotations_change_percent }}%
                            </span>
                        </p>
                    </div>
                    <span class="mt-2 text-sm text-muted-foreground">
                        View All
                    </span>
                </div>
            </div>

            <!-- Revenue chart -->
            <div
                class="rounded-xl border border-sidebar-border/70 bg-card p-4 dark:border-sidebar-border"
            >
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="font-semibold">
                        Total Revenue
                        <span
                            v-if="
                                (props.products_change_percent ?? 0) !== 0
                            "
                            class="ml-2 text-sm font-normal text-green-600"
                        >
                            +{{ props.products_change_percent }}%
                        </span>
                    </h3>
                </div>
                <div class="flex h-32 items-end justify-between gap-2">
                    <div
                        v-for="bar in props.revenue_chart"
                        :key="bar.day"
                        class="flex flex-1 flex-col items-center gap-1"
                    >
                        <div class="flex h-24 w-full items-end gap-0.5">
                            <div
                                class="flex-1 rounded-t bg-primary/80"
                                :style="{
                                    height: `${
                                        (bar.received / revenueMax) * 100
                                    }%`,
                                    minHeight: bar.received ? '4px' : '0',
                                }"
                                :title="`Received: ${formatCurrency(bar.received)}`"
                            />
                            <div
                                class="flex-1 rounded-t bg-primary/40"
                                :style="{
                                    height: `${
                                        (bar.outstanding / revenueMax) * 100
                                    }%`,
                                    minHeight: bar.outstanding ? '4px' : '0',
                                }"
                                :title="`Outstanding: ${formatCurrency(bar.outstanding)}`"
                            />
                        </div>
                        <span class="text-xs text-muted-foreground">
                            {{ bar.day }}
                        </span>
                    </div>
                </div>
                <div class="mt-2 flex gap-4 text-xs text-muted-foreground">
                    <span class="flex items-center gap-1">
                        <span
                            class="inline-block size-3 rounded bg-primary/80"
                        />
                        Received
                    </span>
                    <span class="flex items-center gap-1">
                        <span
                            class="inline-block size-3 rounded bg-primary/40"
                        />
                        Outstanding
                    </span>
                </div>
            </div>

            <!-- Customers & Invoices row -->
            <div class="grid gap-6 lg:grid-cols-2">
                <div
                    class="rounded-xl border border-sidebar-border/70 bg-card dark:border-sidebar-border"
                >
                    <div class="flex items-center justify-between border-b border-sidebar-border/70 px-4 py-3 dark:border-sidebar-border">
                        <h3 class="font-semibold">Customers</h3>
                        <Link
                            href="/manage/users"
                            class="text-sm text-primary hover:underline"
                        >
                            View All Customers
                        </Link>
                    </div>
                    <div class="divide-y divide-sidebar-border/70">
                        <div
                            v-for="customer in props.recent_customers"
                            :key="customer.id"
                            class="flex items-center justify-between px-4 py-3"
                        >
                            <div>
                                <p class="font-medium">{{ customer.name }}</p>
                                <p
                                    v-if="customer.email"
                                    class="text-xs text-muted-foreground"
                                >
                                    {{ customer.email }}
                                </p>
                            </div>
                            <div class="text-right text-sm">
                                <p v-if="customer.invoices_count">
                                    {{ customer.invoices_count }} invoices
                                </p>
                                <p
                                    v-if="customer.outstanding"
                                    class="font-medium text-amber-600"
                                >
                                    {{ formatCurrency(customer.outstanding) }}
                                </p>
                            </div>
                        </div>
                        <div
                            v-if="!props.recent_customers?.length"
                            class="px-4 py-8 text-center text-muted-foreground"
                        >
                            No customers yet
                        </div>
                    </div>
                </div>

                <div
                    class="rounded-xl border border-sidebar-border/70 bg-card dark:border-sidebar-border"
                >
                    <div class="flex items-center justify-between border-b border-sidebar-border/70 px-4 py-3 dark:border-sidebar-border">
                        <h3 class="font-semibold">Invoices</h3>
                        <span class="text-sm text-primary">View all Invoices</span>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr
                                    class="border-b border-sidebar-border/70 dark:border-sidebar-border"
                                >
                                    <th
                                        class="px-4 py-2 text-left font-medium"
                                    >
                                        ID
                                    </th>
                                    <th
                                        class="px-4 py-2 text-left font-medium"
                                    >
                                        Customer
                                    </th>
                                    <th
                                        class="px-4 py-2 text-left font-medium"
                                    >
                                        Amount
                                    </th>
                                    <th
                                        class="px-4 py-2 text-left font-medium"
                                    >
                                        Due Date
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="inv in props.recent_invoices"
                                    :key="inv.id"
                                    class="border-b border-sidebar-border/70 last:border-0 dark:border-sidebar-border"
                                >
                                    <td class="px-4 py-2">{{ inv.id }}</td>
                                    <td class="px-4 py-2">
                                        {{ inv.customer_name }}
                                    </td>
                                    <td class="px-4 py-2">
                                        {{ formatCurrency(inv.amount) }}
                                    </td>
                                    <td class="px-4 py-2">
                                        {{ inv.due_date }}
                                    </td>
                                </tr>
                                <tr
                                    v-if="!props.recent_invoices?.length"
                                >
                                    <td
                                        colspan="4"
                                        class="px-4 py-8 text-center text-muted-foreground"
                                    >
                                        No invoices yet
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Recent Transactions & Quotations -->
            <div class="grid gap-6 lg:grid-cols-2">
                <div
                    class="rounded-xl border border-sidebar-border/70 bg-card dark:border-sidebar-border"
                >
                    <h3 class="border-b border-sidebar-border/70 px-4 py-3 font-semibold dark:border-sidebar-border">
                        Recent Transactions
                    </h3>
                    <div class="divide-y divide-sidebar-border/70">
                        <div
                            v-for="(tx, i) in props.recent_transactions"
                            :key="i"
                            class="flex items-center justify-between px-4 py-3"
                        >
                            <div>
                                <p class="font-medium">{{ tx.name }}</p>
                                <p class="text-xs text-muted-foreground">
                                    {{ tx.reference }} • {{ tx.date }}
                                </p>
                            </div>
                            <p
                                :class="[
                                    'font-semibold',
                                    tx.amount >= 0
                                        ? 'text-green-600'
                                        : 'text-red-600',
                                ]"
                            >
                                {{ tx.amount >= 0 ? '+' : ''
                                }}{{ formatCurrency(tx.amount) }}
                            </p>
                        </div>
                        <div
                            v-if="!props.recent_transactions?.length"
                            class="px-4 py-8 text-center text-muted-foreground"
                        >
                            No recent transactions
                        </div>
                    </div>
                </div>

                <div
                    class="rounded-xl border border-sidebar-border/70 bg-card dark:border-sidebar-border"
                >
                    <h3 class="border-b border-sidebar-border/70 px-4 py-3 font-semibold dark:border-sidebar-border">
                        Quotations
                    </h3>
                    <div class="divide-y divide-sidebar-border/70">
                        <div
                            v-for="q in props.recent_quotations"
                            :key="q.id"
                            class="flex items-center justify-between px-4 py-3"
                        >
                            <div>
                                <p class="font-medium">{{ q.name }}</p>
                                <p class="text-xs text-muted-foreground">
                                    {{ q.id }} • {{ q.date }}
                                </p>
                            </div>
                            <span
                                :class="[
                                    'rounded-full px-2 py-0.5 text-xs font-medium',
                                    q.status === 'Accepted'
                                        ? 'bg-green-100 text-green-800 dark:bg-green-900/30'
                                        : q.status === 'Sent'
                                          ? 'bg-blue-100 text-blue-800 dark:bg-blue-900/30'
                                          : q.status === 'Expired'
                                            ? 'bg-amber-100 text-amber-800'
                                            : 'bg-muted text-muted-foreground',
                                ]"
                            >
                                {{ q.status }}
                            </span>
                        </div>
                        <div
                            v-if="!props.recent_quotations?.length"
                            class="px-4 py-8 text-center text-muted-foreground"
                        >
                            No quotations yet
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Income & Top Sales -->
            <div class="grid gap-6 lg:grid-cols-2">
                <div
                    class="rounded-xl border border-sidebar-border/70 bg-card p-4 dark:border-sidebar-border"
                >
                    <h3 class="font-semibold">
                        Total Income on Invoice
                    </h3>
                    <p class="mt-2 text-2xl font-bold">
                        {{
                            formatCurrency(
                                props.total_income_on_invoice ?? 0,
                            )
                        }}
                    </p>
                    <p
                        v-if="
                            (props.total_income_change_percent ?? 0) !== 0
                        "
                        class="mt-1 text-sm text-green-600"
                    >
                        {{ props.total_income_change_percent }}% vs Last Week
                    </p>
                </div>

                <div
                    class="rounded-xl border border-sidebar-border/70 bg-card p-4 dark:border-sidebar-border"
                >
                    <h3 class="font-semibold">Top Sales Statistics</h3>
                    <div
                        v-if="props.top_products?.length"
                        class="mt-4 flex flex-wrap gap-4"
                    >
                        <div
                            v-for="(prod, i) in props.top_products"
                            :key="prod.name"
                            class="flex items-center gap-2"
                        >
                            <span
                                class="inline-block size-4 rounded-full"
                                :style="{
                                    backgroundColor:
                                        prod.color ||
                                        ['hsl(var(--primary))', '#ec4899', '#22c55e'][
                                            i % 3
                                        ],
                                }"
                            />
                            <span class="text-sm">{{ prod.name }}</span>
                        </div>
                    </div>
                    <p
                        v-else
                        class="mt-4 text-muted-foreground"
                    >
                        No sales data yet
                    </p>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
