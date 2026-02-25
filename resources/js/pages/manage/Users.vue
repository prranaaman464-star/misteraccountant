<script setup lang="ts">
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import {
    ArrowDownToLine,
    ChevronDown,
    Trash2,
    X,
} from 'lucide-vue-next';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

interface Member {
    id: number;
    name: string;
    email: string;
    role: string;
    is_active: boolean;
    joined_at: string;
}

const props = defineProps<{
    organization: { id: number; name: string; owner_id?: number };
    members: Member[];
    memberLimit: number | null;
    canAddMore: boolean;
    canManageMembers: boolean;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Manage', href: '/manage' },
    { title: 'Manage Users', href: '/manage/users' },
];

const addMemberOpen = ref(false);
const selectedMembers = ref<number[]>([]);
const bulkActionsOpen = ref(false);

function toggleSelectAll(checked: boolean | 'indeterminate'): void {
    if (checked === true) {
        selectedMembers.value = props.members.map((m) => m.id);
    } else {
        selectedMembers.value = [];
    }
}

function toggleSelectMember(id: number): void {
    const idx = selectedMembers.value.indexOf(id);
    if (idx === -1) {
        selectedMembers.value.push(id);
    } else {
        selectedMembers.value.splice(idx, 1);
    }
}

function clearSelection(): void {
    selectedMembers.value = [];
    bulkActionsOpen.value = false;
}

function downloadSelectedCsv(): void {
    if (selectedMembers.value.length === 0) return;
    const ids = selectedMembers.value.join(',');
    window.location.href = `/manage/members/csv?ids=${ids}`;
}

function handleBulkRemove(): void {
    if (selectedMembers.value.length === 0) return;
    if (
        !confirm(
            `Are you sure you want to remove ${selectedMembers.value.length} member(s) from the organization?`,
        )
    ) {
        return;
    }
    bulkActionsOpen.value = false;
    router.post('/manage/members/bulk-remove', {
        ids: selectedMembers.value,
    });
}

const isAllSelected = computed(
    () =>
        selectedMembers.value.length === props.members.length &&
        props.members.length > 0,
);

const isSomeSelected = computed(
    () =>
        selectedMembers.value.length > 0 &&
        selectedMembers.value.length < props.members.length,
);

function isOwner(memberId: number): boolean {
    return props.organization.owner_id === memberId;
}

const form = useForm({
    email: '',
    name: '',
    role: 'staff',
});

const page = usePage();
const flash = page.props.flash as
    | { success?: string; error?: string }
    | undefined;

function submitAddMember() {
    form.post('/manage/members', {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            addMemberOpen.value = false;
        },
    });
}
</script>

