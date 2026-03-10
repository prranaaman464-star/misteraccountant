<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, ChevronDown, Copy, Eye, Upload, User } from 'lucide-vue-next';
import { ref } from 'vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import {
    Collapsible,
    CollapsibleContent,
    CollapsibleTrigger,
} from '@/components/ui/collapsible';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import type { BreadcrumbItem } from '@/types';

const props = withDefaults(
    defineProps<{
        currencies?: Record<string, string>;
        countries?: string[];
        states?: string[];
        cities?: string[];
    }>(),
    {
        currencies: () => ({}),
        countries: () => [],
        states: () => [],
        cities: () => [],
    },
);

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Home', href: dashboard().url },
    {
        title: 'Customer',
        href: '/sales/clients-and-prospects',
    },
    {
        title: 'Add New Customer',
        href: '/sales/clients-and-prospects/create',
    },
];

const imageInputRef = ref<HTMLInputElement | null>(null);

const billingAddress = ref({
    name: '',
    address_line_1: '',
    address_line_2: '',
    country: '',
    state: '',
    city: '',
    pincode: '',
});

const shippingAddress = ref({
    name: '',
    address_line_1: '',
    address_line_2: '',
    country: '',
    state: '',
    city: '',
    pincode: '',
});

function copyFromBilling(): void {
    shippingAddress.value = { ...billingAddress.value };
}

function triggerImageUpload(): void {
    imageInputRef.value?.click();
}

const basicDetailsOpen = ref(true);
const billingAddressOpen = ref(false);
const shippingAddressOpen = ref(false);
const bankingDetailsOpen = ref(false);
</script>

