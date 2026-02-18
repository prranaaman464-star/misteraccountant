<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { dashboard } from '@/routes';

defineProps<{
    item: string;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Home', href: dashboard().url },
    { title: 'Items', href: '/inventory/items' },
    { title: 'Edit', href: '#' },
];
</script>

<template>
    <Head title="Edit Item" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <h1 class="text-2xl font-semibold tracking-tight">Edit Item</h1>
            <p class="text-muted-foreground">Edit form for: {{ item }}</p>
        </div>
    </AppLayout>
</template>
