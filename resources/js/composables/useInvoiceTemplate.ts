import { ref, computed, onMounted } from 'vue';

export type InvoiceTemplateType = 1 | 2 | 3 | 4;

export function useInvoiceTemplate() {
    const selectedTemplateId = ref<InvoiceTemplateType>(1);

    onMounted(() => {
        const stored = localStorage.getItem('selectedInvoiceTemplate');
        if (stored) {
            const parsed = parseInt(stored, 10);
            if ([1, 2, 3, 4].includes(parsed)) {
                selectedTemplateId.value = parsed as InvoiceTemplateType;
            }
        }

        // Also check URL query parameter
        const urlParams = new URLSearchParams(window.location.search);
        const templateParam = urlParams.get('template');
        if (templateParam) {
            const parsed = parseInt(templateParam, 10);
            if ([1, 2, 3, 4].includes(parsed)) {
                selectedTemplateId.value = parsed as InvoiceTemplateType;
                localStorage.setItem(
                    'selectedInvoiceTemplate',
                    parsed.toString(),
                );
            }
        }
    });

    const templateName = computed(() => {
        const names: Record<InvoiceTemplateType, string> = {
            1: 'General Invoice 1',
            2: 'General Invoice 2',
            3: 'General Invoice 3',
            4: 'General Invoice 4',
        };
        return names[selectedTemplateId.value];
    });

    return {
        selectedTemplateId,
        templateName,
    };
}
