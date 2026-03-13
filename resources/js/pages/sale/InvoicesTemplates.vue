<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Check, Eye, Star, X } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { Button } from '@/components/ui/button';
import Dialog from '@/components/ui/dialog/Dialog.vue';
import DialogContent from '@/components/ui/dialog/DialogContent.vue';
import DialogTitle from '@/components/ui/dialog/DialogTitle.vue';
import AppLayout from '@/layouts/AppLayout.vue';

type TemplateTab = {
    id: string;
    label: string;
};

type TemplateRow = {
    item: string;
    note: string;
    quantity: string;
    price: string;
    total: string;
};

type TemplatePreview = {
    id: number;
    name: string;
    badge: string;
    summary: string;
    category: string;
    rows: TemplateRow[];
    total: string;
};

const tabs: TemplateTab[] = [
    { id: 'invoice', label: 'Invoice' },
    { id: 'purchases', label: 'Purchases' },
    { id: 'receipt', label: 'Receipt' },
];

const activeTab = ref('invoice');
const selectedTemplateId = ref(1);
const previewTemplateId = ref<number | null>(null);

// Load selected template from localStorage on mount
const storedTemplateId = localStorage.getItem('selectedInvoiceTemplate');
if (storedTemplateId) {
    selectedTemplateId.value = parseInt(storedTemplateId, 10);
}

const sampleRows: TemplateRow[] = [
    {
        item: 'Website Design',
        note: 'Landing page and revision work',
        quantity: '1',
        price: '$350',
        total: '$350',
    },
    {
        item: 'Web Development',
        note: 'Frontend implementation',
        quantity: '1',
        price: '$600',
        total: '$600',
    },
    {
        item: 'App Development',
        note: 'iOS and Android support',
        quantity: '2',
        price: '$200',
        total: '$400',
    },
];

