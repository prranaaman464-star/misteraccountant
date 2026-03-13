<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import { usePage } from '@inertiajs/vue3';
import {
    Building2,
    CreditCard,
    FileText,
    Gem,
    LayoutGrid,
    Package,
    Settings,
    ShoppingBag,
    ShoppingCart,
    Wallet,
} from 'lucide-vue-next';
import { computed } from 'vue';
import NavMain from '@/components/NavMain.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
    SidebarSeparator,
} from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import inventory from '@/routes/inventory';
import { edit as editProfile } from '@/routes/profile';
import { type NavItem } from '@/types';
import AppLogo from './AppLogo.vue';

const page = usePage();
const isSuperadmin = computed(
    () => (page.props.auth as { is_superadmin?: boolean })?.is_superadmin ?? false,
);
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
            {
                title: 'Invoices',
                href: '/sales/invoices',
                items: [
                    {
                        title: 'Invoice',
                        href: '/sales/invoices',
                        activeWhen: (path) =>
                            /^\/sales\/invoices\/[^/]+$/.test(path) &&
                            !['create', 'templates', 'recurring'].includes(
                                path.split('/').pop() ?? '',
                            ),
                    },
                    {
                        title: 'Create Invoice',
                        href: '/sales/invoices/create',
                    },
                    {
                        title: 'Invoice Details',
                        href: '/sales/invoices-details',
                        activeWhen: (path) => path === '/sales/invoices-details',
                    },
                    {
                        title: 'Invoice Templates',
                        href: '/sales/invoices/templates',
                    },
                    {
                        title: 'Recurring Invoices',
                        href: '/sales/invoices/recurring',
                    },
                ],
            },
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

const administrationNavItems = computed<NavItem[]>(() => [
    ...(isSuperadmin.value
        ? [
              {
                  title: 'Super Admin',
                  href: '/superadmin/dashboard',
                  icon: LayoutGrid,
              },
          ]
        : []),
    {
        title: 'Organization',
        href: '/organization',
        icon: Building2,
    },
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
]);

</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader class="p-3">
            <SidebarMenu class="gap-2">
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="dashboard()" class="rounded-xl">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
                <SidebarMenuItem v-if="organizations.length > 1">
                    <select
                        :value="currentOrganizationId ?? ''"
                        class="h-9 w-full rounded-lg border border-sidebar-border/80 bg-sidebar-accent/50 px-3 text-sm font-medium outline-none transition-colors focus:ring-2 focus:ring-sidebar-ring/50 hover:bg-sidebar-accent"
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

        <SidebarContent class="gap-0 px-2">
            <NavMain label="Navigation" :items="manageNavItems" />
            <SidebarSeparator class="my-2" />
            <NavMain label="Administration" :items="administrationNavItems" />
        </SidebarContent>

        <!--
        <SidebarFooter class="border-t border-sidebar-border/50 p-3">
            <NavFooter :items="footerNavItems" />
        </SidebarFooter>
        -->
    </Sidebar>
    <slot />
</template>
