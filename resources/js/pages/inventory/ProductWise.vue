<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import {
    ArrowDownToLine,
    Calendar,
    ChevronDown,
    ChevronLeft,
    ChevronRight,
    Columns3,
    GripVertical,
    Package,
    X,
} from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Input } from '@/components/ui/input';
import { Switch } from '@/components/ui/switch';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import type { BreadcrumbItem } from '@/types';

type ProductWiseRow = {
    id: number;
    name: string;
    item_code: string;
    hsn_sac_code: string;
    buying_price: number;
    selling_price: number;
    total_sold_quantity: number;
    total_purchase_quantity: number;
    current_stock: number;
    average_selling_price: number;
    average_buying_price: number;
    average_landed_cost: number;
    gross_profit_margin: number | null;
    package_item: string;
};

type ColumnKey =
    | 'item'
    | 'package_item'
    | 'sku'
    | 'hsn_sac'
    | 'buying_price'
    | 'selling_price'
    | 'total_sold_quantity'
    | 'total_purchase_quantity'
    | 'current_stock'
    | 'average_selling_price'
    | 'average_buying_price'
    | 'average_landed_cost'
    | 'gross_profit_margin';

const COLUMN_STORAGE_KEY = 'product-wise-columns';

const COLUMN_ORDER: ColumnKey[] = [
    'item',
    'package_item',
    'sku',
    'hsn_sac',
    'buying_price',
    'selling_price',
    'total_sold_quantity',
    'total_purchase_quantity',
    'current_stock',
    'average_selling_price',
    'average_buying_price',
    'average_landed_cost',
    'gross_profit_margin',
];

const COLUMN_CONFIG: Record<
    ColumnKey,
    { label: string; defaultVisible: boolean; sortKey?: string }
> = {
    item: {
        label: 'Item',
        defaultVisible: true,
        sortKey: 'name',
    },
    package_item: { label: 'Package Item', defaultVisible: true },
    sku: {
        label: 'SKU',
        defaultVisible: true,
        sortKey: 'item_code',
    },
    hsn_sac: {
        label: 'HSN/SAC',
        defaultVisible: true,
        sortKey: 'hsn_sac_code',
    },
    buying_price: {
        label: 'Buying Price',
        defaultVisible: true,
        sortKey: 'buying_price',
    },
    selling_price: {
        label: 'Selling Price',
        defaultVisible: true,
        sortKey: 'selling_price',
    },
    total_sold_quantity: {
        label: 'Total Sold Quantity',
        defaultVisible: true,
        sortKey: 'total_sold_quantity',
    },
    total_purchase_quantity: {
        label: 'Total Purchase Quantity',
        defaultVisible: true,
        sortKey: 'total_purchase_quantity',
    },
    current_stock: {
        label: 'Current Stock',
        defaultVisible: true,
        sortKey: 'current_stock',
    },
    average_selling_price: {
        label: 'Average Selling Price',
        defaultVisible: true,
        sortKey: 'selling_price',
    },
    average_buying_price: {
        label: 'Average Buying Price',
        defaultVisible: true,
        sortKey: 'buying_price',
    },
    average_landed_cost: {
        label: 'Average Landed Cost',
        defaultVisible: true,
    },
    gross_profit_margin: {
        label: 'Gross Profit Margin(%)',
        defaultVisible: true,
        sortKey: 'gross_profit_margin',
    },
};

function loadColumnVisibility(): Record<ColumnKey, boolean> {
    try {
        const stored = localStorage.getItem(COLUMN_STORAGE_KEY);
        if (stored) {
            const parsed = JSON.parse(stored) as Record<string, boolean>;
            return Object.fromEntries(
                (Object.keys(COLUMN_CONFIG) as ColumnKey[]).map((k) => [
                    k,
                    parsed[k] ?? COLUMN_CONFIG[k].defaultVisible,
                ]),
            ) as Record<ColumnKey, boolean>;
        }
    } catch {
        // ignore
    }
    return Object.fromEntries(
        (Object.keys(COLUMN_CONFIG) as ColumnKey[]).map((k) => [
            k,
            COLUMN_CONFIG[k].defaultVisible,
        ]),
    ) as Record<ColumnKey, boolean>;
}

