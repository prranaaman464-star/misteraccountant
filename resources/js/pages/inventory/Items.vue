<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { useDebounceFn } from '@vueuse/core';
import {
    ArrowDownToLine,
    ChevronDown,
    Columns3,
    GripVertical,
    PackageMinus,
    ChevronLeft,
    ChevronRight,
    MoreHorizontal,
    Package,
    PackagePlus,
    Plus,
    Clock,
    Search,
    X,
} from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import InputError from '@/components/InputError.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import inventory from '@/routes/inventory';
import {
    create,
    show,
    edit,
    destroy,
    stockIn,
    stockOut,
} from '@/routes/inventory/items';
import type { BreadcrumbItem } from '@/types';

type Item = {
    id: number;
    name: string;
    item_code: string | null;
    brand: string | null;
    category: { id: number; name: string } | null;
    pricing: {
        sale_price: number | null;
        purchase_price: number | null;
        mrp: number | null;
    } | null;
    inventory: {
        primary_unit: string | null;
        stock_quantity: number | null;
    } | null;
    item_image: string | null;
    status: string;
    item_type: string;
};

type ColumnKey =
    | 'product'
    | 'code'
    | 'category'
    | 'unit'
    | 'quantity'
    | 'sale_price'
    | 'purchase_price'
    | 'mrp'
    | 'brand'
    | 'item_type'
    | 'status';

const COLUMN_STORAGE_KEY = 'inventory-items-columns';

const COLUMN_ORDER: ColumnKey[] = [
    'product',
    'code',
    'category',
    'unit',
    'quantity',
    'sale_price',
    'purchase_price',
    'mrp',
    'brand',
    'item_type',
    'status',
];

const COLUMN_CONFIG: Record<
    ColumnKey,
    { label: string; defaultVisible: boolean }
