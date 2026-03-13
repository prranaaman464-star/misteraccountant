<script setup lang="ts">
import { computed } from 'vue';
import {
    Dialog,
    DialogScrollContent,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import InvoiceTemplate1 from '@/components/invoice-templates/InvoiceTemplate1.vue';
import InvoiceTemplate2 from '@/components/invoice-templates/InvoiceTemplate2.vue';
import InvoiceTemplate3 from '@/components/invoice-templates/InvoiceTemplate3.vue';
import InvoiceTemplate4 from '@/components/invoice-templates/InvoiceTemplate4.vue';
import type { InvoiceTemplateType } from '@/composables/useInvoiceTemplate';

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

type PreviewData = {
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
    open: boolean;
    data: PreviewData;
    templateId?: InvoiceTemplateType;
}>();

const emit = defineEmits<{
    (e: 'update:open', value: boolean): void;
}>();

const currentTemplateId = computed(() => props.templateId || 1);

const templateComponent = computed(() => {
    const templates = {
        1: InvoiceTemplate1,
        2: InvoiceTemplate2,
        3: InvoiceTemplate3,
        4: InvoiceTemplate4,
    };
    return templates[currentTemplateId.value];
});
</script>

<template>
    <Dialog :open="open" @update:open="emit('update:open', $event)">
        <DialogScrollContent class="max-w-4xl">
            <DialogHeader>
                <DialogTitle>Invoice Preview</DialogTitle>
            </DialogHeader>

            <!-- Dynamic Template Component -->
            <component :is="templateComponent" :data="data" />

            <!-- Footer Actions -->
            <div class="flex justify-end gap-3 border-t pt-4">
                <Button variant="outline" @click="emit('update:open', false)">
                    Close
                </Button>
            </div>
        </DialogScrollContent>
    </Dialog>
</template>
