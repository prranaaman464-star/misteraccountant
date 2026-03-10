<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import {
    ArrowUpFromLine,
    Check,
    ChevronDown,
    Columns3,
    FileText,
    Filter,
    Plus,
    Search,
    Settings,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import AppLayout from '@/layouts/AppLayout.vue';
import { getInitials } from '@/composables/useInitials';
import type { BreadcrumbItem } from '@/types';

type StatItem = {
    value: number;
    change: number;
    trend: 'up' | 'down';
};

type InvoiceStatus =
    | 'paid'
    | 'pending'
    | 'overdue'
    | 'refunded'
    | 'partially_paid'
    | 'unpaid'
    | 'draft'
    | 'cancelled'
    | 'upcoming';

type Invoice = {
    id: string;
    customer: { name: string; avatar: string | null };
    created_on: string;
    amount: number;
    paid: number;
    status: InvoiceStatus;
    payment_mode: string;
    due_date: string;
};

type TeamMember = { id: number; name: string };

const props = withDefaults(
    defineProps<{
        stats?: {
            total: StatItem;
            paid: StatItem;
            pending: StatItem;
            overdue: StatItem;
        };
        invoices?: Invoice[];
        teamMembers?: TeamMember[];
    }>(),
    {
        stats: () => ({
            total: { value: 0, change: 0, trend: 'up' as const },
            paid: { value: 0, change: 0, trend: 'up' as const },
            pending: { value: 0, change: 0, trend: 'up' as const },
            overdue: { value: 0, change: 0, trend: 'down' as const },
        }),
        invoices: () => [],
        teamMembers: () => [],
    },
);

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Invoices', href: '/sales/invoices' },
];

const activeTab = ref<InvoiceStatus | 'all'>('all');
const searchQuery = ref('');
const sortBy = ref('latest');
const columnsOpen = ref(false);
const selectedRows = ref<string[]>([]);

const tabs: { key: InvoiceStatus | 'all'; label: string }[] = [
    { key: 'all', label: 'All' },
    { key: 'paid', label: 'Paid' },
    { key: 'overdue', label: 'Overdue' },
    { key: 'upcoming', label: 'Upcoming' },
    { key: 'cancelled', label: 'Cancelled' },
    { key: 'partially_paid', label: 'Partially Paid' },
    { key: 'unpaid', label: 'Unpaid' },
    { key: 'refunded', label: 'Refunded' },
    { key: 'draft', label: 'Draft' },
];

