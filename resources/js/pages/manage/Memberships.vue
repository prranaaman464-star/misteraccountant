<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

defineProps<{
    organization: { id: number; name: string };
    subscription: { plan_name: string; ends_at: string | null } | null;
    memberCount: number;
    memberLimit: number | null;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Manage', href: '/manage' },
    { title: 'Manage Memberships', href: '/manage/memberships' },
];
</script>

<template>
    <Head title="Manage Memberships" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
        >
            <div class="flex flex-col gap-4">
                <h1 class="text-xl font-semibold">Manage Memberships</h1>
                <p class="text-muted-foreground">
                    Organization: <strong>{{ organization.name }}</strong>
                </p>

                <div v-if="subscription" class="grid gap-4 md:grid-cols-2">
                    <div class="rounded-lg border border-sidebar-border p-4">
                        <h2 class="font-medium">Current plan</h2>
                        <p class="text-lg">{{ subscription.plan_name }}</p>
                    </div>
                    <div class="rounded-lg border border-sidebar-border p-4">
                        <h2 class="font-medium">Members</h2>
                        <p class="text-lg">
                            {{ memberCount }}
                            <span v-if="memberLimit !== null"
                                >/ {{ memberLimit }}</span
                            >
                        </p>
                    </div>
                </div>

                <p v-else class="text-muted-foreground">
                    No active subscription. Go to
                    <a href="/plan" class="text-primary underline">My Plan</a>
                    for details.
                </p>
            </div>
        </div>
    </AppLayout>
</template>
