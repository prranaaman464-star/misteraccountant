<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import SuperadminLayout from '@/layouts/SuperadminLayout.vue';
import superadmin from '@/routes/superadmin';
import { type BreadcrumbItem } from '@/types';

type Plan = {
    id: number;
    name: string;
    slug: string;
    description: string | null;
    price: string;
    billing_cycle: string;
    member_limit: number | null;
    is_active: boolean;
};

type Props = {
    packages: Plan[];
};

defineProps<Props>();

const dashboardUrl = superadmin.dashboard?.url?.() ?? '/superadmin/dashboard';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Home', href: dashboardUrl },
    { title: 'Super Admin', href: dashboardUrl },
    { title: 'Packages', href: superadmin.packages?.index?.url?.() ?? '/superadmin/packages' },
];
</script>

<template>
    <Head title="Packages - Super Admin" />

    <SuperadminLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-4"
        >
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-semibold">Packages</h1>
            </div>

            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                <div
                    v-for="pkg in packages"
                    :key="pkg.id"
                    class="flex flex-col rounded-xl border border-sidebar-border/70 bg-card p-6 dark:border-sidebar-border"
                >
                    <h3 class="text-lg font-semibold">{{ pkg.name }}</h3>
                    <p
                        v-if="pkg.description"
                        class="mt-1 text-sm text-muted-foreground"
                    >
                        {{ pkg.description }}
                    </p>
                    <p class="mt-4 text-2xl font-bold">
                        ${{ parseFloat(pkg.price).toFixed(2) }}
                        <span class="text-sm font-normal text-muted-foreground">
                            / {{ pkg.billing_cycle }}
                        </span>
                    </p>
                    <p
                        v-if="pkg.member_limit"
                        class="mt-2 text-sm text-muted-foreground"
                    >
                        {{ pkg.member_limit }} members
                    </p>
                    <span
                        :class="[
                            'mt-4 inline-flex w-fit rounded-full px-2 py-0.5 text-xs font-medium',
                            pkg.is_active
                                ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400'
                                : 'bg-muted text-muted-foreground',
                        ]"
                    >
                        {{ pkg.is_active ? 'Active' : 'Inactive' }}
                    </span>
                </div>
            </div>
            <p
                v-if="!packages?.length"
                class="py-8 text-center text-muted-foreground"
            >
                No packages yet
            </p>
        </div>
    </SuperadminLayout>
</template>
