<script setup lang="ts">
import { Form, Head, Link, usePage } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { dashboard } from '@/routes';
import { Calendar, RefreshCw, Users } from 'lucide-vue-next';

const page = usePage();
const auth = page.props.auth as { can_manage_organization?: boolean };
const canManageOrganization = auth?.can_manage_organization ?? false;

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
}

const props = defineProps<{
    organization: Organization | null;
    subscription: Subscription | null;
    plan: Plan | null;
    member_count: number;
}>();

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
    const diff = Math.ceil((end.getTime() - now.getTime()) / (1000 * 60 * 60 * 24));
    return diff > 0 ? diff : 0;
}
</script>

<template>
    <Head title="My Plan" />

    <AppLayout :breadcrumbs="[{ title: 'My Plan', href: '/plan' }]">
        <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-4">
            <div class="flex flex-col gap-4">
                <h1 class="text-xl font-semibold">Plan & subscription</h1>

                <template v-if="organization">
                    <p class="text-muted-foreground">
                        Organization: <strong>{{ organization.name }}</strong>
                    </p>
                </template>

                <template v-if="plan && subscription">
                    <div class="grid gap-4 rounded-lg border border-sidebar-border p-4 md:grid-cols-2">
                        <div>
                            <h2 class="font-medium">Current plan</h2>
                            <p class="text-2xl font-semibold">{{ plan.name }}</p>
                            <p class="text-muted-foreground">
                                ${{ plan.price }}/{{ plan.billing_cycle }}
                            </p>
                        </div>
                        <div>
                            <h2 class="flex items-center gap-2 font-medium">
                                <Calendar class="h-4 w-4" />
                                Expires
                            </h2>
                            <p class="text-lg">
                                {{ formatDate(subscription.ends_at) }}
                            </p>
                            <p
                                v-if="daysLeft(subscription.ends_at) !== null"
                                class="text-sm text-muted-foreground"
                            >
                                {{ daysLeft(subscription.ends_at) }} days left
                            </p>
                            <Form
                                v-if="canManageOrganization"
                                method="post"
                                :action="'/plan/renew'"
                                class="mt-3"
                            >
                                <Button type="submit" variant="outline" size="sm">
                                    <RefreshCw class="mr-2 size-4" />
                                    Renew plan
                                </Button>
                            </Form>
                        </div>
                    </div>

                    <div class="rounded-lg border border-sidebar-border p-4">
                        <h2 class="mb-2 flex items-center gap-2 font-medium">
                            <Users class="h-4 w-4" />
                            Team members
                        </h2>
                        <p class="text-lg">
                            <span class="font-semibold">{{ member_count }}</span>
                            <template v-if="plan.member_limit !== null">
                                / {{ plan.member_limit }} used
                            </template>
                            <template v-else>
                                (unlimited)
                            </template>
                        </p>
                        <p class="mt-1 text-sm text-muted-foreground">
                            You can create
                            {{
                                plan.member_limit === null
                                    ? 'unlimited'
                                    : Math.max(0, plan.member_limit - member_count)
                            }}
                            more team member(s).
                        </p>
                    </div>

                    <div
                        v-if="plan.modules?.length"
                        class="rounded-lg border border-sidebar-border p-4"
                    >
                        <h2 class="mb-2 font-medium">Included modules</h2>
                        <ul class="list-inside list-disc text-muted-foreground">
                            <li
                                v-for="mod in plan.modules"
                                :key="mod.name"
                            >
                                {{ mod.name }}
                            </li>
                        </ul>
                    </div>

                    <div
                        v-if="plan.feature_limits?.length"
                        class="rounded-lg border border-sidebar-border p-4"
                    >
                        <h2 class="mb-2 font-medium">Feature limits</h2>
                        <ul class="space-y-1 text-sm text-muted-foreground">
                            <li
                                v-for="fl in plan.feature_limits"
                                :key="fl.feature_name"
                            >
                                {{ fl.feature_name }}:
                                {{ fl.limit_value === null ? 'Unlimited' : fl.limit_value }}
                            </li>
                        </ul>
                    </div>
                </template>

                <template v-else>
                    <p class="text-muted-foreground">No active subscription.</p>
                    <Link
                        :href="dashboard()"
                        class="text-primary underline"
                    >
                        Go to dashboard
                    </Link>
                </template>
            </div>
        </div>
    </AppLayout>
</template>
