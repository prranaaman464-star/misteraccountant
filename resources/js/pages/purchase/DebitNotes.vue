<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
</script>

<template>
    <Head title="Debit Notes" />
    <AppLayout
        :breadcrumbs="[
            { title: 'Debit Notes', href: '/purchases/debit-notes' },
        ]"
    >
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <h1 class="text-2xl font-semibold tracking-tight">
                Debit Notes coming soon
            </h1>
        </div>
    </AppLayout>
</template>