function saveColumnVisibility(cols: Record<ColumnKey, boolean>): void {
    localStorage.setItem(COLUMN_STORAGE_KEY, JSON.stringify(cols));
}

function defaultDateRange(): { from: string; to: string } {
    const to = new Date();
    const from = new Date();
    from.setMonth(from.getMonth() - 3);
    return {
        from: from.toISOString().slice(0, 10),
        to: to.toISOString().slice(0, 10),
    };
}

type Props = {
    items: {
        data: ProductWiseRow[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
        links: Array<{ url: string | null; label: string; active: boolean }>;
    };
    filters?: {
        date_from?: string;
        date_to?: string;
        sort?: string;
        order?: string;
        per_page?: number;
    };
};

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Home', href: dashboard().url },
    {
        title: 'Product-wise P&L',
        href: '/inventory/product-wise-pl',
    },
];

const columnVisibility = ref<Record<ColumnKey, boolean>>(loadColumnVisibility());
const columnsOpen = ref(false);
const dateFrom = ref(props.filters?.date_from ?? defaultDateRange().from);
const dateTo = ref(props.filters?.date_to ?? defaultDateRange().to);
const selectedRows = ref<number[]>([]);

function setColumnVisible(key: ColumnKey, visible: boolean): void {
    if (key === 'item') return;
    columnVisibility.value = {
        ...columnVisibility.value,
        [key]: visible,
    };
    saveColumnVisibility(columnVisibility.value);
}

function isColumnVisible(key: ColumnKey): boolean {
    return columnVisibility.value[key] ?? COLUMN_CONFIG[key].defaultVisible;
}

function applyFilters(overrides?: Partial<Props['filters']>): void {
    const params: Record<string, string | number | undefined> = {
        date_from: dateFrom.value || undefined,
        date_to: dateTo.value || undefined,
        sort: props.filters?.sort ?? 'name',
        order: props.filters?.order ?? 'asc',
        per_page: props.filters?.per_page ?? 10,
    };
    Object.assign(params, overrides);
    router.get('/inventory/product-wise-pl', params, {
        preserveState: true,
        replace: true,
    });
}

function clearDateRange(): void {
    dateFrom.value = '';
    dateTo.value = '';
    applyFilters({ date_from: undefined, date_to: undefined });
}

function sortBy(column: string): void {
    const currentSort = props.filters?.sort ?? 'name';
    const currentOrder = props.filters?.order ?? 'asc';
    const newOrder =
        currentSort === column && currentOrder === 'asc' ? 'desc' : 'asc';
    applyFilters({ sort: column, order: newOrder });
}

function downloadCsv(): void {
    const params = new URLSearchParams();
    if (dateFrom.value) params.set('date_from', dateFrom.value);
    if (dateTo.value) params.set('date_to', dateTo.value);
    window.location.href = `/inventory/product-wise-pl/csv?${params.toString()}`;
}

let applyTimeout: ReturnType<typeof setTimeout> | null = null;
watch([dateFrom, dateTo], () => {
    if (applyTimeout) clearTimeout(applyTimeout);
    applyTimeout = setTimeout(() => applyFilters(), 300);
});

function formatPrice(price: number): string {
    return `₹${price.toLocaleString('en-IN', { minimumFractionDigits: 0, maximumFractionDigits: 2 })}`;
}

function toggleSelectAll(checked: boolean | 'indeterminate'): void {
    if (checked === true) {
        selectedRows.value = props.items.data.map((r) => r.id);
    } else {
        selectedRows.value = [];
    }
}