function formatPrice(value: number): string {
    return new Intl.NumberFormat('en-IN', {
        style: 'currency',
        currency: 'INR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(value);
}

function formatDate(dateStr: string): string {
    const d = new Date(dateStr);
    return d.toLocaleDateString('en-GB', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
    });
}

function statusBadgeVariant(status: InvoiceStatus): string {
    const map: Record<InvoiceStatus, string> = {
        paid: 'default',
        refunded: 'secondary',
        pending: 'outline',
        overdue: 'destructive',
        partially_paid: 'outline',
        unpaid: 'outline',
        draft: 'outline',
        cancelled: 'outline',
        upcoming: 'outline',
    };
    return map[status] ?? 'outline';
}

function exportInvoices(): void {
    window.location.href = '/sales/invoices/export';
}

function toggleSelectAll(checked: boolean | 'indeterminate'): void {
    if (checked === true) {
        selectedRows.value = props.invoices.map((i) => i.id);
    } else {
        selectedRows.value = [];
    }
}

function toggleSelectRow(id: string): void {
    const idx = selectedRows.value.indexOf(id);
    if (idx === -1) {
        selectedRows.value.push(id);
    } else {
        selectedRows.value.splice(idx, 1);
    }
}

const isAllSelected = computed(
    () =>
        selectedRows.value.length === props.invoices.length &&
        props.invoices.length > 0,
);

const isSomeSelected = computed(
    () =>
        selectedRows.value.length > 0 &&
        selectedRows.value.length < props.invoices.length,
);

const filteredInvoices = computed(() => {
    let list = props.invoices;
    if (activeTab.value !== 'all') {
        list = list.filter((i) => i.status === activeTab.value);
    }
    if (searchQuery.value.trim()) {
        const q = searchQuery.value.toLowerCase();
        list = list.filter(
            (i) =>
                i.id.toLowerCase().includes(q) ||
                i.customer.name.toLowerCase().includes(q),
        );
    }
    return list;
});
</script>

<template>
    <Head title="Invoices" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-6"
        >
            <!-- Header: Title + Actions -->
            <div
                class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
            >
                <div>
                    <h1
                        class="text-2xl font-bold tracking-tight text-foreground sm:text-3xl"
                    >
                        Invoices
                    </h1>
                    <p class="mt-1 text-sm text-muted-foreground">
                        Manage and track all your invoices in one place
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <Button
                        variant="outline"
                        size="default"
                        class="rounded-lg shadow-sm transition-shadow hover:shadow"
                        @click="exportInvoices"
                    >
                        <ArrowUpFromLine class="size-4" />
                        Export
                    </Button>
                    <Link href="/sales/invoices/create" as="span">
                        <Button
                            size="default"
                            class="rounded-lg shadow-md transition-all hover:shadow-lg"
                        >
                            <Plus class="size-4" />
                            New Invoice
                        </Button>
                    </Link>
                </div>
            </div>

            <!-- Stats cards -->
            <div
                class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4"
            >
                <Card
                    v-for="(stat, key) in stats"
                    :key="key"
                    class="relative overflow-hidden border-sidebar-border/70 transition-shadow hover:shadow-md"
                >
                    <CardContent class="p-6">
                        <div class="flex flex-col gap-2">
                            <span
                                class="text-xs font-medium uppercase tracking-wider text-muted-foreground"
                            >
                                {{ key === 'total' ? 'Total Invoices' : key.charAt(0).toUpperCase() + key.slice(1) + ' Invoices' }}
                            </span>
                            <span
                                class="text-2xl font-bold tracking-tight text-foreground"
                            >
                                {{ formatPrice(stat.value) }}
                            </span>
                            <span
                                class="flex items-center gap-1 text-xs font-medium"
                                :class="
                                    stat.trend === 'up'
                                        ? 'text-emerald-600 dark:text-emerald-400'
                                        : 'text-rose-600 dark:text-rose-400'
                                "
                            >
                                <span v-if="stat.trend === 'up'">↑</span>
                                <span v-else>↓</span>
                                {{ Math.abs(stat.change) }}% from last month
                            </span>
                        </div>
                        <div
                            class="absolute right-5 top-6 flex size-11 items-center justify-center rounded-xl"
                            :class="{
                                'bg-primary/10 text-primary': key === 'total',
                                'bg-emerald-500/15 text-emerald-600 dark:text-emerald-400':
                                    key === 'paid',
                                'bg-amber-500/15 text-amber-600 dark:text-amber-400':
                                    key === 'pending',
                                'bg-rose-500/15 text-rose-600 dark:text-rose-400':
                                    key === 'overdue',
                            }"
                        >
                            <FileText
                                v-if="key === 'total'"
                                class="size-5"
                            />
                            <Check
                                v-else-if="key === 'paid'"
                                class="size-5"
                            />
                            <span
                                v-else-if="key === 'pending'"
                                class="text-base font-bold"
                            >
                                8
                            </span>
                            <span v-else class="text-base font-semibold">!</span>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Tabs + Settings -->
            <div
                class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
            >
                <div class="flex min-w-0 flex-wrap items-center gap-1 overflow-x-auto rounded-lg bg-muted/50 p-1">
                    <button
                        v-for="tab in tabs"
                        :key="tab.key"
                        type="button"
                        class="rounded-md px-3 py-2 text-sm font-medium transition-all"
                        :class="
                            activeTab === tab.key
                                ? 'bg-background text-foreground shadow-sm'
                                : 'text-muted-foreground hover:text-foreground'
                        "
                        @click="activeTab = tab.key"
                    >
                        {{ tab.label }}
                    </button>
                </div>
                <Button
                    variant="ghost"
                    size="icon"
                    class="size-9 shrink-0 rounded-lg"
                    aria-label="Table settings"
                >
                    <Settings class="size-5" />
                </Button>
            </div>

            <!-- Table controls -->
            <div
                class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
            >
                <div class="flex min-w-0 flex-1 flex-wrap items-center gap-2">
                    <div
                        class="flex min-w-0 flex-1 items-center gap-2 rounded-lg border border-input bg-background px-3 py-2 shadow-sm sm:max-w-xs"
                    >
                        <Search
                            class="size-4 shrink-0 text-muted-foreground"
                            aria-hidden
                        />
                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Search invoices..."
                            class="min-w-0 flex-1 bg-transparent text-sm outline-none placeholder:text-muted-foreground"
                        />
                    </div>
                    <Button
                        variant="outline"
                        size="default"
                        class="rounded-lg shadow-sm"
                    >
                        <Filter class="size-4" />
                        Filter
                    </Button>
                </div>
                <div class="flex shrink-0 gap-2">
                    <DropdownMenu v-model:open="columnsOpen">
                        <DropdownMenuTrigger as-child>
                            <Button
                                variant="outline"
                                size="default"
                                class="rounded-lg shadow-sm"
                            >
                                Sort By: Latest
                                <ChevronDown class="ml-1 size-4" />
                            </Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent align="end" class="w-40">
                            <button
                                type="button"
                                class="w-full px-4 py-2 text-left text-sm hover:bg-muted/80"
                                @click="sortBy = 'latest'"
                            >
                                Latest
                            </button>
                            <button
                                type="button"
                                class="w-full px-4 py-2 text-left text-sm hover:bg-muted/80"
                                @click="sortBy = 'oldest'"
                            >
                                Oldest
                            </button>
                        </DropdownMenuContent>
                    </DropdownMenu>
                    <Button
                        variant="outline"
                        size="default"
                        class="rounded-lg shadow-sm"
                    >
                        <Columns3 class="size-4" />
                        Column
                        <ChevronDown class="ml-1 size-4" />
                    </Button>
                </div>
            </div>

            <!-- Invoice table -->
            <div
                class="flex-1 overflow-auto rounded-xl border border-sidebar-border/70 bg-card shadow-sm"
            >
                <table class="w-full caption-bottom text-sm">
                    <thead>
                        <tr
                            class="border-b border-sidebar-border/70 bg-muted/40"
                        >
                            <th class="h-12 w-12 px-4 text-left align-middle">
                                <Checkbox
                                    :model-value="
                                        isAllSelected
                                            ? true
                                            : isSomeSelected
                                              ? 'indeterminate'
                                              : false
                                    "
                                    @update:model-value="toggleSelectAll"
                                />
                            </th>
                            <th class="h-12 px-4 text-left font-medium text-muted-foreground">
                                ID
                            </th>
                            <th class="h-12 px-4 text-left font-medium text-muted-foreground">
                                Customer
                            </th>
                            <th class="h-12 px-4 text-left font-medium text-muted-foreground">
                                Created On
                            </th>
                            <th class="h-12 px-4 text-left font-medium text-muted-foreground">
                                Amount
                            </th>
                            <th class="h-12 px-4 text-left font-medium text-muted-foreground">
                                Paid
                            </th>
                            <th class="h-12 px-4 text-left font-medium text-muted-foreground">
                                Status
                            </th>
                            <th class="h-12 px-4 text-left font-medium text-muted-foreground">
                                Payment Mode
                            </th>
                            <th class="h-12 px-4 text-left font-medium text-muted-foreground">
                                Due
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="inv in filteredInvoices"
                            :key="inv.id"
                            class="group border-b border-sidebar-border/50 transition-colors hover:bg-muted/40"
                        >
                            <td class="px-4 py-3">
                                <Checkbox
                                    :model-value="selectedRows.includes(inv.id)"
                                    @update:model-value="
                                        () => toggleSelectRow(inv.id)
                                    "
                                />
                            </td>
                            <td class="px-4 py-3 font-medium">
                                {{ inv.id }}
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <Avatar class="size-8 shrink-0 overflow-hidden rounded-full">
                                        <AvatarImage
                                            v-if="inv.customer.avatar"
                                            :src="inv.customer.avatar"
                                            :alt="inv.customer.name"
                                        />
                                        <AvatarFallback
                                            class="rounded-full bg-primary/20 text-xs font-semibold text-primary"
                                        >
                                            {{ getInitials(inv.customer.name) }}
                                        </AvatarFallback>
                                    </Avatar>
                                    <span class="font-medium">
                                        {{ inv.customer.name }}
                                    </span>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-muted-foreground">
                                {{ formatDate(inv.created_on) }}
                            </td>
                            <td class="px-4 py-3 font-medium">
                                {{ formatPrice(inv.amount) }}
                            </td>
                            <td class="px-4 py-3">
                                {{ formatPrice(inv.paid) }}
                            </td>
                            <td class="px-4 py-3">
                                <Badge
                                    :variant="statusBadgeVariant(inv.status)"
                                    class="inline-flex items-center gap-1 capitalize"
                                >
                                    <Check
                                        v-if="inv.status === 'paid' || inv.status === 'refunded'"
                                        class="size-3"
                                    />
                                    {{ inv.status.replace('_', ' ') }}
                                </Badge>
                            </td>
                            <td class="px-4 py-3 text-muted-foreground">
                                {{ inv.payment_mode }}
                            </td>
                            <td class="px-4 py-3 text-muted-foreground">
                                {{ formatDate(inv.due_date).split(' ')[0] }}
                            </td>
                        </tr>
                        <tr v-if="filteredInvoices.length === 0">
                            <td colspan="9" class="p-12">
                                <div
                                    class="flex flex-col items-center justify-center gap-3 rounded-lg border border-dashed border-sidebar-border/70 bg-muted/20 py-12"
                                >
                                    <FileText
                                        class="size-12 text-muted-foreground/50"
                                    />
                                    <p class="text-sm font-medium text-muted-foreground">
                                        No invoices found
                                    </p>
                                    <p class="text-xs text-muted-foreground">
                                        Try adjusting your search or filters
                                    </p>
                                    <Link href="/sales/invoices/create" as="span">
                                        <Button size="sm" class="mt-1">
                                            <Plus class="size-4" />
                                            Create your first invoice
                                        </Button>
                                    </Link>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AppLayout>
</template>
