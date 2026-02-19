<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import { usePage } from '@inertiajs/vue3';
import {
    Gem,
    LayoutGrid,
    Package,
    Settings,
    ShoppingBag,
    ShoppingCart,
    Wallet,
    FileText,
    Plus,
    CreditCard,
} from 'lucide-vue-next';
import { computed } from 'vue';
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import { edit as editProfile } from '@/routes/profile';
import { type NavItem } from '@/types';
import AppLogo from './AppLogo.vue';
import inventory from '@/routes/inventory';

const page = usePage();
const organizations = computed(
    () =>
        (
            page.props.auth as {
                organizations?: Array<{ id: number; name: string }>;
            }
        )?.organizations ?? [],
);
const currentOrganizationId = computed(
    () =>
        (page.props.auth as { current_organization_id?: number })
            ?.current_organization_id,
);

function switchOrganization(event: Event) {
    const target = event.target as HTMLSelectElement;
    const id = target?.value;
    if (id)
        router.put('/current-organization', { organization_id: Number(id) });
}

const manageNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: dashboard(),
        icon: LayoutGrid,
    },
    {
        title: 'Inventory',
        href: '/inventory',
        icon: Package,
        items: [
            { title: 'All Items', href: '/inventory/items' },
            {
                title: 'Warehouses',
                href: inventory.warehouses(),
                badgeIcon: Gem,
            },
            { title: 'Product-wise P&L', href: '/inventory/product-wise-pl' },
            {
                title: 'Stock Value Report',
                href: '/inventory/stock-value-report',
                badge: 'New',
            },
            {
                title: 'Batch Expiry Report',
                href: '/inventory/batch-expiry-report',
            },
            {
                title: 'Party Transactions Report',
                href: '/inventory/party-transactions',
            },
            {
                title: 'All Transactions Report',
                href: '/inventory/all-transactions',
                badge: 'New',
            },
            {
                title: 'Stock Status Report',
                href: '/inventory/stock-status-report',
                badge: 'New',
            },
        ],
    },
    {
        title: 'Sales',
        href: '/sales',
        icon: ShoppingCart,
        items: [
            {
                title: 'Clients & Prospects',
                href: '/sales/clients-and-prospects',
            },

            {
                title: 'Quotation & Estimates',
                href: '/sales/quotation-and-estimates',
            },
            { title: 'Proforma Invoices', href: '/sales/proforma-invoices' },
            { title: 'Invoices', href: '/sales/invoices' },
            { title: 'Payment Receipts', href: '/sales/payment-receipts' },
            { title: 'Sales Orders (New)', href: '/sales/sales-orders' },
            {
                title: 'Delivery Challans (New)',
                href: '/sales/delivery-challans',
            },
            { title: 'Credit Notes (New)', href: '/sales/credit-notes' },
        ],
    },
    {
        title: 'Purchases',
        href: '/purchases',
        icon: ShoppingBag,
        items: [
            { title: 'Vendors Leads', href: '/purchases/vendors-leads' },
            {
                title: 'Vendors & Suppliers',
                href: '/purchases/vendors-and-suppliers',
            },
            {
                title: 'Purchases & Expenses (New)',
                href: '/purchases/purchases-and-expenses',
            },
            { title: 'Purchase Orders', href: '/purchases/purchase-orders' },
            {
                title: 'Payout Receipts (New)',
                href: '/purchases/payout-receipts',
            },
            { title: 'Debit Notes (New)', href: '/purchases/debit-notes' },
            {
                title: 'Hire The Best Vendors',
                href: '/purchases/hire-the-best-vendors',
            },
        ],
    },
    {
        title: 'Finance & Accounts',
        href: '/finance',
        icon: Wallet,
        items: [
            { title: 'Expenses', href: '/finance/expenses' },
            { title: 'Income', href: '/finance/income' },
            { title: 'Payments', href: '/finance/payments' },
            { title: 'Transactions', href: '/finance/transactions' },
            { title: 'Bank Accounts', href: '/finance/bank-accounts' },
            { title: 'Money Transfer', href: '/finance/money-transfer' },
        ],
    },
    {
        title: 'Manage',
        href: '/manage',
        icon: LayoutGrid,
        items: [
            { title: 'Manage Users', href: '/manage/users' },
            { title: 'Manage Memberships', href: '/manage/memberships' },
            { title: 'Permissions', href: '/manage/permissions' },
        ],
    },
];

const administrationNavItems: NavItem[] = [
    {
        title: 'My Plan',
        href: '/plan',
        icon: CreditCard,
    },
    {
        title: 'Reports',
        href: '/reports',
        icon: FileText,
    },
    {
        title: 'Settings',
        href: editProfile(),
        icon: Settings,
    },
];

const footerNavItems: NavItem[] = [
    {
        title: 'Quick Start',
        href: 'https://misteraccountant.com/',
        icon: Plus,
    },
];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="dashboard()">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
                <SidebarMenuItem v-if="organizations.length > 1">
                    <select
                        :value="currentOrganizationId ?? ''"
                        class="h-8 w-full rounded-md border border-input bg-background px-2 text-sm"
                        @change="switchOrganization"
                    >
                        <option
                            v-for="org in organizations"
                            :key="org.id"
                            :value="org.id"
                        >
                            {{ org.name }}
                        </option>
                    </select>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain label="Manage" :items="manageNavItems" />
            <NavMain label="Administration" :items="administrationNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <!-- <NavUser /> -->
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