const templates: TemplatePreview[] = [
    {
        id: 1,
        name: 'General Invoice 1',
        badge: 'Popular',
        summary: 'Clean default layout for standard billing.',
        category: 'invoice',
        rows: sampleRows,
        total: '$1,815.00',
    },
    {
        id: 2,
        name: 'General Invoice 2',
        badge: 'Simple',
        summary: 'Balanced spacing with a lighter table layout.',
        category: 'invoice',
        rows: sampleRows,
        total: '$1,815.00',
    },
    {
        id: 3,
        name: 'General Invoice 3',
        badge: 'Compact',
        summary: 'Tighter preview for denser invoice details.',
        category: 'invoice',
        rows: sampleRows,
        total: '$1,815.00',
    },
    {
        id: 4,
        name: 'General Invoice 4',
        badge: 'Modern',
        summary: 'Soft card framing with strong totals emphasis.',
        category: 'invoice',
        rows: sampleRows,
        total: '$1,815.00',
    },
    {
        id: 5,
        name: 'Purchase Order 1',
        badge: 'Purchase',
        summary: 'Vendor-focused layout for purchase order workflows.',
        category: 'purchases',
        rows: [
            {
                item: 'Office Chairs',
                note: 'Ergonomic mesh back chairs',
                quantity: '6',
                price: '$120',
                total: '$720',
            },
            {
                item: 'Desk Lamps',
                note: 'LED lamps for workstation setup',
                quantity: '8',
                price: '$28',
                total: '$224',
            },
            {
                item: 'Storage Units',
                note: 'Metal filing cabinet units',
                quantity: '2',
                price: '$260',
                total: '$520',
            },
        ],
        total: '$1,464.00',
    },
    {
        id: 6,
        name: 'Purchase Order 2',
        badge: 'Vendor',
        summary: 'Compact purchasing card with clean totals summary.',
        category: 'purchases',
        rows: [
            {
                item: 'Printer Paper',
                note: 'A4 reams for monthly stock',
                quantity: '20',
                price: '$6',
                total: '$120',
            },
            {
                item: 'Ink Cartridges',
                note: 'CMYK cartridge replacement set',
                quantity: '4',
                price: '$48',
                total: '$192',
            },
            {
                item: 'Courier Boxes',
                note: 'Medium shipping cartons',
                quantity: '15',
                price: '$5',
                total: '$75',
            },
        ],
        total: '$387.00',
    },
    {
        id: 7,
        name: 'Purchase Bill 3',
        badge: 'Stock',
        summary: 'Inventory purchase template for restocking flows.',
        category: 'purchases',
        rows: [
            {
                item: 'Protein Bars',
                note: 'Retail shelf stock replenishment',
                quantity: '30',
                price: '$9',
                total: '$270',
            },
            {
                item: 'Beverage Cans',
                note: 'Cold storage refill items',
                quantity: '48',
                price: '$2',
                total: '$96',
            },
            {
                item: 'Display Tags',
                note: 'Barcode labels and holders',
                quantity: '50',
                price: '$1',
                total: '$50',
            },
        ],
        total: '$416.00',
    },
    {
        id: 8,
        name: 'Purchase Bill 4',
        badge: 'Detailed',
        summary: 'Detailed supplier bill with stronger item breakdown.',
        category: 'purchases',
        rows: [
            {
                item: 'Laptop Stands',
                note: 'Adjustable aluminum stands',
                quantity: '12',
                price: '$24',
                total: '$288',
            },
            {
                item: 'Wireless Mouse',
                note: 'Rechargeable office mouse',
                quantity: '10',
                price: '$18',
                total: '$180',
            },
            {
                item: 'Keyboard Sets',
                note: 'Wireless keyboard combo packs',
                quantity: '8',
                price: '$42',
                total: '$336',
            },
        ],
        total: '$804.00',
    },
    {
        id: 9,
        name: 'Receipt 1',
        badge: 'Receipt',
        summary: 'Simple payment receipt with clear paid amount focus.',
        category: 'receipt',
        rows: [
            {
                item: 'Consulting Fee',
                note: 'Project strategy and review session',
                quantity: '1',
                price: '$450',
                total: '$450',
            },
            {
                item: 'Advance Payment',
                note: 'Deposit received from customer',
                quantity: '1',
                price: '$300',
                total: '$300',
            },
            {
                item: 'Service Charge',
                note: 'Processing and support charge',
                quantity: '1',
                price: '$50',
                total: '$50',
            },
        ],
        total: '$800.00',
    },
    {
        id: 10,
        name: 'Receipt 2',
        badge: 'Cash',
        summary: 'Compact receipt design for over-the-counter payments.',
        category: 'receipt',
        rows: [
            {
                item: 'Product Sale',
                note: 'Retail billing collection',
                quantity: '2',
                price: '$90',
                total: '$180',
            },
            {
                item: 'Packaging',
                note: 'Protective wrap and boxing',
                quantity: '1',
                price: '$12',
                total: '$12',
            },
            {
                item: 'Delivery Fee',
                note: 'Local delivery collection',
                quantity: '1',
                price: '$25',
                total: '$25',
            },
        ],
        total: '$217.00',
    },
    {
        id: 11,
        name: 'Receipt 3',
        badge: 'Payment',
        summary:
            'Balanced receipt template for online or bank transfer records.',
        category: 'receipt',
        rows: [
            {
                item: 'Subscription Renewal',
                note: 'Annual platform subscription',
                quantity: '1',
                price: '$999',
                total: '$999',
            },
            {
                item: 'Support Add-on',
                note: 'Priority support package',
                quantity: '1',
                price: '$120',
                total: '$120',
            },
            {
                item: 'Tax Collected',
                note: 'Applicable tax amount',
                quantity: '1',
                price: '$90',
                total: '$90',
            },
        ],
        total: '$1,209.00',
    },
    {
        id: 12,
        name: 'Receipt 4',
        badge: 'Detailed',
        summary: 'Receipt card with clearer line-level payment notes.',
        category: 'receipt',
        rows: [
            {
                item: 'Maintenance Fee',
                note: 'Quarterly service maintenance payment',
                quantity: '1',
                price: '$260',
                total: '$260',
            },
            {
                item: 'Parts Replacement',
                note: 'Hardware replacement collection',
                quantity: '1',
                price: '$140',
                total: '$140',
            },
            {
                item: 'Transport Charges',
                note: 'Pickup and drop logistics',
                quantity: '1',
                price: '$35',
                total: '$35',
            },
        ],
        total: '$435.00',
    },
];

