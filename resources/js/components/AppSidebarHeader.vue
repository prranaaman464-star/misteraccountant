<script setup lang="ts">
import { router, usePage } from '@inertiajs/vue3';
import { onClickOutside, useDebounceFn } from '@vueuse/core';
import {
    Bell,
    ChevronDown,
    Loader2,
    Mail,
    MessageCircle,
    Package,
    Phone,
    Search,
    X,
} from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import Breadcrumbs from '@/components/Breadcrumbs.vue';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogTitle,
} from '@/components/ui/dialog';
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

const contactExpertOpen = ref(false);
const searchQuery = ref('');
const searchResults = ref<Array<{ type: string; id: number; title: string; subtitle: string | null; url: string }>>([]);
const searchOpen = ref(false);
const searchLoading = ref(false);
const searchInputRef = ref<HTMLInputElement | null>(null);
const searchContainerRef = ref<HTMLDivElement | null>(null);

onClickOutside(searchContainerRef, () => {
    searchOpen.value = false;
});

const fetchSearch = useDebounceFn(async () => {
    const q = searchQuery.value.trim();
    if (q.length < 2) {
        searchResults.value = [];
        searchOpen.value = false;
        return;
    }
    searchLoading.value = true;
    try {
        const res = await fetch(`/search?q=${encodeURIComponent(q)}`);
        const data = (await res.json()) as { results: typeof searchResults.value };
        searchResults.value = data.results ?? [];
        searchOpen.value = data.results?.length > 0;
    } catch {
        searchResults.value = [];
    } finally {
        searchLoading.value = false;
    }
}, 300);

watch(searchQuery, () => {
    fetchSearch();
});

function onSearchResultClick(url: string): void {
    searchQuery.value = '';
    searchResults.value = [];
    searchOpen.value = false;
    router.visit(url);
}

function closeSearchDropdown(): void {
    searchOpen.value = false;
}

const page = usePage();
const accountManager = computed(() => {
    const am = (page.props.accountManager as { name: string; role?: string; intro?: string; email: string; phone: string; phone_2?: string; whatsapp?: string } | null) ?? null;
    if (!am) {
        return { name: '', role: 'Your Account Manager', intro: '', email: '', phone: '', phone_2: null as string | null, whatsapp: '' };
    }
    return {
        name: am.name ?? '',
        role: am.role ?? 'Your Account Manager',
        intro: am.intro ?? '',
        email: am.email ?? '',
        phone: am.phone ?? '',
        phone_2: am.phone_2 ?? null,
        whatsapp: am.whatsapp ?? am.phone ?? '',
    };
});

function openMailto(email: string): void {
    window.location.href = `mailto:${email}`;
}

function openTel(phone: string): void {
    window.location.href = `tel:${phone.replace(/\s/g, '')}`;
}

withDefaults(
    defineProps<{
        breadcrumbs?: BreadcrumbItem[];
    }>(),
    {
        breadcrumbs: () => [],
    },
);

const auth = computed(
    () =>
        page.props.auth as {
            user: { name: string; avatar?: string };
            organizations?: Array<{ id: number; name: string }>;
            current_organization_id?: number;
            current_organization?: { id: number; name: string };
        },
);

