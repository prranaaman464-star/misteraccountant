<script setup lang="ts">
import AppContent from '@/components/AppContent.vue';
import AppIconSidebar from '@/components/AppIconSidebar.vue';
import AppShell from '@/components/AppShell.vue';
import AppSidebarHeader from '@/components/AppSidebarHeader.vue';
import SuperadminSidebar from '@/components/SuperadminSidebar.vue';
import type { BreadcrumbItem } from '@/types';

type Props = {
    breadcrumbs?: BreadcrumbItem[];
};

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});
</script>

<template>
    <AppShell variant="sidebar">
        <div
            class="flex min-h-svh w-full flex-1 [--sidebar-left:0] md:[--sidebar-left:3.5rem]"
        >
            <div class="hidden w-14 shrink-0 md:block" aria-hidden />
            <AppIconSidebar />
            <SuperadminSidebar />
            <AppContent variant="sidebar" class="overflow-x-hidden">
                <AppSidebarHeader :breadcrumbs="breadcrumbs" />
                <slot />
            </AppContent>
        </div>
    </AppShell>
</template>
