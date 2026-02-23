<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
</script>

<template>
    <Head title="Product-wise P&L" />
    <AppLayout
        :breadcrumbs="[
            { title: 'Product-wise P&L', href: '/inventory/product-wise-pl' },
        ]"
    >
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <h1 class="text-2xl font-semibold tracking-tight">
                Product-wise P&L coming soon
            </h1>
        </div>
    </AppLayout>
</template>