const currentOrg = computed(
    () => auth.value.current_organization ?? null,
);
const orgsForSwitch = computed(() => {
    const orgs = auth.value.organizations ?? [];
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
        class="sticky top-0 z-10 flex h-16 shrink-0 items-center gap-2 border-b border-sidebar-border/70 bg-background px-6 transition-[width,height] ease-linear group-has-data-[collapsible=icon]/sidebar-wrapper:h-12 md:px-4"
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

        <div ref="searchContainerRef" class="relative mx-4 min-w-0 max-w-xl flex-1">
            <div
                class="flex h-9 w-full items-center gap-2 rounded-lg border border-input bg-muted/30 pr-2 pl-3 focus-within:bg-background focus-within:ring-2 focus-within:ring-ring/50"
            >
                <Search class="size-4 shrink-0 text-muted-foreground" />
                <input
                    ref="searchInputRef"
                    v-model="searchQuery"
                    type="text"
                    name="search"
                    placeholder="Search items, clients..."
                    class="h-8 min-w-0 flex-1 bg-transparent text-sm outline-none placeholder:text-muted-foreground"
                    autocomplete="off"
                />
                <Button
                    v-if="searchQuery"
                    variant="ghost"
                    size="icon"
                    class="size-7 shrink-0"
                    @click="searchQuery = ''; searchOpen = false"
                >
                    <X class="size-4" />
                </Button>
                <Loader2
                    v-else-if="searchLoading"
                    class="size-4 shrink-0 animate-spin text-muted-foreground"
                />
            </div>
            <div
                v-if="searchOpen && searchResults.length > 0"
                class="search-dropdown absolute top-full left-0 right-0 z-50 mt-1 max-h-80 overflow-auto rounded-lg border border-sidebar-border/70 bg-popover shadow-lg dark:border-sidebar-border"
            >
                <div class="p-1">
                    <button
                        v-for="r in searchResults"
                        :key="`${r.type}-${r.id}`"
                        type="button"
                        class="flex w-full items-center gap-3 rounded-md px-3 py-2.5 text-left text-sm transition-colors hover:bg-muted/80"
                        @click="onSearchResultClick(r.url)"
                    >
                        <div
                            class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-muted"
                        >
                            <Package class="size-4 text-muted-foreground" />
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="truncate font-medium">{{ r.title }}</div>
                            <div
                                v-if="r.subtitle"
                                class="truncate text-xs text-muted-foreground"
                            >
                                {{ r.subtitle }}
                            </div>
                        </div>
                        <span class="shrink-0 text-xs text-muted-foreground">
                            {{ r.type }}
                        </span>
                    </button>
                </div>
            </div>
        </div>

        <div class="ml-auto flex items-center gap-2">
            <!-- Contact expert -->
            <Button
                v-if="accountManager.name"
                variant="outline"
                size="default"
                class="flex items-center gap-2 rounded-lg border-primary/30 bg-primary/5 px-3 py-2 text-primary hover:bg-primary/10 hover:text-primary"
                @click="contactExpertOpen = true"
            >
                <Avatar class="size-6 shrink-0 overflow-hidden rounded-full">
                    <AvatarFallback
                        class="rounded-full bg-primary/20 text-xs font-semibold text-primary"
                    >
                        {{ getInitials(accountManager.name) }}
                    </AvatarFallback>
                </Avatar>
                <span class="hidden text-left text-sm sm:block">
                    <span class="block font-medium leading-tight">
                        Meet {{ accountManager.name }}
                    </span>
                    <span class="block text-xs font-normal opacity-90">
                        {{ accountManager.role }}
                    </span>
                </span>
            </Button>

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

    <!-- Account Manager / Contact Expert Modal -->
    <Dialog v-model:open="contactExpertOpen">
        <DialogContent
            class="gap-0 overflow-hidden p-0 sm:max-w-md"
            :show-close-button="false"
            @pointer-down-outside="contactExpertOpen = false"
        >
            <div class="flex flex-col gap-6 p-6">
                <!-- Header -->
                <div class="flex items-start justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <Avatar
                            class="size-14 shrink-0 overflow-hidden rounded-full ring-2 ring-primary/20"
                        >
                            <AvatarFallback
                                class="rounded-full bg-primary/15 text-lg font-semibold text-primary"
                            >
                                {{ getInitials(accountManager.name) }}
                            </AvatarFallback>
                        </Avatar>
                        <div>
                            <DialogTitle class="text-lg font-bold">
                                Hi, I am {{ accountManager.name }}
                            </DialogTitle>
                            <p class="text-sm font-semibold text-primary">
                                {{ accountManager.role }}
                            </p>
                        </div>
                    </div>
                    <Button
                        variant="ghost"
                        size="icon"
                        class="size-8 shrink-0 -m-2"
                        aria-label="Close"
                        @click="contactExpertOpen = false"
                    >
                        <X class="size-4" />
                    </Button>
                </div>

                <!-- Intro -->
                <p class="text-sm text-muted-foreground">
                    {{ accountManager.intro }}
                </p>

                <!-- Request Call Back -->
                <Button
                    size="default"
                    class="w-full rounded-lg"
                    @click="openTel(accountManager.phone)"
                >
                    Request Call Back
                </Button>

                <!-- Contact options -->
                <div class="grid grid-cols-2 gap-2">
                    <button
                        v-if="accountManager.email"
                        type="button"
                        class="flex items-center gap-2 rounded-lg border border-input px-3 py-2.5 text-left text-sm transition-colors hover:bg-muted/50"
                        @click="openMailto(accountManager.email)"
                    >
                        <Mail class="size-4 shrink-0 text-muted-foreground" />
                        <span class="truncate">{{ accountManager.email }}</span>
                    </button>
                    <button
                        v-if="accountManager.phone"
                        type="button"
                        class="flex items-center gap-2 rounded-lg border border-input px-3 py-2.5 text-left text-sm transition-colors hover:bg-muted/50"
                        @click="openTel(accountManager.phone)"
                    >
                        <Phone class="size-4 shrink-0 text-muted-foreground" />
                        <span class="truncate">{{ accountManager.phone }}</span>
                    </button>
                    <button
                        v-if="accountManager.phone_2"
                        type="button"
                        class="flex items-center gap-2 rounded-lg border border-input px-3 py-2.5 text-left text-sm transition-colors hover:bg-muted/50"
                        @click="openTel(accountManager.phone_2!)"
                    >
                        <Phone class="size-4 shrink-0 text-muted-foreground" />
                        <span class="truncate">{{ accountManager.phone_2 }}</span>
                    </button>
                    <a
                        v-if="accountManager.whatsapp"
                        :href="`https://wa.me/${accountManager.whatsapp.replace(/\D/g, '')}`"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="flex items-center gap-2 rounded-lg border border-input px-3 py-2.5 text-left text-sm transition-colors hover:bg-muted/50"
                    >
                        <MessageCircle
                            class="size-4 shrink-0 text-[#25D366]"
                            aria-hidden="true"
                        />
                        <span class="truncate">{{ accountManager.whatsapp }}</span>
                    </a>
                </div>
            </div>
        </DialogContent>
    </Dialog>
</template>
