<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import {
    Building2,
    CreditCard,
    DollarSign,
    LayoutGrid,
    Package,
    ShoppingCart,
} from 'lucide-vue-next';
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
import superadmin from '@/routes/superadmin';
import { type NavItem } from '@/types';
import AppLogo from './AppLogo.vue';

const superadminNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: superadmin.dashboard?.url?.() ?? '/superadmin/dashboard',
        icon: LayoutGrid,
    },
    {
        title: 'Companies',
        href: superadmin.companies?.index?.url?.() ?? '/superadmin/companies',
        icon: Building2,
    },
    {
        title: 'Subscriptions',
        href: superadmin.subscriptions?.index?.url?.() ?? '/superadmin/subscriptions',
        icon: CreditCard,
    },
    {
        title: 'Packages',
        href: superadmin.packages?.index?.url?.() ?? '/superadmin/packages',
        icon: Package,
    },
    {
        title: 'Domain',
        href: superadmin.domain?.index?.url?.() ?? '/superadmin/domain',
        icon: Building2,
    },
    {
        title: 'Purchase Transaction',
        href: superadmin.purchaseTransaction?.index?.url?.() ?? '/superadmin/purchase-transaction',
        icon: ShoppingCart,
    },
];

const dashboardUrl = superadmin.dashboard?.url?.() ?? '/superadmin/dashboard';

const footerNavItems: NavItem[] = [
    {
        title: 'Upgrade to More',
        href: 'https://misteraccountant.com/',
        icon: DollarSign,
    },
];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="dashboardUrl">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain label="Super Admin" :items="superadminNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
        </SidebarFooter>
    </Sidebar>
</template>
