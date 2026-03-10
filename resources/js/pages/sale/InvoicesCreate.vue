<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import {
    ArrowLeft,
    Banknote,
    Calendar,
    ChevronDown,
    ChevronUp,
    FileText,
    Plus,
    Upload,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';
import AppLogo from '@/components/AppLogo.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader } from '@/components/ui/card';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

type Client = { id: string; name: string };
type Product = { id: string; name: string; item_type: string; rate: number; unit: string; tax_rate: number };
type TeamMember = { id: string; name: string };
type Signature = { id: string; name: string };

type LineItem = {
    id: string;
    productName: string;
    quantity: number;
    unit: string;
    rate: number;
    discount: number;
    taxPercent: number;
    amount: number;
};

const props = withDefaults(
    defineProps<{
        clients?: Client[];
        products?: Product[];
        teamMembers?: TeamMember[];
        currencies?: Record<string, string>;
        statuses?: Record<string, string>;
        signatures?: Signature[];
        invoiceNumber?: string;
        referenceNumber?: string;
    }>(),
    {
        clients: () => [],
        products: () => [],
        teamMembers: () => [],
        currencies: () => ({}),
        statuses: () => ({}),
        signatures: () => [],
        invoiceNumber: () => 'INV-0000001',
        referenceNumber: () => '',
    },
);

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Invoices', href: '/sales/invoices' },
    { title: 'Create Invoice', href: '/sales/invoices/create' },
];

const invoiceNumber = ref(props.invoiceNumber);
const referenceNumber = ref(props.referenceNumber);
const invoiceDate = ref(new Date().toISOString().slice(0, 10));
const dueDate = ref<string | null>(null);
const isRecurring = ref(true);
const recurringFrequency = ref('monthly');
const recurringInterval = ref('1');
const status = ref('');
const currency = ref('USD');
const enableTax = ref(true);
const billedBy = ref('');
const customerId = ref('');
const itemType = ref<'product' | 'service'>('product');
const selectedProductId = ref('');
const roundOffTotal = ref(true);
const additionalNotes = ref('');
const notesActive = ref(false);
const termsActive = ref(false);
const bankDetailsActive = ref(true);
const accountId = ref('');
const selectedSignatureId = ref('');
const signatureName = ref('');
const discountPercent = ref(0);
const productDropdownOpen = ref(false);

function uniqueId(): string {
    return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/x/g, () =>
        Math.floor(Math.random() * 16).toString(16),
    );
}

const lineItems = ref<LineItem[]>([
    {
        id: uniqueId(),
        productName: '',
        quantity: 1,
        unit: 'Pcs',
        rate: 0,
        discount: 0,
        taxPercent: 18,
        amount: 0,
    },
]);

function addLineItem(): void {
    lineItems.value.push({
        id: uniqueId(),
        productName: '',
        quantity: 1,
        unit: 'Pcs',
        rate: 0,
        discount: 0,
        taxPercent: 18,
        amount: 0,
    });
}

function removeLineItem(id: string): void {
    if (lineItems.value.length > 1) {
        lineItems.value = lineItems.value.filter((i) => i.id !== id);
    }
}

function selectProduct(idx: number): void {
    const pid = selectedProductId.value;
    const product = props.products.find((p) => p.id === pid);
    if (product && lineItems.value[idx]) {
        const item = lineItems.value[idx];
        item.productName = product.name;
        item.rate = product.rate;
        item.unit = product.unit;
        item.taxPercent = product.tax_rate || 18;
        updateItemAmount(idx);
    }
}

function updateItemAmount(idx: number): void {
    const item = lineItems.value[idx];
    if (!item) return;
    let subtotal = item.quantity * item.rate;
    subtotal -= (subtotal * item.discount) / 100;
    const taxAmount = (subtotal * item.taxPercent) / 100;
    item.amount = subtotal + taxAmount;
}

