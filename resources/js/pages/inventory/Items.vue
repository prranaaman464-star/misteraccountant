<script setup lang="ts">
import { computed, ref } from 'vue';
import { Head } from '@inertiajs/vue3';
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
    Search,
    Clock,
    Columns3,
} from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Checkbox } from '@/components/ui/checkbox';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import type { BreadcrumbItem } from '@/types';
import { dashboard } from '@/routes';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Home', href: dashboard().url },
    { title: 'Items', href: '/inventory/items' },
];

const searchQuery = ref('');
const sortBy = ref('Latest');
const selectedItems = ref<number[]>([]);
const currentPage = ref(1);
const rowsPerPage = ref(10);

const items = [
    { id: 1, name: 'Apple iPhone 15', code: 'PR00025', unit: 'Piece', quantity: 2, sellingPrice: 100, purchasePrice: 98, image: null },
    { id: 2, name: 'Dell XPS 13 9310', code: 'PR00014', unit: 'Piece', quantity: 12, sellingPrice: 25, purchasePrice: 24, image: null },
    { id: 3, name: 'Bose QuietComfort 45', code: 'PR00012', unit: 'Piece', quantity: 2, sellingPrice: 34, purchasePrice: 58, image: null },
    { id: 4, name: 'Nike Dri-FIT T-shirt', code: 'PR00016', unit: 'Pack', quantity: 24, sellingPrice: 75, purchasePrice: 72, image: null },
    { id: 5, name: 'Adidas Ultraboost 22 Running Shoe', code: 'PR00022', unit: 'Pack', quantity: 13, sellingPrice: 9, purchasePrice: 89, image: null },
    { id: 6, name: 'Samsung French Door Refrigerator', code: 'PR00047', unit: 'Litre', quantity: 67, sellingPrice: 120, purchasePrice: 115, image: null },
    { id: 7, name: 'Dyson V15 Detect Vacuum Cleaner', code: 'PR00014', unit: 'Piece', quantity: 13, sellingPrice: 250, purchasePrice: 240, image: null },
    { id: 8, name: 'HP Spectre x360 14', code: 'PR00031', unit: 'Piece', quantity: 25, sellingPrice: 541, purchasePrice: 525, image: null },
    { id: 9, name: 'Dyson Supersonic Hair Dryer', code: 'PR00077', unit: 'Litre', quantity: 24, sellingPrice: 741, purchasePrice: 750, image: null },
    { id: 10, name: 'Apple AirPods Pro', code: 'PR00045', unit: 'Piece', quantity: 65, sellingPrice: 89, purchasePrice: 49, image: null },
];

