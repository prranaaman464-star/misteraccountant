<script setup lang="ts">
import { router, usePage } from '@inertiajs/vue3';
import { Bell, ChevronDown } from 'lucide-vue-next';
import { computed } from 'vue';
import Breadcrumbs from '@/components/Breadcrumbs.vue';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { SidebarTrigger } from '@/components/ui/sidebar';
import UserMenuContent from '@/components/UserMenuContent.vue';
import { getInitials } from '@/composables/useInitials';
import type { BreadcrumbItem } from '@/types';

withDefaults(
    defineProps<{
        breadcrumbs?: BreadcrumbItem[];
    }>(),
    {
        breadcrumbs: () => [],
    },
);

const page = usePage();
const auth = computed(() => page.props.auth as {
    user: { name: string; avatar?: string };
    organizations?: Array<{ id: number; name: string }>;
    current_organization_id?: number;
    current_organization?: { id: number; name: string };
});

const currentOrg = computed(
    () => auth.value.current_organization ?? null,
);
const orgsForSwitch = computed(() => {
    const orgs = auth.value.organizations ?? [];
    const currentId = auth.value.current_organization_id;
    const current = auth.value.current_organization;
    if (current && !orgs.some((o) => o.id === current.id)) {
        return [current, ...orgs];
    }
    return orgs;
});
const hasMultipleOrgs = computed(() => orgsForSwitch.value.length > 1);

function switchOrganization(orgId: number) {
    router.put('/current-organization', { organization_id: orgId });
}
</script>

<template>
    <header
        class="flex h-16 shrink-0 items-center gap-2 border-b border-sidebar-border/70 px-6 transition-[width,height] ease-linear group-has-data-[collapsible=icon]/sidebar-wrapper:h-12 md:px-4"
    >
        <div class="flex items-center gap-2">
            <SidebarTrigger class="-ml-1" />
            <template v-if="currentOrg">
                <DropdownMenu v-if="hasMultipleOrgs">
                    <DropdownMenuTrigger as-child>
                        <Button
                            variant="ghost"
                            class="h-9 gap-1 px-2 font-semibold"
                        >
                            {{ currentOrg.name }}
                            <ChevronDown class="size-4" />
                        </Button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent align="start" class="min-w-[12rem]">
                        <DropdownMenuItem
                            v-for="org in orgsForSwitch"
                            :key="org.id"
                            :class="{
                                'bg-accent': org.id === auth.current_organization_id,
                            }"
                            @click="switchOrganization(org.id)"
                        >
                            {{ org.name }}
                        </DropdownMenuItem>
                    </DropdownMenuContent>
                </DropdownMenu>
                <span
                    v-else
                    class="px-2 font-semibold"
                >
                    {{ currentOrg.name }}
                </span>
            </template>
            <template v-else-if="breadcrumbs && breadcrumbs.length > 0">
                <Breadcrumbs :breadcrumbs="breadcrumbs" />
            </template>
        </div>

        <div class="mx-4 min-w-0 flex-1">
            <div
                class="flex h-full w-full items-center gap-2 rounded-md border border-input"
            >
                <input
                    type="text"
                    name="search"
                    id="search"
                    placeholder="Search"
                    class="h-9 w-full min-w-0 flex-1 px-3 py-1 text-sm shadow-xs transition-[color,box-shadow] outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50 aria-invalid:border-destructive aria-invalid:ring-destructive/20 dark:aria-invalid:ring-destructive/40"
                />
            </div>
        </div>

        <div class="ml-auto flex items-center gap-2">
            <!-- Contact expert -->
            <div class="flex items-center gap-4 rounded-md border border-input">
                <label
                    for="contact-expert"
                    class="cursor-pointer px-4 py-2 text-sm font-medium text-gray-700 hover:text-gray-900"
                >
                    Contact expert
                </label>
            </div>

            <!-- notification button -->
            <Button
                variant="ghost"
                size="icon"
                class="group h-9 w-9 cursor-pointer"
            >
                <Bell class="size-5 opacity-80 group-hover:opacity-100" />
            </Button>
            <DropdownMenu>
                <DropdownMenuTrigger :as-child="true">
                    <Button
                        variant="ghost"
                        size="icon"
                        class="relative size-10 w-auto rounded-full p-1 focus-within:ring-2 focus-within:ring-primary"
                    >
                        <Avatar class="size-8 overflow-hidden rounded-full">
                            <AvatarImage
                                v-if="auth.user.avatar"
                                :src="auth.user.avatar"
                                :alt="auth.user.name"
                            />
                            <AvatarFallback
                                class="rounded-lg bg-neutral-200 font-semibold text-black dark:bg-neutral-700 dark:text-white"
                            >
                                {{ getInitials(auth.user?.name) }}
                            </AvatarFallback>
                        </Avatar>
                    </Button>
                </DropdownMenuTrigger>
                <DropdownMenuContent align="end" class="w-56">
                    <UserMenuContent :user="auth.user" />
                </DropdownMenuContent>
            </DropdownMenu>
        </div>
    </header>
</template>