<template>
    <Head title="Manage Users" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
        >
            <div class="flex flex-col gap-4">
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
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <div>
                        <h1 class="text-xl font-semibold">Manage Users</h1>
                        <p class="text-muted-foreground">
                            Organization:
                            <strong>{{ organization.name }}</strong>
                            <span
                                v-if="memberLimit !== null"
                                class="ml-2 text-sm"
                            >
                                ({{ members.length }} /
                                {{ memberLimit }} members)
                            </span>
                            <span v-else class="ml-2 text-sm"
                                >({{ members.length }} members)</span
                            >
                        </p>
                    </div>
                    <Dialog v-model:open="addMemberOpen">
                        <DialogTrigger as-child>
                            <Button
                                v-if="canManageMembers && canAddMore"
                                variant="default"
                                size="sm"
                            >
                                Add team member
                            </Button>
                        </DialogTrigger>
                        <DialogContent class="sm:max-w-md">
                            <DialogHeader>
                                <DialogTitle>Add team member</DialogTitle>
                                <DialogDescription>
                                    Add a member by email. If they don't have an
                                    account, they'll receive a link to set their
                                    password.
                                </DialogDescription>
                            </DialogHeader>
                            <form
                                @submit.prevent="submitAddMember"
                                class="space-y-4"
                            >
                                <div class="grid gap-2">
                                    <Label for="member-email">Email</Label>
                                    <Input
                                        id="member-email"
                                        v-model="form.email"
                                        type="email"
                                        required
                                        autocomplete="email"
                                        placeholder="colleague@example.com"
                                    />
                                    <InputError :message="form.errors.email" />
                                </div>
                                <div class="grid gap-2">
                                    <Label for="member-name"
                                        >Name (optional)</Label
                                    >
                                    <Input
                                        id="member-name"
                                        v-model="form.name"
                                        type="text"
                                        autocomplete="name"
                                        placeholder="Full name"
                                    />
                                    <InputError :message="form.errors.name" />
                                </div>
                                <div class="grid gap-2">
                                    <Label for="member-role">Role</Label>
                                    <select
                                        id="member-role"
                                        v-model="form.role"
                                        class="h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm"
                                    >
                                        <option value="admin">Admin</option>
                                        <option value="staff">Staff</option>
                                        <option value="client">Client</option>
                                    </select>
                                    <InputError :message="form.errors.role" />
                                </div>
                                <DialogFooter>
                                    <Button
                                        type="button"
                                        variant="outline"
                                        @click="addMemberOpen = false"
                                    >
                                        Cancel
                                    </Button>
                                    <Button
                                        type="submit"
                                        :disabled="form.processing"
                                    >
                                        Add member
                                    </Button>
                                </DialogFooter>
                            </form>
                        </DialogContent>
                    </Dialog>
                </div>

                <div class="rounded-lg border border-sidebar-border">
                    <table class="w-full text-sm">
                        <thead>
                            <tr
                                class="border-b border-sidebar-border bg-muted/50"
                            >
                                <th
                                    v-if="canManageMembers"
                                    class="h-12 w-12 px-4 text-left align-middle"
                                    @click.stop
                                >
                                    <Checkbox
                                        :model-value="
                                            isAllSelected
                                                ? true
                                                : isSomeSelected
                                                  ? 'indeterminate'
                                                  : false
                                        "
                                        @update:model-value="toggleSelectAll"
                                    />
                                </th>
                                <th class="p-3 text-left font-medium">Name</th>
                                <th class="p-3 text-left font-medium">Email</th>
                                <th class="p-3 text-left font-medium">Role</th>
                                <th class="p-3 text-left font-medium">
                                    Status
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="member in members"
                                :key="member.id"
                                class="border-b border-sidebar-border last:border-0"
                            >
                                <td
                                    v-if="canManageMembers"
                                    class="p-4 align-middle"
                                    @click.stop
                                >
                                    <Checkbox
                                        :model-value="
                                            selectedMembers.includes(member.id)
                                        "
                                        :disabled="isOwner(member.id)"
                                        @update:model-value="
                                            () => toggleSelectMember(member.id)
                                        "
                                    />
                                </td>
                                <td class="p-3">{{ member.name }}</td>
                                <td class="p-3">{{ member.email }}</td>
                                <td class="p-3 capitalize">
                                    {{ member.role }}
                                </td>
                                <td class="p-3">
                                    <span
                                        :class="
                                            member.is_active
                                                ? 'text-green-600'
                                                : 'text-muted-foreground'
                                        "
                                    >
                                        {{
                                            member.is_active
                                                ? 'Active'
                                                : 'Inactive'
                                        }}
                                    </span>
                                </td>
                            </tr>
                            <tr v-if="members.length === 0">
                                <td
                                    :colspan="canManageMembers ? 5 : 4"
                                    class="p-6 text-center text-muted-foreground"
                                >
                                    No members yet.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <p v-if="canAddMore" class="text-sm text-muted-foreground">
                    You can add more members (subject to your plan limit).
                </p>
                <p v-else class="text-sm text-amber-600">
                    Member limit reached. Upgrade your plan to add more members.
                </p>
            </div>

            <!-- Bulk actions bar (shown when members selected) - Teleport ensures visibility -->
            <Teleport v-if="canManageMembers" to="body">
                <Transition
                    enter-active-class="transition duration-200 ease-out"
                    enter-from-class="translate-y-full opacity-0"
                    enter-to-class="translate-y-0 opacity-100"
                    leave-active-class="transition duration-150 ease-in"
                    leave-from-class="translate-y-0 opacity-100"
                    leave-to-class="translate-y-full opacity-0"
                >
                    <div
                        v-if="selectedMembers.length > 0"
                        class="fixed inset-x-0 bottom-0 z-50 flex items-center justify-between gap-4 border-t border-sidebar-border/70 bg-card px-6 py-4 shadow-lg dark:border-sidebar-border"
                    >
                    <span class="text-sm font-medium">
                        {{ selectedMembers.length }}
                        {{
                            selectedMembers.length === 1
                                ? 'member'
                                : 'members'
                        }}
                        selected
                    </span>
                    <div class="flex items-center gap-2">
                        <Button
                            variant="default"
                            size="default"
                            class="rounded-lg"
                            @click="downloadSelectedCsv"
                        >
                            <ArrowDownToLine class="size-4" />
                            Download CSV
                        </Button>
                        <DropdownMenu v-model:open="bulkActionsOpen">
                            <DropdownMenuTrigger as-child>
                                <Button
                                    variant="default"
                                    size="default"
                                    class="rounded-lg"
                                >
                                    Bulk Actions
                                    <ChevronDown class="ml-1 size-4" />
                                </Button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent align="end" class="w-56">
                                <DropdownMenuItem
                                    class="cursor-pointer"
                                    @click="downloadSelectedCsv"
                                >
                                    <ArrowDownToLine class="mr-2 size-4" />
                                    Download CSV
                                </DropdownMenuItem>
                                <DropdownMenuItem
                                    class="cursor-pointer text-destructive focus:text-destructive"
                                    @click="handleBulkRemove"
                                >
                                    <Trash2 class="mr-2 size-4" />
                                    Bulk Remove
                                </DropdownMenuItem>
                            </DropdownMenuContent>
                        </DropdownMenu>
                        <Button
                            variant="ghost"
                            size="icon"
                            class="size-8 rounded-lg"
                            aria-label="Clear selection"
                            @click="clearSelection"
                        >
                            <X class="size-4" />
                        </Button>
                    </div>
                    </div>
                </Transition>
            </Teleport>
        </div>
    </AppLayout>
</template>