function toggleSelectAll(checked: boolean | 'indeterminate'): void {
    if (checked === true) {
        selectedItems.value = items.map((i) => i.id);
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

const isAllSelected = computed(
    () => selectedItems.value.length === items.length && items.length > 0,
);
const isSomeSelected = computed(
    () => selectedItems.value.length > 0 && selectedItems.value.length < items.length,
);
</script>

<template>
    <Head title="Items" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-6 overflow-x-auto p-6"
        >
            <!-- Page title and actions -->
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <h1 class="text-2xl font-semibold tracking-tight">Items</h1>
                <div class="flex gap-2">
                    <Button variant="outline" size="default">
                        <ArrowDownToLine class="size-4" />
                        Export
                    </Button>
                    <Button size="default">
                        <Plus class="size-4" />
                        New Items
                    </Button>
                </div>
            </div>

            <!-- Search and filters -->
            <div class="flex flex-wrap items-center gap-3">
                <div class="relative flex-1 min-w-[200px]">
                    <Input
                        v-model="searchQuery"
                        placeholder="Search"
                        class="pl-9"
                    />
                </div>
                <Button variant="outline" size="default">
                    <Filter class="size-4" />
                    Filter
                </Button>
                <DropdownMenu>
                    <DropdownMenuTrigger as-child>
                        <Button variant="outline" size="default">
                            Sort By: {{ sortBy }}
                            <ChevronDown class="size-4" />
                        </Button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent align="end">
                        <DropdownMenuItem @click="sortBy = 'Latest'">
                            Latest
                        </DropdownMenuItem>
                        <DropdownMenuItem @click="sortBy = 'Oldest'">
                            Oldest
                        </DropdownMenuItem>
                        <DropdownMenuItem @click="sortBy = 'Name'">
                            Name
                        </DropdownMenuItem>
                        <DropdownMenuItem @click="sortBy = 'Price'">
                            Price
                        </DropdownMenuItem>
                    </DropdownMenuContent>
                </DropdownMenu>
                <DropdownMenu>
                    <DropdownMenuTrigger as-child>
                        <Button variant="outline" size="default">
                            <Columns3 class="size-4" />
                            Column
                        </Button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent align="end">
                        <DropdownMenuItem>Product/Service</DropdownMenuItem>
                        <DropdownMenuItem>Code</DropdownMenuItem>
                        <DropdownMenuItem>Unit</DropdownMenuItem>
                        <DropdownMenuItem>Quantity</DropdownMenuItem>
                        <DropdownMenuItem>Selling Price</DropdownMenuItem>
                        <DropdownMenuItem>Purchase Price</DropdownMenuItem>
                    </DropdownMenuContent>
                </DropdownMenu>
            </div>

            <!-- Table -->
            <div
                class="flex-1 overflow-auto rounded-lg border border-sidebar-border/70 dark:border-sidebar-border"
            >
                <table class="w-full caption-bottom text-sm">
                    <thead>
                        <tr class="border-b border-sidebar-border/70 bg-muted/50 dark:border-sidebar-border">
                            <th class="h-12 w-12 px-4 text-left align-middle">
                                <Checkbox
                                    :checked="isAllSelected ? true : isSomeSelected ? 'indeterminate' : false"
                                    @update:checked="toggleSelectAll"
                                />
                            </th>
                            <th class="h-12 px-4 text-left font-medium">Product/Service</th>
                            <th class="h-12 px-4 text-left font-medium">Code</th>
                            <th class="h-12 px-4 text-left font-medium">Unit</th>
                            <th class="h-12 px-4 text-left font-medium">Quantity</th>
                            <th class="h-12 px-4 text-left font-medium">
                                Selling Price
                            </th>
                            <th class="h-12 px-4 text-left font-medium">
                                Purchase Price
                            </th>
                            <th class="h-12 px-4 text-left font-medium">Actions</th>
                            <th class="h-12 w-12 px-4"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="item in items"
                            :key="item.id"
                            class="border-b border-sidebar-border/70 transition-colors hover:bg-muted/50 dark:border-sidebar-border"
                        >
                            <td class="p-4 align-middle">
                                <Checkbox
                                    :checked="selectedItems.includes(item.id)"
                                    @update:checked="() => toggleSelectItem(item.id)"
                                />
                            </td>
                            <td class="p-4 align-middle">
                                <div class="flex items-center gap-3">
                                    <Avatar class="size-9 shrink-0">
                                        <AvatarImage v-if="item.image" :src="item.image" :alt="item.name" />
                                        <AvatarFallback
                                            class="rounded-lg bg-muted text-xs font-medium"
                                        >
                                            <Package class="size-4" />
                                        </AvatarFallback>
                                    </Avatar>
                                    <span class="font-medium">{{ item.name }}</span>
                                </div>
                            </td>
                            <td class="p-4 align-middle text-muted-foreground">{{ item.code }}</td>
                            <td class="p-4 align-middle">{{ item.unit }}</td>
                            <td class="p-4 align-middle">{{ item.quantity }}</td>
                            <td class="p-4 align-middle">${{ item.sellingPrice }}</td>
                            <td class="p-4 align-middle">${{ item.purchasePrice }}</td>
                            <td class="p-4 align-middle">
                                <div class="flex flex-wrap gap-2">
                                    <Button variant="default" size="sm">
                                        <Clock class="size-4" />
                                        History
                                    </Button>
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
                                        <Button variant="ghost" size="icon" class="size-8">
                                            <MoreHorizontal class="size-4" />
                                        </Button>
                                    </DropdownMenuTrigger>
                                    <DropdownMenuContent align="end">
                                        <DropdownMenuItem>Edit</DropdownMenuItem>
                                        <DropdownMenuItem>Duplicate</DropdownMenuItem>
                                        <DropdownMenuItem class="text-destructive">
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
                        <span class="text-sm text-muted-foreground">Row Per Page</span>
                        <DropdownMenu>
                            <DropdownMenuTrigger as-child>
                                <Button variant="outline" size="sm">
                                    {{ rowsPerPage }} Entries
                                    <ChevronDown class="size-4" />
                                </Button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent align="start">
                                <DropdownMenuItem @click="rowsPerPage = 10">
                                    10 Entries
                                </DropdownMenuItem>
                                <DropdownMenuItem @click="rowsPerPage = 25">
                                    25 Entries
                                </DropdownMenuItem>
                                <DropdownMenuItem @click="rowsPerPage = 50">
                                    50 Entries
                                </DropdownMenuItem>
                                <DropdownMenuItem @click="rowsPerPage = 100">
                                    100 Entries
                                </DropdownMenuItem>
                            </DropdownMenuContent>
                        </DropdownMenu>
                    </div>
                    <span class="text-sm text-muted-foreground">
                        © 2025 Mister Accountant, All Rights Reserved
                    </span>
                </div>
                <div class="flex items-center gap-4">
                    <div class="flex items-center gap-1">
                        <Button
                            variant="outline"
                            size="icon"
                            class="size-8"
                            :disabled="currentPage === 1"
                            @click="currentPage = Math.max(1, currentPage - 1)"
                        >
                            <ChevronLeft class="size-4" />
                        </Button>
                        <Button
                            variant="outline"
                            size="sm"
                            class="min-w-8"
                        >
                            {{ currentPage }}
                        </Button>
                        <Button
                            variant="outline"
                            size="sm"
                            class="min-w-8"
                        >
                            2
                        </Button>
                        <Button
                            variant="outline"
                            size="icon"
                            class="size-8"
                            @click="currentPage = currentPage + 1"
                        >
                            <ChevronRight class="size-4" />
                        </Button>
                    </div>
                    <span class="text-sm text-muted-foreground">Version: 1.3.8</span>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