const selectedTemplate = computed(
    () =>
        templates.find(
            (template) => template.id === selectedTemplateId.value,
        ) ?? templates[0],
);

const previewTemplate = computed(
    () =>
        templates.find((template) => template.id === previewTemplateId.value) ??
        selectedTemplate.value,
);

const visibleTemplates = computed(() =>
    templates.filter((template) => template.category === activeTab.value),
);

const previewDocumentLabel = computed(() =>
    previewTemplate.value.category === 'purchases'
        ? 'Purchase Order'
        : previewTemplate.value.category === 'receipt'
          ? 'Payment Receipt'
          : 'Tax Invoice',
);

function setActiveTab(tabId: string): void {
    activeTab.value = tabId;

    const firstTemplateInTab = templates.find(
        (template) => template.category === tabId,
    );

    if (firstTemplateInTab) {
        selectedTemplateId.value = firstTemplateInTab.id;
    }
}

function openPreview(templateId: number): void {
    previewTemplateId.value = templateId;
}

function closePreview(): void {
    previewTemplateId.value = null;
}

function selectTemplate(templateId: number): void {
    selectedTemplateId.value = templateId;
    // Store selected template in localStorage
    localStorage.setItem('selectedInvoiceTemplate', templateId.toString());
}
</script>

<template>
    <Head title="Invoice Templates" />
    <AppLayout
        :breadcrumbs="[
            { title: 'Invoices', href: '/sales/invoices' },
            { title: 'Invoice Templates', href: '/sales/invoices/templates' },
        ]"
    >
        <div class="flex h-full flex-1 flex-col gap-6 bg-slate-50 p-6">
            <div
                class="rounded-3xl border border-slate-200 bg-white px-6 py-5 shadow-sm"
            >
                <h1
                    class="text-2xl font-semibold tracking-tight text-slate-950"
                >
                    Invoice Templates
                </h1>
                <p class="mt-1 text-sm text-slate-500">
                    Choose a template and preview it before using it in a new
                    invoice.
                </p>

                <div
                    class="mt-5 flex flex-wrap gap-2 border-b border-slate-200 pb-3"
                >
                    <button
                        v-for="tab in tabs"
                        :key="tab.id"
                        type="button"
                        class="rounded-full px-4 py-2 text-sm font-medium transition-colors"
                        :class="
                            activeTab === tab.id
                                ? 'bg-violet-600 text-white'
                                : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900'
                        "
                        @click="setActiveTab(tab.id)"
                    >
                        {{ tab.label }}
                    </button>
                </div>

                <div
                    v-if="
                        activeTab === 'invoice' ||
                        activeTab === 'purchases' ||
                        activeTab === 'receipt'
                    "
                    class="mt-6 grid gap-5 md:grid-cols-2 xl:grid-cols-4"
                >
                    <article
                        v-for="template in visibleTemplates"
                        :key="template.id"
                        class="group rounded-2xl border bg-[#f6f6f7] p-3 shadow-sm transition-all hover:-translate-y-0.5 hover:shadow-md"
                        :class="
                            selectedTemplateId === template.id
                                ? 'border-violet-500 shadow-[0_0_0_1px_rgba(139,92,246,0.16)]'
                                : 'border-slate-200'
                        "
                    >
                        <div>
                            <div
                                class="relative h-[300px] overflow-hidden rounded-xl border border-slate-200 bg-white p-3 shadow-[0_6px_18px_rgba(15,23,42,0.04)]"
                            >
                                <div
                                    class="space-y-2.5 text-[8px] text-slate-600"
                                >
                                    <div
                                        class="flex items-start justify-between border-b border-slate-100 pb-1.5"
                                    >
                                        <div>
                                            <p
                                                class="text-[11px] font-semibold text-slate-900"
                                            >
                                                Mister Accountant
                                            </p>
                                            <p
                                                class="text-[7px] text-slate-400"
                                            >
                                                Original For Recipient
                                            </p>
                                        </div>
                                        <div class="text-right">
                                            <p
                                                class="font-semibold text-slate-800 uppercase"
                                            >
                                                {{
                                                    template.category ===
                                                    'purchases'
                                                        ? 'Purchase Order'
                                                        : template.category ===
                                                            'receipt'
                                                          ? 'Payment Receipt'
                                                          : 'Tax Invoice'
                                                }}
                                            </p>
                                            <p
                                                class="text-[7px] text-slate-400"
                                            >
                                                05/12/2024
                                            </p>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-2 gap-2.5">
                                        <div>
                                            <p
                                                class="font-semibold text-violet-600"
                                            >
                                                Invoice To
                                            </p>
                                            <p class="mt-1 text-slate-800">
                                                Walter Roberson
                                            </p>
                                            <p>Panama City, Florida</p>
                                        </div>
                                        <div>
                                            <p
                                                class="font-semibold text-violet-600"
                                            >
                                                Pay To
                                            </p>
                                            <p class="mt-1 text-slate-800">
                                                Lowell H. Dominguez
                                            </p>
                                            <p>London, UK</p>
                                        </div>
                                    </div>

                                    <div
                                        class="overflow-hidden rounded-lg border border-slate-200"
                                    >
                                        <div
                                            class="grid grid-cols-[1.2fr_0.45fr_0.75fr] bg-slate-50 px-2 py-1 font-semibold text-slate-500"
                                        >
                                            <span>Item</span>
                                            <span class="text-center">Qty</span>
                                            <span class="text-right"
                                                >Total</span
                                            >
                                        </div>
                                        <div
                                            v-for="row in template.rows"
                                            :key="`${template.id}-${row.item}`"
                                            class="grid grid-cols-[1.2fr_0.45fr_0.75fr] border-t border-slate-100 px-2 py-1"
                                        >
                                            <span class="truncate">{{
                                                row.item
                                            }}</span>
                                            <span class="text-center">{{
                                                row.quantity
                                            }}</span>
                                            <span class="text-right">{{
                                                row.total
                                            }}</span>
                                        </div>
                                    </div>

                                    <div
                                        class="flex items-end justify-between border-t border-slate-100 pt-1.5"
                                    >
                                        <div>
                                            <p
                                                class="font-semibold text-slate-800"
                                            >
                                                Bank Details
                                            </p>
                                            <p>YES Bank</p>
                                        </div>
                                        <div class="text-right">
                                            <p
                                                class="text-[7px] text-slate-400"
                                            >
                                                Total
                                            </p>
                                            <p
                                                class="text-[11px] font-semibold text-slate-900"
                                            >
                                                {{ template.total }}
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <div
                                    class="absolute inset-0 flex items-center justify-center bg-slate-950/0 opacity-0 transition-all duration-200 group-hover:bg-slate-950/30 group-hover:opacity-100"
                                >
                                    <button
                                        type="button"
                                        class="inline-flex items-center gap-2 rounded-full bg-white px-4 py-2 text-sm font-medium text-violet-700 shadow-lg"
                                        :aria-label="`View ${template.name}`"
                                        @click="openPreview(template.id)"
                                    >
                                        <Eye class="size-4" />
                                        View
                                    </button>
                                </div>
                            </div>

                            <div
                                class="flex items-center justify-between gap-3 px-1 pt-4"
                            >
                                <button
                                    type="button"
                                    class="text-left text-[1.05rem] font-medium tracking-tight transition-colors"
                                    :class="
                                        selectedTemplateId === template.id
                                            ? 'text-violet-600'
                                            : 'text-slate-950'
                                    "
                                    @click="openPreview(template.id)"
                                >
                                    {{ template.name }}
                                </button>
                                <button
                                    type="button"
                                    class="grid size-8 shrink-0 place-items-center rounded-full border transition-colors"
                                    :class="
                                        selectedTemplateId === template.id
                                            ? 'border-violet-500 bg-violet-500 text-white'
                                            : 'border-slate-300 bg-slate-100 text-slate-500 hover:border-violet-300 hover:text-violet-600'
                                    "
                                    :aria-label="`Select ${template.name}`"
                                    @click="selectTemplate(template.id)"
                                >
                                    <Check
                                        v-if="
                                            selectedTemplateId === template.id
                                        "
                                        class="size-4"
                                    />
                                    <Star v-else class="size-4" />
                                </button>
                            </div>
                        </div>
                    </article>
                </div>
            </div>
        </div>

        <Dialog
            :open="previewTemplateId !== null"
            @update:open="
                (open) => {
                    if (!open) closePreview();
                }
            "
        >
            <DialogContent
                :show-close-button="false"
                class="max-h-[92vh] max-w-[95vw] overflow-hidden border-slate-200 bg-white p-0 sm:max-w-[1120px]"
            >
                <div
                    class="flex items-center justify-between border-b border-slate-200 px-5 py-4"
                >
                    <DialogTitle
                        class="text-xl font-semibold tracking-tight text-slate-950"
                    >
                        {{ previewTemplate.name }}
                    </DialogTitle>
                    <button
                        type="button"
                        class="grid size-8 place-items-center rounded-full bg-red-500 text-white"
                        aria-label="Close preview"
                        @click="closePreview()"
                    >
                        <X class="size-4" />
                    </button>
                </div>

                <div
                    class="max-h-[calc(92vh-72px)] overflow-y-auto bg-slate-100 p-5"
                >
                    <div
                        class="mx-auto max-w-[920px] rounded-2xl border border-slate-200 bg-white p-8 shadow-sm"
                    >
                        <div
                            class="flex flex-col justify-between gap-8 border-b border-slate-200 pb-6 md:flex-row"
                        >
                            <div>
                                <p
                                    class="text-4xl font-semibold tracking-tight text-slate-950"
                                >
                                    Mister Accountant
                                </p>
                                <p class="mt-1 text-sm text-slate-400">
                                    Original For Recipient
                                </p>
                            </div>
                            <div class="text-right">
                                <p
                                    class="text-2xl font-bold text-slate-900 uppercase"
                                >
                                    {{ previewDocumentLabel }}
                                </p>
                                <p class="mt-2 text-sm text-slate-500">
                                    Date: 05/12/2024
                                </p>
                                <p class="text-sm text-slate-500">
                                    Invoice No: INV 00001
                                </p>
                            </div>
                        </div>

                        <div
                            class="grid gap-6 border-b border-slate-200 py-6 text-sm leading-6 text-slate-600 md:grid-cols-3"
                        >
                            <div>
                                <p class="font-semibold text-violet-700">
                                    Invoice To :
                                </p>
                                <p class="mt-2 font-medium text-slate-900">
                                    Walter Roberson
                                </p>
                                <p>299 Star Trek Drive, Panama City</p>
                                <p>Florida, 32405, USA</p>
                                <p>walter@gmail.com</p>
                            </div>
                            <div>
                                <p class="font-semibold text-violet-700">
                                    Pay To :
                                </p>
                                <p class="mt-2 font-medium text-slate-900">
                                    Lowell H. Dominguez
                                </p>
                                <p>84 Spilman Street, London</p>
                                <p>United Kingdom</p>
                                <p>domlowell@gmail.com</p>
                            </div>
                            <div class="text-left md:text-right">
                                <p>GST IN : 22AABCU9603R1ZX</p>
                                <p>High Wycombe HP12 3JL</p>
                                <p>Mobile : +91 98765 43210</p>
                            </div>
                        </div>

                        <div class="overflow-hidden border-b border-slate-200">
                            <div
                                class="grid grid-cols-[0.4fr_1.3fr_2.4fr_0.6fr_0.9fr_0.9fr] bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-700"
                            >
                                <span>#</span>
                                <span>Item</span>
                                <span>Tax Value</span>
                                <span>Qty</span>
                                <span class="text-right">Price</span>
                                <span class="text-right">Total</span>
                            </div>
                            <div
                                v-for="(row, index) in previewTemplate.rows"
                                :key="`${previewTemplate.id}-modal-${row.item}`"
                                class="grid grid-cols-[0.4fr_1.3fr_2.4fr_0.6fr_0.9fr_0.9fr] border-t border-slate-100 px-4 py-4 text-sm text-slate-700"
                            >
                                <span>{{ index + 1 }}</span>
                                <span>{{ row.item }}</span>
                                <span>{{ row.note }}</span>
                                <span>{{ row.quantity }}</span>
                                <span class="text-right">{{ row.price }}</span>
                                <span class="text-right">{{ row.total }}</span>
                            </div>
                        </div>

                        <div
                            class="flex flex-col gap-6 border-b border-slate-200 py-6 md:flex-row md:items-end md:justify-between"
                        >
                            <p class="text-sm text-slate-600">
                                Total Items / Qty : 3 / 4.00
                            </p>

                            <div
                                class="w-full max-w-[300px] space-y-3 text-sm text-slate-700"
                            >
                                <div class="flex justify-between">
                                    <span>Taxable Amount</span>
                                    <span>$1650.00</span>
                                </div>
                                <div class="flex justify-between">
                                    <span>IGST 18.0%</span>
                                    <span>$165.00</span>
                                </div>
                                <div
                                    class="flex justify-between border-t border-slate-200 pt-3 text-lg font-semibold text-slate-950"
                                >
                                    <span>Total</span>
                                    <span>{{ previewTemplate.total }}</span>
                                </div>
                            </div>
                        </div>

                        <div
                            class="border-b border-slate-200 py-5 text-sm text-slate-600"
                        >
                            Total amount (in words): One Thousand Eight Hundred
                            Fifteen Dollars Only.
                        </div>

                        <div
                            class="grid gap-8 border-b border-slate-200 py-6 md:grid-cols-[1.2fr_0.8fr]"
                        >
                            <div class="space-y-1 text-sm text-slate-600">
                                <p
                                    class="mb-2 text-xl font-semibold text-slate-900"
                                >
                                    Bank Details
                                </p>
                                <p>Bank : YES Bank</p>
                                <p>Account # : 6677889944551</p>
                                <p>IFSC : YESBIN4567</p>
                                <p>Branch : RS Puram</p>
                            </div>
                            <div
                                class="flex flex-col items-end justify-end text-right"
                            >
                                <p class="text-sm text-slate-600">
                                    For Dreamguys
                                </p>
                                <p class="mt-5 text-4xl text-slate-700 italic">
                                    James Paylo
                                </p>
                            </div>
                        </div>

                        <div class="pt-6 text-sm leading-7 text-slate-600">
                            <p
                                class="mb-2 text-xl font-semibold text-slate-900"
                            >
                                Terms &amp; Conditions
                            </p>
                            <p>
                                1. Goods once sold cannot be taken back or
                                exchanged.
                            </p>
                            <p>
                                2. Warranty will follow the original supplier
                                terms and conditions.
                            </p>
                        </div>
                    </div>
                </div>

                <div
                    class="flex items-center justify-between border-t border-slate-200 bg-white px-5 py-4"
                >
                    <p class="text-sm text-slate-500">
                        Selected template: {{ selectedTemplate.name }}
                    </p>
                    <Link
                        :href="`/sales/invoices/create?template=${selectedTemplateId}`"
                    >
                        <Button
                            class="bg-violet-600 text-white hover:bg-violet-700"
                        >
                            Use This Template
                        </Button>
                    </Link>
                </div>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>
