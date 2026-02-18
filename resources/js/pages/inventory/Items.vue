<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import {
    ArrowDownToLine,
    PackageMinus,
    ChevronDown,
    ChevronLeft,
    ChevronRight,
    Filter,
    MoreHorizontal,
    Package,
    PackagePlus,
    Plus,
    Clock,
    Columns3,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { computed, ref } from 'vue';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Input } from '@/components/ui/input';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import inventory from '@/routes/inventory';
import { create, show, edit, destroy } from '@/routes/inventory/items';
import type { BreadcrumbItem } from '@/types';

type Item = {
    id: number;
    name: string;
    item_code: string | null;
    category: { id: number; name: string } | null;
    pricing: {
        sale_price: number | null;
        purchase_price: number | null;
    } | null;
    inventory: {
        primary_unit: string | null;
        stock_quantity: number | null;
    } | null;
    item_image: string | null;
    status: string;
    item_type: string;
};

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
</script>

<template>
    <Head title="Items" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto p-6">
            <!-- Page title and actions -->
            <div
                class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
            >
                <h1 class="text-2xl font-semibold tracking-tight">Items</h1>
                <div class="flex gap-2">
                    <Button variant="outline" size="default">
                        <ArrowDownToLine class="size-4" />
                        Export
                    </Button>
                    <Link :href="create().url">
                        <Button size="default">
                            <Plus class="size-4" />
                            New Items
                        </Button>
                    </Link>
                </div>
            </div>

            <!-- Search and filters -->
            <div class="flex flex-wrap items-center gap-3">
                <div class="relative min-w-[200px] flex-1">
                    <Search
                        class="absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground"
                    />
                    <Input
                        v-model="searchQuery"
                        placeholder="Search items..."
                        class="pl-9"
                        @keyup.enter="handleSearch"
                    />
                </div>
                <Button variant="outline" size="default" @click="handleSearch">
                    <Search class="size-4" />
                    Search
                </Button>
            </div>

            <!-- Table -->
            <div
                class="flex-1 overflow-auto rounded-lg border border-sidebar-border/70 dark:border-sidebar-border"
            >
                <table class="w-full caption-bottom text-sm">
                    <thead>
                        <tr
                            class="border-b border-sidebar-border/70 bg-muted/50 dark:border-sidebar-border"
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
                            <th class="h-12 px-4 text-left font-medium">
                                Product/Service
                            </th>
                            <th class="h-12 px-4 text-left font-medium">
                                Code
                            </th>
                            <th class="h-12 px-4 text-left font-medium">
                                Unit
                            </th>
                            <th class="h-12 px-4 text-left font-medium">
                                Quantity
                            </th>
                            <th class="h-12 px-4 text-left font-medium">
                                Selling Price
                            </th>
                            <th class="h-12 px-4 text-left font-medium">
                                Purchase Price
                            </th>
                            <th class="h-12 px-4 text-left font-medium">
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
                                colspan="9"
                                class="p-8 text-center text-muted-foreground"
                            >
                                No items found.
                                <Link
                                    :href="create().url"
                                    class="text-primary underline"
                                    >Create your first item</Link
                                >
                            </td>
                        </tr>
                        <tr
                            v-for="item in items.data"
                            :key="item.id"
                            class="border-b border-sidebar-border/70 transition-colors hover:bg-muted/50 dark:border-sidebar-border"
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
                                    <Avatar class="size-9 shrink-0">
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
                                            v-if="item.category"
                                            class="text-xs text-muted-foreground"
                                            >{{ item.category.name }}</span
                                        >
                                    </div>
                                </div>
                            </td>
                            <td class="p-4 align-middle text-muted-foreground">
                                {{ item.item_code || '-' }}
                            </td>
                            <td class="p-4 align-middle">
                                {{ item.inventory?.primary_unit || '-' }}
                            </td>
                            <td class="p-4 align-middle">
                                {{ item.inventory?.stock_quantity ?? '-' }}
                            </td>
                            <td class="p-4 align-middle">
                                {{ formatPrice(item.pricing?.sale_price) }}
                            </td>
                            <td class="p-4 align-middle">
                                {{ formatPrice(item.pricing?.purchase_price) }}
                            </td>
                            <td class="p-4 align-middle">
                                <div class="flex flex-wrap gap-2">
                                    <Link :href="show(item.id).url">
                                        <Button variant="default" size="sm">
                                            <Clock class="size-4" />
                                            View
                                        </Button>
                                    </Link>
                                    <Button
                                        variant="outline"
                                        size="sm"
                                        class="border-emerald-600 text-emerald-600 hover:bg-emerald-50 hover:text-emerald-700 dark:border-emerald-500 dark:text-emerald-400 dark:hover:bg-emerald-950"
                                    >
                                        <PackagePlus class="size-4" />
                                        Stock In
                                    </Button>
                                    <Button
                                        variant="outline"
                                        size="sm"
                                        class="border-red-600 text-red-600 hover:bg-red-50 hover:text-red-700 dark:border-red-500 dark:text-red-400 dark:hover:bg-red-950"
                                    >
                                        <PackageMinus class="size-4" />
                                        Stock Out
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
                            :href="items.links[items.links.length - 1].url"
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
        </div>
    </AppLayout>
</template>
