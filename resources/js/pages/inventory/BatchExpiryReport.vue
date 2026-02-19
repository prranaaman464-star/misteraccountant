<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
</script>

<template>
    <Head title="Batch Expiry Report" />
    <AppLayout :breadcrumbs="[{ title: 'Batch Expiry Report', href: '/inventory/batch-expiry-report' }]">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <h1 class="text-2xl font-semibold tracking-tight">Batch Expiry Report coming soon</h1>
        </div>
    </AppLayout>
</template>