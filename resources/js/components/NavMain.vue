<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ChevronDown, ChevronUp } from 'lucide-vue-next';
import { Collapsible, CollapsibleContent, CollapsibleTrigger } from '@/components/ui/collapsible';
import {
    SidebarGroup,
    SidebarGroupLabel,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
    SidebarMenuSub,
    SidebarMenuSubButton,
    SidebarMenuSubItem,
} from '@/components/ui/sidebar';
import { useCurrentUrl } from '@/composables/useCurrentUrl';
import { type NavItem } from '@/types';

withDefaults(
    defineProps<{
        items: NavItem[];
        label?: string;
    }>(),
    { label: 'Platform' },
);

const { isCurrentUrl } = useCurrentUrl();
</script>

<template>
    <SidebarGroup class="px-2 py-0">
        <SidebarGroupLabel>{{ label }}</SidebarGroupLabel>
        <SidebarMenu>
            <SidebarMenuItem v-for="item in items" :key="item.title">
                <Collapsible
                    v-if="item.items?.length"
                    :default-open="item.items?.some((sub) => isCurrentUrl(sub.href))"
                    class="group/collapsible"
                >
                    <CollapsibleTrigger as-child>
                        <SidebarMenuButton
                            :is-active="item.items?.some((sub) => isCurrentUrl(sub.href))"
                            :tooltip="item.title"
                        >
                            <component :is="item.icon" />
                            <span>{{ item.title }}</span>
                            <ChevronDown
                                class="ml-auto size-4 shrink-0 transition-transform group-data-[state=open]/collapsible:rotate-180"
                            />
                        </SidebarMenuButton>
                    </CollapsibleTrigger>
                    <CollapsibleContent>
                        <SidebarMenuSub>
                            <SidebarMenuSubItem
                                v-for="sub in item.items"
                                :key="sub.title"
                            >
                                <SidebarMenuSubButton
                                    as-child
                                    :is-active="isCurrentUrl(sub.href)"
                                >
                                    <Link
                                        :href="sub.href"
                                        :title="sub.title"
                                        class="flex min-w-0 w-full items-center gap-2 overflow-hidden"
                                    >
                                        <span class="min-w-0 truncate">{{ sub.title }}</span>
                                        <span
                                            v-if="sub.badge"
                                            class="shrink-0 text-[10px] font-medium text-pink-600 dark:text-pink-400"
                                        >
                                            {{ sub.badge }}
                                        </span>
                                        <component
                                            v-else-if="sub.badgeIcon"
                                            :is="sub.badgeIcon"
                                            class="size-3.5 shrink-0 text-orange-500"
                                        />
                                    </Link>
                                </SidebarMenuSubButton>
                            </SidebarMenuSubItem>
                        </SidebarMenuSub>
                    </CollapsibleContent>
                </Collapsible>
                <SidebarMenuButton
                    v-else
                    as-child
                    :is-active="isCurrentUrl(item.href)"
                    :tooltip="item.title"
                >
                    <Link :href="item.href">
                        <component :is="item.icon" />
                        <span>{{ item.title }}</span>
                    </Link>
                </SidebarMenuButton>
            </SidebarMenuItem>
        </SidebarMenu>
    </SidebarGroup>
</template>
