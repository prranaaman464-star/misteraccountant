<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import SuperadminLayout from '@/layouts/SuperadminLayout.vue';
import superadmin from '@/routes/superadmin';
import { type BreadcrumbItem } from '@/types';

type Props = {
    transactions: unknown[];
};

defineProps<Props>();

const dashboardUrl = superadmin.dashboard?.url?.() ?? '/superadmin/dashboard';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Home', href: dashboardUrl },
    { title: 'Super Admin', href: dashboardUrl },
    {
        title: 'Purchase Transaction',
        href: superadmin.purchaseTransaction?.index?.url?.() ?? '/superadmin/purchase-transaction',
    },
];
</script>

<template>
    <Head title="Purchase Transaction - Super Admin" />

    <SuperadminLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-4"
        >
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-semibold">Purchase Transaction</h1>
            </div>

            <div
                class="rounded-xl border border-sidebar-border/70 bg-card p-8 text-center dark:border-sidebar-border"
            >
                <p class="text-muted-foreground">
                    Purchase transactions coming soon.
                </p>
            </div>
        </div>
    </SuperadminLayout>
</template>
