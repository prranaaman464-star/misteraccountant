<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import {
    ArrowDownToLine,
    ChevronDown,
    Columns3,
    GripVertical,
    PackageMinus,
    ChevronLeft,
    ChevronRight,
    Package,
    PackagePlus,
    Plus,
    Clock,
    Trash2,
    X,
} from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import InputError from '@/components/InputError.vue';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import {
    Dialog,
    DialogContent,
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
import { Spinner } from '@/components/ui/spinner';
import { Switch } from '@/components/ui/switch';
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

const columnVisibility = ref<Record<ColumnKey, boolean>>(
    loadColumnVisibility(),
);

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

const selectedItems = ref<number[]>([]);
const columnsOpen = ref(false);

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

function clearSelection(): void {
    selectedItems.value = [];
    bulkActionsOpen.value = false;
}

function downloadSelectedCsv(): void {
    if (selectedItems.value.length === 0) return;
    const ids = selectedItems.value.join(',');
    window.location.href = `/inventory/items/csv?ids=${ids}`;
}

function handleBulkDelete(): void {
    if (selectedItems.value.length === 0) return;
    if (
        !confirm(
            `Are you sure you want to delete ${selectedItems.value.length} item(s)?`,
        )
    ) {
        return;
    }
    bulkActionsOpen.value = false;
    router.post('/inventory/items/bulk-destroy', {
        ids: selectedItems.value,
    });
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

const primaryUnitOptions: Record<string, string> = {
    Nos: 'Nos',
    'Piece (Pcs)': 'Piece (Pcs)',
    Box: 'Box',
    Pack: 'Pack',
    Dozen: 'Dozen',
    Pair: 'Pair',
    Set: 'Set',
    Roll: 'Roll',
    Bundle: 'Bundle',
    Carton: 'Carton',
    Kg: 'Kg',
    Gram: 'Gram',
    Quintal: 'Quintal',
    Ton: 'Ton',
    Litre: 'Litre',
    ML: 'ML',
    Feet: 'Feet',
    Meter: 'Meter',
    Inch: 'Inch',
    Yard: 'Yard',
    'Sq Ft': 'Sq Ft',
    'Sq Meter': 'Sq Meter',
};

const stockModalOpen = ref(false);
const bulkActionsOpen = ref(false);
const bulkStockModalOpen = ref(false);
const bulkStockType = ref<'in' | 'out'>('in');
const bulkStockQuantity = ref('');
const bulkStockUnit = ref('');
const bulkStockReference = ref('');
const bulkStockErrors = ref<Record<string, string>>({});
const bulkStockSubmitting = ref(false);
const stockModalItem = ref<Item | null>(null);
const stockModalType = ref<'in' | 'out'>('in');
const stockQuantity = ref('');
const stockUnit = ref('');
const stockReference = ref('');
const stockErrors = ref<Record<string, string>>({});
const stockSubmitting = ref(false);

/** Unit options for stock modal: standard list + current item's saved primary_unit if not in list */
const stockUnitOptions = computed(() => {
    const opts = { ...primaryUnitOptions };
    const saved = stockModalItem.value?.inventory?.primary_unit;
    if (saved && typeof saved === 'string' && !(saved in opts)) {
        opts[saved] = saved;
    }
    return opts;
});

function openStockModal(item: Item, type: 'in' | 'out'): void {
    stockModalItem.value = item;
    stockModalType.value = type;
    stockQuantity.value = '';
    const savedUnit =
        (item.inventory?.primary_unit as string)?.trim() || '';
    stockUnit.value =
        savedUnit || Object.keys(primaryUnitOptions)[0] || '';
    stockReference.value = '';
    stockErrors.value = {};
    stockModalOpen.value = true;
}

function closeStockModal(): void {
    stockModalOpen.value = false;
    stockModalItem.value = null;
    stockQuantity.value = '';
    stockUnit.value = '';
    stockReference.value = '';
    stockErrors.value = {};
}

function openBulkStockModal(type: 'in' | 'out'): void {
    bulkActionsOpen.value = false;
    bulkStockType.value = type;
    bulkStockQuantity.value = '';
    bulkStockUnit.value = Object.keys(primaryUnitOptions)[0] || '';
    bulkStockReference.value = '';
    bulkStockErrors.value = {};
    bulkStockModalOpen.value = true;
}

function closeBulkStockModal(): void {
    bulkStockModalOpen.value = false;
    bulkStockQuantity.value = '';
    bulkStockUnit.value = '';
    bulkStockReference.value = '';
    bulkStockErrors.value = {};
}

async function submitBulkStockMovement(): Promise<void> {
    if (selectedItems.value.length === 0) return;

    bulkStockErrors.value = {};
    const quantity = parseFloat(bulkStockQuantity.value);
    if (Number.isNaN(quantity) || quantity <= 0) {
        bulkStockErrors.value = {
            quantity: 'Quantity must be greater than zero.',
        };
        return;
    }

    bulkStockSubmitting.value = true;
    const csrfToken =
        document
            .querySelector('meta[name="csrf-token"]')
            ?.getAttribute('content') ?? '';

    const ids = [...selectedItems.value];
    let failed = 0;

    for (const itemId of ids) {
        const url =
            bulkStockType.value === 'in'
                ? `/inventory/items/${itemId}/stock-in`
                : `/inventory/items/${itemId}/stock-out`;

        const formData = new FormData();
        formData.append('quantity', String(quantity));
        formData.append('unit', bulkStockUnit.value || '');
        formData.append('reference', bulkStockReference.value.trim());
        formData.append('_token', csrfToken);

        try {
            const response = await fetch(url, {
                method: 'POST',
                body: formData,
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            if (!response.ok) {
                const data = await response.json().catch(() => ({}));
                const msg =
                    data?.errors?.quantity?.[0] ??
                    data?.message ??
                    'Request failed.';
                bulkStockErrors.value = { general: msg };
                failed++;
                break;
            }
        } catch {
            failed++;
            bulkStockErrors.value = {
                general: 'An error occurred. Please try again.',
            };
            break;
        }
    }

    bulkStockSubmitting.value = false;
    if (failed === 0) {
        closeBulkStockModal();
        clearSelection();
        router.reload();
    }
}

watch(stockModalOpen, (open: boolean) => {
    if (!open) {
        stockModalItem.value = null;
        stockQuantity.value = '';
        stockUnit.value = '';
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
        document
            .querySelector('meta[name="csrf-token"]')
            ?.getAttribute('content') ?? '';

    try {
        const formData = new FormData();
        formData.append('quantity', String(quantity));
        formData.append('unit', stockUnit.value || '');
        formData.append('reference', stockReference.value.trim());
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
        ).length + 3
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
                    <h1
                        class="text-2xl font-bold tracking-tight text-foreground"
                    >
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

            <!-- Column toggle -->
            <div
                class="rounded-xl border border-sidebar-border/70 bg-card p-4 shadow-sm dark:border-sidebar-border"
            >
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:gap-3">
                    <div class="flex shrink-0">
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
                            <th
                                class="h-12 px-4 text-left font-medium text-muted-foreground"
                            >
                                Actions
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
                            <td
                                class="p-4 align-middle"
                                @click.stop
                            >
                                <Checkbox
                                    :model-value="selectedItems.includes(item.id)"
                                    @update:model-value="
                                        () => toggleSelectItem(item.id)
                                    "
                                />
                            </td>
                            <td class="p-4 align-middle">
                                <div class="flex items-center gap-3">
                                    <Avatar
                                        class="size-9 shrink-0 ring-1 ring-muted/50"
                                    >
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
                                class="p-4 align-middle font-medium tabular-nums"
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
                                <div
                                    class="flex flex-nowrap items-center gap-1"
                                >
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
                                    <Button
                                        variant="outline"
                                        size="icon"
                                        class="size-8 border-red-600 text-red-600 hover:bg-red-50 hover:text-red-700 dark:border-red-500 dark:text-red-400 dark:hover:bg-red-950"
                                        title="Delete"
                                        @click="handleDelete(item.id)"
                                    >
                                        <Trash2 class="size-4" />
                                    </Button>
                                </div>
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

            <!-- Bulk actions bar (shown when items selected) - Teleport ensures visibility -->
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
                        v-if="selectedItems.length > 0"
                        class="fixed inset-x-0 bottom-0 z-50 flex items-center justify-between gap-4 border-t border-sidebar-border/70 bg-card px-6 py-4 shadow-lg dark:border-sidebar-border"
                    >
                    <span class="text-sm font-medium">
                        {{ selectedItems.length }}
                        {{ selectedItems.length === 1 ? 'item' : 'items' }}
                        selected
                    </span>
                    <div class="flex items-center gap-2">
                        <Button
                            variant="default"
                            size="default"
                            class="rounded-lg"
                            @click="downloadSelectedCsv"
                        >
                            <ArrowDownToLine class="size-4" />
                            Download CSV
                        </Button>
                        <DropdownMenu v-model:open="bulkActionsOpen">
                            <DropdownMenuTrigger as-child>
                                <Button
                                    variant="default"
                                    size="default"
                                    class="rounded-lg"
                                >
                                    Bulk Actions
                                    <ChevronDown class="ml-1 size-4" />
                                </Button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent
                                align="end"
                                class="w-56"
                            >
                                <DropdownMenuItem
                                    class="cursor-pointer"
                                    @click="downloadSelectedCsv"
                                >
                                    <ArrowDownToLine class="mr-2 size-4" />
                                    Download CSV
                                </DropdownMenuItem>
                                <DropdownMenuItem
                                    class="cursor-pointer"
                                    @click="openBulkStockModal('in')"
                                >
                                    <PackagePlus class="mr-2 size-4" />
                                    Bulk Adjust Stock
                                </DropdownMenuItem>
                                <DropdownMenuItem
                                    class="cursor-pointer text-destructive focus:text-destructive"
                                    @click="handleBulkDelete"
                                >
                                    <Trash2 class="mr-2 size-4" />
                                    Bulk Delete
                                </DropdownMenuItem>
                            </DropdownMenuContent>
                        </DropdownMenu>
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

            <!-- Bulk Stock Adjust Modal -->
            <Dialog v-model:open="bulkStockModalOpen">
                <DialogContent
                    class="gap-0 overflow-hidden p-0 sm:max-w-lg"
                    @pointer-down-outside="closeBulkStockModal"
                >
                    <div
                        class="flex items-center gap-3 border-b border-sidebar-border/70 px-6 py-4 dark:border-sidebar-border"
                        :class="
                            bulkStockType === 'in'
                                ? 'bg-emerald-500/10 dark:bg-emerald-500/15'
                                : 'bg-red-500/10 dark:bg-red-500/15'
                        "
                    >
                        <div
                            class="flex size-11 shrink-0 items-center justify-center rounded-xl"
                            :class="
                                bulkStockType === 'in'
                                    ? 'bg-emerald-500/20 text-emerald-600 dark:text-emerald-400'
                                    : 'bg-red-500/20 text-red-600 dark:text-red-400'
                            "
                        >
                            <PackagePlus
                                v-if="bulkStockType === 'in'"
                                class="size-6"
                            />
                            <PackageMinus
                                v-else
                                class="size-6"
                            />
                        </div>
                        <div class="min-w-0 flex-1">
                            <DialogTitle class="text-lg font-semibold">
                                Bulk
                                {{
                                    bulkStockType === 'in'
                                        ? 'Stock In'
                                        : 'Stock Out'
                                }}
                            </DialogTitle>
                            <p class="mt-0.5 text-sm text-muted-foreground">
                                Apply to {{ selectedItems.length }} selected
                                item(s)
                            </p>
                        </div>
                    </div>

                    <form
                        class="flex flex-col gap-0"
                        @submit.prevent="submitBulkStockMovement"
                    >
                        <div class="flex flex-col gap-4 px-6 py-5">
                            <div
                                v-if="bulkStockErrors.general"
                                class="rounded-lg border border-destructive/30 bg-destructive/10 px-3 py-2.5 text-sm text-destructive"
                            >
                                {{ bulkStockErrors.general }}
                            </div>

                            <div class="flex gap-2">
                                <Button
                                    type="button"
                                    :variant="
                                        bulkStockType === 'in'
                                            ? 'default'
                                            : 'outline'
                                    "
                                    class="flex-1"
                                    @click="bulkStockType = 'in'"
                                >
                                    <PackagePlus class="mr-2 size-4" />
                                    Stock In
                                </Button>
                                <Button
                                    type="button"
                                    :variant="
                                        bulkStockType === 'out'
                                            ? 'default'
                                            : 'outline'
                                    "
                                    class="flex-1"
                                    @click="bulkStockType = 'out'"
                                >
                                    <PackageMinus class="mr-2 size-4" />
                                    Stock Out
                                </Button>
                            </div>

                            <div class="space-y-2">
                                <Label
                                    for="bulk-stock-unit"
                                    class="flex items-center gap-1 font-medium"
                                >
                                    Units
                                    <span class="text-destructive">*</span>
                                </Label>
                                <select
                                    id="bulk-stock-unit"
                                    v-model="bulkStockUnit"
                                    required
                                    class="flex h-11 w-full rounded-lg border border-input bg-transparent px-3 py-2 text-base shadow-xs focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50 focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-50 md:text-sm"
                                >
                                    <option
                                        v-for="(label, value) in primaryUnitOptions"
                                        :key="value"
                                        :value="value"
                                    >
                                        {{ label }}
                                    </option>
                                </select>
                            </div>

                            <div class="space-y-2">
                                <Label
                                    for="bulk-stock-quantity"
                                    class="flex items-center gap-1 font-medium"
                                >
                                    Quantity
                                    <span class="text-destructive">*</span>
                                </Label>
                                <Input
                                    id="bulk-stock-quantity"
                                    v-model="bulkStockQuantity"
                                    type="number"
                                    min="0"
                                    step="0.0001"
                                    placeholder="e.g. 15"
                                    class="h-11"
                                    required
                                />
                                <InputError
                                    :message="bulkStockErrors.quantity"
                                />
                            </div>

                            <div class="space-y-2">
                                <Label
                                    for="bulk-stock-notes"
                                    class="font-medium text-muted-foreground"
                                >
                                    Notes (optional)
                                </Label>
                                <textarea
                                    id="bulk-stock-notes"
                                    v-model="bulkStockReference"
                                    rows="2"
                                    class="flex min-h-16 w-full rounded-lg border border-input bg-transparent px-3 py-2 text-sm placeholder:text-muted-foreground focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50 focus-visible:outline-none"
                                    placeholder="Reference or remark"
                                    maxlength="255"
                                />
                            </div>
                        </div>

                        <div
                            class="flex flex-row justify-end gap-2 border-t border-sidebar-border/70 bg-muted/30 px-6 py-4 dark:border-sidebar-border"
                        >
                            <Button
                                type="button"
                                variant="outline"
                                @click="closeBulkStockModal"
                            >
                                Cancel
                            </Button>
                            <Button
                                type="submit"
                                :disabled="bulkStockSubmitting"
                                :class="
                                    bulkStockType === 'in'
                                        ? 'bg-emerald-600 text-white hover:bg-emerald-700 dark:bg-emerald-600 dark:hover:bg-emerald-700'
                                        : 'bg-red-600 text-white hover:bg-red-700 dark:bg-red-600 dark:hover:bg-red-700'
                                "
                            >
                                <Spinner
                                    v-if="bulkStockSubmitting"
                                    class="mr-2 size-4"
                                />
                                {{
                                    bulkStockSubmitting
                                        ? 'Applying...'
                                        : bulkStockType === 'in'
                                          ? 'Add Quantity'
                                          : 'Remove Quantity'
                                }}
                            </Button>
                        </div>
                    </form>
                </DialogContent>
            </Dialog>

            <!-- Stock In/Out Modal (movements saved in DB) -->
            <Dialog v-model:open="stockModalOpen">
                <DialogContent
                    class="gap-0 overflow-hidden p-0 sm:max-w-lg"
                    @pointer-down-outside="closeStockModal"
                >
                    <!-- Header with icon and type -->
                    <div
                        class="flex items-center gap-3 border-b border-sidebar-border/70 px-6 py-4 dark:border-sidebar-border"
                        :class="
                            stockModalType === 'in'
                                ? 'bg-emerald-500/10 dark:bg-emerald-500/15'
                                : 'bg-red-500/10 dark:bg-red-500/15'
                        "
                    >
                        <div
                            class="flex size-11 shrink-0 items-center justify-center rounded-xl"
                            :class="
                                stockModalType === 'in'
                                    ? 'bg-emerald-500/20 text-emerald-600 dark:text-emerald-400'
                                    : 'bg-red-500/20 text-red-600 dark:text-red-400'
                            "
                        >
                            <PackagePlus
                                v-if="stockModalType === 'in'"
                                class="size-6"
                            />
                            <PackageMinus
                                v-else
                                class="size-6"
                            />
                        </div>
                        <div class="min-w-0 flex-1">
                            <DialogTitle class="text-lg font-semibold">
                                {{
                                    stockModalType === 'in'
                                        ? 'Add Stock In'
                                        : 'Stock Out'
                                }}
                            </DialogTitle>
                            <p
                                class="mt-0.5 text-sm text-muted-foreground"
                            >
                                This will be saved in stock history.
                            </p>
                        </div>
                    </div>

                    <form
                        class="flex flex-col gap-0"
                        @submit.prevent="submitStockMovement"
                    >
                        <div class="flex flex-col gap-4 px-6 py-5">
                            <div
                                v-if="stockErrors.general"
                                class="rounded-lg border border-destructive/30 bg-destructive/10 px-3 py-2.5 text-sm text-destructive"
                            >
                                {{ stockErrors.general }}
                            </div>

                            <!-- Item summary card -->
                            <div
                                v-if="stockModalItem"
                                class="rounded-xl border border-sidebar-border/70 bg-muted/40 p-4 dark:border-sidebar-border"
                            >
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0 flex-1">
                                        <p class="font-medium text-foreground">
                                            {{ stockModalItem.name }}
                                        </p>
                                        <p class="mt-1 flex flex-wrap items-center gap-x-3 gap-y-0.5 text-sm text-muted-foreground">
                                            <span>
                                                Code:
                                                {{
                                                    stockModalItem.item_code ??
                                                    '-'
                                                }}
                                            </span>
                                            <span>
                                                Unit:
                                                {{
                                                    stockModalItem.inventory
                                                        ?.primary_unit ?? '-'
                                                }}
                                            </span>
                                        </p>
                                    </div>
                                    <span
                                        class="shrink-0 rounded-full bg-primary/15 px-2.5 py-1 text-sm font-medium text-primary"
                                    >
                                        Stock:
                                        {{
                                            stockModalItem.inventory
                                                ?.stock_quantity ?? 0
                                        }}
                                    </span>
                                </div>
                            </div>

                            <!-- Units -->
                            <div class="space-y-2">
                                <Label
                                    for="stock-unit"
                                    class="flex items-center gap-1 font-medium"
                                >
                                    Units
                                    <span class="text-destructive">*</span>
                                </Label>
                                <select
                                    id="stock-unit"
                                    v-model="stockUnit"
                                    required
                                    class="flex h-11 w-full rounded-lg border border-input bg-transparent px-3 py-2 text-base shadow-xs focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50 focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-50 md:text-sm"
                                >
                                    <option
                                        v-for="(label, value) in stockUnitOptions"
                                        :key="value"
                                        :value="value"
                                    >
                                        {{ label }}
                                    </option>
                                </select>
                                <InputError
                                    :message="stockErrors.unit"
                                />
                            </div>

                            <!-- Quantity -->
                            <div class="space-y-2">
                                <Label
                                    for="stock-quantity"
                                    class="flex items-center gap-1 font-medium"
                                >
                                    Quantity
                                    <span class="text-destructive">*</span>
                                </Label>
                                <Input
                                    id="stock-quantity"
                                    v-model="stockQuantity"
                                    type="number"
                                    min="0"
                                    step="0.0001"
                                    placeholder="e.g. 15"
                                    class="h-11"
                                    required
                                />
                                <InputError
                                    :message="stockErrors.quantity"
                                />
                            </div>

                            <!-- Notes -->
                            <div class="space-y-2">
                                <Label
                                    for="stock-notes"
                                    class="font-medium text-muted-foreground"
                                >
                                    Notes (optional)
                                </Label>
                                <textarea
                                    id="stock-notes"
                                    v-model="stockReference"
                                    rows="2"
                                    class="flex min-h-16 w-full rounded-lg border border-input bg-transparent px-3 py-2 text-sm placeholder:text-muted-foreground focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50 focus-visible:outline-none"
                                    placeholder="Reference or remark"
                                    maxlength="255"
                                />
                                <InputError
                                    :message="stockErrors.reference"
                                />
                            </div>
                        </div>

                        <!-- Footer -->
                        <div
                            class="flex flex-row justify-end gap-2 border-t border-sidebar-border/70 bg-muted/30 px-6 py-4 dark:border-sidebar-border"
                        >
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
                                :class="
                                    stockModalType === 'in'
                                        ? 'bg-emerald-600 text-white hover:bg-emerald-700 dark:bg-emerald-600 dark:hover:bg-emerald-700'
                                        : 'bg-red-600 text-white hover:bg-red-700 dark:bg-red-600 dark:hover:bg-red-700'
                                "
                            >
                                <Spinner
                                    v-if="stockSubmitting"
                                    class="mr-2 size-4"
                                />
                                {{
                                    stockSubmitting
                                        ? 'Saving...'
                                        : stockModalType === 'in'
                                          ? 'Add Quantity'
                                          : 'Remove Quantity'
                                }}
                            </Button>
                        </div>
                    </form>
                </DialogContent>
            </Dialog>
        </div>
    </AppLayout>
</template>
