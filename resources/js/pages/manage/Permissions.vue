<script setup lang="ts">
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

interface Module {
    id: number;
    name: string;
    slug: string;
}

interface Role {
    key: string;
    name: string;
    description: string;
}

interface Permission {
    id: number;
    name: string;
    key: string;
}

defineProps<{
    organization: { id: number; name: string };
    plan: { name: string } | null;
    modules: Module[];
    roles: Role[];
    permissions: Permission[];
    canManageOrganization: boolean;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Manage', href: '/manage' },
    { title: 'Permissions', href: '/manage/permissions' },
];

const addPermissionOpen = ref(false);
const form = useForm({
    name: '',
    key: '',
});

function submitAddPermission() {
    form.post('/manage/permissions', {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            addPermissionOpen.value = false;
        },
    });
}

const page = usePage();
const flash = page.props.flash as { success?: string; error?: string } | undefined;

function keyFromName() {
    if (!form.name) return;
    form.key = form.name
        .toLowerCase()
        .replace(/\s+/g, '_')
        .replace(/[^a-z0-9_.-]/g, '');
}
</script>

<template>
    <Head title="Permissions" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
            <div class="flex flex-col gap-6">
                <div
                    v-if="flash?.success"
                    class="rounded-lg border border-green-200 bg-green-50 p-3 text-sm text-green-800 dark:border-green-800 dark:bg-green-900/20 dark:text-green-200"
                >
                    {{ flash.success }}
                </div>
                <div
                    v-if="flash?.error"
                    class="rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-800 dark:border-red-800 dark:bg-red-900/20 dark:text-red-200"
                >
                    {{ flash.error }}
                </div>
                <h1 class="text-xl font-semibold">Permissions</h1>
                <p class="text-muted-foreground">
                    Organization: <strong>{{ organization.name }}</strong>
                    <span v-if="plan" class="ml-2">· Plan: {{ plan.name }}</span>
                </p>

                <div class="rounded-lg border border-sidebar-border p-4">
                    <h2 class="mb-2 font-medium">Roles</h2>
                    <p class="mb-3 text-sm text-muted-foreground">
                        Role defines what a user can do in this organization.
                    </p>
                    <ul class="space-y-2">
                        <li
                            v-for="role in roles"
                            :key="role.key"
                            class="flex flex-col rounded border border-sidebar-border/70 p-3"
                        >
                            <span class="font-medium capitalize">{{ role.name }}</span>
                            <span class="text-sm text-muted-foreground">{{ role.description }}</span>
                        </li>
                    </ul>
                </div>

                <div class="rounded-lg border border-sidebar-border p-4">
                    <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
                        <div>
                            <h2 class="font-medium">Custom permissions</h2>
                            <p class="text-sm text-muted-foreground">
                                Create permissions for this organization (e.g. reports.export,
                                inventory.edit).
                            </p>
                        </div>
                        <Dialog v-model:open="addPermissionOpen">
                            <DialogTrigger as-child>
                                <Button v-if="canManageOrganization" variant="default" size="sm">
                                    Add permission
                                </Button>
                            </DialogTrigger>
                            <DialogContent class="sm:max-w-md">
                                <DialogHeader>
                                    <DialogTitle>Create permission</DialogTitle>
                                    <DialogDescription>
                                        Add a custom permission. Use a unique key (e.g.
                                        reports.export).
                                    </DialogDescription>
                                </DialogHeader>
                                <form @submit.prevent="submitAddPermission" class="space-y-4">
                                    <div class="grid gap-2">
                                        <Label for="perm-name">Name</Label>
                                        <Input
                                            id="perm-name"
                                            v-model="form.name"
                                            type="text"
                                            required
                                            placeholder="Export reports"
                                            @blur="keyFromName"
                                        />
                                        <InputError :message="form.errors.name" />
                                    </div>
                                    <div class="grid gap-2">
                                        <Label for="perm-key">Key</Label>
                                        <Input
                                            id="perm-key"
                                            v-model="form.key"
                                            type="text"
                                            required
                                            placeholder="reports.export"
                                        />
                                        <InputError :message="form.errors.key" />
                                        <p class="text-xs text-muted-foreground">
                                            Lowercase letters, numbers, dots, underscores, hyphens
                                            only.
                                        </p>
                                    </div>
                                    <DialogFooter>
                                        <Button
                                            type="button"
                                            variant="outline"
                                            @click="addPermissionOpen = false"
                                        >
                                            Cancel
                                        </Button>
                                        <Button type="submit" :disabled="form.processing">
                                            Create permission
                                        </Button>
                                    </DialogFooter>
                                </form>
                            </DialogContent>
                        </Dialog>
                    </div>
                    <ul v-if="permissions.length" class="space-y-2">
                        <li
                            v-for="perm in permissions"
                            :key="perm.id"
                            class="flex items-center justify-between rounded border border-sidebar-border/70 p-3"
                        >
                            <span class="font-medium">{{ perm.name }}</span>
                            <code class="text-sm text-muted-foreground">{{ perm.key }}</code>
                        </li>
                    </ul>
                    <p v-else class="text-sm text-muted-foreground">
                        No custom permissions yet. Add one to get started.
                    </p>
                </div>

                <div class="rounded-lg border border-sidebar-border p-4">
                    <h2 class="mb-2 font-medium">Modules (from your plan)</h2>
                    <p class="mb-3 text-sm text-muted-foreground">
                        These modules are available in your current plan. Staff and above can access
                        them based on role.
                    </p>
                    <ul v-if="modules.length" class="list-inside list-disc space-y-1 text-sm">
                        <li v-for="mod in modules" :key="mod.id">{{ mod.name }}</li>
                    </ul>
                    <p v-else class="text-sm text-muted-foreground">
                        No modules in current plan. Upgrade to get more features.
                    </p>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