<template>
    <Head title="Add New Customer" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto p-6">
            <!-- Header: Back + Preview -->
            <div
                class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
            >
                <Link
                    href="/sales/clients-and-prospects"
                    class="inline-flex items-center gap-2 text-sm font-medium text-muted-foreground transition-colors hover:text-foreground"
                >
                    <ArrowLeft class="size-4" />
                    Back to Customers
                </Link>
                <Button variant="outline" size="default" class="rounded-lg">
                    <Eye class="size-4" />
                    Preview
                </Button>
            </div>

            <Form
                action="/sales/clients-and-prospects"
                method="post"
                enctype="multipart/form-data"
                class="flex flex-col gap-6"
            >
                <template #default="{ errors, processing }">
                <Card class="overflow-hidden rounded-xl border border-sidebar-border/70 shadow-sm">
                    <CardHeader class="border-b border-sidebar-border/70 bg-muted/20 px-6 py-5">
                        <CardTitle class="text-xl font-semibold">Add Customer</CardTitle>
                        <p class="mt-1 text-sm text-muted-foreground">
                            Enter customer details below. Required fields are marked with *
                        </p>
                    </CardHeader>
                    <CardContent class="space-y-4 p-6">
                        <!-- Basic Details -->
                        <Collapsible v-model:open="basicDetailsOpen">
                            <div class="rounded-lg border border-sidebar-border/70">
                                <CollapsibleTrigger as-child>
                                    <button
                                        type="button"
                                        class="flex w-full items-center justify-between px-4 py-3 text-left hover:bg-muted/50"
                                    >
                                        <h2 class="text-base font-semibold text-foreground">
                                            Basic Details
                                        </h2>
                                        <ChevronDown
                                            class="size-5 shrink-0 text-muted-foreground transition-transform duration-200"
                                            :class="{
                                                'rotate-180': basicDetailsOpen,
                                            }"
                                        />
                                    </button>
                                </CollapsibleTrigger>
                                <CollapsibleContent>
                                    <div class="space-y-6 border-t border-sidebar-border/70 px-4 pb-4 pt-4">
                                        <!-- Image Upload -->
                            <div class="flex flex-wrap items-start gap-4">
                                <div
                                    class="flex size-24 shrink-0 items-center justify-center rounded-lg bg-primary/10"
                                >
                                    <User class="size-10 text-primary/60" />
                                </div>
                                <div class="flex flex-1 flex-col gap-2">
                                    <input
                                        ref="imageInputRef"
                                        type="file"
                                        name="avatar"
                                        accept="image/jpeg,image/png"
                                        class="hidden"
                                    />
                                    <Button
                                        type="button"
                                        variant="default"
                                        size="default"
                                        class="w-fit rounded-lg"
                                        @click="triggerImageUpload"
                                    >
                                        <Upload class="size-4" />
                                        Upload Image
                                    </Button>
                                    <p class="text-xs text-muted-foreground">
                                        JPG or PNG format, not exceeding 5MB.
                                    </p>
                                </div>
                            </div>

                            <!-- Basic fields grid -->
                            <div class="grid gap-4 md:grid-cols-3">
                                <div>
                                    <Label for="name" class="flex items-center gap-1">
                                        Name
                                        <span class="text-destructive">*</span>
                                    </Label>
                                    <Input
                                        id="name"
                                        name="name"
                                        class="mt-1"
                                        required
                                        placeholder="Enter name"
                                    />
                                    <InputError :message="errors.name" />
                                </div>
                                <div>
                                    <Label for="email" class="flex items-center gap-1">
                                        Email
                                        <span class="text-destructive">*</span>
                                    </Label>
                                    <Input
                                        id="email"
                                        name="email"
                                        type="email"
                                        class="mt-1"
                                        required
                                        placeholder="Enter email"
                                    />
                                    <InputError :message="errors.email" />
                                </div>
                                <div>
                                    <Label for="phone" class="flex items-center gap-1">
                                        Phone Number
                                        <span class="text-destructive">*</span>
                                    </Label>
                                    <Input
                                        id="phone"
                                        name="phone"
                                        type="tel"
                                        class="mt-1"
                                        required
                                        placeholder="Enter phone"
                                    />
                                    <InputError :message="errors.phone" />
                                </div>
                                <div>
                                    <Label for="currency">Currency</Label>
                                    <select
                                        id="currency"
                                        name="currency"
                                        class="mt-1 flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-base shadow-xs focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50 focus-visible:outline-none md:text-sm"
                                    >
                                        <option value="">Select</option>
                                        <option
                                            v-for="(label, value) in currencies"
                                            :key="value"
                                            :value="value"
                                        >
                                            {{ label }}
                                        </option>
                                    </select>
                                </div>
                                <div>
                                    <Label for="website">Website</Label>
                                    <Input
                                        id="website"
                                        name="website"
                                        type="url"
                                        class="mt-1"
                                        placeholder="https://"
                                    />
                                </div>
                                <div class="md:col-span-3">
                                    <Label for="notes">Notes</Label>
                                    <Input
                                        id="notes"
                                        name="notes"
                                        class="mt-1"
                                        placeholder="Enter notes"
                                    />
                                </div>
                            </div>
                                    </div>
                                </CollapsibleContent>
                            </div>
                        </Collapsible>

                        <!-- Billing Address -->
                        <Collapsible v-model:open="billingAddressOpen">
                            <div class="rounded-lg border border-sidebar-border/70">
                                <CollapsibleTrigger as-child>
                                    <button
                                        type="button"
                                        class="flex w-full items-center justify-between px-4 py-3 text-left hover:bg-muted/50"
                                    >
                                        <h2 class="text-base font-semibold text-foreground">
                                            Billing Address
                                        </h2>
                                        <ChevronDown
                                            class="size-5 shrink-0 text-muted-foreground transition-transform duration-200"
                                            :class="{
                                                'rotate-180':
                                                    billingAddressOpen,
                                            }"
                                        />
                                    </button>
                                </CollapsibleTrigger>
                                <CollapsibleContent>
                                    <div class="grid gap-4 border-t border-sidebar-border/70 px-4 pb-4 pt-4">
                                    <div>
                                        <Label for="billing_name">Name</Label>
                                        <Input
                                            id="billing_name"
                                            v-model="billingAddress.name"
                                            name="billing_name"
                                            class="mt-1"
                                            placeholder="Enter name"
                                        />
                                    </div>
                                    <div>
                                        <Label for="billing_address_line_1"
                                            >Address Line 1</Label
                                        >
                                        <Input
                                            id="billing_address_line_1"
                                            v-model="billingAddress.address_line_1"
                                            name="billing_address_line_1"
                                            class="mt-1"
                                            placeholder="Address line 1"
                                        />
                                    </div>
                                    <div>
                                        <Label for="billing_address_line_2"
                                            >Address Line 2</Label
                                        >
                                        <Input
                                            id="billing_address_line_2"
                                            v-model="billingAddress.address_line_2"
                                            name="billing_address_line_2"
                                            class="mt-1"
                                            placeholder="Address line 2"
                                        />
                                    </div>
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <Label for="billing_country"
                                                >Country</Label
                                            >
                                            <select
                                                id="billing_country"
                                                v-model="billingAddress.country"
                                                name="billing_country"
                                                class="mt-1 flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-base shadow-xs focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50 focus-visible:outline-none md:text-sm"
                                            >
                                                <option value="">Select</option>
                                                <option
                                                    v-for="c in countries"
                                                    :key="c"
                                                    :value="c"
                                                >
                                                    {{ c }}
                                                </option>
                                            </select>
                                        </div>
                                        <div>
                                            <Label for="billing_state"
                                                >State</Label
                                            >
                                            <select
                                                id="billing_state"
                                                v-model="billingAddress.state"
                                                name="billing_state"
                                                class="mt-1 flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-base shadow-xs focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50 focus-visible:outline-none md:text-sm"
                                            >
                                                <option value="">Select</option>
                                                <option
                                                    v-for="s in states"
                                                    :key="s"
                                                    :value="s"
                                                >
                                                    {{ s }}
                                                </option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <Label for="billing_city">City</Label>
                                            <select
                                                id="billing_city"
                                                v-model="billingAddress.city"
                                                name="billing_city"
                                                class="mt-1 flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-base shadow-xs focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50 focus-visible:outline-none md:text-sm"
                                            >
                                                <option value="">Select</option>
                                                <option
                                                    v-for="c in cities"
                                                    :key="c"
                                                    :value="c"
                                                >
                                                    {{ c }}
                                                </option>
                                            </select>
                                        </div>
                                        <div>
                                            <Label for="billing_pincode"
                                                >Pincode</Label
                                            >
                                            <Input
                                                id="billing_pincode"
                                                v-model="billingAddress.pincode"
                                                name="billing_pincode"
                                                class="mt-1"
                                                placeholder="Pincode"
                                            />
                                        </div>
                                    </div>
                                    </div>
                                </CollapsibleContent>
                            </div>
                        </Collapsible>

                        <!-- Shipping Address -->
                        <Collapsible v-model:open="shippingAddressOpen">
                            <div class="rounded-lg border border-sidebar-border/70">
                                <div class="flex items-center justify-between px-4 py-3">
                                    <CollapsibleTrigger as-child>
                                        <button
                                            type="button"
                                            class="flex flex-1 items-center justify-between text-left hover:opacity-80"
                                        >
                                            <h2 class="text-base font-semibold text-foreground">
                                                Shipping Address
                                            </h2>
                                            <ChevronDown
                                                class="size-5 shrink-0 text-muted-foreground transition-transform duration-200"
                                                :class="{
                                                    'rotate-180':
                                                        shippingAddressOpen,
                                                }"
                                            />
                                        </button>
                                    </CollapsibleTrigger>
                                </div>
                                <CollapsibleContent>
                                    <div class="grid gap-4 border-t border-sidebar-border/70 px-4 pb-4 pt-4">
                                    <div>
                                        <Label for="shipping_name">Name</Label>
                                        <Input
                                            id="shipping_name"
                                            v-model="shippingAddress.name"
                                            name="shipping_name"
                                            class="mt-1"
                                            placeholder="Enter name"
                                        />
                                    </div>
                                    <div>
                                        <Label for="shipping_address_line_1"
                                            >Address Line 1</Label
                                        >
                                        <Input
                                            id="shipping_address_line_1"
                                            v-model="shippingAddress.address_line_1"
                                            name="shipping_address_line_1"
                                            class="mt-1"
                                            placeholder="Address line 1"
                                        />
                                    </div>
                                    <div>
                                        <Label for="shipping_address_line_2"
                                            >Address Line 2</Label
                                        >
                                        <Input
                                            id="shipping_address_line_2"
                                            v-model="shippingAddress.address_line_2"
                                            name="shipping_address_line_2"
                                            class="mt-1"
                                            placeholder="Address line 2"
                                        />
                                    </div>
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <Label for="shipping_country"
                                                >Country</Label
                                            >
                                            <select
                                                id="shipping_country"
                                                v-model="shippingAddress.country"
                                                name="shipping_country"
                                                class="mt-1 flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-base shadow-xs focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50 focus-visible:outline-none md:text-sm"
                                            >
                                                <option value="">Select</option>
                                                <option
                                                    v-for="c in countries"
                                                    :key="c"
                                                    :value="c"
                                                >
                                                    {{ c }}
                                                </option>
                                            </select>
                                        </div>
                                        <div>
                                            <Label for="shipping_state"
                                                >State</Label
                                            >
                                            <select
                                                id="shipping_state"
                                                v-model="shippingAddress.state"
                                                name="shipping_state"
                                                class="mt-1 flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-base shadow-xs focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50 focus-visible:outline-none md:text-sm"
                                            >
                                                <option value="">Select</option>
                                                <option
                                                    v-for="s in states"
                                                    :key="s"
                                                    :value="s"
                                                >
                                                    {{ s }}
                                                </option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <Label for="shipping_city"
                                                >City</Label
                                            >
                                            <select
                                                id="shipping_city"
                                                v-model="shippingAddress.city"
                                                name="shipping_city"
                                                class="mt-1 flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-base shadow-xs focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50 focus-visible:outline-none md:text-sm"
                                            >
                                                <option value="">Select</option>
                                                <option
                                                    v-for="c in cities"
                                                    :key="c"
                                                    :value="c"
                                                >
                                                    {{ c }}
                                                </option>
                                            </select>
                                        </div>
                                        <div>
                                            <Label for="shipping_pincode"
                                                >Pincode</Label
                                            >
                                            <Input
                                                id="shipping_pincode"
                                                v-model="shippingAddress.pincode"
                                                name="shipping_pincode"
                                                class="mt-1"
                                                placeholder="Pincode"
                                            />
                                        </div>
                                    </div>
                                    </div>
                                </CollapsibleContent>
                            </div>
                        </Collapsible>

                        <!-- Banking Details -->
                        <Collapsible v-model:open="bankingDetailsOpen">
                            <div class="rounded-lg border border-sidebar-border/70">
                                <CollapsibleTrigger as-child>
                                    <button
                                        type="button"
                                        class="flex w-full items-center justify-between px-4 py-3 text-left hover:bg-muted/50"
                                    >
                                        <h2 class="text-base font-semibold text-foreground">
                                            Banking Details
                                        </h2>
                                        <ChevronDown
                                            class="size-5 shrink-0 text-muted-foreground transition-transform duration-200"
                                            :class="{
                                                'rotate-180':
                                                    bankingDetailsOpen,
                                            }"
                                        />
                                    </button>
                                </CollapsibleTrigger>
                                <CollapsibleContent>
                                    <div class="grid gap-4 border-t border-sidebar-border/70 px-4 pb-4 pt-4 md:grid-cols-2">
                                <div>
                                    <Label for="bank_name">Bank Name</Label>
                                    <Input
                                        id="bank_name"
                                        name="bank_name"
                                        class="mt-1"
                                        placeholder="Enter bank name"
                                    />
                                </div>
                                <div>
                                    <Label for="branch">Branch</Label>
                                    <Input
                                        id="branch"
                                        name="branch"
                                        class="mt-1"
                                        placeholder="Enter branch"
                                    />
                                </div>
                                <div class="md:col-span-2">
                                    <Label for="account_holder"
                                        >Account Holder</Label
                                    >
                                    <Input
                                        id="account_holder"
                                        name="account_holder"
                                        class="mt-1"
                                        placeholder="Enter account holder name"
                                    />
                                </div>
                                <div>
                                    <Label for="account_number"
                                        >Account Number</Label
                                    >
                                    <Input
                                        id="account_number"
                                        name="account_number"
                                        class="mt-1"
                                        placeholder="Enter account number"
                                    />
                                </div>
                                <div>
                                    <Label for="ifsc">IFSC</Label>
                                    <Input
                                        id="ifsc"
                                        name="ifsc"
                                        class="mt-1"
                                        placeholder="Enter IFSC code"
                                    />
                                </div>
                                    </div>
                                </CollapsibleContent>
                            </div>
                        </Collapsible>
                    </CardContent>
                </Card>

                <!-- Form actions -->
                <div class="flex flex-wrap items-center justify-end gap-4">
                    <Link href="/sales/clients-and-prospects">
                        <Button type="button" variant="outline" class="rounded-lg">
                            Cancel
                        </Button>
                    </Link>
                    <Button
                        type="submit"
                        class="rounded-lg"
                        :disabled="processing"
                    >
                        <Spinner v-if="processing" class="size-4" />
                        Create New
                    </Button>
                </div>
                </template>
            </Form>
        </div>
    </AppLayout>
</template>
