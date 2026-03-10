<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import SuperadminLayout from '@/layouts/SuperadminLayout.vue';
import superadmin from '@/routes/superadmin';
import { type BreadcrumbItem } from '@/types';

type Props = {
    domains: unknown[];
};

defineProps<Props>();

const dashboardUrl = superadmin.dashboard?.url?.() ?? '/superadmin/dashboard';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Home', href: dashboardUrl },
    { title: 'Super Admin', href: dashboardUrl },
    { title: 'Domain', href: superadmin.domain?.index?.url?.() ?? '/superadmin/domain' },
];
</script>

<template>
    <Head title="Domain - Super Admin" />

    <SuperadminLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-4"
        >
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-semibold">Domain</h1>
            </div>

            <div
                class="rounded-xl border border-sidebar-border/70 bg-card p-8 text-center dark:border-sidebar-border"
            >
                <p class="text-muted-foreground">
                    Domain management coming soon.
                </p>
            </div>
        </div>
    </SuperadminLayout>
</template>