> = {
    product: { label: 'Product/Service', defaultVisible: true },
    code: { label: 'Code', defaultVisible: true },
    category: { label: 'Category', defaultVisible: true },
    unit: { label: 'Unit', defaultVisible: true },
    quantity: { label: 'Quantity', defaultVisible: true },
    sale_price: { label: 'Selling Price', defaultVisible: true },
    purchase_price: { label: 'Purchase Price', defaultVisible: true },
    mrp: { label: 'MRP', defaultVisible: false },
    brand: { label: 'Brand', defaultVisible: false },
    item_type: { label: 'Item Type', defaultVisible: false },
    status: { label: 'Status', defaultVisible: false },
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

const columnVisibility = ref<Record<ColumnKey, boolean>>(loadColumnVisibility());

function toggleColumn(key: ColumnKey): void {
    if (key === 'product') return;
    columnVisibility.value = {
        ...columnVisibility.value,
        [key]: !columnVisibility.value[key],
    };
    saveColumnVisibility(columnVisibility.value);
}

function setColumnVisible(key: ColumnKey, visible: boolean): void {
    if (key === 'product') return;
    columnVisibility.value = {
        ...columnVisibility.value,
        [key]: visible,
    };
    saveColumnVisibility(columnVisibility.value);
}

function isColumnVisible(key: ColumnKey): boolean {
    return columnVisibility.value[key] ?? COLUMN_CONFIG[key].defaultVisible;
}

type Props = {
    items: {
        data: Item[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
        links: Array<{ url: string | null; label: string; active: boolean }>;
    };
    filters?: {
        search?: string;
        status?: string;
        item_type?: string;
        per_page?: number;
    };
};

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Home', href: dashboard().url },
    { title: 'Items', href: inventory.items.url() },
];

const searchQuery = ref(props.filters?.search || '');
const selectedItems = ref<number[]>([]);
const columnsOpen = ref(false);

const debouncedSearch = useDebounceFn(() => {
    router.get(
        inventory.items.url(),
        {
            search: searchQuery.value || undefined,
            status: props.filters?.status,
            item_type: props.filters?.item_type,
            per_page: props.items.per_page,
        },
        { preserveState: true, replace: true },
    );
}, 400);

watch(searchQuery, () => {
    debouncedSearch();
});

function toggleSelectAll(checked: boolean | 'indeterminate'): void {
    if (checked === true) {
        selectedItems.value = props.items.data.map((i) => i.id);
    } else {
        selectedItems.value = [];
    }
}

function toggleSelectItem(id: number): void {
    const idx = selectedItems.value.indexOf(id);
    if (idx === -1) {
        selectedItems.value.push(id);
    } else {
        selectedItems.value.splice(idx, 1);
    }
}

function handleSearch(): void {
    router.get(
        inventory.items.url(),
        {
            search: searchQuery.value || undefined,
            status: props.filters?.status,
            item_type: props.filters?.item_type,
            per_page: props.items.per_page,
        },
        {
            preserveState: true,
            replace: true,
        },
    );
}

function handleDelete(itemId: number): void {
    if (confirm('Are you sure you want to delete this item?')) {
        router.delete(destroy(itemId).url);
    }
}

function formatPrice(price: number | null | undefined): string {
    if (price === null || price === undefined) {
        return '₹0';
    }
    return `₹${price.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
}

function getImageUrl(imagePath: string | null): string | null {
    if (!imagePath) {
        return null;
    }
    return `/storage/${imagePath}`;
}

const stockModalOpen = ref(false);
const stockModalItem = ref<Item | null>(null);
const stockModalType = ref<'in' | 'out'>('in');
const stockQuantity = ref('');
const stockReference = ref('');
const stockErrors = ref<Record<string, string>>({});
const stockSubmitting = ref(false);

function openStockModal(item: Item, type: 'in' | 'out'): void {
    stockModalItem.value = item;
    stockModalType.value = type;
    stockQuantity.value = '';
    stockReference.value = '';
    stockErrors.value = {};
    stockModalOpen.value = true;
}

function closeStockModal(): void {
    stockModalOpen.value = false;
    stockModalItem.value = null;
    stockQuantity.value = '';
    stockReference.value = '';
    stockErrors.value = {};
}

watch(stockModalOpen, (open) => {
    if (!open) {
        stockModalItem.value = null;
        stockQuantity.value = '';
        stockReference.value = '';
        stockErrors.value = {};
    }
});

async function submitStockMovement(): Promise<void> {
    const item = stockModalItem.value;
    if (!item) return;

    stockErrors.value = {};
    const quantity = parseFloat(stockQuantity.value);
    if (Number.isNaN(quantity) || quantity <= 0) {
        stockErrors.value = { quantity: 'Quantity must be greater than zero.' };
        return;
    }

    const currentStock = item.inventory?.stock_quantity ?? 0;
    if (
        stockModalType.value === 'out' &&
        (currentStock === null || quantity > currentStock)
    ) {
        stockErrors.value = {
            quantity: 'Stock out quantity cannot exceed current stock.',
        };
        return;
    }

    stockSubmitting.value = true;
    const url =
        stockModalType.value === 'in'
            ? stockIn(item.id).url
            : stockOut(item.id).url;
    const csrfToken =
        document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';

    try {
        const formData = new FormData();
        formData.append('quantity', String(quantity));
        formData.append('reference', stockReference.value);
        formData.append('_token', csrfToken);

        const response = await fetch(url, {
            method: 'POST',
            body: formData,
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        });

        if (response.status === 422) {
            const data = await response.json().catch(() => ({}));
            if (data.errors) {
                stockErrors.value = Object.fromEntries(
                    Object.entries(data.errors).map(([k, v]) => [
                        k,
                        Array.isArray(v) ? (v[0] as string) : String(v),
                    ]),
                );
            } else {
                stockErrors.value = {
                    general: data.message ?? 'Validation failed.',
                };
            }
            return;
        }

        if (!response.ok) {
            const data = await response.json().catch(() => ({}));
            stockErrors.value = {
                general: data.message ?? 'An error occurred. Please try again.',
            };
            return;
        }

        closeStockModal();
        router.reload();
    } finally {
        stockSubmitting.value = false;
    }
}

const isAllSelected = computed(
    () =>
        selectedItems.value.length === props.items.data.length &&
        props.items.data.length > 0,
);
const isSomeSelected = computed(
    () =>
        selectedItems.value.length > 0 &&
        selectedItems.value.length < props.items.data.length,
);

function formatItemType(type: string): string {
    const map: Record<string, string> = {
        goods: 'Goods',
        service: 'Service',
        raw_material: 'Raw Material',
        finished_goods: 'Finished Goods',
    };
    return map[type] ?? type;
}

const visibleColumnCount = computed(() => {
    return (
        (Object.keys(COLUMN_CONFIG) as ColumnKey[]).filter((k) =>
            isColumnVisible(k),
        ).length + 4
    );
});
</script>

<template>
    <Head title="Items" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto p-6">
            <!-- Page title and actions -->
            <div
                class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
            >
                <div>
                    <h1 class="text-2xl font-bold tracking-tight text-foreground">
                        Items
                    </h1>
                    <p class="mt-1 text-sm text-muted-foreground">
                        Manage your inventory items, stock, and pricing
                    </p>
                </div>
                <div class="flex shrink-0 gap-2">
                    <Button variant="outline" size="default">
                        <ArrowDownToLine class="size-4" />
                        Export
                    </Button>
                    <Link :href="create().url">
                        <Button size="default" class="shadow-sm">
                            <Plus class="size-4" />
                            New Item
                        </Button>
                    </Link>
                </div>
            </div>

            <!-- Search, filters and column toggle -->
            <div
                class="rounded-xl border border-sidebar-border/70 bg-card p-4 shadow-sm dark:border-sidebar-border"
            >
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:gap-3">
                    <div class="relative min-w-0 flex-1">
                        <Search
                            class="absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground"
                        />
                        <Input
                            v-model="searchQuery"
                            placeholder="Search by name, code, brand, or description..."
                            class="h-10 rounded-lg border-muted-foreground/20 bg-muted/30 pl-9 pr-4 transition-colors placeholder:text-muted-foreground focus-visible:bg-background focus-visible:ring-2"
                            @keyup.enter="handleSearch"
                        />
                        <Button
                            v-if="searchQuery"
                            variant="ghost"
                            size="icon"
                            class="absolute top-1/2 right-1 size-7 -translate-y-1/2 text-muted-foreground hover:text-foreground"
                            @click="searchQuery = ''"
                        >
                            <X class="size-4" />
                        </Button>
                    </div>
                    <div class="flex shrink-0 gap-2">
                        <Button
                            variant="outline"
                            size="default"
                            class="rounded-lg"
                            @click="handleSearch"
                        >
                            <Search class="size-4" />
                            Search
                        </Button>
                        <DropdownMenu v-model:open="columnsOpen">
                            <DropdownMenuTrigger as-child>
                                <Button
                                    variant="outline"
                                    size="default"
                                    class="rounded-lg"
                                >
                                    <Columns3 class="size-4" />
                                    Column
                                    <ChevronDown class="ml-1 size-4" />
                                </Button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent
                                align="end"
                                class="w-72 overflow-visible p-2"
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
                                    <span
                                        class="min-w-0 flex-1 text-sm font-medium"
                                    >
                                        {{ COLUMN_CONFIG[key].label }}
                                    </span>
                                    <Switch
                                        :model-value="isColumnVisible(key)"
                                        :disabled="key === 'product'"
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
                            <th class="h-12 w-12 px-4 text-left align-middle">
                                <Checkbox
                                    :checked="
                                        isAllSelected
                                            ? true
                                            : isSomeSelected
                                              ? 'indeterminate'
                                              : false
                                    "
                                    @update:checked="toggleSelectAll"
                                />
                            </th>
                            <th
                                class="h-12 px-4 text-left font-medium text-muted-foreground"
                            >
                                Product/Service
                            </th>
                            <th
                                v-if="isColumnVisible('code')"
                                class="h-12 px-4 text-left font-medium text-muted-foreground"
                            >
                                Code
                            </th>
                            <th
                                v-if="isColumnVisible('category')"
                                class="h-12 px-4 text-left font-medium text-muted-foreground"
                            >
                                Category
                            </th>
                            <th
                                v-if="isColumnVisible('unit')"
                                class="h-12 px-4 text-left font-medium text-muted-foreground"
                            >
                                Unit
                            </th>
                            <th
                                v-if="isColumnVisible('quantity')"
                                class="h-12 px-4 text-left font-medium text-muted-foreground"
                            >
                                Quantity
                            </th>
                            <th
                                v-if="isColumnVisible('sale_price')"
                                class="h-12 px-4 text-left font-medium text-muted-foreground"
                            >
                                Selling Price
                            </th>
                            <th
                                v-if="isColumnVisible('purchase_price')"
                                class="h-12 px-4 text-left font-medium text-muted-foreground"
                            >
                                Purchase Price
                            </th>
                            <th
                                v-if="isColumnVisible('mrp')"
                                class="h-12 px-4 text-left font-medium text-muted-foreground"
                            >
                                MRP
                            </th>
                            <th
                                v-if="isColumnVisible('brand')"
                                class="h-12 px-4 text-left font-medium text-muted-foreground"
                            >
                                Brand
                            </th>
                            <th
                                v-if="isColumnVisible('item_type')"
                                class="h-12 px-4 text-left font-medium text-muted-foreground"
                            >
                                Item Type
                            </th>
                            <th
                                v-if="isColumnVisible('status')"
                                class="h-12 px-4 text-left font-medium text-muted-foreground"
                            >
                                Status
                            </th>
                            <th class="h-12 px-4 text-left font-medium text-muted-foreground">
                                Actions
                            </th>
                            <th class="h-12 w-12 px-4"></th>
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
                                <Package class="mx-auto mb-3 size-12 opacity-40" />
                                <p class="font-medium">No items found</p>
                                <p class="mt-1 text-sm">
                                    <Link
                                        :href="create().url"
                                        class="text-primary underline hover:no-underline"
                                        >Create your first item</Link
                                    >
                                    to get started
                                </p>
                            </td>
                        </tr>
                        <tr
                            v-for="item in items.data"
                            :key="item.id"
                            class="group border-b border-sidebar-border/70 transition-colors hover:bg-muted/40 dark:border-sidebar-border"
                        >
                            <td class="p-4 align-middle">
                                <Checkbox
                                    :checked="selectedItems.includes(item.id)"
                                    @update:checked="
                                        () => toggleSelectItem(item.id)
                                    "
                                />
                            </td>
                            <td class="p-4 align-middle">
                                <div class="flex items-center gap-3">
                                    <Avatar class="size-9 shrink-0 ring-1 ring-muted/50">
                                        <AvatarImage
                                            v-if="getImageUrl(item.item_image)"
                                            :src="getImageUrl(item.item_image)!"
                                            :alt="item.name"
                                        />
                                        <AvatarFallback
                                            class="rounded-lg bg-muted text-xs font-medium"
                                        >
                                            <Package class="size-4" />
                                        </AvatarFallback>
                                    </Avatar>
                                    <div class="flex flex-col">
                                        <span class="font-medium">{{
                                            item.name
                                        }}</span>
                                        <span
                                            v-if="
                                                item.category &&
                                                !isColumnVisible('category')
                                            "
                                            class="text-xs text-muted-foreground"
                                            >{{ item.category.name }}</span
                                        >
                                    </div>
                                </div>
                            </td>
                            <td
                                v-if="isColumnVisible('code')"
                                class="p-4 align-middle text-muted-foreground"
                            >
                                {{ item.item_code || '-' }}
                            </td>
                            <td
                                v-if="isColumnVisible('category')"
                                class="p-4 align-middle text-muted-foreground"
                            >
                                {{ item.category?.name || '-' }}
                            </td>
                            <td
                                v-if="isColumnVisible('unit')"
                                class="p-4 align-middle"
                            >
                                {{ item.inventory?.primary_unit || '-' }}
                            </td>
                            <td
                                v-if="isColumnVisible('quantity')"
                                class="p-4 align-middle tabular-nums"
                            >
                                {{ item.inventory?.stock_quantity ?? '-' }}
                            </td>
                            <td
                                v-if="isColumnVisible('sale_price')"
                                class="p-4 align-middle tabular-nums font-medium"
                            >
                                {{ formatPrice(item.pricing?.sale_price) }}
                            </td>
                            <td
                                v-if="isColumnVisible('purchase_price')"
                                class="p-4 align-middle tabular-nums"
                            >
                                {{ formatPrice(item.pricing?.purchase_price) }}
                            </td>
                            <td
                                v-if="isColumnVisible('mrp')"
                                class="p-4 align-middle tabular-nums"
                            >
                                {{ formatPrice(item.pricing?.mrp) }}
                            </td>
                            <td
                                v-if="isColumnVisible('brand')"
                                class="p-4 align-middle text-muted-foreground"
                            >
                                {{ item.brand || '-' }}
                            </td>
                            <td
                                v-if="isColumnVisible('item_type')"
                                class="p-4 align-middle"
                            >
                                <span
                                    class="rounded-md bg-muted px-2 py-0.5 text-xs font-medium"
                                >
                                    {{ formatItemType(item.item_type) }}
                                </span>
                            </td>
                            <td
                                v-if="isColumnVisible('status')"
                                class="p-4 align-middle"
                            >
                                <span
                                    :class="[
                                        'rounded-md px-2 py-0.5 text-xs font-medium capitalize',
                                        item.status === 'active'
                                            ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400'
                                            : 'bg-muted text-muted-foreground',
                                    ]"
                                >
                                    {{ item.status }}
                                </span>
                            </td>
                            <td class="p-4 align-middle">
                                <div class="flex flex-wrap items-center gap-1">
                                    <Link :href="show(item.id).url">
                                        <Button
                                            variant="default"
                                            size="icon"
                                            class="size-8"
                                            title="View"
                                        >
                                            <Clock class="size-4" />
                                        </Button>
                                    </Link>
                                    <Button
                                        variant="outline"
                                        size="icon"
                                        class="size-8 border-emerald-600 text-emerald-600 hover:bg-emerald-50 hover:text-emerald-700 dark:border-emerald-500 dark:text-emerald-400 dark:hover:bg-emerald-950"
                                        title="Stock In"
                                        @click="openStockModal(item, 'in')"
                                    >
                                        <PackagePlus class="size-4" />
                                    </Button>
                                    <Button
                                        variant="outline"
                                        size="icon"
                                        class="size-8 border-red-600 text-red-600 hover:bg-red-50 hover:text-red-700 dark:border-red-500 dark:text-red-400 dark:hover:bg-red-950"
                                        title="Stock Out"
                                        @click="openStockModal(item, 'out')"
                                    >
                                        <PackageMinus class="size-4" />
                                    </Button>
                                </div>
                            </td>
                            <td class="p-4 align-middle">
                                <DropdownMenu>
                                    <DropdownMenuTrigger as-child>
                                        <Button
                                            variant="ghost"
                                            size="icon"
                                            class="size-8"
                                        >
                                            <MoreHorizontal class="size-4" />
                                        </Button>
                                    </DropdownMenuTrigger>
                                    <DropdownMenuContent align="end">
                                        <DropdownMenuItem :as-child="true">
                                            <Link :href="edit(item.id).url"
                                                >Edit</Link
                                            >
                                        </DropdownMenuItem>
                                        <DropdownMenuItem
                                            class="text-destructive"
                                            @click="handleDelete(item.id)"
                                        >
                                            Delete
                                        </DropdownMenuItem>
                                    </DropdownMenuContent>
                                </DropdownMenu>
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
                        <span class="text-sm text-muted-foreground"
                            >Showing
                            {{
                                (items.current_page - 1) * items.per_page + 1
                            }}
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
                    <span class="text-sm text-muted-foreground"
                        >Version: 1.3.8</span
                    >
                </div>
            </div>

            <!-- Stock In/Out Modal -->
            <Dialog v-model:open="stockModalOpen">
                <DialogContent
                    class="sm:max-w-md"
                    @pointer-down-outside="closeStockModal"
                >
                    <DialogHeader>
                        <DialogTitle>
                            {{ stockModalType === 'in' ? 'Stock In' : 'Stock Out' }}
                        </DialogTitle>
                        <DialogDescription>
                            {{
                                stockModalItem
                                    ? `${stockModalItem.name}${stockModalItem.inventory?.primary_unit ? ` (${stockModalItem.inventory.primary_unit})` : ''}`
                                    : ''
                            }}
                            <span
                                v-if="stockModalItem?.inventory?.stock_quantity != null"
                                class="block mt-1 text-muted-foreground"
                            >
                                Current stock:
                                {{ stockModalItem.inventory.stock_quantity }}
                            </span>
                        </DialogDescription>
                    </DialogHeader>
                    <form
                        class="space-y-4"
                        @submit.prevent="submitStockMovement"
                    >
                        <div
                            v-if="stockErrors.general"
                            class="rounded-md bg-destructive/10 p-3 text-sm text-destructive"
                        >
                            {{ stockErrors.general }}
                        </div>
                        <div class="space-y-2">
                            <Label for="stock-quantity">Quantity</Label>
                            <Input
                                id="stock-quantity"
                                v-model="stockQuantity"
                                type="number"
                                min="0"
                                step="0.0001"
                                placeholder="Enter quantity"
                                required
                            />
                            <InputError
                                :message="stockErrors.quantity"
                            />
                        </div>
                        <div class="space-y-2">
                            <Label for="stock-reference"
                                >Reference (optional)</Label
                            >
                            <Input
                                id="stock-reference"
                                v-model="stockReference"
                                type="text"
                                maxlength="255"
                                placeholder="e.g. Invoice #123"
                            />
                            <InputError
                                :message="stockErrors.reference"
                            />
                        </div>
                        <DialogFooter>
                            <Button
                                type="button"
                                variant="outline"
                                @click="closeStockModal"
                            >
                                Cancel
                            </Button>
                            <Button
                                type="submit"
                                :disabled="stockSubmitting"
                            >
                                {{
                                    stockSubmitting
                                        ? 'Processing...'
                                        : stockModalType === 'in'
                                          ? 'Add Stock'
                                          : 'Remove Stock'
                                }}
                            </Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>

        </div>
    </AppLayout>
</template>
