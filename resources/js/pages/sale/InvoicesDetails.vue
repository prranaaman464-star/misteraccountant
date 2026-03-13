<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, Download, Eye, Mail, Printer } from 'lucide-vue-next';
import { computed } from 'vue';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';

type InvoiceClient = {
    id: string;
    name: string;
    email?: string;
    phone?: string;
    billing_address?: string;
};

type Organization = {
    name: string;
    address?: string;
    phone?: string;
    email?: string;
    gst_number?: string;
};

type Invoice = {
    id: string;
    invoice_number: string;
    invoice_date: string;
    due_date?: string;
    status: string;
    is_recurring: boolean;
    recurring_frequency?: string;
    recurring_interval?: string;
    client?: InvoiceClient;
    organization?: Organization;
};

const props = defineProps<{
    invoice: Invoice | null;
    organization?: Organization | null;
}>();

const displayInvoice = computed(() => {
    if (props.invoice) return props.invoice;
    return {
        id: 'sample',
        invoice_number: 'INV215654',
        invoice_date: '2025-01-25',
        due_date: '2025-01-31',
        status: 'unpaid',
        is_recurring: true,
        recurring_frequency: 'Monthly',
        recurring_interval: '1',
        client: {
            id: '1',
            name: 'Timesquare Tech',
            email: 'info@example.com',
            phone: '+154664 75945',
            billing_address: '299 Star Trek Drive, Florida, 3240, USA',
        },
        organization: {
            name: 'Dreams Technologies Pvt Ltd.',
            address: '15 Hodges Mews, High Wycombe HP12 3JL, United Kingdom',
            phone: '+154664 75945',
            email: 'info@example.com',
            gst_number: '243E45767889',
        },
    } as Invoice;
});

const billingFrom = computed(() => {
    const org = props.organization ?? displayInvoice.value.organization;
    return {
        name: org?.name ?? 'Mister Accountant  Invoice Management',
        address: org?.address ?? '15 Hodges Mews, HP12 3JL, United Kingdom',
        phone: org?.phone ?? '+154664 75945',
        email: org?.email ?? 'info@example.com',
        gst: org?.gst_number ?? '243E45767889',
    };
});

const billingTo = computed(() => ({
    name: displayInvoice.value.client?.name ?? 'Timesquare Tech',
    address: displayInvoice.value.client?.billing_address ?? '299 Star Trek Drive, Florida, 3240, USA',
    phone: displayInvoice.value.client?.phone ?? '+154664 75945',
    email: displayInvoice.value.client?.email ?? 'info@example.com',
    gst: '243E45767889',
}));

function formatDate(dateStr: string): string {
    if (!dateStr) return '—';
    const d = new Date(dateStr);
    return d.toLocaleDateString('en-GB', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
    });
}

function getDaysUntilDue(dueDateStr?: string): number | null {
    if (!dueDateStr) return null;
    const due = new Date(dueDateStr);
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    due.setHours(0, 0, 0, 0);
    const diff = Math.ceil((due.getTime() - today.getTime()) / (1000 * 60 * 60 * 24));
    return diff;
}

const dueInDays = computed(() => getDaysUntilDue(displayInvoice.value.due_date));

const statusLabel = computed(() => {
    const s = displayInvoice.value.status;
    if (s === 'paid') return 'PAID';
    if (s === 'overdue') return 'OVERDUE';
    return 'NOT PAID';
});

const statusColor = computed(() => {
    const s = displayInvoice.value.status;
    if (s === 'paid') return 'border-green-600 text-green-600';
    if (s === 'overdue') return 'border-red-600 text-red-600';
    return 'border-red-600 text-red-600';
});

const breadcrumbs = [
    { title: 'Invoices', href: '/sales/invoices' },
    { title: 'Invoice Details', href: '/sales/invoices-details' },
];
</script>