function clearSelection(): void {
    selectedRows.value = [];
}

function toggleSelectRow(id: number): void {
    const idx = selectedRows.value.indexOf(id);
    if (idx === -1) {
        selectedRows.value.push(id);
    } else {
        selectedRows.value.splice(idx, 1);
    }
}

const isAllSelected = computed(
    () =>
        selectedRows.value.length === props.items.data.length &&
        props.items.data.length > 0,
);

const isSomeSelected = computed(
    () =>
        selectedRows.value.length > 0 &&
        selectedRows.value.length < props.items.data.length,
);

const visibleColumnCount = computed(() => {
    return (
        (Object.keys(COLUMN_CONFIG) as ColumnKey[]).filter((k) =>
            isColumnVisible(k),
        ).length + 1
    );
});

const sortIcon = (col: string) => {
    const s = props.filters?.sort ?? 'name';
    const o = props.filters?.order ?? 'asc';
    if (s !== col) return null;
    return o === 'asc' ? '↑' : '↓';
};
</script>

<template>
    <Head title="Product-wise Profitability Report" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto p-6">
            <!-- Page title and actions -->
            <div
                class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
            >
                <div>
                    <h1
                        class="text-2xl font-bold tracking-tight text-foreground"
                    >
                        Product-wise Profitability Report
                    </h1>
                    <p class="mt-1 text-sm text-muted-foreground">
                        View item-wise profitability, margins and stock
                    </p>
                </div>
                <div class="flex shrink-0 gap-2">
                    <Button
                        variant="outline"
                        size="default"
                        class="rounded-lg"
                        @click="downloadCsv"
                    >
                        <ArrowDownToLine class="size-4" />
                        Download CSV
                    </Button>
                </div>
            </div>

            <!-- Filters and column toggle -->
            <div
                class="rounded-xl border border-sidebar-border/70 bg-card p-4 shadow-sm dark:border-sidebar-border"
            >
                <div
                    class="flex flex-col gap-4 sm:flex-row sm:items-center sm:gap-3"
                >
                    <div class="flex min-w-0 flex-1 items-center gap-2">
                        <div
                            class="flex items-center gap-2 rounded-lg border border-muted-foreground/20 bg-muted/30 px-3 py-2 dark:border-sidebar-border"
                        >
                            <Calendar
                                class="size-4 shrink-0 text-muted-foreground"
                                aria-hidden="true"
                            />
                            <Input
                                v-model="dateFrom"
                                type="date"
                                class="h-9 w-36 border-0 bg-transparent p-0 text-sm shadow-none focus-visible:ring-0"
                            />
                            <span class="text-muted-foreground">–</span>
                            <Input
                                v-model="dateTo"
                                type="date"
                                class="h-9 w-36 border-0 bg-transparent p-0 text-sm shadow-none focus-visible:ring-0"
                            />
                            <button
                                v-if="dateFrom || dateTo"
                                type="button"
                                class="rounded p-1 text-muted-foreground transition-colors hover:bg-muted hover:text-foreground"
                                aria-label="Clear date range"
                                @click="clearDateRange"
                            >
                                <X class="size-4" />
                            </button>
                        </div>
                    </div>
                    <div class="flex shrink-0 gap-2">
                        <DropdownMenu v-model:open="columnsOpen">
                            <DropdownMenuTrigger as-child>
                                <Button
                                    variant="outline"
                                    size="default"
                                    class="rounded-lg"
                                >
                                    <Columns3 class="size-4" />
                                    Columns
                                    <ChevronDown class="ml-1 size-4" />
                                </Button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent
                                align="end"
                                class="max-h-[min(320px,70vh)] w-72 overflow-y-auto p-2"
                            >
                                <div
                                    v-for="key in COLUMN_ORDER"
                                    :key="key"
                                    class="flex cursor-default items-center gap-3 px-3 py-2.5 transition-colors hover:bg-muted/50"
                                    @click.stop
                                >
                                    <GripVertical
                                        class="size-4 shrink-0 cursor-grab text-muted-foreground"
                                    />
                                    <span class="min-w-0 flex-1 text-sm font-medium">
                                        {{ COLUMN_CONFIG[key].label }}
                                    </span>
                                    <Switch
                                        :model-value="isColumnVisible(key)"
                                        :disabled="key === 'item'"
                                        @update:model-value="
                                            setColumnVisible(key, $event)
                                        "
                                    />
                                </div>
                            </DropdownMenuContent>
                        </DropdownMenu>
                    </div>
                </div>
            </div>

            <!-- Table -->
            <div
                class="flex-1 overflow-auto rounded-xl border border-sidebar-border/70 shadow-sm dark:border-sidebar-border"
            >
                <table class="w-full caption-bottom text-sm">
                    <thead>
                        <tr
                            class="border-b border-sidebar-border/70 bg-muted/40 dark:border-sidebar-border"
                        >
                            <th
                                class="h-12 w-12 px-4 text-left align-middle"
                                @click.stop
                            >
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
                            <th
                                v-if="isColumnVisible('item')"
                                class="h-12 px-4 text-left font-medium text-muted-foreground"
                            >
                                <button
                                    type="button"
                                    class="flex items-center gap-1 hover:text-foreground"
                                    @click="sortBy('name')"
                                >
                                    Item
                                    <span
                                        v-if="sortIcon('name')"
                                        class="text-foreground"
                                    >
                                        {{ sortIcon('name') }}
                                    </span>
                                </button>
                            </th>
                            <th
                                v-if="isColumnVisible('package_item')"
                                class="h-12 px-4 text-left font-medium text-muted-foreground"
                            >
                                Package Item
                            </th>
                            <th
                                v-if="isColumnVisible('sku')"
                                class="h-12 px-4 text-left font-medium text-muted-foreground"
                            >
                                <button
                                    type="button"
                                    class="flex items-center gap-1 hover:text-foreground"
                                    @click="sortBy('item_code')"
                                >
                                    SKU
                                    <span
                                        v-if="sortIcon('item_code')"
                                        class="text-foreground"
                                    >
                                        {{ sortIcon('item_code') }}
                                    </span>
                                </button>
                            </th>
                            <th
                                v-if="isColumnVisible('hsn_sac')"
                                class="h-12 px-4 text-left font-medium text-muted-foreground"
                            >
                                <button
                                    type="button"
                                    class="flex items-center gap-1 hover:text-foreground"
                                    @click="sortBy('hsn_sac_code')"
                                >
                                    HSN/SAC
                                    <span
                                        v-if="sortIcon('hsn_sac_code')"
                                        class="text-foreground"
                                    >
                                        {{ sortIcon('hsn_sac_code') }}
                                    </span>
                                </button>
                            </th>
                            <th
                                v-if="isColumnVisible('buying_price')"
                                class="h-12 px-4 text-left font-medium text-muted-foreground"
                            >
                                <button
                                    type="button"
                                    class="flex items-center gap-1 hover:text-foreground"
                                    @click="sortBy('buying_price')"
                                >
                                    Buying Price
                                    <span
                                        v-if="sortIcon('buying_price')"
                                        class="text-foreground"
                                    >
                                        {{ sortIcon('buying_price') }}
                                    </span>
                                </button>
                            </th>
                            <th
                                v-if="isColumnVisible('selling_price')"
                                class="h-12 px-4 text-left font-medium text-muted-foreground"
                            >
                                <button
                                    type="button"
                                    class="flex items-center gap-1 hover:text-foreground"
                                    @click="sortBy('selling_price')"
                                >
                                    Selling Price
                                    <span
                                        v-if="sortIcon('selling_price')"
                                        class="text-foreground"
                                    >
                                        {{ sortIcon('selling_price') }}
                                    </span>
                                </button>
                            </th>
                            <th
                                v-if="isColumnVisible('total_sold_quantity')"
                                class="h-12 px-4 text-left font-medium text-muted-foreground"
                            >
                                <button
                                    type="button"
                                    class="flex items-center gap-1 hover:text-foreground"
                                    @click="sortBy('total_sold_quantity')"
                                >
                                    Total Sold Quantity
                                    <span
                                        v-if="
                                            sortIcon('total_sold_quantity')
                                        "
                                        class="text-foreground"
                                    >
                                        {{
                                            sortIcon('total_sold_quantity')
                                        }}
                                    </span>
                                </button>
                            </th>
                            <th
                                v-if="isColumnVisible('total_purchase_quantity')"
                                class="h-12 px-4 text-left font-medium text-muted-foreground"
                            >
                                <button
                                    type="button"
                                    class="flex items-center gap-1 hover:text-foreground"
                                    @click="sortBy('total_purchase_quantity')"
                                >
                                    Total Purchase Quantity
                                    <span
                                        v-if="sortIcon('total_purchase_quantity')"
                                        class="text-foreground"
                                    >
                                        {{ sortIcon('total_purchase_quantity') }}
                                    </span>
                                </button>
                            </th>
                            <th
                                v-if="isColumnVisible('current_stock')"
                                class="h-12 px-4 text-left font-medium text-muted-foreground"
                            >
                                <button
                                    type="button"
                                    class="flex items-center gap-1 hover:text-foreground"
                                    @click="sortBy('current_stock')"
                                >
                                    Current Stock
                                    <span
                                        v-if="sortIcon('current_stock')"
                                        class="text-foreground"
                                    >
                                        {{ sortIcon('current_stock') }}
                                    </span>
                                </button>
                            </th>
                            <th
                                v-if="isColumnVisible('average_selling_price')"
                                class="h-12 px-4 text-left font-medium text-muted-foreground"
                            >
                                Average Selling Price
                            </th>
                            <th
                                v-if="isColumnVisible('average_buying_price')"
                                class="h-12 px-4 text-left font-medium text-muted-foreground"
                            >
                                Average Buying Price
                            </th>
                            <th
                                v-if="isColumnVisible('average_landed_cost')"
                                class="h-12 px-4 text-left font-medium text-muted-foreground"
                            >
                                Average Landed Cost
                            </th>
                            <th
                                v-if="isColumnVisible('gross_profit_margin')"
                                class="h-12 px-4 text-left font-medium text-muted-foreground"
                            >
                                <button
                                    type="button"
                                    class="flex items-center gap-1 hover:text-foreground"
                                    @click="sortBy('gross_profit_margin')"
                                >
                                    Gross Profit Margin(%)
                                    <span
                                        v-if="sortIcon('gross_profit_margin')"
                                        class="text-foreground"
                                    >
                                        {{ sortIcon('gross_profit_margin') }}
                                    </span>
                                </button>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-if="items.data.length === 0"
                            class="border-b border-sidebar-border/70 dark:border-sidebar-border"
                        >
                            <td
                                :colspan="visibleColumnCount"
                                class="p-12 text-center text-muted-foreground"
                            >
                                <Package
                                    class="mx-auto mb-3 size-12 opacity-40"
                                />
                                <p class="font-medium">
                                    No records found for the selected date range
                                </p>
                                <p class="mt-1 text-sm">
                                    Try adjusting your filters.
                                </p>
                            </td>
                        </tr>
                        <tr
                            v-for="row in items.data"
                            :key="row.id"
                            class="group border-b border-sidebar-border/70 transition-colors hover:bg-muted/40 dark:border-sidebar-border"
                        >
                            <td
                                class="p-4 align-middle"
                                @click.stop
                            >
                                <Checkbox
                                    :model-value="selectedRows.includes(row.id)"
                                    @update:model-value="
                                        () => toggleSelectRow(row.id)
                                    "
                                />
                            </td>
                            <td
                                v-if="isColumnVisible('item')"
                                class="max-w-xs p-4 align-middle font-medium"
                            >
                                <span class="line-clamp-2">{{ row.name }}</span>
                            </td>
                            <td
                                v-if="isColumnVisible('package_item')"
                                class="p-4 align-middle text-muted-foreground"
                            >
                                {{ row.package_item }}
                            </td>
                            <td
                                v-if="isColumnVisible('sku')"
                                class="p-4 align-middle font-mono text-muted-foreground"
                            >
                                {{ row.item_code }}
                            </td>
                            <td
                                v-if="isColumnVisible('hsn_sac')"
                                class="p-4 align-middle text-muted-foreground"
                            >
                                {{ row.hsn_sac_code }}
                            </td>
                            <td
                                v-if="isColumnVisible('buying_price')"
                                class="p-4 align-middle tabular-nums"
                            >
                                {{ formatPrice(row.buying_price) }}
                            </td>
                            <td
                                v-if="isColumnVisible('selling_price')"
                                class="p-4 align-middle font-medium tabular-nums"
                            >
                                {{ formatPrice(row.selling_price) }}
                            </td>
                            <td
                                v-if="isColumnVisible('total_sold_quantity')"
                                class="p-4 align-middle tabular-nums"
                            >
                                {{ row.total_sold_quantity }}
                            </td>
                            <td
                                v-if="isColumnVisible('total_purchase_quantity')"
                                class="p-4 align-middle tabular-nums"
                            >
                                {{ row.total_purchase_quantity }}
                            </td>
                            <td
                                v-if="isColumnVisible('current_stock')"
                                class="p-4 align-middle tabular-nums"
                            >
                                {{ row.current_stock }}
                            </td>
                            <td
                                v-if="isColumnVisible('average_selling_price')"
                                class="p-4 align-middle tabular-nums"
                            >
                                {{ formatPrice(row.average_selling_price) }}
                            </td>
                            <td
                                v-if="isColumnVisible('average_buying_price')"
                                class="p-4 align-middle tabular-nums"
                            >
                                {{ formatPrice(row.average_buying_price) }}
                            </td>
                            <td
                                v-if="isColumnVisible('average_landed_cost')"
                                class="p-4 align-middle tabular-nums"
                            >
                                {{ formatPrice(row.average_landed_cost) }}
                            </td>
                            <td
                                v-if="isColumnVisible('gross_profit_margin')"
                                class="p-4 align-middle tabular-nums"
                            >
                                <span
                                    v-if="row.gross_profit_margin !== null"
                                    :class="{
                                        'text-emerald-600 dark:text-emerald-400':
                                            row.gross_profit_margin > 0,
                                        'text-red-600 dark:text-red-400':
                                            row.gross_profit_margin < 0,
                                    }"
                                >
                                    {{ row.gross_profit_margin }}%
                                </span>
                                <span v-else class="text-muted-foreground">-</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination and footer -->
            <div
                class="flex flex-col gap-4 border-t border-sidebar-border/70 pt-4 sm:flex-row sm:items-center sm:justify-between dark:border-sidebar-border"
            >
                <div class="flex items-center gap-4">
                    <div class="flex items-center gap-2">
                        <span class="text-sm text-muted-foreground">
                            Showing
                            {{ (items.current_page - 1) * items.per_page + 1 }}
                            to
                            {{
                                Math.min(
                                    items.current_page * items.per_page,
                                    items.total,
                                )
                            }}
                            of {{ items.total }} entries</span
                        >
                    </div>
                    <span class="text-sm text-muted-foreground">
                        © 2025 Mister Accountant, All Rights Reserved
                    </span>
                </div>
                <div class="flex items-center gap-4">
                    <div class="flex items-center gap-2">
                        <DropdownMenu>
                            <DropdownMenuTrigger as-child>
                                <Button
                                    variant="outline"
                                    size="default"
                                    class="min-w-20 rounded-lg"
                                >
                                    {{ items.per_page }}
                                    <ChevronDown class="ml-1 size-4" />
                                </Button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent align="end">
                                <button
                                    v-for="n in [10, 25, 50, 100]"
                                    :key="n"
                                    type="button"
                                    class="flex w-full cursor-pointer items-center px-3 py-2 text-sm hover:bg-muted"
                                    :class="{
                                        'bg-muted font-medium':
                                            items.per_page === n,
                                    }"
                                    @click="applyFilters({ per_page: n })"
                                >
                                    {{ n }}
                                </button>
                            </DropdownMenuContent>
                        </DropdownMenu>
                        <div class="flex items-center gap-1">
                            <Link
                                v-if="items.links[0]?.url"
                                :href="items.links[0].url"
                                preserve-state
                            >
                                <Button
                                    variant="outline"
                                    size="icon"
                                    class="size-8"
                                    :disabled="items.current_page === 1"
                                >
                                    <ChevronLeft class="size-4" />
                                </Button>
                            </Link>
                            <template
                                v-for="(link, index) in items.links"
                                :key="index"
                            >
                                <Link
                                    v-if="
                                        link.url &&
                                        index > 0 &&
                                        index < items.links.length - 1
                                    "
                                    :href="link.url"
                                    preserve-state
                                >
                                    <Button
                                        variant="outline"
                                        size="sm"
                                        class="min-w-8"
                                        :class="{
                                            'bg-primary text-primary-foreground':
                                                link.active,
                                        }"
                                    >
                                        {{ link.label }}
                                    </Button>
                                </Link>
                            </template>
                            <Link
                                v-if="items.links[items.links.length - 1]?.url"
                                :href="items.links[items.links.length - 1]!.url!"
                                preserve-state
                            >
                                <Button
                                    variant="outline"
                                    size="icon"
                                    class="size-8"
                                    :disabled="
                                        items.current_page === items.last_page
                                    "
                                >
                                    <ChevronRight class="size-4" />
                                </Button>
                            </Link>
                        </div>
                    </div>
                    <span class="text-sm text-muted-foreground"
                        >Version: 1.3.8</span
                    >
                </div>
            </div>

            <!-- Bulk actions bar (shown when rows selected) - Teleport ensures visibility -->
            <Teleport to="body">
                <Transition
                    enter-active-class="transition duration-200 ease-out"
                    enter-from-class="translate-y-full opacity-0"
                    enter-to-class="translate-y-0 opacity-100"
                    leave-active-class="transition duration-150 ease-in"
                    leave-from-class="translate-y-0 opacity-100"
                    leave-to-class="translate-y-full opacity-0"
                >
                    <div
                        v-if="selectedRows.length > 0"
                        class="fixed inset-x-0 bottom-0 z-50 flex items-center justify-between gap-4 border-t border-sidebar-border/70 bg-card px-6 py-4 shadow-lg dark:border-sidebar-border md:left-[19.5rem]"
                    >
                    <span class="text-sm font-medium">
                        {{ selectedRows.length }}
                        {{ selectedRows.length === 1 ? 'item' : 'items' }}
                        selected
                    </span>
                    <div class="flex items-center gap-2">
                        <Button
                            variant="default"
                            size="default"
                            class="rounded-lg"
                            @click="downloadCsv"
                        >
                            <ArrowDownToLine class="size-4" />
                            Download CSV
                        </Button>
                        <Button
                            variant="ghost"
                            size="icon"
                            class="size-8 rounded-lg"
                            aria-label="Clear selection"
                            @click="clearSelection"
                        >
                            <X class="size-4" />
                        </Button>
                    </div>
                    </div>
                </Transition>
            </Teleport>
        </div>
    </AppLayout>
</template>
