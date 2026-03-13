<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, FileText } from 'lucide-vue-next';
import { computed } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader } from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

type LineItem = {
    id: string;
    product_name: string;
    quantity: number;
    unit: string;
    rate: number;
    discount_percent: number;
    tax_percent: number;
    amount: number;
};

type InvoiceClient = {
    id: string;
    name: string;
    email?: string;
    phone?: string;
    billing_address?: string;
};

type Invoice = {
    id: string;
    invoice_number: string;
    reference_number?: string;
    invoice_date: string;
    due_date?: string;
    status: string;
    currency: string;
    enable_tax: boolean;
    round_off_total: boolean;
    discount_percent: number;
    subtotal_amount: number;
    cgst_amount: number;
    sgst_amount: number;
    discount_amount: number;
    total_amount: number;
    additional_notes?: string;
    terms_and_conditions?: string;
    signature_name?: string;
    is_recurring: boolean;
    recurring_frequency?: string;
    recurring_interval?: string;
    client?: InvoiceClient;
    billed_by?: string;
    line_items: LineItem[];
};

const props = defineProps<{
    invoice: Invoice;
    currencies?: Record<string, string>;
    statuses?: Record<string, string>;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Invoices', href: '/sales/invoices' },
    {
        title: props.invoice.invoice_number,
        href: `/sales/invoices/${props.invoice.id}`,
    },
];

const currencySymbol = computed(() => {
    const map: Record<string, string> = {
        USD: '$',
        INR: '₹',
        EUR: '€',
        GBP: '£',
    };
    return map[props.invoice.currency] ?? props.invoice.currency;
});

function formatDate(dateStr: string): string {
    if (!dateStr) return '—';
    const d = new Date(dateStr);
    return d.toLocaleDateString('en-GB', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
    });
}

function numberToWords(num: number): string {
    const intPart = Math.floor(num);
    const decPart = Math.round((num - intPart) * 100);
    const ones = [
        '',
        'One',
        'Two',
        'Three',
        'Four',
        'Five',
        'Six',
        'Seven',
        'Eight',
        'Nine',
    ];
    const tens = [
        '',
        '',
        'Twenty',
        'Thirty',
        'Forty',
        'Fifty',
        'Sixty',
        'Seventy',
        'Eighty',
        'Ninety',
    ];
    const teens = [
        'Ten',
        'Eleven',
        'Twelve',
        'Thirteen',
        'Fourteen',
        'Fifteen',
        'Sixteen',
        'Seventeen',
        'Eighteen',
        'Nineteen',
    ];

    function toWords(n: number): string {
        if (n === 0) return '';
        if (n < 10) return ones[n];
        if (n < 20) return teens[n - 10];
        if (n < 100)
            return (
                tens[Math.floor(n / 10)] + (n % 10 ? ' ' + ones[n % 10] : '')
            );
        if (n < 1000)
            return (
                ones[Math.floor(n / 100)] +
                ' Hundred' +
                (n % 100 ? ' & ' + toWords(n % 100) : '')
            );
        if (n < 100000)
            return (
                toWords(Math.floor(n / 1000)) +
                ' Thousand' +
                (n % 1000 ? ' ' + toWords(n % 1000) : '')
            );
        if (n < 10000000)
            return (
                toWords(Math.floor(n / 100000)) +
                ' Lakh' +
                (n % 100000 ? ' ' + toWords(n % 100000) : '')
            );
        return (
            toWords(Math.floor(n / 10000000)) +
            ' Crore' +
            (n % 10000000 ? ' ' + toWords(n % 10000000) : '')
        );
    }

    const word = intPart === 0 ? 'Zero' : toWords(intPart);
    const currencyName =
        props.invoice.currency === 'USD'
            ? 'Dollars'
            : props.invoice.currency === 'INR'
              ? 'Rupees'
              : 'Units';
    return word + ' ' + currencyName + (decPart ? ` and ${decPart}/100` : '');
}

function statusBadgeVariant(status: string): string {
    const map: Record<string, string> = {
        paid: 'default',
        draft: 'secondary',
        sent: 'outline',
        overdue: 'destructive',
        unpaid: 'outline',
        partially_paid: 'outline',
    };
    return map[status] ?? 'outline';
}
</script>

