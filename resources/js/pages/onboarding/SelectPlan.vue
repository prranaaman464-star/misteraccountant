<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
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

interface Organization {
    id: number;
    name: string;
}

defineProps<{
    organization: Organization;
    plans: Plan[];
}>();
</script>

<template>
    <AuthBase
        title="Choose your plan"
        :description="`Select a plan for ${organization?.name}`"
    >
        <Head title="Select Plan" />

        <Form
            :action="'/onboarding/plans'"
            method="post"
            class="flex flex-col gap-6"
            v-slot="{ processing }"
        >
            <div class="grid gap-4">
                <div
                    v-for="(plan, index) in plans"
                    :key="plan.id"
                    class="flex flex-col gap-2 rounded-lg border border-sidebar-border p-4 transition-colors hover:border-primary/50"
                >
                    <label class="flex cursor-pointer items-start gap-3">
                        <input
                            type="radio"
                            name="plan_id"
                            :value="plan.id"
                            :disabled="processing"
                            :required="true"
                            :checked="index === 0"
                            class="mt-1"
                        />
                        <div class="flex-1">
                            <div class="font-semibold">{{ plan.name }}</div>
                            <p
                                v-if="plan.description"
                                class="mt-1 text-sm text-muted-foreground"
                            >
                                {{ plan.description }}
                            </p>
                            <div class="mt-2 flex flex-wrap gap-2 text-sm">
                                <span v-if="plan.member_limit">
                                    {{ plan.member_limit }} members
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
                        </div>
                    </label>
                </div>
            </div>

            <Button
                type="submit"
                class="w-full"
                :disabled="processing"
            >
                <Spinner v-if="processing" />
                Activate & go to dashboard
            </Button>
        </Form>
    </AuthBase>
</template>
