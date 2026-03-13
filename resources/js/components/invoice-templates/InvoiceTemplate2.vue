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
    <!-- General Invoice 2 - Based on your second image -->
    <div class="mx-auto max-w-[900px] bg-white p-8 text-sm">
        <!-- Header -->
        <div
            class="mb-6 flex items-start justify-between border-b-2 border-purple-100 pb-4"
        >
            <div>
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
                <p class="text-xs text-gray-500">Original For Recipient</p>
            </div>
            <div class="text-right">
                <h1 class="text-2xl font-bold text-gray-900 uppercase">
                    Tax Invoice
                </h1>
                <p class="mt-2 text-xs text-gray-600">
                    Date:
                    <span class="font-semibold text-gray-900">{{
                        formatDate(data.invoiceDate)
                    }}</span>
                </p>
                <p class="text-xs text-gray-600">
                    Invoice No:
                    <span class="font-semibold text-gray-900">{{
                        data.invoiceNumber
                    }}</span>
                </p>
            </div>
        </div>

        <!-- Billing Info - 3 columns -->
        <div class="mb-6 grid grid-cols-3 gap-6 text-xs">
            <div>
                <h3 class="mb-2 font-bold text-purple-600">Dreamguys</h3>
                <p class="text-gray-700">GST IN: 22AABCU9603R1ZX</p>
                <p class="text-gray-700">+91 98765 43210</p>
            </div>
            <div>
                <h3 class="mb-2 font-bold text-purple-600">Invoice To:</h3>
                <p class="font-semibold text-gray-900">
                    {{ data.customerName || 'Walter Roberson' }}
                </p>
                <p class="text-gray-700">299 Star Trek Drive, Panama City</p>
                <p class="text-gray-700">Florida, 32405, USA</p>
                <p class="text-gray-700">+91 98765 43210</p>
            </div>
            <div class="text-right">
                <h3 class="mb-2 font-bold text-purple-600">Pay To:</h3>
                <p class="font-semibold text-gray-900">
                    {{ data.billedByName || 'Lowell H. Dominguez' }}
                </p>
                <p class="text-gray-700">84 Spilman Street, London</p>
                <p class="text-gray-700">United Kingdom</p>
                <p class="text-gray-700">+44 20 7946 0958</p>
            </div>
        </div>

        <!-- Items Table -->
        <div class="mb-6">
            <table class="w-full border-collapse">
                <thead>
                    <tr class="border-b border-gray-300 bg-gray-50">
                        <th
                            class="px-3 py-2 text-left text-xs font-semibold text-gray-700"
                        >
                            #
                        </th>
                        <th
                            class="px-3 py-2 text-left text-xs font-semibold text-gray-700"
                        >
                            Item
                        </th>
                        <th
                            class="px-3 py-2 text-left text-xs font-semibold text-gray-700"
                        >
                            Tax Value
                        </th>
                        <th
                            class="px-3 py-2 text-center text-xs font-semibold text-gray-700"
                        >
                            Qty
                        </th>
                        <th
                            class="px-3 py-2 text-right text-xs font-semibold text-gray-700"
                        >
                            Price
                        </th>
                        <th
                            class="px-3 py-2 text-right text-xs font-semibold text-gray-700"
                        >
                            Total
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="(item, idx) in data.lineItems.filter(
                            (i) => i.productName,
                        )"
                        :key="item.id"
                        class="border-b border-gray-100"
                    >
                        <td class="px-3 py-3 text-xs text-gray-700">
                            {{ idx + 1 }}
                        </td>
                        <td class="px-3 py-3 text-xs font-medium text-gray-900">
                            {{ item.productName }}
                        </td>
                        <td class="px-3 py-3 text-xs text-gray-600">
                            {{ item.taxPercent }}% Tax applied
                        </td>
                        <td class="px-3 py-3 text-center text-xs text-gray-700">
                            {{ item.quantity }}
                        </td>
                        <td
                            class="px-3 py-3 text-right text-xs text-gray-700 tabular-nums"
                        >
                            {{ currencySymbol }}{{ item.rate.toFixed(2) }}
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

        <!-- Summary Section -->
        <div class="mb-6 flex justify-end">
            <div class="w-80 space-y-2 rounded-lg bg-gray-50 p-4 text-xs">
                <div class="flex justify-between">
                    <span class="text-gray-600">Taxable Amount</span>
                    <span class="font-semibold text-gray-900 tabular-nums"
                        >{{ currencySymbol
                        }}{{ data.subtotalAmount.toFixed(2) }}</span
                    >
                </div>
                <div v-if="data.enableTax" class="flex justify-between">
                    <span class="text-gray-600">IGST 18%</span>
                    <span class="font-semibold text-gray-900 tabular-nums"
                        >{{ currencySymbol
                        }}{{
                            (data.cgstAmount + data.sgstAmount).toFixed(2)
                        }}</span
                    >
                </div>
                <div
                    v-if="data.discountAmount > 0"
                    class="flex justify-between"
                >
                    <span class="text-gray-600">Discount</span>
                    <span class="font-semibold text-gray-900 tabular-nums"
                        >-{{ currencySymbol
                        }}{{ data.discountAmount.toFixed(2) }}</span
                    >
                </div>
                <div
                    class="flex justify-between border-t-2 border-gray-300 pt-2"
                >
                    <span class="text-sm font-bold text-gray-900">Total</span>
                    <span class="text-lg font-bold text-purple-600 tabular-nums"
                        >{{ currencySymbol }}{{ data.total.toFixed(2) }}</span
                    >
                </div>
            </div>
        </div>

        <!-- Total in Words -->
        <div class="mb-6 border-t border-gray-200 py-3 text-xs">
            <p class="text-gray-600">
                Total amount (in words):
                <span class="font-semibold text-gray-900">{{
                    data.totalInWords
                }}</span>
            </p>
        </div>

        <!-- Payment Info -->
        <div class="mb-6 rounded-lg border border-gray-200 bg-blue-50 p-4">
            <h4 class="mb-2 text-xs font-bold text-gray-900">Payment Info:</h4>
            <p class="text-xs text-gray-700">Debit Card: ************4567</p>
            <p class="text-xs text-gray-700">
                Amount: {{ currencySymbol }}{{ data.total.toFixed(2) }}
            </p>
        </div>

        <!-- Signature -->
        <div class="flex justify-end border-t border-gray-200 pt-6">
            <div class="text-center">
                <p class="mb-2 text-xs text-gray-600">For Dreamguys</p>
                <div class="mb-2 h-16 w-40"></div>
                <p class="font-serif text-2xl text-gray-700 italic">
                    {{ data.signatureName || 'James Paylo' }}
                </p>
            </div>
        </div>
    </div>
</template>
