<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    Building2,
    Eye,
    FileStack,
    Monitor,
    TrendingDown,
    TrendingUp,
    User,
    UserX,
} from 'lucide-vue-next';
import { computed } from 'vue';
import { useDateTime } from '@/composables/useDateTime';
import { useGreeting } from '@/composables/useGreeting';
import SuperadminLayout from '@/layouts/SuperadminLayout.vue';
import superadmin from '@/routes/superadmin';
import { type BreadcrumbItem } from '@/types';

type Props = {
    user: { name: string };
    total_users: number;
    total_companies: number;
    active_companies: number;
    inactive_companies: number;
    total_active_domains: number;
    total_change_percent: number;
    active_change_percent: number;
    inactive_change_percent: number;
    domains_change_percent: number;
    new_companies_this_week: number;
    most_ordered_plan: {
        name: string;
        total_order: number;
        amount: number;
    } | null;
    top_company: {
        name: string;
        email: string;
        plans_count: number;
    } | null;
    most_domains: {
        name: string;
        email: string;
        users_count: number;
    } | null;
    latest_companies: Array<{ id: number; name: string; plan: string; due_date: string }>;
    earnings_chart: Array<{ month: string; amount: number }>;
    recent_plan_expired: Array<{
        company: string;
        plan: string;
        expired_on: string;
    }>;
    recent_domain: Array<{ company: string; plan: string }>;
    companies_registered_chart: Array<{ day: string; count: number }>;
    top_plans: Array<{ name: string; price: number; count: number; sales?: number }>;
    invoices: Array<{
        id: string;
        company: string;
        plan: string;
        created_on: string;
        expiring_on: string;
        amount: number;
        payment_mode: string;
    }>;
};

const props = defineProps<Props>();
const { greeting } = useGreeting();
const { formatted: dateTime } = useDateTime();

const dashboardUrl =
    typeof superadmin.dashboard?.url === 'function'
        ? superadmin.dashboard.url()
        : '/superadmin/dashboard';
const companiesUrl =
    superadmin.companies?.index?.url?.() ?? '/superadmin/companies';
const packagesUrl =
    superadmin.packages?.index?.url?.() ?? '/superadmin/packages';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Home', href: dashboardUrl },
    { title: 'Super Admin', href: dashboardUrl },
    { title: 'Dashboard', href: dashboardUrl },
];

const formatCurrency = (n: number) =>
    `$${n.toLocaleString(undefined, {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    })}`;

// Date range for header (e.g. 02/19/2026 - 02/25/2026)
const dateRange = computed(() => {
    const end = new Date();
    const start = new Date();
    start.setDate(start.getDate() - 6);
    return `${start.toLocaleDateString('en-US', {
        month: '2-digit',
        day: '2-digit',
        year: 'numeric',
    })} - ${end.toLocaleDateString('en-US', {
        month: '2-digit',
        day: '2-digit',
        year: 'numeric',
    })}`;
});

const earningsMax = computed(
    () =>
        Math.max(
            ...(props.earnings_chart?.map((e) => e.amount) ?? [1]),
            1,
        ),
);

const companiesChartMax = computed(
    () =>
        Math.max(
            ...(props.companies_registered_chart?.map((c) => c.count) ?? [1]),
            1,
        ),
);

const planColors = [
    'bg-primary',
    'bg-blue-500',
    'bg-emerald-500',
    'bg-amber-500',
    'bg-rose-500',
];
</script>

