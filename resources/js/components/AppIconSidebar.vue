<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import {
    Plus,
    Settings,
    LogOut,
} from 'lucide-vue-next';
import { cn } from '@/lib/utils';
import { logout } from '@/routes';
import { edit } from '@/routes/profile';
import { Button } from './ui/button';

const handleLogout = () => {
    router.flushAll();
};

const iconItems = [
    { id: 'Settings', icon: Settings, label: 'Settings', href: edit(), isLogout: false },
    { id: 'logout', icon: LogOut, label: 'Logout', href: logout(), isLogout: true },
];
</script>

<template>
    <aside
        class="fixed inset-y-0 left-0 z-20 hidden h-svh w-14 shrink-0 flex-col border-r border-zinc-700/50 bg-zinc-800 md:flex"
        aria-label="Icon navigation"
    >
        <nav class="flex h-full flex-col justify-between items-center px-2 py-4">
            <!-- TOP ICON -->
            <div>
                <Button
                    variant="ghost"
                    size="icon"
                    class="group h-9 w-9 cursor-pointer text-white transition-colors hover:bg-zinc-700"
                >
                    <Plus class="size-5 opacity-80 group-hover:opacity-100" />
                </Button>
            </div>

            <!-- BOTTOM ICONS -->
            <div class="flex flex-col gap-1">
                <Link
                    v-for="item in iconItems.filter((i) => !i.isLogout)"
                    :key="item.id"
                    :href="item.href"
                    :title="item.label"
                    :class="cn(
                        'flex size-10 items-center justify-center rounded-lg text-white transition-colors hover:bg-zinc-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-white/30'
                    )"
                >
                    <component :is="item.icon" class="size-5" aria-hidden />
                </Link>
                <button
                    v-for="item in iconItems.filter((i) => i.isLogout)"
                    :key="item.id"
                    type="button"
                    :title="item.label"
                    :class="cn(
                        'flex size-10 items-center justify-center rounded-lg text-white transition-colors hover:bg-zinc-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-white/30'
                    )"
                    @click="handleLogout(); router.post(item.href)"
                >
                    <component :is="item.icon" class="size-5" aria-hidden />
                </button>
            </div>
        </nav>
    </aside>
</template>
