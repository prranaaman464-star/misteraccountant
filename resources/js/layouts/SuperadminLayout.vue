<script setup lang="ts">
import SuperadminSidebarLayout from '@/layouts/app/SuperadminSidebarLayout.vue';
import type { BreadcrumbItem } from '@/types';

type Props = {
    breadcrumbs?: BreadcrumbItem[];
};

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});
</script>

<template>
    <SuperadminSidebarLayout :breadcrumbs="breadcrumbs">
        <slot />
    </SuperadminSidebarLayout>
</template>
