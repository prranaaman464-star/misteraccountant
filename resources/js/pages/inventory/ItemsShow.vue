<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ChevronDown, Package } from 'lucide-vue-next';
import { ref } from 'vue';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import {
    Collapsible,
    CollapsibleContent,
    CollapsibleTrigger,
} from '@/components/ui/collapsible';
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
    pricing: {
        purchase_price: number | null;
        sale_price: number | null;
        mrp: number | null;
        minimum_sale_price: number | null;
        discount_percent_allowed: number | null;
        retail_price: number | null;
        wholesale_price: number | null;
        dealer_price: number | null;
    } | null;
    inventory: {
        primary_unit: string | null;
        conversion_factor: number | null;
        opening_stock_quantity: number | null;
        opening_stock_value: number | null;
        stock_quantity: number | null;
        reorder_level: number | null;
        minimum_stock_level: number | null;
        batch_enabled: boolean;
        expiry_date_tracking: boolean;
        serial_number_tracking: boolean;
        godown_warehouse: string | null;
    } | null;
    taxDetail: {
        gst_applicable: boolean;
        hsn_sac_code: string | null;
        gst_rate: number | null;
        cgst_rate: number | null;
        sgst_rate: number | null;
        igst_rate: number | null;
        cess_rate: number | null;
        price_inclusive_of_tax: boolean;
    } | null;
    compliance: {
        e_invoice_applicable: boolean;
        e_way_bill_applicable: boolean;
    } | null;
};

defineProps<{
    item: Item;
}>();

const basicOpen = ref(true);
const taxOpen = ref(true);
const pricingOpen = ref(true);
const unitOpen = ref(true);
const inventoryOpen = ref(true);
const advancedOpen = ref(true);

const breadcrumbs = (item: Item): BreadcrumbItem[] => [
    { title: 'Home', href: dashboard().url },
    { title: 'Items', href: inventory.items.url() },
    { title: item.name, href: show(item.id).url },
];

