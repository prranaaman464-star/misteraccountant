<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { RefreshCw, ArrowLeft } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
</script>

<template>
    <Head title="Recurring Invoices" />
    <AppLayout
        :breadcrumbs="[
            { title: 'Invoices', href: '/sales/invoices' },
            { title: 'Recurring Invoices', href: '/sales/invoices/recurring' },
        ]"
    >
        <div class="flex h-full flex-1 flex-col items-center justify-center gap-6 p-6">
            <div
                class="flex flex-col items-center gap-4 rounded-2xl border border-dashed border-sidebar-border/70 bg-muted/20 px-12 py-16 text-center"
            >
                <div
                    class="flex size-16 items-center justify-center rounded-full bg-primary/10"
                >
                    <RefreshCw class="size-8 text-primary" />
                </div>
                <div class="space-y-2">
                    <h1 class="text-xl font-semibold tracking-tight">
                        Recurring Invoices
                    </h1>
                    <p class="max-w-sm text-sm text-muted-foreground">
                        Set up recurring invoices for subscriptions and repeat
                        billing. Automate your invoicing workflow.
                    </p>
                </div>
                <Link href="/sales/invoices">
                    <Button variant="outline" class="mt-2">
                        <ArrowLeft class="size-4" />
                        Back to Invoices
                    </Button>
                </Link>
            </div>
        </div>
    </AppLayout>
</template>
