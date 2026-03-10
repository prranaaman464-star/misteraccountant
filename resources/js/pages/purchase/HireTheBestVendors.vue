<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
</script>

<template>
    <Head title="Hire The Best Vendors" />
    <AppLayout
        :breadcrumbs="[
            {
                title: 'Hire The Best Vendors',
                href: '/purchases/hire-the-best-vendors',
            },
        ]"
    >
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <h1 class="text-2xl font-semibold tracking-tight">
                Hire The Best Vendors coming soon
            </h1>
        </div>
    </AppLayout>
</template>
