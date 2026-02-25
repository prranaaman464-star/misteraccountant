<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import { usePage } from '@inertiajs/vue3';
import { onClickOutside } from '@vueuse/core';
import { ChevronDown, Plus, Search } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { update } from '@/actions/App/Http/Controllers/Inventory/ItemsController';
import AlertError from '@/components/AlertError.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import {
    Collapsible,
    CollapsibleContent,
    CollapsibleTrigger,
} from '@/components/ui/collapsible';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import inventory from '@/routes/inventory';
import { edit, show } from '@/routes/inventory/items';
import type { BreadcrumbItem } from '@/types';

type Item = {
    id: number;
    name: string;
    item_code: string | null;
    sub_category: string | null;
    brand: string | null;
    model_no: string | null;
    description: string | null;
    item_type: string;
    status: string;
    item_image: string | null;
    category: { id: number; name: string } | null;
    pricing: Record<string, number | null> | null;
    inventory: Record<string, unknown> | null;
    taxDetail: Record<string, unknown> | null;
    compliance: Record<string, unknown> | null;
};

const props = defineProps<{
    item: Item;
}>();

const breadcrumbs = computed<BreadcrumbItem[]>(() => [
    { title: 'Home', href: dashboard().url },
    { title: 'Items', href: inventory.items.url() },
    { title: props.item.name, href: show(props.item.id).url },
    { title: 'Edit', href: edit(props.item.id).url },
]);

const gstApplicable = ref(Boolean(props.item.taxDetail?.gst_applicable));
const priceInclusiveOfTax = ref(
    Boolean(props.item.taxDetail?.price_inclusive_of_tax),
);
const batchEnabled = ref(Boolean(props.item.inventory?.batch_enabled));
const expiryDateTracking = ref(
    Boolean(props.item.inventory?.expiry_date_tracking),
);
const serialNumberTracking = ref(
    Boolean(props.item.inventory?.serial_number_tracking),
);
const eInvoiceApplicable = ref(
    Boolean(props.item.compliance?.e_invoice_applicable),
);
const eWayBillApplicable = ref(
    Boolean(props.item.compliance?.e_way_bill_applicable),
);
const description = ref(props.item.description ?? '');
const basicOpen = ref(true);
const taxOpen = ref(false);
const pricingOpen = ref(false);
const unitOpen = ref(false);
const inventoryOpen = ref(false);
const advancedOpen = ref(false);

const primaryUnitOptions: Record<string, string> = {
    Nos: 'Nos',
    'Piece (Pcs)': 'Piece (Pcs)',
    Box: 'Box',
    Pack: 'Pack',
    Dozen: 'Dozen',
    Pair: 'Pair',
    Set: 'Set',
    Roll: 'Roll',
    Bundle: 'Bundle',
    Carton: 'Carton',
    Kg: 'Kg',
    Gram: 'Gram',
    Quintal: 'Quintal',
    Ton: 'Ton',
    Litre: 'Litre',
    ML: 'ML',
    Feet: 'Feet',
    Meter: 'Meter',
    Inch: 'Inch',
    Yard: 'Yard',
    'Sq Ft': 'Sq Ft',
    'Sq Meter': 'Sq Meter',
};

const itemTypeOptions: Record<string, string> = {
    goods: 'Goods',
    service: 'Service',
    raw_material: 'Raw Material',
    finished_goods: 'Finished Goods',
};

const statusOptions: Record<string, string> = {
    active: 'Active',
    inactive: 'Inactive',
};

const page = usePage<{ categories?: { id: number; name: string }[] }>();
const categoryModalOpen = ref(false);
const newCategoryName = ref('');
const serverCategories = page.props.categories ?? [];
const itemCategory = props.item.category;
const initialCategories = ((): { id: string; name: string }[] => {
    const list = serverCategories.map((c) => ({
        id: String(c.id),
        name: c.name,
    }));
    if (itemCategory && !list.some((c) => c.id === String(itemCategory.id))) {
        list.push({
            id: String(itemCategory.id),
            name: itemCategory.name,
        });
        list.sort((a, b) => a.name.localeCompare(b.name));
    }
    return list;
})();
const categories = ref(initialCategories);
const selectedCategory = ref(itemCategory ? String(itemCategory.id) : '');
const categoryDropdownOpen = ref(false);
const categorySearchQuery = ref('');
const newCategoryNameForSubmit = ref('');
const savingCategory = ref(false);
const categoryError = ref('');

const filteredCategories = computed(() => {
    const q = categorySearchQuery.value.trim().toLowerCase();
    if (!q) return categories.value;
    return categories.value.filter((cat) => cat.name.toLowerCase().includes(q));
});

const selectedCategoryLabel = computed(() => {
    if (!selectedCategory.value) return '';
    const cat = categories.value.find((c) => c.id === selectedCategory.value);
    return cat?.name ?? '';
});

