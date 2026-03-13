<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ChevronDown } from 'lucide-vue-next';
import {
    Collapsible,
    CollapsibleContent,
    CollapsibleTrigger,
} from '@/components/ui/collapsible';
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
import { type NavItem, type NavSubItem } from '@/types';

withDefaults(
    defineProps<{
        items: NavItem[];
        label?: string;
    }>(),
    { label: 'Platform' },
);

const { isCurrentUrl, currentUrl } = useCurrentUrl();

function isSubItemActive(sub: NavSubItem): boolean {
    if (sub.activeWhen) {
        return sub.activeWhen(currentUrl.value);
    }
    return isCurrentUrl(sub.href);
}

function hasActiveChild(sub: NavSubItem): boolean {
    if (isSubItemActive(sub)) return true;
    return sub.items?.some(hasActiveChild) ?? false;
}

function hasActiveItem(items: NavSubItem[]): boolean {
    return items.some(hasActiveChild);
}
</script>

<template>
    <SidebarGroup class="px-2 py-1">
        <SidebarGroupLabel class="mb-1">{{ label }}</SidebarGroupLabel>
        <SidebarMenu>
            <SidebarMenuItem v-for="item in items" :key="item.title">
                <Collapsible
                    v-if="item.items?.length"
                    :default-open="hasActiveItem(item.items)"
                    class="group/collapsible"
                >
                    <CollapsibleTrigger as-child>
                        <SidebarMenuButton
                            :is-active="hasActiveItem(item.items)"
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
                        <SidebarMenuSub class="gap-1.5">
                            <SidebarMenuSubItem
                                v-for="sub in item.items"
                                :key="sub.title"
                            >
                                <Collapsible
                                    v-if="sub.items?.length"
                                    :default-open="hasActiveChild(sub)"
                                    class="group/nested"
                                >
                                    <CollapsibleTrigger as-child>
                                        <SidebarMenuSubButton
                                            :is-active="hasActiveChild(sub)"
                                            class="w-full justify-between"
                                        >
                                            <span class="min-w-0 truncate">{{
                                                sub.title
                                            }}</span>
                                            <ChevronDown
                                                class="ml-auto size-3.5 shrink-0 transition-transform group-data-[state=open]/nested:rotate-180"
                                            />
                                        </SidebarMenuSubButton>
                                    </CollapsibleTrigger>
                                    <CollapsibleContent>
                                        <SidebarMenuSub class="gap-1">
                                            <SidebarMenuSubItem
                                                v-for="nested in sub.items"
                                                :key="nested.title"
                                            >
                                                <SidebarMenuSubButton
                                                    as-child
                                                    :is-active="
                                                        isSubItemActive(nested)
                                                    "
                                                >
                                                    <Link
                                                        :href="nested.href"
                                                        :title="nested.title"
                                                        class="flex w-full min-w-0 items-center gap-2 overflow-hidden"
                                                    >
                                                        <span
                                                            class="min-w-0 truncate"
                                                        >
                                                            {{
                                                                nested.title
                                                            }}
                                                        </span>
                                                        <span
                                                            v-if="nested.badge"
                                                            class="shrink-0 text-[10px] font-medium text-pink-600 dark:text-pink-400"
                                                        >
                                                            {{ nested.badge }}
                                                        </span>
                                                        <component
                                                            v-else-if="
                                                                nested.badgeIcon
                                                            "
                                                            :is="nested.badgeIcon"
                                                            class="size-3.5 shrink-0 text-orange-500"
                                                        />
                                                    </Link>
                                                </SidebarMenuSubButton>
                                            </SidebarMenuSubItem>
                                        </SidebarMenuSub>
                                    </CollapsibleContent>
                                </Collapsible>
                                <SidebarMenuSubButton
                                    v-else
                                    as-child
                                    :is-active="isSubItemActive(sub)"
                                >
                                    <Link
                                        :href="sub.href"
                                        :title="sub.title"
                                        class="flex w-full min-w-0 items-center gap-2 overflow-hidden"
                                    >
                                        <span class="min-w-0 truncate">{{
                                            sub.title
                                        }}</span>
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
                    <Link
                        v-if="!item.external"
                        :href="item.href"
                    >
                        <component :is="item.icon" />
                        <span>{{ item.title }}</span>
                    </Link>
                    <a
                        v-else
                        :href="item.href"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="flex flex-1 items-center gap-2"
                    >
                        <component :is="item.icon" />
                        <span>{{ item.title }}</span>
                    </a>
                </SidebarMenuButton>
            </SidebarMenuItem>
        </SidebarMenu>
    </SidebarGroup>
</template>
