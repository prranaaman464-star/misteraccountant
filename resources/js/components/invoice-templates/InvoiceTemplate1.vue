<script setup lang="ts">
import { computed } from 'vue';

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

type TemplateData = {
    invoiceNumber: string;
    referenceNumber: string;
    invoiceDate: string;
    dueDate: string | null;
    customerName: string;
    billedByName: string;
    currency: string;
    lineItems: LineItem[];
    subtotalAmount: number;
    cgstAmount: number;
    sgstAmount: number;
    discountAmount: number;
    total: number;
    totalInWords: string;
    additionalNotes: string;
    termsAndConditions: string;
    signatureName: string;
    enableTax: boolean;
};

const props = defineProps<{
    data: TemplateData;
}>();

const currencySymbol = computed(() => {
    const map: Record<string, string> = {
        USD: '$',
        INR: '₹',
        EUR: '€',
        GBP: '£',
    };
    return map[props.data.currency] ?? props.data.currency;
});

function formatDate(dateStr: string | null): string {
    if (!dateStr) return '-';
    const date = new Date(dateStr);
    return date.toLocaleDateString('en-GB', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
    });
}
</script>

<template>
    <!-- General Invoice 1 - Based on your first image -->
    <div class="mx-auto max-w-[900px] bg-white p-8 text-sm">
        <!-- Header -->
        <div class="mb-6 flex items-start justify-between">
            <div>
                <h1 class="text-xl font-bold text-gray-900">Invoice</h1>
                <p class="mt-1 text-xs text-gray-500">
                    Dreamz Technologies INDIA.,
                </p>
                <p class="text-xs text-gray-500">
                    9 Rathore Nagar, Vaishali Nagar, JAIPUR, Rajasthan, 302021,
                    India
                </p>
            </div>
            <div class="text-right">
                <div
                    class="mb-2 inline-flex items-center gap-2 rounded-lg bg-gradient-to-r from-purple-600 to-blue-500 px-4 py-2"
                >
                    <svg
                        class="h-5 w-5 text-white"
                        viewBox="0 0 24 24"
                        fill="currentColor"
                    >
                        <path
                            d="M12 2L2 7v10c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V7l-10-5z"
                        />
                    </svg>
                    <span class="text-sm font-bold text-white">Mister Accountant</span>
                </div>
                <p class="text-xs font-semibold text-gray-700 uppercase">
                    Tax Invoice
                </p>
                <p class="text-xs text-gray-500">
                    Date: {{ formatDate(data.invoiceDate) }}
                </p>
                <p class="text-xs text-gray-500">
                    Invoice No: {{ data.invoiceNumber }}
                </p>
            </div>
        </div>

        <!-- Billing Section -->
        <div class="mb-6 grid grid-cols-2 gap-8 border-y border-gray-200 py-4">
            <div>
                <h3 class="mb-2 text-xs font-bold text-purple-600">
                    Billing From
                </h3>
                <p class="text-sm font-semibold text-gray-900">
                    {{ data.billedByName || 'Dreamz Technologies' }}
                </p>
                <p class="text-xs text-gray-600">
                    9 Rathore Nagar, Vaishali Nagar
                </p>
                <p class="text-xs text-gray-600">JAIPUR, Rajasthan, 302021</p>
                <p class="text-xs text-gray-600">India</p>
            </div>
            <div>
                <h3 class="mb-2 text-xs font-bold text-purple-600">
                    Billing To
                </h3>
                <p class="text-sm font-semibold text-gray-900">
                    {{ data.customerName || 'Customer Name' }}
                </p>
                <p class="text-xs text-gray-600">Customer Address</p>
            </div>
        </div>

        <!-- Items Table -->
        <div class="mb-6">
            <h3 class="mb-3 text-sm font-bold text-gray-800">
                Product / Service Items
            </h3>
            <table class="w-full border-collapse">
                <thead>
                    <tr class="border-b-2 border-gray-300 bg-gray-50">
                        <th
                            class="px-3 py-2 text-left text-xs font-bold text-gray-700"
                        >
                            #
                        </th>
                        <th
                            class="px-3 py-2 text-left text-xs font-bold text-gray-700"
                        >
                            Product/Service
                        </th>
                        <th
                            class="px-3 py-2 text-center text-xs font-bold text-gray-700"
                        >
                            Qty
                        </th>
                        <th
                            class="px-3 py-2 text-center text-xs font-bold text-gray-700"
                        >
                            Unit
                        </th>
                        <th
                            class="px-3 py-2 text-right text-xs font-bold text-gray-700"
                        >
                            Rate
                        </th>
                        <th
                            class="px-3 py-2 text-right text-xs font-bold text-gray-700"
                        >
                            Discount
                        </th>
                        <th
                            class="px-3 py-2 text-right text-xs font-bold text-gray-700"
                        >
                            Tax
                        </th>
                        <th
                            class="px-3 py-2 text-right text-xs font-bold text-gray-700"
                        >
                            Amount
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="(item, idx) in data.lineItems.filter(
                            (i) => i.productName,
                        )"
                        :key="item.id"
                        class="border-b border-gray-200"
                    >
                        <td class="px-3 py-3 text-xs text-gray-700">
                            {{ idx + 1 }}
                        </td>
                        <td class="px-3 py-3 text-xs text-gray-900">
                            {{ item.productName }}
                        </td>
                        <td class="px-3 py-3 text-center text-xs text-gray-700">
                            {{ item.quantity }}
                        </td>
                        <td class="px-3 py-3 text-center text-xs text-gray-700">
                            {{ item.unit }}
                        </td>
                        <td
                            class="px-3 py-3 text-right text-xs text-gray-700 tabular-nums"
                        >
                            {{ currencySymbol }}{{ item.rate.toFixed(2) }}
                        </td>
                        <td class="px-3 py-3 text-right text-xs text-gray-700">
                            {{ item.discount }}%
                        </td>
                        <td class="px-3 py-3 text-right text-xs text-gray-700">
                            {{ item.taxPercent }}%
                        </td>
                        <td
                            class="px-3 py-3 text-right text-xs font-semibold text-gray-900 tabular-nums"
                        >
                            {{ currencySymbol }}{{ item.amount.toFixed(2) }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- QR Code and Summary Section -->
        <div class="mb-6 flex gap-8">
            <!-- QR Code -->
            <div class="flex-shrink-0">
                <div
                    class="h-24 w-24 rounded border border-gray-300 bg-gray-100 p-2"
                >
                    <svg viewBox="0 0 100 100" class="h-full w-full">
                        <rect width="100" height="100" fill="white" />
                        <rect
                            x="10"
                            y="10"
                            width="30"
                            height="30"
                            fill="black"
                        />
                        <rect
                            x="60"
                            y="10"
                            width="30"
                            height="30"
                            fill="black"
                        />
                        <rect
                            x="10"
                            y="60"
                            width="30"
                            height="30"
                            fill="black"
                        />
                    </svg>
                </div>
                <p class="mt-1 text-center text-xs text-gray-500">
                    Scan to Pay
                </p>
            </div>

            <!-- Summary -->
            <div class="flex-1">
                <div class="space-y-2 text-xs">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Amount</span>
                        <span class="font-semibold text-gray-900 tabular-nums">
                            {{ currencySymbol
                            }}{{ data.subtotalAmount.toFixed(2) }}
                        </span>
                    </div>
                    <div v-if="data.enableTax" class="flex justify-between">
                        <span class="text-gray-600">CGST (9%)</span>
                        <span class="font-semibold text-gray-900 tabular-nums">
                            {{ currencySymbol }}{{ data.cgstAmount.toFixed(2) }}
                        </span>
                    </div>
                    <div v-if="data.enableTax" class="flex justify-between">
                        <span class="text-gray-600">SGST (9%)</span>
                        <span class="font-semibold text-gray-900 tabular-nums">
                            {{ currencySymbol }}{{ data.sgstAmount.toFixed(2) }}
                        </span>
                    </div>
                    <div
                        v-if="data.discountAmount > 0"
                        class="flex justify-between"
                    >
                        <span class="text-gray-600">Discount</span>
                        <span class="font-semibold text-gray-900 tabular-nums">
                            -{{ currencySymbol
                            }}{{ data.discountAmount.toFixed(2) }}
                        </span>
                    </div>
                    <div
                        class="flex justify-between border-t-2 border-gray-300 pt-2"
                    >
                        <span class="font-bold text-gray-900"
                            >Total ({{ data.currency }})</span
                        >
                        <span
                            class="text-lg font-bold text-gray-900 tabular-nums"
                        >
                            {{ currencySymbol }}{{ data.total.toFixed(2) }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total in Words -->
        <div class="mb-6 border-t border-gray-200 pt-4">
            <p class="text-xs text-gray-600">Total In Words</p>
            <p class="text-sm font-semibold text-gray-900">
                {{ data.totalInWords }}
            </p>
        </div>

        <!-- Terms and Bank Details -->
        <div class="mb-6 grid grid-cols-2 gap-8 border-t border-gray-200 pt-4">
            <div>
                <h4 class="mb-2 text-xs font-bold text-gray-800">
                    Terms and Conditions:
                </h4>
                <p class="text-xs text-gray-600">
                    {{
                        data.termsAndConditions || 'Thank you for your business'
                    }}
                </p>
            </div>
            <div>
                <h4 class="mb-2 text-xs font-bold text-gray-800">Notes</h4>
                <p class="text-xs text-gray-600">
                    {{
                        data.additionalNotes ||
                        'All payments should be made within 30 days'
                    }}
                </p>
            </div>
        </div>

        <!-- Signature -->
        <div class="flex justify-end border-t border-gray-200 pt-6">
            <div class="text-center">
                <div class="mb-2 h-16 w-40"></div>
                <div class="border-t-2 border-gray-800 pt-1">
                    <p class="text-xs font-bold text-gray-900">
                        {{ data.signatureName || 'Authorized Signature' }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>