const selectedCategoryIsNew = computed(() => {
    if (!selectedCategory.value) return false;
    const fromServer = page.props.categories ?? [];
    return !fromServer.some((c) => String(c.id) === selectedCategory.value);
});

async function addCategory(): Promise<void> {
    const name = newCategoryName.value.trim();
    if (!name) return;

    savingCategory.value = true;
    categoryError.value = '';

    try {
        const csrfToken =
            document
                .querySelector('meta[name="csrf-token"]')
                ?.getAttribute('content') || '';

        const response = await fetch('/inventory/categories', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({ name }),
        });

        if (!response.ok) {
            const errorData = await response.json();
            const errorMessage =
                errorData.message ||
                errorData.errors?.name?.[0] ||
                'Failed to save category';
            throw new Error(errorMessage);
        }

        const category = await response.json();

        // Add the new category to the list and sort by name
        categories.value.push({ id: String(category.id), name: category.name });
        categories.value.sort((a, b) => a.name.localeCompare(b.name));
        selectedCategory.value = String(category.id);
        newCategoryNameForSubmit.value = '';
        newCategoryName.value = '';
        categoryModalOpen.value = false;

        // Update page props to reflect the new category
        if (page.props.categories) {
            page.props.categories.push({
                id: category.id,
                name: category.name,
            });
            page.props.categories.sort((a, b) => a.name.localeCompare(b.name));
        }
    } catch (error) {
        categoryError.value =
            error instanceof Error ? error.message : 'Failed to save category';
    } finally {
        savingCategory.value = false;
    }
}

function openNewCategoryDialog(): void {
    categoryDropdownOpen.value = false;
    categorySearchQuery.value = '';
    categoryModalOpen.value = true;
}

const categoryDropdownRef = ref<HTMLElement | null>(null);
onClickOutside(categoryDropdownRef, () => {
    categoryDropdownOpen.value = false;
});
</script>

