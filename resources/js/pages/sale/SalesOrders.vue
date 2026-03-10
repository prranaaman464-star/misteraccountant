<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
</script>

<template>
    <Head title="Sales Orders" />
    <AppLayout
        :breadcrumbs="[{ title: 'Sales Orders', href: '/sales/sales-orders' }]"
    >
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <h1 class="text-2xl font-semibold tracking-tight">
                Sales Orders coming soon
            </h1>
        </div>
    </AppLayout>
</template>
