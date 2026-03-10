<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import {
    ArrowUpFromLine,
    ArrowUpDown,
    Check,
    ChevronDown,
    Columns3,
    Filter,
    Plus,
    Search,
    Settings,
    UserCircle,
    X,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import AppLayout from '@/layouts/AppLayout.vue';
import { getInitials } from '@/composables/useInitials';
import type { BreadcrumbItem } from '@/types';

type CustomerStatus = 'active' | 'inactive';

type Customer = {
    id: string;
    name: string;
    avatar: string | null;
    phone: string;
    country: string;
    country_flag: string;
    balance: number;
    total_invoice: number;
    created_on: string;
    status: CustomerStatus;
};

const props = withDefaults(
    defineProps<{
        customers?: Customer[];
    }>(),
    {
        customers: () => [],
    },
);

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Clients & Prospects', href: '/sales/clients-and-prospects' },
];

const page = usePage();
const flash = page.props.flash as
    | { success?: string; error?: string }
    | undefined;

const searchQuery = ref('');
const sortBy = ref('latest');
const columnsOpen = ref(false);
const selectedRows = ref<string[]>([]);

function formatPrice(value: number): string {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
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

function exportCustomers(): void {
    window.location.href = '/sales/clients-and-prospects/export';
}

function toggleSelectAll(checked: boolean | 'indeterminate'): void {
    if (checked === true) {
        selectedRows.value = props.customers.map((c) => c.id);
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
        selectedRows.value.length === props.customers.length &&
        props.customers.length > 0,
);

const isSomeSelected = computed(
    () =>
        selectedRows.value.length > 0 &&
        selectedRows.value.length < props.customers.length,
);

const filteredCustomers = computed(() => {
    let list = [...props.customers];
    if (searchQuery.value.trim()) {
        const q = searchQuery.value.toLowerCase();
        list = list.filter(
            (c) =>
                c.name.toLowerCase().includes(q) ||
                c.phone.includes(q) ||
                c.country.toLowerCase().includes(q),
        );
    }
    if (sortBy.value === 'oldest') {
        list = [...list].sort(
            (a, b) =>
                new Date(a.created_on).getTime() -
                new Date(b.created_on).getTime(),
        );
    } else {
        list = [...list].sort(
            (a, b) =>
                new Date(b.created_on).getTime() -
                new Date(a.created_on).getTime(),
        );
    }
    return list;
});
</script>

<template>
    <Head title="Customers" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="relative flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-6"
        >
            <!-- Flash messages -->
            <div
                v-if="flash?.success"
                class="rounded-lg border border-green-200 bg-green-50 p-3 text-sm text-green-800 dark:border-green-800 dark:bg-green-900/20 dark:text-green-200"
            >
                {{ flash.success }}
            </div>
            <div
                v-if="flash?.error"
                class="rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-800 dark:border-red-800 dark:bg-red-900/20 dark:text-red-200"
            >
                {{ flash.error }}
            </div>

            <!-- Header: Title + Actions -->
            <div
                class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
            >
                <div>
                    <h1
                        class="text-2xl font-bold tracking-tight text-foreground sm:text-3xl"
                    >
                        Customers
                    </h1>
                    <p class="mt-1 text-sm text-muted-foreground">
                        Manage your clients and prospects
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <Button
                        variant="outline"
                        size="default"
                        class="rounded-lg shadow-sm transition-shadow hover:shadow"
                        @click="exportCustomers"
                    >
                        <ArrowUpFromLine class="size-4" />
                        Export
                    </Button>
                    <Link href="/sales/clients-and-prospects/create" as="span">
                        <Button
                            size="default"
                            class="rounded-lg shadow-md transition-all hover:shadow-lg"
                        >
                            <Plus class="size-4" />
                            New Customer
                        </Button>
                    </Link>
                </div>
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
                            placeholder="Search customers..."
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
                        <DropdownMenuContent align="end">
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

            <!-- Table -->
            <div class="relative flex-1 overflow-auto">
                <div
                    class="overflow-auto rounded-xl border border-sidebar-border/70 bg-card shadow-sm"
                >
                    <table class="w-full caption-bottom text-sm">
                        <thead>
                            <tr
                                class="border-b border-sidebar-border/70 bg-muted/40"
                            >
                                <th
                                    class="h-12 w-12 px-4 text-left align-middle"
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
                                    <span
                                        class="inline-flex items-center gap-1"
                                    >
                                        Customer
                                        <ArrowUpDown
                                            class="size-3.5 text-muted-foreground/70"
                                            aria-hidden
                                        />
                                    </span>
                                </th>
                                <th
                                    class="h-12 px-4 text-left font-medium text-muted-foreground"
                                >
                                    <span
                                        class="inline-flex items-center gap-1"
                                    >
                                        Phone
                                        <ArrowUpDown
                                            class="size-3.5 text-muted-foreground/70"
                                            aria-hidden
                                        />
                                    </span>
                                </th>
                                <th
                                    class="h-12 px-4 text-left font-medium text-muted-foreground"
                                >
                                    <span
                                        class="inline-flex items-center gap-1"
                                    >
                                        Country
                                        <ArrowUpDown
                                            class="size-3.5 text-muted-foreground/70"
                                            aria-hidden
                                        />
                                    </span>
                                </th>
                                <th
                                    class="h-12 px-4 text-left font-medium text-muted-foreground"
                                >
                                    <span
                                        class="inline-flex items-center gap-1"
                                    >
                                        Balance
                                        <ArrowUpDown
                                            class="size-3.5 text-muted-foreground/70"
                                            aria-hidden
                                        />
                                    </span>
                                </th>
                                <th
                                    class="h-12 px-4 text-left font-medium text-muted-foreground"
                                >
                                    Total Invoice
                                </th>
                                <th
                                    class="h-12 px-4 text-left font-medium text-muted-foreground"
                                >
                                    <span
                                        class="inline-flex items-center gap-1"
                                    >
                                        Created On
                                        <ArrowUpDown
                                            class="size-3.5 text-muted-foreground/70"
                                            aria-hidden
                                        />
                                    </span>
                                </th>
                                <th
                                    class="h-12 px-4 text-left font-medium text-muted-foreground"
                                >
                                    Status
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="customer in filteredCustomers"
                                :key="customer.id"
                                class="border-b border-sidebar-border/50 transition-colors hover:bg-muted/40"
                            >
                                <td class="px-4 py-3">
                                    <Checkbox
                                        :model-value="
                                            selectedRows.includes(customer.id)
                                        "
                                        @update:model-value="
                                            () => toggleSelectRow(customer.id)
                                        "
                                    />
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        <Avatar
                                            class="size-8 shrink-0 overflow-hidden rounded-full"
                                        >
                                            <AvatarImage
                                                v-if="customer.avatar"
                                                :src="customer.avatar"
                                                :alt="customer.name"
                                            />
                                            <AvatarFallback
                                                class="rounded-full bg-primary/20 text-xs font-semibold text-primary"
                                            >
                                                {{ getInitials(customer.name) }}
                                            </AvatarFallback>
                                        </Avatar>
                                        <span class="font-medium">
                                            {{ customer.name }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-muted-foreground">
                                    {{ customer.phone }}
                                </td>
                                <td class="px-4 py-3 text-muted-foreground">
                                    <span class="inline-flex items-center gap-2">
                                        <span
                                            class="inline-block text-base leading-none"
                                            aria-hidden
                                        >
                                            {{ customer.country_flag }}
                                        </span>
                                        {{ customer.country }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 font-medium">
                                    {{ formatPrice(customer.balance) }}
                                </td>
                                <td class="px-4 py-3 text-muted-foreground">
                                    {{ customer.total_invoice }}
                                </td>
                                <td class="px-4 py-3 text-muted-foreground">
                                    {{ formatDate(customer.created_on) }}
                                </td>
                                <td class="px-4 py-3">
                                    <Badge
                                        v-if="customer.status === 'active'"
                                        class="inline-flex items-center gap-1 border-transparent bg-green-500/90 text-white hover:bg-green-500/90"
                                    >
                                        <Check class="size-3" />
                                        Active
                                    </Badge>
                                    <Badge
                                        v-else
                                        variant="destructive"
                                        class="inline-flex items-center gap-1"
                                    >
                                        <X class="size-3" />
                                        Inactive
                                    </Badge>
                                </td>
                            </tr>
                            <tr v-if="filteredCustomers.length === 0">
                                <td colspan="9" class="p-12">
                                    <div
                                        class="flex flex-col items-center justify-center gap-3 rounded-lg border border-dashed border-sidebar-border/70 bg-muted/20 py-12"
                                    >
                                        <UserCircle
                                            class="size-12 text-muted-foreground/50"
                                        />
                                        <p class="text-sm font-medium text-muted-foreground">
                                            No customers found
                                        </p>
                                        <p class="text-xs text-muted-foreground">
                                            Try adjusting your search or add a new customer
                                        </p>
                                        <Link
                                            href="/sales/clients-and-prospects/create"
                                            as="span"
                                        >
                                            <Button size="sm" class="mt-1">
                                                <Plus class="size-4" />
                                                Add your first customer
                                            </Button>
                                        </Link>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