<template>
    <Head title="Edit Item" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto p-6">
            <div
                class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
            >
                <h1 class="text-2xl font-semibold tracking-tight">Edit Item</h1>
                <Link :href="show(props.item.id).url">
                    <Button variant="outline" size="default">Cancel</Button>
                </Link>
            </div>

            <Form
                v-bind="update.form(props.item.id)"
                class="flex flex-col gap-6"
                enctype="multipart/form-data"
                v-slot="{ errors, processing }"
            >
                <AlertError
                    v-if="errors.general"
                    :errors="[errors.general]"
                    title="Error updating item"
                />
                <!-- 1. Basic Item Details -->
                <Collapsible v-model:open="basicOpen">
                    <Card>
                        <CardHeader>
                            <CollapsibleTrigger as-child>
                                <button
                                    type="button"
                                    class="flex w-full items-center justify-between text-left"
                                >
                                    <CardTitle>Basic Item Details</CardTitle>
                                    <ChevronDown
                                        class="size-5 shrink-0 transition-transform duration-200"
                                        :class="{ 'rotate-180': basicOpen }"
                                    />
                                </button>
                            </CollapsibleTrigger>
                        </CardHeader>
                        <CollapsibleContent>
                            <CardContent>
                                <div class="grid gap-4 md:grid-cols-2">
                                    <div class="md:col-span-2">
                                        <Label for="name">Item Name</Label>
                                        <Input
                                            id="name"
                                            name="name"
                                            class="mt-1"
                                            required
                                            maxlength="255"
                                            placeholder="Enter item name"
                                            :default-value="props.item.name"
                                        />
                                        <InputError :message="errors.name" />
                                    </div>
                                    <div>
                                        <Label for="item_code"
                                            >Item Code / SKU</Label
                                        >
                                        <Input
                                            id="item_code"
                                            name="item_code"
                                            class="mt-1"
                                            maxlength="100"
                                            placeholder="e.g. SKU-001"
                                            :default-value="
                                                props.item.item_code ?? ''
                                            "
                                        />
                                        <InputError
                                            :message="errors.item_code"
                                        />
                                    </div>
                                    <div>
                                        <Label for="item_category"
                                            >Item Category</Label
                                        >
                                        <div
                                            ref="categoryDropdownRef"
                                            class="relative mt-1"
                                        >
                                            <input
                                                type="hidden"
                                                name="item_category"
                                                :value="
                                                    selectedCategoryIsNew
                                                        ? ''
                                                        : selectedCategory
                                                "
                                            />
                                            <input
                                                type="hidden"
                                                name="new_category_name"
                                                :value="
                                                    selectedCategoryIsNew
                                                        ? selectedCategoryLabel
                                                        : ''
                                                "
                                            />
                                            <button
                                                id="item_category"
                                                type="button"
                                                aria-haspopup="listbox"
                                                :aria-expanded="
                                                    categoryDropdownOpen
                                                "
                                                class="flex h-9 w-full items-center justify-between gap-2 rounded-md border border-input bg-transparent px-3 py-1 text-left text-base shadow-xs focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50 focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-50 md:text-sm"
                                                @click="
                                                    categoryDropdownOpen =
                                                        !categoryDropdownOpen
                                                "
                                            >
                                                <span
                                                    class="min-w-0 flex-1 truncate"
                                                    :class="{
                                                        'text-muted-foreground':
                                                            !selectedCategoryLabel,
                                                    }"
                                                >
                                                    {{
                                                        selectedCategoryLabel ||
                                                        'Select Category'
                                                    }}
                                                </span>
                                                <ChevronDown
                                                    class="size-4 shrink-0 text-muted-foreground transition-transform"
                                                    :class="{
                                                        'rotate-180':
                                                            categoryDropdownOpen,
                                                    }"
                                                />
                                            </button>
                                            <Transition
                                                enter-active-class="animate-in fade-in-0 zoom-in-95"
                                                leave-active-class="animate-out fade-out-0 zoom-out-95"
                                            >
                                                <div
                                                    v-show="
                                                        categoryDropdownOpen
                                                    "
                                                    class="absolute top-full z-50 mt-1 flex max-h-72 w-full flex-col overflow-hidden rounded-md border border-input bg-popover text-popover-foreground shadow-md"
                                                >
                                                    <div
                                                        class="flex shrink-0 items-center gap-2 border-b border-input bg-muted/30 px-2 py-1.5"
                                                    >
                                                        <Search
                                                            class="size-4 shrink-0 text-muted-foreground"
                                                        />
                                                        <input
                                                            v-model="
                                                                categorySearchQuery
                                                            "
                                                            type="text"
                                                            placeholder="Search"
                                                            class="h-8 min-w-0 flex-1 rounded border-0 bg-transparent px-1 text-sm outline-none placeholder:text-muted-foreground focus-visible:ring-0"
                                                            @keydown.stop
                                                        />
                                                    </div>
                                                    <div
                                                        class="min-h-0 flex-1 overflow-y-auto p-1"
                                                    >
                                                        <button
                                                            v-for="cat in filteredCategories"
                                                            :key="cat.id"
                                                            type="button"
                                                            role="option"
                                                            :aria-selected="
                                                                selectedCategory ===
                                                                cat.id
                                                            "
                                                            class="w-full rounded-md px-2 py-1.5 text-left text-sm outline-none hover:bg-accent focus:bg-accent"
                                                            :class="{
                                                                'bg-primary text-primary-foreground hover:bg-primary/90 focus:bg-primary/90':
                                                                    selectedCategory ===
                                                                    cat.id,
                                                            }"
                                                            @click="
                                                                selectedCategory =
                                                                    cat.id;
                                                                categoryDropdownOpen = false;
                                                            "
                                                        >
                                                            {{ cat.name }}
                                                        </button>
                                                        <button
                                                            type="button"
                                                            class="flex w-full items-center gap-2 rounded-md px-2 py-1.5 text-left text-sm text-primary outline-none hover:bg-accent focus:bg-accent"
                                                            @click="
                                                                openNewCategoryDialog
                                                            "
                                                        >
                                                            <Plus
                                                                class="size-4 shrink-0"
                                                            />
                                                            New Category
                                                        </button>
                                                    </div>
                                                </div>
                                            </Transition>
                                        </div>
                                        <Dialog
                                            v-model:open="categoryModalOpen"
                                        >
                                            <DialogContent>
                                                <DialogHeader>
                                                    <DialogTitle
                                                        >Add
                                                        Category</DialogTitle
                                                    >
                                                </DialogHeader>
                                                <div class="grid gap-4 py-4">
                                                    <div class="grid gap-2">
                                                        <Label
                                                            for="new_category"
                                                            >Category
                                                            Name</Label
                                                        >
                                                        <Input
                                                            id="new_category"
                                                            v-model="
                                                                newCategoryName
                                                            "
                                                            placeholder="Enter category name"
                                                            :disabled="
                                                                savingCategory
                                                            "
                                                            @keydown.enter.prevent="
                                                                addCategory
                                                            "
                                                        />
                                                        <InputError
                                                            v-if="categoryError"
                                                            :message="
                                                                categoryError
                                                            "
                                                        />
                                                    </div>
                                                </div>
                                                <DialogFooter>
                                                    <DialogClose as-child>
                                                        <Button
                                                            type="button"
                                                            variant="outline"
                                                            :disabled="
                                                                savingCategory
                                                            "
                                                            >Cancel</Button
                                                        >
                                                    </DialogClose>
                                                    <Button
                                                        type="button"
                                                        :disabled="
                                                            !newCategoryName.trim() ||
                                                            savingCategory
                                                        "
                                                        @click="addCategory"
                                                    >
                                                        <Spinner
                                                            v-if="
                                                                savingCategory
                                                            "
                                                        />
                                                        {{
                                                            savingCategory
                                                                ? 'Saving...'
                                                                : 'Add Category'
                                                        }}
                                                    </Button>
                                                </DialogFooter>
                                            </DialogContent>
                                        </Dialog>
                                        <InputError
                                            :message="errors.item_category"
                                        />
                                    </div>
                                    <div>
                                        <Label for="sub_category"
                                            >Sub-Category</Label
                                        >
                                        <Input
                                            id="sub_category"
                                            name="sub_category"
                                            class="mt-1"
                                            maxlength="100"
                                            placeholder="Sub-category"
                                            :default-value="
                                                props.item.sub_category ?? ''
                                            "
                                        />
                                    </div>
                                    <div>
                                        <Label for="brand">Brand</Label>
                                        <Input
                                            id="brand"
                                            name="brand"
                                            class="mt-1"
                                            maxlength="100"
                                            placeholder="Brand"
                                            :default-value="
                                                props.item.brand ?? ''
                                            "
                                        />
                                    </div>
                                    <div>
                                        <Label for="model_no">Model No</Label>
                                        <Input
                                            id="model_no"
                                            name="model_no"
                                            class="mt-1"
                                            maxlength="100"
                                            placeholder="Model number"
                                            :default-value="
                                                props.item.model_no ?? ''
                                            "
                                        />
                                    </div>
                                    <div class="md:col-span-2">
                                        <Label for="description"
                                            >Description</Label
                                        >
                                        <textarea
                                            id="description"
                                            v-model="description"
                                            name="description"
                                            rows="3"
                                            class="mt-1 flex h-20 w-full rounded-md border border-input bg-transparent px-3 py-2 text-base shadow-xs placeholder:text-muted-foreground focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50 focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-50 md:text-sm"
                                            placeholder="Item description"
                                        />
                                        <InputError
                                            :message="errors.description"
                                        />
                                    </div>
                                </div>
                            </CardContent>
                        </CollapsibleContent>
                    </Card>
                </Collapsible>

                <!-- 2. Tax & GST Details -->
                <Collapsible v-model:open="taxOpen">
                    <Card>
                        <CardHeader>
                            <CollapsibleTrigger as-child>
                                <button
                                    type="button"
                                    class="flex w-full items-center justify-between text-left"
                                >
                                    <CardTitle
                                        >Tax & GST Details (India
                                        Specific)</CardTitle
                                    >
                                    <ChevronDown
                                        class="size-5 shrink-0 transition-transform duration-200"
                                        :class="{ 'rotate-180': taxOpen }"
                                    />
                                </button>
                            </CollapsibleTrigger>
                        </CardHeader>
                        <CollapsibleContent>
                            <CardContent>
                                <div class="grid gap-4 md:grid-cols-2">
                                    <div class="flex items-center gap-2">
                                        <input
                                            type="hidden"
                                            name="gst_applicable"
                                            :value="gstApplicable ? '1' : '0'"
                                        />
                                        <Checkbox
                                            id="gst_applicable"
                                            v-model="gstApplicable"
                                        />
                                        <Label
                                            for="gst_applicable"
                                            class="cursor-pointer font-normal"
                                            >GST Applicable</Label
                                        >
                                    </div>
                                    <template v-if="gstApplicable">
                                        <div>
                                            <Label for="hsn_sac_code"
                                                >HSN / SAC Code</Label
                                            >
                                            <Input
                                                id="hsn_sac_code"
                                                name="hsn_sac_code"
                                                class="mt-1"
                                                maxlength="50"
                                                placeholder="e.g. 9983"
                                                :default-value="
                                                    (props.item.taxDetail
                                                        ?.hsn_sac_code as string) ??
                                                    ''
                                                "
                                            />
                                            <InputError
                                                :message="errors.hsn_sac_code"
                                            />
                                        </div>
                                        <div>
                                            <Label for="gst_rate"
                                                >GST Rate (%)</Label
                                            >
                                            <Input
                                                id="gst_rate"
                                                name="gst_rate"
                                                type="number"
                                                class="mt-1"
                                                min="0"
                                                max="100"
                                                step="0.01"
                                                placeholder="0"
                                                :default-value="
                                                    String(
                                                        props.item.taxDetail
                                                            ?.gst_rate ?? '',
                                                    )
                                                "
                                            />
                                            <InputError
                                                :message="errors.gst_rate"
                                            />
                                        </div>
                                        <div>
                                            <Label for="cgst_rate"
                                                >CGST Rate (%)</Label
                                            >
                                            <Input
                                                id="cgst_rate"
                                                name="cgst_rate"
                                                type="number"
                                                class="mt-1"
                                                min="0"
                                                max="100"
                                                step="0.01"
                                                placeholder="0"
                                                :default-value="
                                                    String(
                                                        props.item.taxDetail
                                                            ?.cgst_rate ?? '',
                                                    )
                                                "
                                            />
                                        </div>
                                        <div>
                                            <Label for="sgst_rate"
                                                >SGST Rate (%)</Label
                                            >
                                            <Input
                                                id="sgst_rate"
                                                name="sgst_rate"
                                                type="number"
                                                class="mt-1"
                                                min="0"
                                                max="100"
                                                step="0.01"
                                                placeholder="0"
                                                :default-value="
                                                    String(
                                                        props.item.taxDetail
                                                            ?.sgst_rate ?? '',
                                                    )
                                                "
                                            />
                                        </div>
                                        <div>
                                            <Label for="igst_rate"
                                                >IGST Rate (%)</Label
                                            >
                                            <Input
                                                id="igst_rate"
                                                name="igst_rate"
                                                type="number"
                                                class="mt-1"
                                                min="0"
                                                max="100"
                                                step="0.01"
                                                placeholder="0"
                                                :default-value="
                                                    String(
                                                        props.item.taxDetail
                                                            ?.igst_rate ?? '',
                                                    )
                                                "
                                            />
                                        </div>
                                        <div>
                                            <Label for="cess_rate"
                                                >Cess Rate (%)</Label
                                            >
                                            <Input
                                                id="cess_rate"
                                                name="cess_rate"
                                                type="number"
                                                class="mt-1"
                                                min="0"
                                                max="100"
                                                step="0.01"
                                                placeholder="0"
                                                :default-value="
                                                    String(
                                                        props.item.taxDetail
                                                            ?.cess_rate ?? '',
                                                    )
                                                "
                                            />
                                        </div>
                                    </template>
                                </div>
                            </CardContent>
                        </CollapsibleContent>
                    </Card>
                </Collapsible>

                <!-- 3. Pricing Details -->
                <Collapsible v-model:open="pricingOpen">
                    <Card>
                        <CardHeader>
                            <CollapsibleTrigger as-child>
                                <button
                                    type="button"
                                    class="flex w-full items-center justify-between text-left"
                                >
                                    <CardTitle>Pricing Details</CardTitle>
                                    <ChevronDown
                                        class="size-5 shrink-0 transition-transform duration-200"
                                        :class="{ 'rotate-180': pricingOpen }"
                                    />
                                </button>
                            </CollapsibleTrigger>
                        </CardHeader>
                        <CollapsibleContent>
                            <CardContent>
                                <div class="grid gap-4 md:grid-cols-2">
                                    <div>
                                        <Label for="purchase_price"
                                            >Purchase Price (₹)</Label
                                        >
                                        <Input
                                            id="purchase_price"
                                            name="purchase_price"
                                            type="number"
                                            class="mt-1"
                                            min="0"
                                            step="0.01"
                                            placeholder="0"
                                            :default-value="
                                                String(
                                                    props.item.pricing
                                                        ?.purchase_price ?? '',
                                                )
                                            "
                                        />
                                        <InputError
                                            :message="errors.purchase_price"
                                        />
                                    </div>
                                    <div>
                                        <Label for="sale_price"
                                            >Sale Price (₹)</Label
                                        >
                                        <Input
                                            id="sale_price"
                                            name="sale_price"
                                            type="number"
                                            class="mt-1"
                                            min="0"
                                            step="0.01"
                                            placeholder="0"
                                            :default-value="
                                                String(
                                                    props.item.pricing
                                                        ?.sale_price ?? '',
                                                )
                                            "
                                        />
                                        <InputError
                                            :message="errors.sale_price"
                                        />
                                    </div>
                                    <div>
                                        <Label for="mrp">MRP (₹)</Label>
                                        <Input
                                            id="mrp"
                                            name="mrp"
                                            type="number"
                                            class="mt-1"
                                            min="0"
                                            step="0.01"
                                            placeholder="0"
                                            :default-value="
                                                String(
                                                    props.item.pricing?.mrp ??
                                                        '',
                                                )
                                            "
                                        />
                                    </div>
                                    <div>
                                        <Label for="minimum_sale_price"
                                            >Minimum Sale Price (₹)</Label
                                        >
                                        <Input
                                            id="minimum_sale_price"
                                            name="minimum_sale_price"
                                            type="number"
                                            class="mt-1"
                                            min="0"
                                            step="0.01"
                                            placeholder="0"
                                            :default-value="
                                                String(
                                                    props.item.pricing
                                                        ?.minimum_sale_price ??
                                                        '',
                                                )
                                            "
                                        />
                                    </div>
                                    <div>
                                        <Label for="discount_percent_allowed"
                                            >Discount % Allowed</Label
                                        >
                                        <Input
                                            id="discount_percent_allowed"
                                            name="discount_percent_allowed"
                                            type="number"
                                            class="mt-1"
                                            min="0"
                                            max="100"
                                            step="0.01"
                                            placeholder="0"
                                            :default-value="
                                                String(
                                                    props.item.pricing
                                                        ?.discount_percent_allowed ??
                                                        '',
                                                )
                                            "
                                        />
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <input
                                            type="hidden"
                                            name="price_inclusive_of_tax"
                                            :value="
                                                priceInclusiveOfTax ? '1' : '0'
                                            "
                                        />
                                        <Checkbox
                                            id="price_inclusive_of_tax"
                                            v-model="priceInclusiveOfTax"
                                        />
                                        <Label
                                            for="price_inclusive_of_tax"
                                            class="cursor-pointer font-normal"
                                        >
                                            Price Inclusive of Tax
                                        </Label>
                                    </div>
                                    <p
                                        class="text-sm text-muted-foreground md:col-span-2"
                                    >
                                        Toggle ON for Inclusive, OFF for
                                        Exclusive
                                    </p>
                                    <div>
                                        <Label for="retail_price"
                                            >Retail Price (₹)</Label
                                        >
                                        <Input
                                            id="retail_price"
                                            name="retail_price"
                                            type="number"
                                            class="mt-1"
                                            min="0"
                                            step="0.01"
                                            placeholder="0"
                                            :default-value="
                                                String(
                                                    props.item.pricing
                                                        ?.retail_price ?? '',
                                                )
                                            "
                                        />
                                    </div>
                                    <div>
                                        <Label for="wholesale_price"
                                            >Wholesale Price (₹)</Label
                                        >
                                        <Input
                                            id="wholesale_price"
                                            name="wholesale_price"
                                            type="number"
                                            class="mt-1"
                                            min="0"
                                            step="0.01"
                                            placeholder="0"
                                            :default-value="
                                                String(
                                                    props.item.pricing
                                                        ?.wholesale_price ?? '',
                                                )
                                            "
                                        />
                                    </div>
                                    <div>
                                        <Label for="dealer_price"
                                            >Dealer Price (₹)</Label
                                        >
                                        <Input
                                            id="dealer_price"
                                            name="dealer_price"
                                            type="number"
                                            class="mt-1"
                                            min="0"
                                            step="0.01"
                                            placeholder="0"
                                            :default-value="
                                                String(
                                                    props.item.pricing
                                                        ?.dealer_price ?? '',
                                                )
                                            "
                                        />
                                    </div>
                                </div>
                            </CardContent>
                        </CollapsibleContent>
                    </Card>
                </Collapsible>

                <!-- 4. Unit & Quantity -->
                <Collapsible v-model:open="unitOpen">
                    <Card>
                        <CardHeader>
                            <CollapsibleTrigger as-child>
                                <button
                                    type="button"
                                    class="flex w-full items-center justify-between text-left"
                                >
                                    <CardTitle>Unit & Quantity</CardTitle>
                                    <ChevronDown
                                        class="size-5 shrink-0 transition-transform duration-200"
                                        :class="{ 'rotate-180': unitOpen }"
                                    />
                                </button>
                            </CollapsibleTrigger>
                        </CardHeader>
                        <CollapsibleContent>
                            <CardContent>
                                <div class="grid gap-4 md:grid-cols-2">
                                    <div>
                                        <Label for="primary_unit"
                                            >Primary Unit</Label
                                        >
                                        <select
                                            id="primary_unit"
                                            name="primary_unit"
                                            :value="
                                                props.item.inventory
                                                    ?.primary_unit ?? ''
                                            "
                                            class="mt-1 flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-base shadow-xs focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50 focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-50 md:text-sm"
                                        >
                                            <option
                                                v-for="(
                                                    label, value
                                                ) in primaryUnitOptions"
                                                :key="value"
                                                :value="value"
                                            >
                                                {{ label }}
                                            </option>
                                        </select>
                                        <InputError
                                            :message="errors.primary_unit"
                                        />
                                    </div>
                                    <div>
                                        <Label for="conversion_factor"
                                            >Conversion Factor</Label
                                        >
                                        <Input
                                            id="conversion_factor"
                                            name="conversion_factor"
                                            type="number"
                                            class="mt-1"
                                            min="0"
                                            step="0.0001"
                                            placeholder="e.g. 1 Box = 10 pcs"
                                            :default-value="
                                                String(
                                                    props.item.inventory
                                                        ?.conversion_factor ??
                                                        '',
                                                )
                                            "
                                        />
                                        <p
                                            class="mt-1 text-xs text-muted-foreground"
                                        >
                                            e.g., 1 Box = 10 pcs
                                        </p>
                                    </div>
                                    <div>
                                        <Label for="opening_stock_quantity"
                                            >Opening Stock Quantity</Label
                                        >
                                        <Input
                                            id="opening_stock_quantity"
                                            name="opening_stock_quantity"
                                            type="number"
                                            class="mt-1"
                                            min="0"
                                            step="0.0001"
                                            placeholder="0"
                                            :default-value="
                                                String(
                                                    props.item.inventory
                                                        ?.opening_stock_quantity ??
                                                        '',
                                                )
                                            "
                                        />
                                    </div>
                                    <div>
                                        <Label for="opening_stock_value"
                                            >Opening Stock Value (₹)</Label
                                        >
                                        <Input
                                            id="opening_stock_value"
                                            name="opening_stock_value"
                                            type="number"
                                            class="mt-1"
                                            min="0"
                                            step="0.01"
                                            placeholder="0"
                                            :default-value="
                                                String(
                                                    props.item.inventory
                                                        ?.opening_stock_value ??
                                                        '',
                                                )
                                            "
                                        />
                                    </div>
                                    <div>
                                        <Label for="stock_quantity"
                                            >Current Stock Quantity</Label
                                        >
                                        <Input
                                            id="stock_quantity"
                                            name="stock_quantity"
                                            type="number"
                                            class="mt-1"
                                            min="0"
                                            placeholder="0"
                                            :default-value="
                                                String(
                                                    props.item.inventory
                                                        ?.stock_quantity ?? '',
                                                )
                                            "
                                        />
                                    </div>
                                    <div>
                                        <Label for="reorder_level"
                                            >Reorder Level</Label
                                        >
                                        <Input
                                            id="reorder_level"
                                            name="reorder_level"
                                            type="number"
                                            class="mt-1"
                                            min="0"
                                            step="0.0001"
                                            placeholder="0"
                                            :default-value="
                                                String(
                                                    props.item.inventory
                                                        ?.reorder_level ?? '',
                                                )
                                            "
                                        />
                                    </div>
                                    <div>
                                        <Label for="minimum_stock_level"
                                            >Minimum Stock Level</Label
                                        >
                                        <Input
                                            id="minimum_stock_level"
                                            name="minimum_stock_level"
                                            type="number"
                                            class="mt-1"
                                            min="0"
                                            step="0.0001"
                                            placeholder="0"
                                            :default-value="
                                                String(
                                                    props.item.inventory
                                                        ?.minimum_stock_level ??
                                                        '',
                                                )
                                            "
                                        />
                                    </div>
                                </div>
                            </CardContent>
                        </CollapsibleContent>
                    </Card>
                </Collapsible>

                <!-- 5. Inventory Controls -->
                <Collapsible v-model:open="inventoryOpen">
                    <Card>
                        <CardHeader>
                            <CollapsibleTrigger as-child>
                                <button
                                    type="button"
                                    class="flex w-full items-center justify-between text-left"
                                >
                                    <CardTitle>Inventory Controls</CardTitle>
                                    <ChevronDown
                                        class="size-5 shrink-0 transition-transform duration-200"
                                        :class="{ 'rotate-180': inventoryOpen }"
                                    />
                                </button>
                            </CollapsibleTrigger>
                        </CardHeader>
                        <CollapsibleContent>
                            <CardContent>
                                <div class="grid gap-4 md:grid-cols-2">
                                    <div class="flex items-center gap-2">
                                        <input
                                            type="hidden"
                                            name="batch_enabled"
                                            :value="batchEnabled ? '1' : '0'"
                                        />
                                        <Checkbox
                                            id="batch_enabled"
                                            v-model="batchEnabled"
                                        />
                                        <Label
                                            for="batch_enabled"
                                            class="cursor-pointer font-normal"
                                            >Batch Enabled</Label
                                        >
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <input
                                            type="hidden"
                                            name="expiry_date_tracking"
                                            :value="
                                                expiryDateTracking ? '1' : '0'
                                            "
                                        />
                                        <Checkbox
                                            id="expiry_date_tracking"
                                            v-model="expiryDateTracking"
                                        />
                                        <Label
                                            for="expiry_date_tracking"
                                            class="cursor-pointer font-normal"
                                            >Expiry Date Tracking</Label
                                        >
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <input
                                            type="hidden"
                                            name="serial_number_tracking"
                                            :value="
                                                serialNumberTracking ? '1' : '0'
                                            "
                                        />
                                        <Checkbox
                                            id="serial_number_tracking"
                                            v-model="serialNumberTracking"
                                        />
                                        <Label
                                            for="serial_number_tracking"
                                            class="cursor-pointer font-normal"
                                            >Serial Number Tracking</Label
                                        >
                                    </div>
                                    <div>
                                        <Label for="godown_warehouse"
                                            >Godown / Warehouse</Label
                                        >
                                        <Input
                                            id="godown_warehouse"
                                            name="godown_warehouse"
                                            class="mt-1"
                                            maxlength="100"
                                            placeholder="Warehouse location"
                                            :default-value="
                                                (props.item.inventory
                                                    ?.godown_warehouse as string) ??
                                                ''
                                            "
                                        />
                                    </div>
                                    <div>
                                        <Label for="item_type"
                                            >Item Type
                                            <span class="text-red-500"
                                                >*</span
                                            ></Label
                                        >
                                        <select
                                            id="item_type"
                                            name="item_type"
                                            required
                                            :value="props.item.item_type"
                                            class="mt-1 flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-base shadow-xs focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50 focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-50 md:text-sm"
                                        >
                                            <option value="">
                                                Select Item Type
                                            </option>
                                            <option
                                                v-for="(
                                                    label, value
                                                ) in itemTypeOptions"
                                                :key="value"
                                                :value="value"
                                            >
                                                {{ label }}
                                            </option>
                                        </select>
                                        <InputError
                                            :message="errors.item_type"
                                        />
                                    </div>
                                    <div>
                                        <Label for="status"
                                            >Status
                                            <span class="text-red-500"
                                                >*</span
                                            ></Label
                                        >
                                        <select
                                            id="status"
                                            name="status"
                                            required
                                            :value="props.item.status"
                                            class="mt-1 flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-base shadow-xs focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50 focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-50 md:text-sm"
                                        >
                                            <option value="">
                                                Select Status
                                            </option>
                                            <option
                                                v-for="(
                                                    label, value
                                                ) in statusOptions"
                                                :key="value"
                                                :value="value"
                                            >
                                                {{ label }}
                                            </option>
                                        </select>
                                        <InputError :message="errors.status" />
                                    </div>
                                </div>
                            </CardContent>
                        </CollapsibleContent>
                    </Card>
                </Collapsible>

                <!-- 6. Advanced Fields -->
                <Collapsible v-model:open="advancedOpen">
                    <Card>
                        <CardHeader>
                            <CollapsibleTrigger as-child>
                                <button
                                    type="button"
                                    class="flex w-full items-center justify-between text-left"
                                >
                                    <CardTitle>Advanced Fields</CardTitle>
                                    <ChevronDown
                                        class="size-5 shrink-0 transition-transform duration-200"
                                        :class="{ 'rotate-180': advancedOpen }"
                                    />
                                </button>
                            </CollapsibleTrigger>
                        </CardHeader>
                        <CollapsibleContent>
                            <CardContent>
                                <div class="grid gap-4 md:grid-cols-2">
                                    <div>
                                        <Label for="item_image"
                                            >Item Image</Label
                                        >
                                        <Input
                                            id="item_image"
                                            name="item_image"
                                            type="file"
                                            accept="image/*"
                                            class="mt-1"
                                        />
                                        <p
                                            class="mt-1 text-xs text-muted-foreground"
                                        >
                                            Max 2MB. Formats: JPG, PNG, WebP
                                        </p>
                                        <InputError
                                            :message="errors.item_image"
                                        />
                                    </div>
                                    <div
                                        class="flex items-center gap-2 md:content-end"
                                    >
                                        <input
                                            type="hidden"
                                            name="e_invoice_applicable"
                                            :value="
                                                eInvoiceApplicable ? '1' : '0'
                                            "
                                        />
                                        <Checkbox
                                            id="e_invoice_applicable"
                                            v-model="eInvoiceApplicable"
                                        />
                                        <Label
                                            for="e_invoice_applicable"
                                            class="cursor-pointer font-normal"
                                            >E-Invoice Applicable</Label
                                        >
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <input
                                            type="hidden"
                                            name="e_way_bill_applicable"
                                            :value="
                                                eWayBillApplicable ? '1' : '0'
                                            "
                                        />
                                        <Checkbox
                                            id="e_way_bill_applicable"
                                            v-model="eWayBillApplicable"
                                        />
                                        <Label
                                            for="e_way_bill_applicable"
                                            class="cursor-pointer font-normal"
                                            >E-Way Bill Applicable</Label
                                        >
                                    </div>
                                </div>
                            </CardContent>
                        </CollapsibleContent>
                    </Card>
                </Collapsible>

                <div class="flex gap-4">
                    <Button type="submit" :disabled="processing">
                        <Spinner v-if="processing" />
                        Update Item
                    </Button>
                    <Link :href="show(props.item.id).url">
                        <Button type="button" variant="outline">Cancel</Button>
                    </Link>
                </div>
            </Form>
        </div>
    </AppLayout>
</template>
