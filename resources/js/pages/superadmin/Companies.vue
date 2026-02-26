<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ExternalLink } from 'lucide-vue-next';
import SuperadminLayout from '@/layouts/SuperadminLayout.vue';
import superadmin from '@/routes/superadmin';
import { type BreadcrumbItem } from '@/types';

type Company = {
    id: number;
    name: string;
    slug: string;
    email?: string;
    owner?: { id: number; name: string; email: string };
    subscriptions?: Array<{ plan: { name: string } }>;
};

type Props = {
    companies: {
        data: Company[];
        links: Array<{ url: string | null; label: string; active: boolean }>;
    };
};

defineProps<Props>();

const dashboardUrl = superadmin.dashboard?.url?.() ?? '/superadmin/dashboard';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Home', href: dashboardUrl },
    { title: 'Super Admin', href: dashboardUrl },
    { title: 'Companies', href: superadmin.companies?.index?.url?.() ?? '/superadmin/companies' },
];
</script>

<template>
    <Head title="Companies - Super Admin" />

    <SuperadminLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-4"
        >
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-semibold">Companies</h1>
            </div>

            <div
                class="rounded-xl border border-sidebar-border/70 bg-card dark:border-sidebar-border"
            >
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-sidebar-border/70 dark:border-sidebar-border">
                                <th class="px-4 py-3 text-left font-medium">
                                    Company
                                </th>
                                <th class="px-4 py-3 text-left font-medium">
                                    Owner
                                </th>
                                <th class="px-4 py-3 text-left font-medium">
                                    Plan
                                </th>
                                <th class="px-4 py-3 text-right font-medium">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="company in companies.data"
                                :key="company.id"
                                class="border-b border-sidebar-border/70 last:border-0 dark:border-sidebar-border"
                            >
                                <td class="px-4 py-3 font-medium">
                                    {{ company.name }}
                                </td>
                                <td class="px-4 py-3">
                                    {{ company.owner?.name ?? '-' }}
                                    <span
                                        v-if="company.owner?.email"
                                        class="block text-xs text-muted-foreground"
                                    >
                                        {{ company.owner.email }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    {{
                                        company.subscriptions?.[0]?.plan?.name ??
                                        '-'
                                    }}
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <Link
                                        href="/superadmin/switch-organization"
                                        method="post"
                                        :data="{ organization_id: company.id }"
                                        as="button"
                                        class="inline-flex items-center gap-1 rounded-md bg-primary px-3 py-1.5 text-sm font-medium text-primary-foreground hover:bg-primary/90"
                                    >
                                        <ExternalLink class="size-4" />
                                        Manage
                                    </Link>
                                </td>
                            </tr>
                            <tr
                                v-if="!companies.data?.length"
                            >
                                <td
                                    colspan="4"
                                    class="px-4 py-8 text-center text-muted-foreground"
                                >
                                    No companies yet
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div
                    v-if="companies.links?.length"
                    class="flex justify-center gap-2 border-t border-sidebar-border/70 px-4 py-3 dark:border-sidebar-border"
                >
                    <Link
                        v-for="(link, i) in companies.links.filter((l) => l.url)"
                        :key="i"
                        :href="link.url ?? '#'"
                        :class="[
                            'rounded px-3 py-1 text-sm',
                            link.active
                                ? 'bg-primary text-primary-foreground'
                                : 'hover:bg-muted',
                        ]"
                        v-html="link.label"
                    />
                </div>
            </div>
        </div>
    </SuperadminLayout>
</template>
