<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
</script>

<template>
    <Head title="Quotation & Estimates" />
    <AppLayout
        :breadcrumbs="[
            {
                title: 'Quotation & Estimates',
                href: '/sales/quotation-and-estimates',
            },
        ]"
    >
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <h1 class="text-2xl font-semibold tracking-tight">
                Quotation & Estimates coming soon
            </h1>
        </div>
    </AppLayout>
</template>
