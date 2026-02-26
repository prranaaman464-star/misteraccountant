<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import SuperadminLayout from '@/layouts/SuperadminLayout.vue';
import superadmin from '@/routes/superadmin';
import { type BreadcrumbItem } from '@/types';

type Subscription = {
    id: number;
    status: string;
    starts_at: string;
    ends_at: string | null;
    organization?: { id: number; name: string; slug: string };
    plan?: { id: number; name: string; slug: string; price: string; billing_cycle: string };
};

type Props = {
    subscriptions: {
        data: Subscription[];
        links: Array<{ url: string | null; label: string; active: boolean }>;
    };
};

defineProps<Props>();

const dashboardUrl = superadmin.dashboard?.url?.() ?? '/superadmin/dashboard';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Home', href: dashboardUrl },
    { title: 'Super Admin', href: dashboardUrl },
    { title: 'Subscriptions', href: superadmin.subscriptions?.index?.url?.() ?? '/superadmin/subscriptions' },
];
</script>

<template>
    <Head title="Subscriptions - Super Admin" />

    <SuperadminLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-4"
        >
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-semibold">Subscriptions</h1>
            </div>

            <div
                class="rounded-xl border border-sidebar-border/70 bg-card dark:border-sidebar-border"
            >
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-sidebar-border/70 dark:border-sidebar-border">
                                <th class="px-4 py-3 text-left font-medium">
                                    Organization
                                </th>
                                <th class="px-4 py-3 text-left font-medium">
                                    Plan
                                </th>
                                <th class="px-4 py-3 text-left font-medium">
                                    Status
                                </th>
                                <th class="px-4 py-3 text-left font-medium">
                                    Ends At
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="sub in subscriptions.data"
                                :key="sub.id"
                                class="border-b border-sidebar-border/70 last:border-0 dark:border-sidebar-border"
                            >
                                <td class="px-4 py-3 font-medium">
                                    {{ sub.organization?.name ?? '-' }}
                                </td>
                                <td class="px-4 py-3">
                                    {{ sub.plan?.name ?? '-' }}
                                    <span
                                        v-if="sub.plan"
                                        class="block text-xs text-muted-foreground"
                                    >
                                        ${{ sub.plan.price }} / {{ sub.plan.billing_cycle }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <span
                                        :class="[
                                            'rounded-full px-2 py-0.5 text-xs font-medium',
                                            sub.status === 'active'
                                                ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400'
                                                : 'bg-muted text-muted-foreground',
                                        ]"
                                    >
                                        {{ sub.status }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-muted-foreground">
                                    {{ sub.ends_at ? new Date(sub.ends_at).toLocaleDateString() : '-' }}
                                </td>
                            </tr>
                            <tr v-if="!subscriptions.data?.length">
                                <td
                                    colspan="4"
                                    class="px-4 py-8 text-center text-muted-foreground"
                                >
                                    No subscriptions yet
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div
                    v-if="subscriptions.links?.length"
                    class="flex justify-center gap-2 border-t border-sidebar-border/70 px-4 py-3 dark:border-sidebar-border"
                >
                    <Link
                        v-for="(link, i) in subscriptions.links.filter((l) => l.url)"
                        :key="i"
                        :href="link.url!"
                        :class="[
                            'rounded px-3 py-1 text-sm',
                            link.active
                                ? 'bg-primary text-primary-foreground'
                                : 'hover:bg-muted',
                        ]"
                    >
                        <span v-html="link.label" />
                    </Link>
                </div>
            </div>
        </div>
    </SuperadminLayout>
</template>