<template>
    <Head title="Invoice Details" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <!-- Header -->
            <div
                class="flex flex-col gap-4 rounded-xl border border-border/60 bg-card p-4 shadow-sm sm:flex-row sm:items-center sm:justify-between"
            >
                <div class="flex items-center gap-3">
                    <Link
                        href="/sales/invoices"
                        class="inline-flex size-9 items-center justify-center rounded-lg border border-input bg-background text-muted-foreground transition-colors hover:bg-muted/50 hover:text-foreground"
                    >
                        <ArrowLeft class="size-4" />
                    </Link>
                    <h1 class="text-xl font-semibold tracking-tight text-foreground">
                        Invoice (Admin)
                    </h1>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <Button variant="outline" size="sm" class="gap-2">
                        <Download class="size-4" />
                        Download PDF
                    </Button>
                    <Button variant="outline" size="sm" class="gap-2">
                        <Mail class="size-4" />
                        Send Email
                    </Button>
                    <Button variant="outline" size="sm" class="gap-2">
                        <Printer class="size-4" />
                        Print
                    </Button>
                    <Button size="sm" class="gap-2 bg-primary text-primary-foreground hover:bg-primary/90">
                        <Eye class="size-4" />
                        View Details
                    </Button>
                </div>
            </div>

            <!-- Invoice Card -->
            <div
                class="relative overflow-hidden rounded-xl border border-border/70 bg-card shadow-sm"
            >
                <!-- Subtle decorative shape in top-right -->
                <div
                    class="pointer-events-none absolute right-0 top-0 h-32 w-48 -translate-y-1/2 translate-x-1/4 rounded-full bg-primary/5 blur-2xl"
                />

                <div class="relative p-6 sm:p-8">
                    <!-- Top section: Invoice title + Company + Status + Logo -->
                    <div class="flex flex-col gap-6 border-b border-border/50 pb-6 sm:flex-row sm:items-start sm:justify-between">
                        <div>
                            <h2 class="text-2xl font-bold tracking-tight text-foreground">
                                Invoice
                            </h2>
                            <p class="mt-1 font-medium text-foreground">
                                {{ billingFrom.name }}
                            </p>
                            <p class="mt-0.5 text-sm text-muted-foreground">
                                {{ billingFrom.address }}
                            </p>
                        </div>
                        <div class="flex flex-col items-end gap-3 sm:flex-row sm:items-start">
                            Mister Accountant 
                        </div>
                    </div>

                    <!-- Three columns: Invoice Details | Billing From | Billing To -->
                    <div class="mt-6 grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
                        <!-- Invoice Details -->
                        <div class="space-y-3">
                            <h3 class="text-sm font-semibold uppercase tracking-wider text-muted-foreground">
                                Invoice Details
                            </h3>
                            <div class="space-y-2 text-sm">
                                <div>
                                    <span class="text-muted-foreground">Invoice Number: </span>
                                    <span class="font-medium">{{ displayInvoice.invoice_number }}</span>
                                </div>
                                <div>
                                    <span class="text-muted-foreground">Issued On: </span>
                                    <span class="font-medium">{{ formatDate(displayInvoice.invoice_date) }}</span>
                                </div>
                                <div>
                                    <span class="text-muted-foreground">Due Date: </span>
                                    <span class="font-medium">{{ formatDate(displayInvoice.due_date ?? '') }}</span>
                                </div>
                                <div v-if="displayInvoice.is_recurring">
                                    <span class="text-muted-foreground">Recurring Invoice: </span>
                                    <span class="font-medium">{{ displayInvoice.recurring_frequency }}</span>
                                </div>
                                <div v-if="dueInDays !== null && dueInDays > 0 && displayInvoice.status !== 'paid'" class="pt-2">
                                    <span
                                        class="inline-flex items-center rounded-md bg-red-100 px-2.5 py-0.5 text-xs font-medium text-red-800 dark:bg-red-900/30 dark:text-red-400"
                                    >
                                        Due in {{ dueInDays }} days
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Billing From -->
                        <div class="space-y-3">
                            <h3 class="text-sm font-semibold uppercase tracking-wider text-muted-foreground">
                                Billing From
                            </h3>
                            <div class="space-y-2 text-sm">
                                <p class="font-medium text-foreground">{{ billingFrom.name }}</p>
                                <p class="text-muted-foreground">{{ billingFrom.address }}</p>
                                <p class="text-muted-foreground">Phone: {{ billingFrom.phone }}</p>
                                <p class="text-muted-foreground">Email: {{ billingFrom.email }}</p>
                                <p class="text-muted-foreground">GST: {{ billingFrom.gst }}</p>
                            </div>
                        </div>

                        <!-- Billing To -->
                        <div class="space-y-3">
                            <h3 class="text-sm font-semibold uppercase tracking-wider text-muted-foreground">
                                Billing To
                            </h3>
                            <div class="flex items-start gap-4">
                                <div
                                    class="flex size-12 shrink-0 items-center justify-center rounded-lg bg-muted text-lg font-bold text-muted-foreground"
                                >
                                    {{ billingTo.name.charAt(0).toUpperCase() }}
                                </div>
                                <div class="min-w-0 flex-1 space-y-2 text-sm">
                                    <p class="font-medium text-foreground">{{ billingTo.name }}</p>
                                    <p class="text-muted-foreground">{{ billingTo.address }}</p>
                                    <p class="text-muted-foreground">Phone: {{ billingTo.phone }}</p>
                                    <p class="text-muted-foreground">Email: {{ billingTo.email }}</p>
                                    <p class="text-muted-foreground">GST: {{ billingTo.gst }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empty state when no invoices exist -->
            <div
                v-if="!invoice"
                class="rounded-xl border border-dashed border-border/70 bg-muted/20 p-6 text-center"
            >
                <p class="text-sm text-muted-foreground">
                    No invoices yet. The layout above shows sample data. Create an invoice to see real data here.
                </p>
                <Link
                    href="/sales/invoices/create"
                    class="mt-3 inline-flex items-center gap-2 text-sm font-medium text-primary hover:underline"
                >
                    Create your first invoice
                </Link>
            </div>
        </div>
    </AppLayout>
</template>
