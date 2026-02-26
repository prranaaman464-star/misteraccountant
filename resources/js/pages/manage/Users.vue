<script setup lang="ts">
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';
import ManageController from '@/actions/App/Http/Controllers/Manage/ManageController';
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

defineProps<{
    organization: { id: number; name: string };
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
const editMemberOpen = ref(false);
const editMemberId = ref<number | null>(null);
const form = useForm({
    email: '',
    name: '',
    role: 'staff',
});
const editForm = useForm({ role: 'staff' });

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

function openEditMember(member: Member) {
    editMemberId.value = member.id;
    editForm.setData('role', member.role);
    editMemberOpen.value = true;
}

function submitEditMember() {
    const id = editMemberId.value;
    if (!id) return;
    editForm.put(ManageController.updateMember.url({ user: id }), {
        preserveScroll: true,
        onSuccess: () => {
            editMemberId.value = null;
            editMemberOpen.value = false;
        },
    });
}

function removeMember(member: Member) {
    if (!confirm(`Remove ${member.name} from the organization?`)) return;
    router.delete(ManageController.destroyMember.url({ user: member.id }), {
        preserveScroll: true,
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
                    <Dialog v-model:open="editMemberOpen">
                        <DialogContent class="sm:max-w-md">
                            <DialogHeader>
                                <DialogTitle>Edit member role</DialogTitle>
                                <DialogDescription>
                                    Change the role for this team member.
                                </DialogDescription>
                            </DialogHeader>
                            <form
                                @submit.prevent="submitEditMember"
                                class="space-y-4"
                            >
                                <div class="grid gap-2">
                                    <Label for="edit-member-role">Role</Label>
                                    <select
                                        id="edit-member-role"
                                        v-model="editForm.role"
                                        class="h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm"
                                    >
                                        <option value="admin">Admin</option>
                                        <option value="staff">Staff</option>
                                        <option value="client">Client</option>
                                    </select>
                                    <InputError :message="editForm.errors.role" />
                                </div>
                                <DialogFooter>
                                    <Button
                                        type="button"
                                        variant="outline"
                                        @click="editMemberOpen = false"
                                    >
                                        Cancel
                                    </Button>
                                    <Button
                                        type="submit"
                                        :disabled="editForm.processing"
                                    >
                                        Update role
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
                                <th class="p-3 text-left font-medium">Name</th>
                                <th class="p-3 text-left font-medium">Email</th>
                                <th class="p-3 text-left font-medium">Role</th>
                                <th class="p-3 text-left font-medium">
                                    Status
                                </th>
                                <th
                                    v-if="canManageMembers"
                                    class="p-3 text-left font-medium"
                                >
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="member in members"
                                :key="member.id"
                                class="border-b border-sidebar-border last:border-0"
                            >
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
                                <td v-if="canManageMembers" class="p-3">
                                    <DropdownMenu>
                                        <DropdownMenuTrigger as-child>
                                            <Button
                                                variant="ghost"
                                                size="sm"
                                                class="h-8"
                                            >
                                                Actions
                                            </Button>
                                        </DropdownMenuTrigger>
                                        <DropdownMenuContent align="end">
                                            <DropdownMenuItem
                                                @click="openEditMember(member)"
                                            >
                                                Edit role
                                            </DropdownMenuItem>
                                            <DropdownMenuItem
                                                v-if="
                                                    (page.props.auth as { user?: { id: number } })?.user?.id !== member.id
                                                "
                                                class="text-destructive"
                                                @click="removeMember(member)"
                                            >
                                                Remove
                                            </DropdownMenuItem>
                                        </DropdownMenuContent>
                                    </DropdownMenu>
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
        </div>
    </AppLayout>
</template>
