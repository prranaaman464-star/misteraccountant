<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
</script>

<template>
    <Head title="Purchase Orders" />
    <AppLayout
        :breadcrumbs="[
            { title: 'Purchase Orders', href: '/purchases/purchase-orders' },
        ]"
    >
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <h1 class="text-2xl font-semibold tracking-tight">
                Purchase Orders coming soon
            </h1>
        </div>
    </AppLayout>
</template>
