<script setup lang="ts">
import { Form, Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { Building2, Calendar, Plus, RefreshCw, Users } from 'lucide-vue-next';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';

interface Plan {
    id: number;
    name: string;
    member_limit: number | null;
    price: string;
    billing_cycle: string;
    modules: Array<{ name: string }>;
    feature_limits: Array<{ feature_name: string; limit_value: number | null }>;
}

interface Subscription {
    id: number;
    status: string;
    starts_at: string | null;
    ends_at: string | null;
}

interface Organization {
    id: number;
    name: string;
    slug: string;
    email: string | null;
    phone: string | null;
    address: string | null;
}

const props = defineProps<{
    organization: Organization;
    subscription: Subscription | null;
    plan: Plan | null;
    member_count: number;
    can_manage_organization: boolean;
}>();

const page = usePage();
const flash = page.props.flash as { success?: string; error?: string } | undefined;

const form = useForm({
    name: props.organization.name,
    email: props.organization.email ?? '',
    phone: props.organization.phone ?? '',
    address: props.organization.address ?? '',
});

function formatDate(iso: string | null): string {
    if (!iso) return '—';
    const d = new Date(iso);
    return d.toLocaleDateString(undefined, {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
}

function daysLeft(iso: string | null): number | null {
    if (!iso) return null;
    const end = new Date(iso);
    const now = new Date();
    const diff = Math.ceil(
        (end.getTime() - now.getTime()) / (1000 * 60 * 60 * 24),
    );
    return diff > 0 ? diff : 0;
}
</script>

<template>
    <Head title="Organization" />

    <AppLayout :breadcrumbs="[{ title: 'Organization', href: '/organization' }]">
        <div
            class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-4"
        >
            <div class="flex flex-col gap-6">
                <h1 class="text-xl font-semibold">Organization</h1>

                <!-- Success/Error flash -->
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

                <!-- Organization details (editable) -->
                <div
                    class="rounded-xl border border-sidebar-border/70 bg-card p-6 dark:border-sidebar-border"
                >
                    <h2 class="mb-4 flex items-center gap-2 font-medium">
                        <Building2 class="size-5" />
                        Organization details
                    </h2>
                    <form
                        v-if="can_manage_organization"
                        class="grid gap-4 sm:grid-cols-2"
                        @submit.prevent="form.put(`/organization/${organization.id}`)"
                    >
                        <div class="grid gap-2 sm:col-span-2">
                            <Label for="name">Organization name</Label>
                            <Input
                                id="name"
                                v-model="form.name"
                                name="name"
                                required
                                autocomplete="organization"
                            />
                            <InputError :message="form.errors.name" />
                        </div>
                        <div class="grid gap-2">
                            <Label for="email">Email</Label>
                            <Input
                                id="email"
                                v-model="form.email"
                                type="email"
                                name="email"
                                autocomplete="email"
                            />
                            <InputError :message="form.errors.email" />
                        </div>
                        <div class="grid gap-2">
                            <Label for="phone">Phone</Label>
                            <Input
                                id="phone"
                                v-model="form.phone"
                                type="text"
                                name="phone"
                            />
                            <InputError :message="form.errors.phone" />
                        </div>
                        <div class="grid gap-2 sm:col-span-2">
                            <Label for="address">Address</Label>
                            <Input
                                id="address"
                                v-model="form.address"
                                type="text"
                                name="address"
                            />
                            <InputError :message="form.errors.address" />
                        </div>
                        <div class="sm:col-span-2">
                            <Button type="submit" :disabled="form.processing">
                                <Spinner v-if="form.processing" class="size-4" />
                                Update organization
                            </Button>
                        </div>
                    </form>
                    <div v-else class="space-y-2">
                        <p class="font-medium">{{ organization.name }}</p>
                        <p v-if="organization.email" class="text-sm text-muted-foreground">
                            {{ organization.email }}
                        </p>
                        <p v-if="organization.phone" class="text-sm text-muted-foreground">
                            {{ organization.phone }}
                        </p>
                        <p v-if="organization.address" class="text-sm text-muted-foreground">
                            {{ organization.address }}
                        </p>
                    </div>
                </div>

                <!-- Plan details -->
                <div
                    v-if="plan && subscription"
                    class="rounded-xl border border-sidebar-border/70 bg-card p-6 dark:border-sidebar-border"
                >
                    <h2 class="mb-4 font-medium">Plan & subscription</h2>
                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <p class="text-2xl font-semibold">{{ plan.name }}</p>
                            <p class="text-muted-foreground">
                                ${{ plan.price }} / {{ plan.billing_cycle }}
                            </p>
                        </div>
                        <div>
                            <p class="flex items-center gap-2 font-medium">
                                <Calendar class="size-4" />
                                Expires {{ formatDate(subscription.ends_at) }}
                            </p>
                            <p
                                v-if="daysLeft(subscription.ends_at) !== null"
                                class="text-sm text-muted-foreground"
                            >
                                {{ daysLeft(subscription.ends_at) }} days left
                            </p>
                            <Form
                                v-if="can_manage_organization"
                                method="post"
                                action="/plan/renew"
                                class="mt-3"
                            >
                                <Button type="submit" variant="outline" size="sm">
                                    <RefreshCw class="mr-2 size-4" />
                                    Renew plan
                                </Button>
                            </Form>
                        </div>
                    </div>
                    <div class="mt-4 flex items-center gap-2">
                        <Users class="size-4 text-muted-foreground" />
                        <span>
                            {{ member_count }}
                            <template v-if="plan.member_limit !== null">
                                / {{ plan.member_limit }} members
                            </template>
                            <template v-else>(unlimited)</template>
                        </span>
                    </div>
                    <div v-if="plan.modules?.length" class="mt-4">
                        <p class="mb-2 text-sm font-medium">Included modules</p>
                        <ul class="list-inside list-disc text-sm text-muted-foreground">
                            <li v-for="mod in plan.modules" :key="mod.name">
                                {{ mod.name }}
                            </li>
                        </ul>
                    </div>
                    <div
                        v-if="plan.feature_limits?.length"
                        class="mt-4"
                    >
                        <p class="mb-2 text-sm font-medium">Feature limits</p>
                        <ul class="space-y-1 text-sm text-muted-foreground">
                            <li
                                v-for="fl in plan.feature_limits"
                                :key="fl.feature_name"
                            >
                                {{ fl.feature_name }}:
                                {{
                                    fl.limit_value === null
                                        ? 'Unlimited'
                                        : fl.limit_value
                                }}
                            </li>
                        </ul>
                    </div>
                </div>

                <div v-else class="rounded-xl border border-sidebar-border/70 bg-card p-6 dark:border-sidebar-border">
                    <p class="text-muted-foreground">No active subscription for this organization.</p>
                    <Link :href="dashboard()" class="mt-2 inline-block text-primary underline">
                        Go to dashboard
                    </Link>
                </div>

                <!-- Create Organization -->
                <div
                    class="rounded-xl border border-sidebar-border/70 bg-card p-6 dark:border-sidebar-border"
                >
                    <h2 class="mb-2 font-medium">Create another organization</h2>
                    <p class="mb-4 text-sm text-muted-foreground">
                        Each organization has its own plan and charges.
                    </p>
                    <Link href="/onboarding/organizations/create">
                        <Button variant="outline">
                            <Plus class="mr-2 size-4" />
                            Create organization
                        </Button>
                    </Link>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
