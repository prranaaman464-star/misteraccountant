<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
</script>

<template>
    <Head title="Delivery Challans" />
    <AppLayout
        :breadcrumbs="[
            { title: 'Delivery Challans', href: '/sales/delivery-challans' },
        ]"
    >
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <h1 class="text-2xl font-semibold tracking-tight">
                Delivery Challans coming soon
            </h1>
        </div>
    </AppLayout>
</template>