function formatPrice(value: number | null | undefined): string {
    if (value == null) return '–';
    return `₹${Number(value).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
}

function formatNumber(value: number | null | undefined): string {
    if (value == null) return '–';
    return String(value);
}

function getImageUrl(imagePath: string | null): string | null {
    if (!imagePath) return null;
    return `/storage/${imagePath}`;
}

const itemTypeLabels: Record<string, string> = {
    goods: 'Goods',
    service: 'Service',
    raw_material: 'Raw Material',
    finished_goods: 'Finished Goods',
};

const statusLabels: Record<string, string> = {
    active: 'Active',
    inactive: 'Inactive',
};
</script>

<template>
    <Head :title="`View: ${item.name}`" />

    <AppLayout :breadcrumbs="breadcrumbs(item)">
        <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto p-6">
            <div
                class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
            >
                <div class="flex items-center gap-4">
                    <Avatar class="size-14 shrink-0">
                        <AvatarImage
                            v-if="getImageUrl(item.item_image)"
                            :src="getImageUrl(item.item_image)!"
                            :alt="item.name"
                            class="object-cover"
                        />
                        <AvatarFallback
                            class="rounded-lg bg-muted text-lg font-medium"
                        >
                            <Package class="size-7" />
                        </AvatarFallback>
                    </Avatar>
                    <div>
                        <h1 class="text-2xl font-semibold tracking-tight">
                            {{ item.name }}
                        </h1>
                        <p
                            v-if="item.item_code"
                            class="text-sm text-muted-foreground"
                        >
                            {{ item.item_code }}
                        </p>
                        <div class="mt-1 flex gap-2">
                            <span
                                class="inline-flex items-center rounded-full bg-muted px-2.5 py-0.5 text-xs font-medium"
                            >
                                {{ itemTypeLabels[item.item_type] ?? item.item_type }}
                            </span>
                            <span
                                class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
                                :class="
                                    item.status === 'active'
                                        ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400'
                                        : 'bg-muted text-muted-foreground'
                                "
                            >
                                {{ statusLabels[item.status] ?? item.status }}
                            </span>
                        </div>
                    </div>
                </div>
                <Link :href="edit(item.id).url">
                    <Button variant="default" size="default">Edit Item</Button>
                </Link>
            </div>

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
                                <div>
                                    <p
                                        class="text-xs font-medium text-muted-foreground"
                                    >
                                        Item Name
                                    </p>
                                    <p class="mt-1">{{ item.name }}</p>
                                </div>
                                <div>
                                    <p
                                        class="text-xs font-medium text-muted-foreground"
                                    >
                                        Item Code / SKU
                                    </p>
                                    <p class="mt-1">
                                        {{ item.item_code || '–' }}
                                    </p>
                                </div>
                                <div>
                                    <p
                                        class="text-xs font-medium text-muted-foreground"
                                    >
                                        Category
                                    </p>
                                    <p class="mt-1">
                                        {{ item.category?.name ?? '–' }}
                                    </p>
                                </div>
                                <div>
                                    <p
                                        class="text-xs font-medium text-muted-foreground"
                                    >
                                        Sub-Category
                                    </p>
                                    <p class="mt-1">
                                        {{ item.sub_category || '–' }}
                                    </p>
                                </div>
                                <div>
                                    <p
                                        class="text-xs font-medium text-muted-foreground"
                                    >
                                        Brand
                                    </p>
                                    <p class="mt-1">
                                        {{ item.brand || '–' }}
                                    </p>
                                </div>
                                <div>
                                    <p
                                        class="text-xs font-medium text-muted-foreground"
                                    >
                                        Model No
                                    </p>
                                    <p class="mt-1">
                                        {{ item.model_no || '–' }}
                                    </p>
                                </div>
                                <div class="md:col-span-2">
                                    <p
                                        class="text-xs font-medium text-muted-foreground"
                                    >
                                        Description
                                    </p>
                                    <p class="mt-1 whitespace-pre-wrap">
                                        {{ item.description || '–' }}
                                    </p>
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
                                <CardTitle>
                                    Tax & GST Details (India Specific)
                                </CardTitle>
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
                                    <p
                                        class="text-xs font-medium text-muted-foreground"
                                    >
                                        GST Applicable
                                    </p>
                                    <span>{{
                                        item.taxDetail?.gst_applicable
                                            ? 'Yes'
                                            : 'No'
                                    }}</span>
                                </div>
                                <template
                                    v-if="item.taxDetail?.gst_applicable"
                                >
                                    <div>
                                        <p
                                            class="text-xs font-medium text-muted-foreground"
                                        >
                                            HSN / SAC Code
                                        </p>
                                        <p class="mt-1">
                                            {{
                                                item.taxDetail?.hsn_sac_code ??
                                                '–'
                                            }}
                                        </p>
                                    </div>
                                    <div>
                                        <p
                                            class="text-xs font-medium text-muted-foreground"
                                        >
                                            GST Rate (%)
                                        </p>
                                        <p class="mt-1">
                                            {{
                                                formatNumber(
                                                    item.taxDetail?.gst_rate,
                                                )
                                            }}
                                        </p>
                                    </div>
                                    <div>
                                        <p
                                            class="text-xs font-medium text-muted-foreground"
                                        >
                                            CGST Rate (%)
                                        </p>
                                        <p class="mt-1">
                                            {{
                                                formatNumber(
                                                    item.taxDetail?.cgst_rate,
                                                )
                                            }}
                                        </p>
                                    </div>
                                    <div>
                                        <p
                                            class="text-xs font-medium text-muted-foreground"
                                        >
                                            SGST Rate (%)
                                        </p>
                                        <p class="mt-1">
                                            {{
                                                formatNumber(
                                                    item.taxDetail?.sgst_rate,
                                                )
                                            }}
                                        </p>
                                    </div>
                                    <div>
                                        <p
                                            class="text-xs font-medium text-muted-foreground"
                                        >
                                            IGST Rate (%)
                                        </p>
                                        <p class="mt-1">
                                            {{
                                                formatNumber(
                                                    item.taxDetail?.igst_rate,
                                                )
                                            }}
                                        </p>
                                    </div>
                                    <div>
                                        <p
                                            class="text-xs font-medium text-muted-foreground"
                                        >
                                            Cess Rate (%)
                                        </p>
                                        <p class="mt-1">
                                            {{
                                                formatNumber(
                                                    item.taxDetail?.cess_rate,
                                                )
                                            }}
                                        </p>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <p
                                            class="text-xs font-medium text-muted-foreground"
                                        >
                                            Price Inclusive of Tax
                                        </p>
                                        <span>{{
                                            item.taxDetail
                                                ?.price_inclusive_of_tax
                                                ? 'Yes'
                                                : 'No'
                                        }}</span>
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
                                    <p
                                        class="text-xs font-medium text-muted-foreground"
                                    >
                                        Purchase Price
                                    </p>
                                    <p class="mt-1 font-medium">
                                        {{
                                            formatPrice(
                                                item.pricing?.purchase_price,
                                            )
                                        }}
                                    </p>
                                </div>
                                <div>
                                    <p
                                        class="text-xs font-medium text-muted-foreground"
                                    >
                                        Sale Price
                                    </p>
                                    <p class="mt-1 font-medium">
                                        {{
                                            formatPrice(
                                                item.pricing?.sale_price,
                                            )
                                        }}
                                    </p>
                                </div>
                                <div>
                                    <p
                                        class="text-xs font-medium text-muted-foreground"
                                    >
                                        MRP
                                    </p>
                                    <p class="mt-1">
                                        {{ formatPrice(item.pricing?.mrp) }}
                                    </p>
                                </div>
                                <div>
                                    <p
                                        class="text-xs font-medium text-muted-foreground"
                                    >
                                        Minimum Sale Price
                                    </p>
                                    <p class="mt-1">
                                        {{
                                            formatPrice(
                                                item.pricing
                                                    ?.minimum_sale_price,
                                            )
                                        }}
                                    </p>
                                </div>
                                <div>
                                    <p
                                        class="text-xs font-medium text-muted-foreground"
                                    >
                                        Discount % Allowed
                                    </p>
                                    <p class="mt-1">
                                        {{
                                            formatNumber(
                                                item.pricing
                                                    ?.discount_percent_allowed,
                                            )
                                        }}
                                    </p>
                                </div>
                                <div>
                                    <p
                                        class="text-xs font-medium text-muted-foreground"
                                    >
                                        Retail Price
                                    </p>
                                    <p class="mt-1">
                                        {{
                                            formatPrice(
                                                item.pricing?.retail_price,
                                            )
                                        }}
                                    </p>
                                </div>
                                <div>
                                    <p
                                        class="text-xs font-medium text-muted-foreground"
                                    >
                                        Wholesale Price
                                    </p>
                                    <p class="mt-1">
                                        {{
                                            formatPrice(
                                                item.pricing?.wholesale_price,
                                            )
                                        }}
                                    </p>
                                </div>
                                <div>
                                    <p
                                        class="text-xs font-medium text-muted-foreground"
                                    >
                                        Dealer Price
                                    </p>
                                    <p class="mt-1">
                                        {{
                                            formatPrice(
                                                item.pricing?.dealer_price,
                                            )
                                        }}
                                    </p>
                                </div>
                            </div>
                        </CardContent>
                    </CollapsibleContent>
                </Card>
            </Collapsible>

            <!-- 4. Unit & Inventory -->
            <Collapsible v-model:open="unitOpen">
                <Card>
                    <CardHeader>
                        <CollapsibleTrigger as-child>
                            <button
                                type="button"
                                class="flex w-full items-center justify-between text-left"
                            >
                                <CardTitle>Unit & Inventory</CardTitle>
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
                                    <p
                                        class="text-xs font-medium text-muted-foreground"
                                    >
                                        Primary Unit
                                    </p>
                                    <p class="mt-1">
                                        {{
                                            item.inventory?.primary_unit ?? '–'
                                        }}
                                    </p>
                                </div>
                                <div>
                                    <p
                                        class="text-xs font-medium text-muted-foreground"
                                    >
                                        Conversion Factor
                                    </p>
                                    <p class="mt-1">
                                        {{
                                            formatNumber(
                                                item.inventory
                                                    ?.conversion_factor,
                                            )
                                        }}
                                    </p>
                                </div>
                                <div>
                                    <p
                                        class="text-xs font-medium text-muted-foreground"
                                    >
                                        Opening Stock Quantity
                                    </p>
                                    <p class="mt-1">
                                        {{
                                            formatNumber(
                                                item.inventory
                                                    ?.opening_stock_quantity,
                                            )
                                        }}
                                    </p>
                                </div>
                                <div>
                                    <p
                                        class="text-xs font-medium text-muted-foreground"
                                    >
                                        Opening Stock Value
                                    </p>
                                    <p class="mt-1">
                                        {{
                                            formatPrice(
                                                item.inventory
                                                    ?.opening_stock_value,
                                            )
                                        }}
                                    </p>
                                </div>
                                <div>
                                    <p
                                        class="text-xs font-medium text-muted-foreground"
                                    >
                                        Current Stock Quantity
                                    </p>
                                    <p class="mt-1 font-medium">
                                        {{
                                            formatNumber(
                                                item.inventory?.stock_quantity,
                                            )
                                        }}
                                    </p>
                                </div>
                                <div>
                                    <p
                                        class="text-xs font-medium text-muted-foreground"
                                    >
                                        Reorder Level
                                    </p>
                                    <p class="mt-1">
                                        {{
                                            formatNumber(
                                                item.inventory?.reorder_level,
                                            )
                                        }}
                                    </p>
                                </div>
                                <div>
                                    <p
                                        class="text-xs font-medium text-muted-foreground"
                                    >
                                        Minimum Stock Level
                                    </p>
                                    <p class="mt-1">
                                        {{
                                            formatNumber(
                                                item.inventory
                                                    ?.minimum_stock_level,
                                            )
                                        }}
                                    </p>
                                </div>
                                <div>
                                    <p
                                        class="text-xs font-medium text-muted-foreground"
                                    >
                                        Godown / Warehouse
                                    </p>
                                    <p class="mt-1">
                                        {{
                                            item.inventory
                                                ?.godown_warehouse ?? '–'
                                        }}
                                    </p>
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
                            <div class="flex flex-wrap gap-6">
                                <div class="flex items-center gap-2">
                                    <span
                                        class="inline-flex size-2 rounded-full"
                                        :class="
                                            item.inventory?.batch_enabled
                                                ? 'bg-emerald-500'
                                                : 'bg-muted'
                                        "
                                    />
                                    <span class="text-sm">Batch Enabled</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span
                                        class="inline-flex size-2 rounded-full"
                                        :class="
                                            item.inventory
                                                ?.expiry_date_tracking
                                                ? 'bg-emerald-500'
                                                : 'bg-muted'
                                        "
                                    />
                                    <span class="text-sm"
                                        >Expiry Date Tracking</span
                                    >
                                </div>
                                <div class="flex items-center gap-2">
                                    <span
                                        class="inline-flex size-2 rounded-full"
                                        :class="
                                            item.inventory
                                                ?.serial_number_tracking
                                                ? 'bg-emerald-500'
                                                : 'bg-muted'
                                        "
                                    />
                                    <span class="text-sm"
                                        >Serial Number Tracking</span
                                    >
                                </div>
                            </div>
                        </CardContent>
                    </CollapsibleContent>
                </Card>
            </Collapsible>

            <!-- 6. Advanced / Compliance -->
            <Collapsible v-model:open="advancedOpen">
                <Card>
                    <CardHeader>
                        <CollapsibleTrigger as-child>
                            <button
                                type="button"
                                class="flex w-full items-center justify-between text-left"
                            >
                                <CardTitle>Compliance</CardTitle>
                                <ChevronDown
                                    class="size-5 shrink-0 transition-transform duration-200"
                                    :class="{ 'rotate-180': advancedOpen }"
                                />
                            </button>
                        </CollapsibleTrigger>
                    </CardHeader>
                    <CollapsibleContent>
                        <CardContent>
                            <div class="flex flex-wrap gap-6">
                                <div class="flex items-center gap-2">
                                    <span
                                        class="inline-flex size-2 rounded-full"
                                        :class="
                                            item.compliance
                                                ?.e_invoice_applicable
                                                ? 'bg-emerald-500'
                                                : 'bg-muted'
                                        "
                                    />
                                    <span class="text-sm"
                                        >E-Invoice Applicable</span
                                    >
                                </div>
                                <div class="flex items-center gap-2">
                                    <span
                                        class="inline-flex size-2 rounded-full"
                                        :class="
                                            item.compliance
                                                ?.e_way_bill_applicable
                                                ? 'bg-emerald-500'
                                                : 'bg-muted'
                                        "
                                    />
                                    <span class="text-sm"
                                        >E-Way Bill Applicable</span
                                    >
                                </div>
                            </div>
                        </CardContent>
                    </CollapsibleContent>
                </Card>
            </Collapsible>

            <div class="flex gap-4">
                <Link :href="edit(item.id).url">
                    <Button variant="default">Edit Item</Button>
                </Link>
                <Link :href="inventory.items.url()">
                    <Button variant="outline">Back to Items</Button>
                </Link>
            </div>
        </div>
    </AppLayout>
</template>
