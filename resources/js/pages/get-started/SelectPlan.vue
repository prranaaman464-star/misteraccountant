<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Spinner } from '@/components/ui/spinner';
import AuthBase from '@/layouts/AuthLayout.vue';

interface Plan {
    id: number;
    name: string;
    slug: string;
    description: string | null;
    member_limit: number | null;
    price: string;
    billing_cycle: string;
    modules: Array<{ name: string; slug: string }>;
    feature_limits: Array<{ feature_name: string; limit_value: number | null }>;
}

defineProps<{
    plans: Plan[];
}>();
</script>

<template>
    <AuthBase
        title="Choose your plan"
        description="Select a plan to get started. You'll sign in or create an account next."
    >
        <Head title="Select Plan" />

        <div class="grid gap-4">
            <Form
                v-for="plan in plans"
                :key="plan.id"
                :action="'/plans/select'"
                method="post"
                class="rounded-lg border border-sidebar-border p-4 transition-colors hover:border-primary/50"
            >
                <input type="hidden" name="plan_id" :value="plan.id" />
                <div class="flex flex-col gap-2">
                    <div class="font-semibold">{{ plan.name }}</div>
                    <p
                        v-if="plan.description"
                        class="text-sm text-muted-foreground"
                    >
                        {{ plan.description }}
                    </p>
                    <div class="mt-2 flex flex-wrap gap-2 text-sm">
                        <span v-if="plan.member_limit">
                            {{ plan.member_limit }} team members
                        </span>
                        <span class="font-medium">
                            ${{ plan.price }}/{{ plan.billing_cycle }}
                        </span>
                    </div>
                    <ul
                        v-if="plan.modules?.length"
                        class="mt-2 list-inside list-disc text-sm text-muted-foreground"
                    >
                        <li
                            v-for="mod in plan.modules"
                            :key="mod.slug"
                        >
                            {{ mod.name }}
                        </li>
                    </ul>
                    <Button type="submit" class="mt-3 w-full sm:w-auto">
                        Get started
                    </Button>
                </div>
            </Form>
        </div>

        <p class="mt-4 text-center text-sm text-muted-foreground">
            Already have an account?
            <Link href="/login" class="underline">Log in</Link>
        </p>
    </AuthBase>
</template>
