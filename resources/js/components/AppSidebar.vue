<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
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
} from 'lucide-vue-next';
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import AppLogo from './AppLogo.vue';
import { edit as editProfile } from '@/routes/profile';
import { dashboard } from '@/routes';

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
                href: '/inventory/warehouses',
                badgeIcon: Gem,
            },
            { title: 'Product-wise P&L', href: '/inventory/product-wise-pl' },
            {
                title: 'Stock Value Report',
                href: '/inventory/stock-value-report',
                badge: 'New',
            },
            { title: 'Batch Expiry Report', href: '/inventory/batch-expiry-report' },
            { title: 'Party Transactions Report', href: '/inventory/party-transactions' },
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
            { title: 'Clients & Prospects', href: '/sales/clients-and-prospects' },

            { title: 'Quotation & Estimates', href: '/sales/quotation-and-estimates' },
            { title: 'Proforma Invoices', href: '/sales/proforma-invoices' },
            { title: 'Invoices', href: '/sales/invoices' },
            { title: 'Payment Receipts', href: '/sales/payment-receipts' },
            { title: 'Sales Orders (New)', href: '/sales/sales-orders' },
            { title: 'Delivery Challans (New)', href: '/sales/delivery-challans' },
            { title: 'Credit Notes (New)', href: '/sales/credit-notes' },
        ],
    },
    {
        title: 'Purchases',
        href: '/purchases',
        icon: ShoppingBag,
        items: [
            { title: 'Vendors Leads', href: '/purchases/vendors-leads' },
            { title: 'Vendors & Suppliers', href: '/purchases/vendors-and-suppliers' },
            { title: 'Purchases & Expenses (New)', href: '/purchases/purchases-and-expenses' },
            { title: 'Purchase Orders', href: '/purchases/purchase-orders' },
            { title: 'Payout Receipts (New)', href: '/purchases/payout-receipts' },
            { title: 'Debit Notes (New)', href: '/purchases/debit-notes' },
            { title: 'Hire The Best Vendors', href: '/purchases/hire-the-best-vendors' },
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
        ],
    },
];

const administrationNavItems: NavItem[] = [
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