function numberToWords(num: number): string {
    const intPart = Math.floor(num);
    const decPart = Math.round((num - intPart) * 100);
    const ones = ['', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine'];
    const tens = ['', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'];
    const teens = ['Ten', 'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen', 'Eighteen', 'Nineteen'];

    function toWords(n: number): string {
        if (n === 0) return '';
        if (n < 10) return ones[n];
        if (n < 20) return teens[n - 10];
        if (n < 100) return tens[Math.floor(n / 10)] + (n % 10 ? ' ' + ones[n % 10] : '');
        if (n < 1000) return ones[Math.floor(n / 100)] + ' Hundred' + (n % 100 ? ' & ' + toWords(n % 100) : '');
        if (n < 100000) return toWords(Math.floor(n / 1000)) + ' Thousand' + (n % 1000 ? ' ' + toWords(n % 1000) : '');
        if (n < 10000000) return toWords(Math.floor(n / 100000)) + ' Lakh' + (n % 100000 ? ' ' + toWords(n % 100000) : '');
        return toWords(Math.floor(n / 10000000)) + ' Crore' + (n % 10000000 ? ' ' + toWords(n % 10000000) : '');
    }

    const word = intPart === 0 ? 'Zero' : toWords(intPart);
    const currencyName = currency.value === 'USD' ? 'Dollars' : currency.value === 'INR' ? 'Rupees' : 'Units';
    return word + ' ' + currencyName + (decPart ? ` and ${decPart}/100` : '');
}

const subtotalAmount = computed(() => {
    return lineItems.value.reduce((sum, i) => {
        const st = i.quantity * i.rate * (1 - i.discount / 100);
        return sum + st;
    }, 0);
});

const cgstAmount = computed(() => {
    if (!enableTax.value) return 0;
    return lineItems.value.reduce((sum, i) => {
        const st = i.quantity * i.rate * (1 - i.discount / 100);
        const halfTax = (st * (i.taxPercent / 2)) / 100;
        return sum + halfTax;
    }, 0);
});

const sgstAmount = computed(() => cgstAmount.value);

const totalBeforeDiscount = computed(() => subtotalAmount.value + cgstAmount.value + sgstAmount.value);

const discountAmount = computed(() => (totalBeforeDiscount.value * discountPercent.value) / 100);

const totalBeforeRound = computed(() => totalBeforeDiscount.value - discountAmount.value);

const total = computed(() => {
    const t = totalBeforeRound.value;
    return roundOffTotal.value ? Math.round(t) : Math.round(t * 100) / 100;
});

const currencySymbol = computed(() => {
    const map: Record<string, string> = { USD: '$', INR: '₹', EUR: '€', GBP: '£' };
    return map[currency.value] ?? currency.value;
});

const filteredProducts = computed(() =>
    props.products.filter((x) =>
        itemType.value === 'product'
            ? ['goods', 'finished_goods'].includes(x.item_type)
            : x.item_type === 'service',
    ),
);

const selectedProductDisplay = computed(() => {
    if (!selectedProductId.value) return 'Select';
    const p = props.products.find((x) => x.id === selectedProductId.value);
    return p?.name ?? 'Select';
});
</script>

<template>
    <Head title="Create Invoice" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-8 overflow-x-auto bg-muted/30 p-6">
            <!-- Page Header -->
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight text-foreground sm:text-3xl">
                        Create Invoice
                    </h1>
                    <p class="mt-1 text-sm text-muted-foreground">
                        Fill in the details below to create a new invoice for your customer
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <Link
                        href="/sales/invoices"
                        class="inline-flex items-center gap-2 rounded-lg border border-input bg-background px-4 py-2 text-sm font-medium text-muted-foreground shadow-sm transition-colors hover:bg-muted/50 hover:text-foreground"
                    >
                        <ArrowLeft class="size-4" />
                        Back
                    </Link>
                    <Button variant="outline" size="default" class="rounded-lg shadow-sm">
                        Save as Draft
                    </Button>
                    <Button size="default" class="rounded-lg shadow-md">
                        Save & Send
                    </Button>
                </div>
            </div>

            <!-- Top row: Only 2 cards (Invoice Details + Settings) - image layout -->
            <div class="grid gap-6 md:grid-cols-2">
                        <Card class="overflow-hidden rounded-xl border border-sidebar-border/70 bg-card shadow-sm">
                            <CardHeader class="border-b border-sidebar-border/50 bg-muted/20 px-6 py-4">
                                <h2 class="text-base font-semibold text-foreground">Invoice Details</h2>
                            </CardHeader>
                            <CardContent class="space-y-4 p-6">
                                <div class="grid gap-4 sm:grid-cols-2">
                                <div>
                                    <Label for="invoice_number" class="text-sm font-medium">Invoice Number</Label>
                                    <Input
                                        id="invoice_number"
                                        v-model="invoiceNumber"
                                        class="mt-2 rounded-lg"
                                        placeholder="e.g. INV-001"
                                    />
                                </div>
                                <div>
                                    <Label for="reference_number" class="text-sm font-medium">Reference Number</Label>
                                    <Input
                                        id="reference_number"
                                        v-model="referenceNumber"
                                        class="mt-2 rounded-lg"
                                        placeholder="Optional reference"
                                    />
                                </div>
                                </div>
                                <div>
                                    <Label for="invoice_date" class="text-sm font-medium">Invoice Date</Label>
                                    <div class="relative mt-2">
                                        <Input
                                            id="invoice_date"
                                            v-model="invoiceDate"
                                            type="date"
                                            class="rounded-lg pr-10"
                                        />
                                        <Calendar
                                            class="pointer-events-none absolute right-3 top-1/2 size-4 -translate-y-1/2 text-muted-foreground"
                                            aria-hidden
                                        />
                                    </div>
                                </div>
                                <button
                                    v-if="!dueDate"
                                    type="button"
                                    class="flex items-center gap-2 rounded-lg border border-dashed border-primary/50 px-3 py-2 text-sm font-medium text-primary transition-colors hover:bg-primary/5"
                                    @click="dueDate = invoiceDate"
                                >
                                    <Plus class="size-4" />
                                    Add Due Date
                                </button>
                                <div v-else>
                                    <Label for="due_date" class="text-sm font-medium">Due Date</Label>
                                    <Input
                                        id="due_date"
                                        v-model="dueDate"
                                        type="date"
                                        class="mt-2 rounded-lg"
                                    />
                                </div>
                                <div class="flex items-center gap-3 rounded-lg border border-sidebar-border/50 bg-muted/30 px-4 py-3">
                                    <Switch v-model:checked="isRecurring" />
                                    <span class="text-sm font-medium">Recurring invoice</span>
                                </div>
                                <div v-if="isRecurring" class="flex gap-3">
                                    <select
                                        v-model="recurringFrequency"
                                        class="h-10 flex-1 rounded-lg border border-input bg-background px-3 py-2 text-sm shadow-sm transition-colors focus:outline-none focus:ring-2 focus:ring-ring"
                                    >
                                        <option value="weekly">Weekly</option>
                                        <option value="monthly">Monthly</option>
                                        <option value="quarterly">Quarterly</option>
                                        <option value="yearly">Yearly</option>
                                    </select>
                                    <select
                                        v-model="recurringInterval"
                                        class="h-10 flex-1 rounded-lg border border-input bg-background px-3 py-2 text-sm shadow-sm transition-colors focus:outline-none focus:ring-2 focus:ring-ring"
                                    >
                                        <option value="1">1 Month</option>
                                        <option value="2">2 Months</option>
                                        <option value="3">3 Months</option>
                                    </select>
                                </div>
                            </CardContent>
                        </Card>

                        <Card class="overflow-hidden rounded-xl border border-sidebar-border/70 bg-card shadow-sm">
                            <CardHeader class="border-b border-sidebar-border/50 bg-muted/20 px-6 py-4">
                                <h2 class="text-base font-semibold text-foreground">Settings</h2>
                            </CardHeader>
                            <CardContent class="flex flex-col gap-5 p-6">
                                <div
                                    class="flex items-center justify-between rounded-xl border border-sidebar-border/60 bg-gradient-to-br from-muted/40 to-muted/20 px-5 py-4"
                                >
                                    <AppLogo />
                                </div>
                                <div>
                                    <Label for="status" class="text-sm font-medium">Status</Label>
                                    <select
                                        id="status"
                                        v-model="status"
                                        class="mt-2 h-10 w-full rounded-lg border border-input bg-background px-3 py-2 text-sm shadow-sm transition-colors focus:outline-none focus:ring-2 focus:ring-ring"
                                    >
                                        <option value="">Select Status</option>
                                        <option
                                            v-for="(label, val) in statuses"
                                            :key="val"
                                            :value="val"
                                        >
                                            {{ label }}
                                        </option>
                                    </select>
                                </div>
                                <div>
                                    <Label for="currency" class="text-sm font-medium">Currency</Label>
                                    <select
                                        id="currency"
                                        v-model="currency"
                                        class="mt-2 h-10 w-full rounded-lg border border-input bg-background px-3 py-2 text-sm shadow-sm transition-colors focus:outline-none focus:ring-2 focus:ring-ring"
                                    >
                                        <option
                                            v-for="(label, code) in currencies"
                                            :key="code"
                                            :value="code"
                                        >
                                            {{ label }}
                                        </option>
                                    </select>
                                </div>
                                <div class="flex items-center justify-between rounded-lg border border-sidebar-border/50 bg-muted/30 px-4 py-3">
                                    <Label for="enable_tax" class="text-sm font-medium">Enable Tax</Label>
                                    <div class="flex items-center gap-2">
                                        <Switch id="enable_tax" v-model:checked="enableTax" />
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
            </div>

            <!-- Second row: Bill From + Bill To -->
            <div class="grid gap-6 md:grid-cols-2">
                        <Card class="overflow-hidden rounded-xl border border-sidebar-border/70 bg-card shadow-sm">
                            <CardHeader class="border-b border-sidebar-border/50 bg-muted/20 px-6 py-4">
                                <h2 class="text-base font-semibold text-foreground">Bill From</h2>
                                <p class="mt-0.5 text-xs text-muted-foreground">Your organization details</p>
                            </CardHeader>
                            <CardContent class="p-6">
                                <div>
                                    <Label class="text-sm font-medium">Billed By</Label>
                                    <select
                                        v-model="billedBy"
                                        class="mt-2 h-10 w-full rounded-lg border border-input bg-background px-3 py-2 text-sm shadow-sm transition-colors focus:outline-none focus:ring-2 focus:ring-ring"
                                    >
                                        <option value="">Select team member</option>
                                        <option
                                            v-for="m in teamMembers"
                                            :key="m.id"
                                            :value="m.id"
                                        >
                                            {{ m.name }}
                                        </option>
                                    </select>
                                </div>
                            </CardContent>
                        </Card>
                        <Card class="overflow-hidden rounded-xl border border-sidebar-border/70 bg-card shadow-sm">
                            <CardHeader class="flex flex-row items-center justify-between border-b border-sidebar-border/50 bg-muted/20 px-6 py-4">
                                <div>
                                    <h2 class="text-base font-semibold text-foreground">Bill To</h2>
                                    <p class="mt-0.5 text-xs text-muted-foreground">Customer billing details</p>
                                </div>
                                <Link
                                    href="/sales/clients-and-prospects/create"
                                    class="inline-flex items-center gap-1.5 rounded-lg bg-primary px-3 py-2 text-xs font-medium text-primary-foreground shadow-sm transition-colors hover:bg-primary/90"
                                >
                                    <Plus class="size-4" />
                                    Add New
                                </Link>
                            </CardHeader>
                            <CardContent class="p-6">
                                <div>
                                    <Label class="text-sm font-medium">Customer</Label>
                                    <select
                                        v-model="customerId"
                                        class="mt-2 h-10 w-full rounded-lg border border-input bg-background px-3 py-2 text-sm shadow-sm transition-colors focus:outline-none focus:ring-2 focus:ring-ring"
                                    >
                                        <option value="">Select customer</option>
                                        <option
                                            v-for="c in clients"
                                            :key="c.id"
                                            :value="c.id"
                                        >
                                            {{ c.name }}
                                        </option>
                                    </select>
                                </div>
                            </CardContent>
                        </Card>
            </div>

            <!-- Items & Details - full width -->
            <Card class="overflow-hidden rounded-xl border border-sidebar-border/70 bg-card shadow-sm">
                <CardHeader class="border-b border-sidebar-border/50 bg-muted/20 px-6 py-4">
                    <h2 class="text-base font-semibold text-foreground">Items & Details</h2>
                    <p class="mt-0.5 text-xs text-muted-foreground">Add products or services to your invoice</p>
                </CardHeader>
                <CardContent class="space-y-5 p-6">
                            <div class="flex flex-wrap items-center gap-6">
                                <div class="flex items-center gap-2">
                                    <div
                                        class="flex cursor-pointer items-center gap-2 rounded-lg border border-input px-4 py-2 transition-colors"
                                        :class="itemType === 'product' ? 'border-primary bg-primary/5' : 'hover:bg-muted/50'"
                                        @click="itemType = 'product'"
                                    >
                                        <input
                                            id="item_product"
                                            v-model="itemType"
                                            type="radio"
                                            value="product"
                                            class="size-4 accent-primary"
                                        />
                                        <Label for="item_product" class="cursor-pointer text-sm font-medium">Product</Label>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <div
                                        class="flex cursor-pointer items-center gap-2 rounded-lg border border-input px-4 py-2 transition-colors"
                                        :class="itemType === 'service' ? 'border-primary bg-primary/5' : 'hover:bg-muted/50'"
                                        @click="itemType = 'service'"
                                    >
                                        <input
                                            id="item_service"
                                            v-model="itemType"
                                            type="radio"
                                            value="service"
                                            class="size-4 accent-primary"
                                        />
                                        <Label for="item_service" class="cursor-pointer text-sm font-medium">Service</Label>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <Label class="text-sm font-medium">Products/Services</Label>
                                <DropdownMenu v-model:open="productDropdownOpen">
                                    <DropdownMenuTrigger as-child>
                                        <button
                                            type="button"
                                            class="mt-2 flex h-10 w-full max-w-sm items-center justify-between rounded-lg border border-input bg-background px-3 py-2 text-left text-sm shadow-sm transition-colors hover:bg-muted/50 focus:outline-none focus:ring-2 focus:ring-ring"
                                        >
                                            <span :class="selectedProductId ? 'text-foreground' : 'text-muted-foreground'">
                                                {{ selectedProductDisplay }}
                                            </span>
                                            <ChevronUp
                                                v-if="productDropdownOpen"
                                                class="size-4 shrink-0 text-muted-foreground"
                                            />
                                            <ChevronDown
                                                v-else
                                                class="size-4 shrink-0 text-muted-foreground"
                                            />
                                        </button>
                                    </DropdownMenuTrigger>
                                    <DropdownMenuContent align="start" class="max-h-60 min-w-[16rem]">
                                        <DropdownMenuItem
                                            class="text-muted-foreground"
                                            @select="selectedProductId = ''"
                                        >
                                            Select
                                        </DropdownMenuItem>
                                        <DropdownMenuItem
                                            v-for="p in filteredProducts"
                                            :key="p.id"
                                            :class="{ 'bg-accent': selectedProductId === p.id }"
                                            @select="
                                                () => {
                                                    selectedProductId = p.id;
                                                    const idx = lineItems.findIndex((i) => !i.productName);
                                                    selectProduct(idx >= 0 ? idx : lineItems.length - 1);
                                                }
                                            "
                                        >
                                            {{ p.name }}
                                        </DropdownMenuItem>
                                        <DropdownMenuSeparator />
                                        <DropdownMenuItem
                                            class="text-primary focus:bg-primary/10 focus:text-primary"
                                            @select="router.visit('/inventory/items/create')"
                                        >
                                            <Plus class="size-4" />
                                            Add New
                                        </DropdownMenuItem>
                                    </DropdownMenuContent>
                                </DropdownMenu>
                            </div>
                            <div class="overflow-x-auto rounded-xl border border-sidebar-border/70">
                                <table class="w-full text-sm">
                                    <thead>
                                        <tr class="border-b border-sidebar-border/70 bg-muted/60">
                                            <th class="px-4 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                                                Product/Service
                                            </th>
                                            <th class="w-24 px-4 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                                                Qty
                                            </th>
                                            <th class="w-24 px-4 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                                                Unit
                                            </th>
                                            <th class="w-28 px-4 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                                                Rate
                                            </th>
                                            <th class="w-24 px-4 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                                                Discount
                                            </th>
                                            <th class="w-20 px-4 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                                                Tax %
                                            </th>
                                            <th class="w-28 px-4 py-3.5 text-right text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                                                Amount
                                            </th>
                                            <th class="w-12 px-2 py-3.5" />
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr
                                            v-for="(item, idx) in lineItems"
                                            :key="item.id"
                                            class="border-b border-sidebar-border/50 transition-colors hover:bg-muted/30"
                                        >
                                            <td class="px-4 py-2">
                                                <Input
                                                    v-model="item.productName"
                                                    class="h-9 rounded-md border-0 bg-transparent shadow-none focus-visible:ring-0"
                                                    placeholder="Product or service"
                                                />
                                            </td>
                                            <td class="px-4 py-2">
                                                <Input
                                                    v-model.number="item.quantity"
                                                    type="number"
                                                    min="0"
                                                    class="h-9 rounded-md text-center"
                                                    @input="updateItemAmount(idx)"
                                                />
                                            </td>
                                            <td class="px-4 py-2">
                                                <Input
                                                    v-model="item.unit"
                                                    class="h-9 rounded-md"
                                                    placeholder="Pcs"
                                                />
                                            </td>
                                            <td class="px-4 py-2">
                                                <Input
                                                    v-model.number="item.rate"
                                                    type="number"
                                                    min="0"
                                                    step="0.01"
                                                    class="h-9 rounded-md text-right tabular-nums"
                                                    @input="updateItemAmount(idx)"
                                                />
                                            </td>
                                            <td class="px-4 py-2">
                                                <div class="flex items-center gap-1">
                                                    <Input
                                                        v-model.number="item.discount"
                                                        type="number"
                                                        min="0"
                                                        max="100"
                                                        class="h-9 w-16 rounded-md text-right tabular-nums"
                                                        @input="updateItemAmount(idx)"
                                                    />
                                                    <span class="text-xs text-muted-foreground">%</span>
                                                </div>
                                            </td>
                                            <td class="px-4 py-2">
                                                <Input
                                                    v-model.number="item.taxPercent"
                                                    type="number"
                                                    min="0"
                                                    max="100"
                                                    class="h-9 w-16 rounded-md text-right tabular-nums"
                                                    @input="updateItemAmount(idx)"
                                                />
                                            </td>
                                            <td class="px-4 py-2.5 text-right font-medium tabular-nums">
                                                {{ currencySymbol }}{{ item.amount.toFixed(2) }}
                                            </td>
                                            <td class="px-2 py-2.5">
                                                <Button
                                                    v-if="lineItems.length > 1"
                                                    variant="ghost"
                                                    size="icon"
                                                    class="size-8 rounded-full text-muted-foreground hover:bg-destructive/10 hover:text-destructive"
                                                    @click="removeLineItem(item.id)"
                                                >
                                                    ×
                                                </Button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <Button
                                type="button"
                                variant="outline"
                                class="w-full rounded-lg border-dashed py-6"
                                @click="addLineItem"
                            >
                                <Plus class="mr-2 size-4" />
                                Add line item
                            </Button>
                        </CardContent>
                    </Card>

            <!-- Extra Information + Summary - 2 columns side by side -->
            <div class="grid gap-6 lg:grid-cols-2">
                <!-- Left: Extra Information -->
                <Card class="overflow-hidden rounded-xl border border-sidebar-border/70 bg-card shadow-sm">
                    <CardContent class="p-6">
                        <h2 class="text-base font-semibold text-foreground">Extra Information</h2>
                        <div class="mt-4 flex flex-wrap gap-2">
                            <button
                                type="button"
                                class="rounded-lg border px-4 py-2.5 text-sm font-medium transition-colors"
                                :class="notesActive ? 'border-primary bg-primary text-primary-foreground' : 'border-input bg-background text-muted-foreground hover:bg-muted/50'"
                                @click="(notesActive = true), (termsActive = false), (bankDetailsActive = false)"
                            >
                                <FileText class="mr-2 inline-block size-4" />
                                Add Notes
                            </button>
                            <button
                                type="button"
                                class="rounded-lg border px-4 py-2.5 text-sm font-medium transition-colors"
                                :class="termsActive ? 'border-primary bg-primary text-primary-foreground' : 'border-input bg-background text-muted-foreground hover:bg-muted/50'"
                                @click="(termsActive = true), (notesActive = false), (bankDetailsActive = false)"
                            >
                                <FileText class="mr-2 inline-block size-4" />
                                Add Terms & Conditions
                            </button>
                            <button
                                type="button"
                                class="rounded-lg border px-4 py-2.5 text-sm font-medium transition-colors"
                                :class="bankDetailsActive ? 'border-primary bg-primary text-primary-foreground' : 'border-input bg-background text-muted-foreground hover:bg-muted/50'"
                                @click="(bankDetailsActive = true), (notesActive = false), (termsActive = false)"
                            >
                                <Banknote class="mr-2 inline-block size-4" />
                                Bank Details
                            </button>
                        </div>
                        <div v-if="notesActive" class="mt-4">
                            <Label class="text-sm font-medium">Additional Notes</Label>
                            <textarea
                                v-model="additionalNotes"
                                rows="4"
                                class="mt-2 w-full rounded-lg border border-input bg-background px-3 py-2.5 text-sm outline-none focus:ring-2 focus:ring-ring"
                                placeholder="Enter additional notes..."
                            />
                        </div>
                        <div v-if="termsActive" class="mt-4">
                            <Label class="text-sm font-medium">Terms & Conditions</Label>
                            <textarea
                                class="mt-2 w-full rounded-lg border border-input bg-background px-3 py-2.5 text-sm outline-none focus:ring-2 focus:ring-ring"
                                placeholder="Enter terms and conditions..."
                                rows="4"
                            />
                        </div>
                        <div v-if="bankDetailsActive" class="mt-4">
                            <Label class="text-sm font-medium">Account</Label>
                            <select
                                v-model="accountId"
                                class="mt-2 h-10 w-full rounded-lg border border-input bg-background px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-ring"
                            >
                                <option value="">Select</option>
                                <option value="1">Primary Account</option>
                                <option value="2">Secondary Account</option>
                            </select>
                        </div>
                    </CardContent>
                </Card>

                <!-- Right: Summary + Signature -->
                <Card class="overflow-hidden rounded-xl border border-sidebar-border/70 bg-card shadow-sm">
                    <CardContent class="space-y-5 p-6">
                        <div class="space-y-3 text-sm">
                            <div class="flex justify-between">
                                <span class="text-muted-foreground">Amount</span>
                                <span class="font-medium tabular-nums">{{ currencySymbol }}{{ subtotalAmount.toFixed(2) }}</span>
                            </div>
                            <div v-if="enableTax" class="flex justify-between">
                                <span class="text-muted-foreground">CGST (9%)</span>
                                <span class="font-medium tabular-nums">{{ currencySymbol }}{{ cgstAmount.toFixed(2) }}</span>
                            </div>
                            <div v-if="enableTax" class="flex justify-between">
                                <span class="text-muted-foreground">SGST (9%)</span>
                                <span class="font-medium tabular-nums">{{ currencySymbol }}{{ sgstAmount.toFixed(2) }}</span>
                            </div>
                            <button
                                type="button"
                                class="flex items-center gap-2 text-sm font-medium text-primary hover:underline"
                            >
                                <Plus class="size-4" />
                                Add Additional Charges
                            </button>
                            <div class="flex items-center justify-between gap-3">
                                <span class="text-muted-foreground">Discount</span>
                                <div class="flex items-center gap-1">
                                    <Input
                                        v-model.number="discountPercent"
                                        type="number"
                                        min="0"
                                        max="100"
                                        class="h-9 w-20 rounded-lg text-right"
                                    />
                                    <span class="text-muted-foreground">%</span>
                                </div>
                            </div>
                            <div class="flex items-center justify-between gap-3">
                                <span class="text-muted-foreground">Round Off Total</span>
                                <div class="flex items-center gap-3">
                                    <Switch v-model:checked="roundOffTotal" />
                                    <span class="font-semibold tabular-nums">{{ currencySymbol }}{{ total }}</span>
                                </div>
                            </div>
                            <div class="flex items-center justify-between border-t border-sidebar-border/50 pt-3">
                                <span class="font-semibold">Total ({{ currency }})</span>
                                <span class="font-bold tabular-nums">{{ currencySymbol }}{{ total }}</span>
                            </div>
                            <div>
                                <span class="text-muted-foreground">Total In Words</span>
                                <p class="mt-0.5 text-sm">{{ numberToWords(total) }}</p>
                            </div>
                        </div>
                        <div class="space-y-4 border-t border-sidebar-border/50 pt-5">
                            <div>
                                <Label class="text-sm font-medium">Select Signature</Label>
                                <select
                                    v-model="selectedSignatureId"
                                    class="mt-2 h-10 w-full rounded-lg border border-input bg-background px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-ring"
                                >
                                    <option value="">Select Signature</option>
                                    <option v-for="s in signatures" :key="s.id" :value="s.id">
                                        {{ s.name }}
                                    </option>
                                </select>
                            </div>
                            <div class="text-center text-sm text-muted-foreground">OR</div>
                            <div>
                                <Label class="text-sm font-medium">Signature Name</Label>
                                <Input
                                    v-model="signatureName"
                                    class="mt-2 rounded-lg"
                                    placeholder="e.g. Adrian"
                                />
                            </div>
                            <Button variant="outline" class="w-full rounded-lg">
                                <Upload class="mr-2 size-4" />
                                Upload Signature
                            </Button>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Cancel & Save buttons -->
            <div class="flex justify-end gap-3 border-t border-sidebar-border/50 pt-6">
                <Button variant="outline" class="rounded-lg">
                    Cancel
                </Button>
                <Button class="rounded-lg">
                    Save
                </Button>
            </div>
        </div>
    </AppLayout>
</template>