<template>
    <Head title="Super Admin Dashboard" />

    <SuperadminLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-4"
        >
            <!-- Header: Dashboard title + date range -->
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <h1 class="text-xl font-semibold">Dashboard</h1>
                <div class="flex items-center gap-2 rounded-md border border-input px-3 py-2 text-sm">
                    <span class="text-muted-foreground">{{ dateRange }}</span>
                </div>
            </div>

            <!-- Greeting card -->
            <div
                class="flex flex-col gap-4 rounded-xl bg-primary p-6 text-primary-foreground md:flex-row md:items-center md:justify-between"
            >
                <div>
                    <h2 class="text-2xl font-semibold">
                        {{ greeting }}, {{ props.user?.name ?? 'Admin' }}
                    </h2>
                    <p class="mt-1 text-primary-foreground/90">
                        {{ props.new_companies_this_week }} New Companies
                        Subscribed.
                    </p>
                    <p class="mt-2 text-sm text-primary-foreground/80">
                        {{ dateTime }}
                    </p>
                    <div class="mt-4 flex gap-2">
                        <Link
                            :href="companiesUrl"
                            class="inline-flex items-center rounded-md bg-primary-foreground px-4 py-2 text-sm font-medium text-primary hover:bg-primary-foreground/90"
                        >
                            View Companies
                        </Link>
                        <Link
                            :href="packagesUrl"
                            class="inline-flex items-center rounded-md border border-primary-foreground/50 px-4 py-2 text-sm font-medium hover:bg-primary-foreground/10"
                        >
                            All Packages
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Summary stats: Total Users, Active, Inactive, Total Active Companies -->
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div
                    class="flex items-center gap-4 rounded-xl border border-sidebar-border/70 bg-card p-4 dark:border-sidebar-border"
                >
                    <FileStack class="size-10 text-muted-foreground" />
                    <div>
                        <p class="text-2xl font-bold">{{ props.total_users }}</p>
                        <p class="text-sm text-muted-foreground">
                            Total Users
                        </p>
                        <p
                            v-if="props.total_change_percent !== 0"
                            :class="[
                                'flex items-center gap-1 text-xs',
                                props.total_change_percent >= 0
                                    ? 'text-green-600'
                                    : 'text-red-600',
                            ]"
                        >
                            <TrendingUp
                                v-if="props.total_change_percent >= 0"
                                class="size-3"
                            />
                            <TrendingDown v-else class="size-3" />
                            {{ Math.abs(props.total_change_percent) }}% last
                            month
                        </p>
                    </div>
                </div>
                <div
                    class="flex items-center gap-4 rounded-xl border border-sidebar-border/70 bg-card p-4 dark:border-sidebar-border"
                >
                    <User class="size-10 text-muted-foreground" />
                    <div>
                        <p class="text-2xl font-bold">
                            {{ props.active_companies }}
                        </p>
                        <p class="text-sm text-muted-foreground">
                            Active Users
                        </p>
                        <p
                            v-if="props.active_change_percent !== 0"
                            :class="[
                                'flex items-center gap-1 text-xs',
                                props.active_change_percent >= 0
                                    ? 'text-green-600'
                                    : 'text-red-600',
                            ]"
                        >
                            <TrendingUp
                                v-if="props.active_change_percent >= 0"
                                class="size-3"
                            />
                            <TrendingDown v-else class="size-3" />
                            {{ Math.abs(props.active_change_percent) }}% last
                            month
                        </p>
                    </div>
                </div>
                <div
                    class="flex items-center gap-4 rounded-xl border border-sidebar-border/70 bg-card p-4 dark:border-sidebar-border"
                >
                    <UserX class="size-10 text-muted-foreground" />
                    <div>
                        <p class="text-2xl font-bold">
                            {{ props.inactive_companies }}
                        </p>
                        <p class="text-sm text-muted-foreground">
                            Inactive Users
                        </p>
                        <p
                            v-if="props.inactive_change_percent !== 0"
                            class="flex items-center gap-1 text-xs text-red-600"
                        >
                            <TrendingUp class="size-3" />
                            {{ Math.abs(props.inactive_change_percent) }}% last
                            month
                        </p>
                    </div>
                </div>
                <div
                    class="flex items-center gap-4 rounded-xl border border-sidebar-border/70 bg-card p-4 dark:border-sidebar-border"
                >
                    <Monitor class="size-10 text-muted-foreground" />
                    <div>
                        <p class="text-2xl font-bold">
                            {{ props.total_active_domains || props.active_companies }}
                        </p>
                        <p class="text-sm text-muted-foreground">
                            Total Active Companies
                        </p>
                        <p
                            v-if="props.domains_change_percent !== 0"
                            class="flex items-center gap-1 text-xs text-green-600"
                        >
                            <TrendingUp class="size-3" />
                            {{ props.domains_change_percent }}% last month
                        </p>
                    </div>
                </div>
            </div>

            <!-- KPI: Most Ordered Plan, Top Company, Most Domains -->
            <div class="grid gap-4 md:grid-cols-3">
                <div
                    class="rounded-xl border border-sidebar-border/70 bg-card p-4 dark:border-sidebar-border"
                >
                    <div class="flex items-center justify-between">
                        <h3 class="text-sm font-medium text-muted-foreground">
                            Most Ordered Plan
                        </h3>
                        <span
                            class="rounded bg-primary/10 px-2 py-0.5 text-xs text-primary"
                        >
                            This Month
                        </span>
                    </div>
                    <p v-if="props.most_ordered_plan" class="mt-2 font-semibold">
                        {{ props.most_ordered_plan.name }}
                    </p>
                    <p
                        v-if="props.most_ordered_plan"
                        class="mt-1 text-sm text-muted-foreground"
                    >
                        Total Order: {{ props.most_ordered_plan.total_order }}
                    </p>
                    <p
                        v-if="props.most_ordered_plan"
                        class="mt-2 text-lg font-bold"
                    >
                        {{ formatCurrency(props.most_ordered_plan.amount) }}
                    </p>
                    <p v-else class="mt-4 text-muted-foreground">—</p>
                </div>
                <div
                    class="rounded-xl border border-sidebar-border/70 bg-card p-4 dark:border-sidebar-border"
                >
                    <div class="flex items-center justify-between">
                        <h3 class="text-sm font-medium text-muted-foreground">
                            Top Company with Plan
                        </h3>
                        <span
                            class="rounded bg-primary/10 px-2 py-0.5 text-xs text-primary"
                        >
                            Today
                        </span>
                    </div>
                    <p v-if="props.top_company" class="mt-2 font-semibold">
                        {{ props.top_company.name }}
                    </p>
                    <p
                        v-if="props.top_company"
                        class="mt-1 text-sm text-muted-foreground"
                    >
                        {{ props.top_company.email }}
                    </p>
                    <p
                        v-if="props.top_company"
                        class="mt-2 text-lg font-bold"
                    >
                        {{ props.top_company.plans_count }} Plans
                    </p>
                    <p v-else class="mt-4 text-muted-foreground">—</p>
                </div>
                <div
                    class="rounded-xl border border-sidebar-border/70 bg-card p-4 dark:border-sidebar-border"
                >
                    <div class="flex items-center justify-between">
                        <h3 class="text-sm font-medium text-muted-foreground">
                            Most Domains
                        </h3>
                        <span
                            class="rounded bg-primary/10 px-2 py-0.5 text-xs text-primary"
                        >
                            This Week
                        </span>
                    </div>
                    <p v-if="props.most_domains" class="mt-2 font-semibold">
                        {{ props.most_domains.name }}
                    </p>
                    <p
                        v-if="props.most_domains"
                        class="mt-1 text-sm text-muted-foreground"
                    >
                        {{ props.most_domains.email }}
                    </p>
                    <p
                        v-if="props.most_domains"
                        class="mt-2 text-lg font-bold"
                    >
                        {{ props.most_domains.users_count }} Users
                    </p>
                    <p v-else class="mt-4 text-muted-foreground">—</p>
                </div>
            </div>

            <!-- Latest Companies | Earnings Chart -->
            <div class="grid gap-6 lg:grid-cols-2">
                <div
                    class="rounded-xl border border-sidebar-border/70 bg-card dark:border-sidebar-border"
                >
                    <div class="flex items-center justify-between border-b border-sidebar-border/70 px-4 py-3 dark:border-sidebar-border">
                        <h3 class="font-semibold">
                            Latest Registered Companies
                        </h3>
                        <Link
                            :href="companiesUrl"
                            class="text-sm text-primary hover:underline"
                        >
                            View all
                        </Link>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr
                                    class="border-b border-sidebar-border/70 dark:border-sidebar-border"
                                >
                                    <th class="px-4 py-3 text-left font-medium">
                                        Company
                                    </th>
                                    <th class="px-4 py-3 text-left font-medium">
                                        Plan
                                    </th>
                                    <th class="px-4 py-3 text-left font-medium">
                                        Due Date
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="c in props.latest_companies"
                                    :key="c.id"
                                    class="border-b border-sidebar-border/70 last:border-0 dark:border-sidebar-border"
                                >
                                    <td class="px-4 py-3 font-medium">
                                        {{ c.name }}
                                    </td>
                                    <td class="px-4 py-3">{{ c.plan }}</td>
                                    <td class="px-4 py-3 text-muted-foreground">
                                        {{ c.due_date }}
                                    </td>
                                </tr>
                                <tr
                                    v-if="!props.latest_companies?.length"
                                >
                                    <td
                                        colspan="3"
                                        class="px-4 py-8 text-center text-muted-foreground"
                                    >
                                        No companies yet
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div
                    class="rounded-xl border border-sidebar-border/70 bg-card p-4 dark:border-sidebar-border"
                >
                    <h3 class="font-semibold">Earnings</h3>
                    <p class="text-sm text-muted-foreground">Income {{ new Date().getFullYear() }}</p>
                    <div class="mt-4 flex h-32 items-end justify-between gap-1">
                        <div
                            v-for="m in props.earnings_chart"
                            :key="m.month"
                            class="flex flex-1 flex-col items-center gap-1"
                        >
                            <div
                                class="w-full rounded-t bg-primary transition-all"
                                :style="{
                                    height: `${
                                        (m.amount / earningsMax) * 100
                                    }%`,
                                    minHeight: m.amount ? '4px' : '0',
                                }"
                            />
                            <span class="text-xs text-muted-foreground">
                                {{ m.month }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Plan Expired | Recent Domain -->
            <div class="grid gap-6 lg:grid-cols-2">
                <div
                    class="rounded-xl border border-sidebar-border/70 bg-card dark:border-sidebar-border"
                >
                    <h3 class="border-b border-sidebar-border/70 px-4 py-3 font-semibold dark:border-sidebar-border">
                        Recent Plan Expired
                    </h3>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr
                                    class="border-b border-sidebar-border/70 dark:border-sidebar-border"
                                >
                                    <th class="px-4 py-3 text-left font-medium">
                                        Company
                                    </th>
                                    <th class="px-4 py-3 text-left font-medium">
                                        Plan
                                    </th>
                                    <th class="px-4 py-3 text-left font-medium">
                                        Expired On
                                    </th>
                                    <th class="px-4 py-3 text-left font-medium">
                                        Status
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="(e, i) in props.recent_plan_expired"
                                    :key="i"
                                    class="border-b border-sidebar-border/70 last:border-0 dark:border-sidebar-border"
                                >
                                    <td class="px-4 py-3 font-medium">
                                        {{ e.company }}
                                    </td>
                                    <td class="px-4 py-3">{{ e.plan }}</td>
                                    <td class="px-4 py-3">
                                        {{ e.expired_on }}
                                    </td>
                                    <td class="px-4 py-3">
                                        <Eye class="inline size-4 text-muted-foreground" />
                                    </td>
                                </tr>
                                <tr
                                    v-if="!props.recent_plan_expired?.length"
                                >
                                    <td
                                        colspan="4"
                                        class="px-4 py-8 text-center text-muted-foreground"
                                    >
                                        No expired plans
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div
                    class="rounded-xl border border-sidebar-border/70 bg-card dark:border-sidebar-border"
                >
                    <h3 class="border-b border-sidebar-border/70 px-4 py-3 font-semibold dark:border-sidebar-border">
                        Recent Domain
                    </h3>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr
                                    class="border-b border-sidebar-border/70 dark:border-sidebar-border"
                                >
                                    <th class="px-4 py-3 text-left font-medium">
                                        Company
                                    </th>
                                    <th class="px-4 py-3 text-left font-medium">
                                        Plan
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="(d, i) in props.recent_domain"
                                    :key="i"
                                    class="border-b border-sidebar-border/70 last:border-0 dark:border-sidebar-border"
                                >
                                    <td class="px-4 py-3 font-medium">
                                        {{ d.company }}
                                    </td>
                                    <td class="px-4 py-3">{{ d.plan }}</td>
                                </tr>
                                <tr
                                    v-if="!props.recent_domain?.length"
                                >
                                    <td
                                        colspan="2"
                                        class="px-4 py-8 text-center text-muted-foreground"
                                    >
                                        No domains yet
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Companies Registered Chart | Top Plans -->
            <div class="grid gap-6 lg:grid-cols-2">
                <div
                    class="rounded-xl border border-sidebar-border/70 bg-card p-4 dark:border-sidebar-border"
                >
                    <div class="flex items-center justify-between">
                        <h3 class="font-semibold">Companies Registered</h3>
                        <span
                            class="rounded bg-primary/10 px-2 py-0.5 text-xs text-primary"
                        >
                            This Week
                        </span>
                    </div>
                    <div
                        class="relative mt-4 flex h-32 items-end justify-between gap-2"
                    >
                        <div
                            v-for="d in props.companies_registered_chart"
                            :key="d.day"
                            class="flex flex-1 flex-col items-center gap-1"
                        >
                            <div
                                class="w-full rounded-t bg-primary/60 transition-all"
                                :style="{
                                    height: `${
                                        (d.count / companiesChartMax) * 100
                                    }%`,
                                    minHeight: d.count ? '4px' : '0',
                                }"
                            />
                            <span class="text-xs text-muted-foreground">
                                {{ d.day }}
                            </span>
                        </div>
                    </div>
                </div>

                <div
                    class="rounded-xl border border-sidebar-border/70 bg-card dark:border-sidebar-border"
                >
                    <div class="flex items-center justify-between border-b border-sidebar-border/70 px-4 py-3 dark:border-sidebar-border">
                        <h3 class="font-semibold">Top Plans</h3>
                        <Link
                            :href="packagesUrl"
                            class="text-sm text-primary hover:underline"
                        >
                            View all
                        </Link>
                    </div>
                    <div class="divide-y divide-sidebar-border/70">
                        <div
                            v-for="(plan, i) in props.top_plans"
                            :key="plan.name"
                            class="flex items-center gap-3 px-4 py-3"
                        >
                            <span
                                class="h-10 w-1 shrink-0 rounded-full"
                                :class="planColors[i % planColors.length]"
                            />
                            <div class="min-w-0 flex-1">
                                <p class="font-medium">{{ plan.name }}</p>
                                <p class="text-xs text-muted-foreground">
                                    Sales:
                                    {{
                                        formatCurrency(
                                            plan.sales ??
                                                plan.price * plan.count,
                                        )
                                    }}
                                    ; {{ plan.count }}
                                </p>
                            </div>
                        </div>
                        <div
                            v-if="!props.top_plans?.length"
                            class="px-4 py-8 text-center text-muted-foreground"
                        >
                            No plans yet
                        </div>
                    </div>
                </div>
            </div>

            <!-- Invoices table -->
            <div
                class="rounded-xl border border-sidebar-border/70 bg-card dark:border-sidebar-border"
            >
                <div class="flex items-center justify-between border-b border-sidebar-border/70 px-4 py-3 dark:border-sidebar-border">
                    <h3 class="font-semibold">Invoices</h3>
                    <span class="text-sm text-primary">View all invoices</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr
                                class="border-b border-sidebar-border/70 dark:border-sidebar-border"
                            >
                                <th class="px-4 py-3 text-left font-medium">
                                    ID
                                </th>
                                <th class="px-4 py-3 text-left font-medium">
                                    Company
                                </th>
                                <th class="px-4 py-3 text-left font-medium">
                                    Plan
                                </th>
                                <th class="px-4 py-3 text-left font-medium">
                                    Created On
                                </th>
                                <th class="px-4 py-3 text-left font-medium">
                                    Expiring On
                                </th>
                                <th class="px-4 py-3 text-left font-medium">
                                    Amount
                                </th>
                                <th class="px-4 py-3 text-left font-medium">
                                    Payment Mode
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="inv in props.invoices"
                                :key="inv.id"
                                class="border-b border-sidebar-border/70 last:border-0 dark:border-sidebar-border"
                            >
                                <td class="px-4 py-3 font-medium">
                                    {{ inv.id }}
                                </td>
                                <td class="px-4 py-3">{{ inv.company }}</td>
                                <td class="px-4 py-3">{{ inv.plan }}</td>
                                <td class="px-4 py-3">{{ inv.created_on }}</td>
                                <td class="px-4 py-3">{{ inv.expiring_on }}</td>
                                <td class="px-4 py-3">
                                    {{ formatCurrency(inv.amount) }}
                                </td>
                                <td class="px-4 py-3">{{ inv.payment_mode }}</td>
                            </tr>
                            <tr v-if="!props.invoices?.length">
                                <td
                                    colspan="7"
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
    </SuperadminLayout>
</template>