<template>
    <Head :title="`Invoice ${invoice.invoice_number}`" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-8 overflow-x-auto bg-muted/30 p-6"
        >
            <!-- Header -->
            <div
                class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
            >
                <div>
                    <h1
                        class="text-2xl font-bold tracking-tight text-foreground sm:text-3xl"
                    >
                        Invoice {{ invoice.invoice_number }}
                    </h1>
                    <p class="mt-1 text-sm text-muted-foreground">
                        Created on {{ formatDate(invoice.invoice_date) }}
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <Link
                        href="/sales/invoices"
                        class="inline-flex items-center gap-2 rounded-lg border border-input bg-background px-4 py-2 text-sm font-medium text-muted-foreground shadow-sm transition-colors hover:bg-muted/50 hover:text-foreground"
                    >
                        <ArrowLeft class="size-4" />
                        Back to Invoices
                    </Link>
                    <Badge
                        :variant="statusBadgeVariant(invoice.status)"
                        class="capitalize"
                    >
                        {{ invoice.status.replace('_', ' ') }}
                    </Badge>
                </div>
            </div>

            <!-- Invoice Details + Bill To -->
            <div class="grid gap-6 md:grid-cols-2">
                <Card
                    class="overflow-hidden rounded-xl border border-sidebar-border/70 bg-card shadow-sm"
                >
                    <CardHeader
                        class="border-b border-sidebar-border/50 bg-muted/20 px-6 py-4"
                    >
                        <h2 class="text-base font-semibold text-foreground">
                            Invoice Details
                        </h2>
                    </CardHeader>
                    <CardContent class="space-y-4 p-6">
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <p
                                    class="text-xs font-medium text-muted-foreground uppercase"
                                >
                                    Invoice Number
                                </p>
                                <p class="mt-0.5 font-medium">
                                    {{ invoice.invoice_number }}
                                </p>
                            </div>
                            <div>
                                <p
                                    class="text-xs font-medium text-muted-foreground uppercase"
                                >
                                    Reference
                                </p>
                                <p class="mt-0.5 font-medium">
                                    {{ invoice.reference_number || '—' }}
                                </p>
                            </div>
                        </div>
                        <div>
                            <p
                                class="text-xs font-medium text-muted-foreground uppercase"
                            >
                                Invoice Date
                            </p>
                            <p class="mt-0.5 font-medium">
                                {{ formatDate(invoice.invoice_date) }}
                            </p>
                        </div>
                        <div>
                            <p
                                class="text-xs font-medium text-muted-foreground uppercase"
                            >
                                Due Date
                            </p>
                            <p class="mt-0.5 font-medium">
                                {{ formatDate(invoice.due_date || '') }}
                            </p>
                        </div>
                        <div>
                            <p
                                class="text-xs font-medium text-muted-foreground uppercase"
                            >
                                Currency
                            </p>
                            <p class="mt-0.5 font-medium">
                                {{ invoice.currency }}
                            </p>
                        </div>
                        <div
                            v-if="invoice.is_recurring"
                            class="flex items-center gap-2 rounded-lg border border-sidebar-border/50 bg-muted/30 px-4 py-3"
                        >
                            <FileText class="size-4 text-muted-foreground" />
                            <span class="text-sm"
                                >Recurring:
                                {{ invoice.recurring_frequency }} every
                                {{ invoice.recurring_interval }}</span
                            >
                        </div>
                    </CardContent>
                </Card>

                <Card
                    class="overflow-hidden rounded-xl border border-sidebar-border/70 bg-card shadow-sm"
                >
                    <CardHeader
                        class="border-b border-sidebar-border/50 bg-muted/20 px-6 py-4"
                    >
                        <h2 class="text-base font-semibold text-foreground">
                            Bill To
                        </h2>
                    </CardHeader>
                    <CardContent class="p-6">
                        <div v-if="invoice.client" class="space-y-2">
                            <p class="font-medium">{{ invoice.client.name }}</p>
                            <p
                                v-if="invoice.client.email"
                                class="text-sm text-muted-foreground"
                            >
                                {{ invoice.client.email }}
                            </p>
                            <p
                                v-if="invoice.client.phone"
                                class="text-sm text-muted-foreground"
                            >
                                {{ invoice.client.phone }}
                            </p>
                            <p
                                v-if="invoice.client.billing_address"
                                class="mt-2 text-sm text-muted-foreground"
                            >
                                {{ invoice.client.billing_address }}
                            </p>
                        </div>
                        <p v-else class="text-muted-foreground">—</p>
                        <div
                            v-if="invoice.billed_by"
                            class="mt-4 border-t border-sidebar-border/50 pt-4"
                        >
                            <p
                                class="text-xs font-medium text-muted-foreground uppercase"
                            >
                                Billed By
                            </p>
                            <p class="mt-0.5 font-medium">
                                {{ invoice.billed_by }}
                            </p>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Line Items -->
            <Card
                class="overflow-hidden rounded-xl border border-sidebar-border/70 bg-card shadow-sm"
            >
                <CardHeader
                    class="border-b border-sidebar-border/50 bg-muted/20 px-6 py-4"
                >
                    <h2 class="text-base font-semibold text-foreground">
                        Items
                    </h2>
                </CardHeader>
                <CardContent class="p-0">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr
                                    class="border-b border-sidebar-border/70 bg-muted/60"
                                >
                                    <th
                                        class="px-4 py-3.5 text-left text-xs font-semibold tracking-wider text-muted-foreground uppercase"
                                    >
                                        Product/Service
                                    </th>
                                    <th
                                        class="w-24 px-4 py-3.5 text-left text-xs font-semibold tracking-wider text-muted-foreground uppercase"
                                    >
                                        Qty
                                    </th>
                                    <th
                                        class="w-24 px-4 py-3.5 text-left text-xs font-semibold tracking-wider text-muted-foreground uppercase"
                                    >
                                        Unit
                                    </th>
                                    <th
                                        class="w-28 px-4 py-3.5 text-left text-xs font-semibold tracking-wider text-muted-foreground uppercase"
                                    >
                                        Rate
                                    </th>
                                    <th
                                        class="w-24 px-4 py-3.5 text-left text-xs font-semibold tracking-wider text-muted-foreground uppercase"
                                    >
                                        Discount %
                                    </th>
                                    <th
                                        class="w-20 px-4 py-3.5 text-left text-xs font-semibold tracking-wider text-muted-foreground uppercase"
                                    >
                                        Tax %
                                    </th>
                                    <th
                                        class="w-28 px-4 py-3.5 text-right text-xs font-semibold tracking-wider text-muted-foreground uppercase"
                                    >
                                        Amount
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="item in invoice.line_items"
                                    :key="item.id"
                                    class="border-b border-sidebar-border/50"
                                >
                                    <td class="px-4 py-3 font-medium">
                                        {{ item.product_name }}
                                    </td>
                                    <td class="px-4 py-3">
                                        {{ item.quantity }}
                                    </td>
                                    <td class="px-4 py-3">{{ item.unit }}</td>
                                    <td class="px-4 py-3 tabular-nums">
                                        {{ currencySymbol
                                        }}{{ item.rate.toFixed(2) }}
                                    </td>
                                    <td class="px-4 py-3 tabular-nums">
                                        {{ item.discount_percent }}%
                                    </td>
                                    <td class="px-4 py-3 tabular-nums">
                                        {{ item.tax_percent }}%
                                    </td>
                                    <td
                                        class="px-4 py-3 text-right font-medium tabular-nums"
                                    >
                                        {{ currencySymbol
                                        }}{{ item.amount.toFixed(2) }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </CardContent>
            </Card>

            <!-- Summary + Notes -->
            <div class="grid gap-6 lg:grid-cols-2">
                <Card
                    class="overflow-hidden rounded-xl border border-sidebar-border/70 bg-card shadow-sm"
                >
                    <CardContent class="space-y-3 p-6">
                        <h2 class="text-base font-semibold text-foreground">
                            Summary
                        </h2>
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-muted-foreground"
                                    >Subtotal</span
                                >
                                <span class="font-medium tabular-nums"
                                    >{{ currencySymbol
                                    }}{{
                                        invoice.subtotal_amount.toFixed(2)
                                    }}</span
                                >
                            </div>
                            <div
                                v-if="invoice.enable_tax"
                                class="flex justify-between"
                            >
                                <span class="text-muted-foreground">CGST</span>
                                <span class="font-medium tabular-nums"
                                    >{{ currencySymbol
                                    }}{{ invoice.cgst_amount.toFixed(2) }}</span
                                >
                            </div>
                            <div
                                v-if="invoice.enable_tax"
                                class="flex justify-between"
                            >
                                <span class="text-muted-foreground">SGST</span>
                                <span class="font-medium tabular-nums"
                                    >{{ currencySymbol
                                    }}{{ invoice.sgst_amount.toFixed(2) }}</span
                                >
                            </div>
                            <div
                                v-if="invoice.discount_amount > 0"
                                class="flex justify-between"
                            >
                                <span class="text-muted-foreground"
                                    >Discount</span
                                >
                                <span class="font-medium tabular-nums"
                                    >-{{ currencySymbol
                                    }}{{
                                        invoice.discount_amount.toFixed(2)
                                    }}</span
                                >
                            </div>
                            <div
                                class="flex justify-between border-t border-sidebar-border/50 pt-3"
                            >
                                <span class="font-semibold">Total</span>
                                <span class="font-bold tabular-nums"
                                    >{{ currencySymbol
                                    }}{{
                                        invoice.total_amount.toFixed(2)
                                    }}</span
                                >
                            </div>
                            <div class="pt-2">
                                <p class="text-xs text-muted-foreground">
                                    Total in words
                                </p>
                                <p class="mt-0.5 text-sm">
                                    {{ numberToWords(invoice.total_amount) }}
                                </p>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card
                    class="overflow-hidden rounded-xl border border-sidebar-border/70 bg-card shadow-sm"
                >
                    <CardContent class="space-y-4 p-6">
                        <div v-if="invoice.additional_notes">
                            <h2 class="text-base font-semibold text-foreground">
                                Additional Notes
                            </h2>
                            <p
                                class="mt-2 text-sm whitespace-pre-wrap text-muted-foreground"
                            >
                                {{ invoice.additional_notes }}
                            </p>
                        </div>
                        <div v-if="invoice.terms_and_conditions">
                            <h2 class="text-base font-semibold text-foreground">
                                Terms & Conditions
                            </h2>
                            <p
                                class="mt-2 text-sm whitespace-pre-wrap text-muted-foreground"
                            >
                                {{ invoice.terms_and_conditions }}
                            </p>
                        </div>
                        <div v-if="invoice.signature_name">
                            <h2 class="text-base font-semibold text-foreground">
                                Signature
                            </h2>
                            <p class="mt-2 text-sm font-medium">
                                {{ invoice.signature_name }}
                            </p>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
