<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
</script>

<template>
    <Head title="Party Transactions" />
    <AppLayout
        :breadcrumbs="[
            {
                title: 'Party Transactions',
                href: '/inventory/party-transactions',
            },
        ]"
    >
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <h1 class="text-2xl font-semibold tracking-tight">
                Party Transactions coming soon
            </h1>
        </div>
    </AppLayout>
</template>
