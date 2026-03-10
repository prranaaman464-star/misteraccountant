<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
</script>

<template>
    <Head title="Vendors & Suppliers" />
    <AppLayout
        :breadcrumbs="[
            {
                title: 'Vendors & Suppliers',
                href: '/purchases/vendors-and-suppliers',
            },
        ]"
    >
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <h1 class="text-2xl font-semibold tracking-tight">
                Vendors & Suppliers coming soon
            </h1>
        </div>
    </AppLayout>
</template>
