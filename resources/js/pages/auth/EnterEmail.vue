<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import AuthBase from '@/layouts/AuthLayout.vue';

defineProps<{
    selectedPlanId: number;
}>();
</script>

<template>
    <AuthBase
        title="Enter your email"
        description="We'll sign you in or create an account based on your email."
    >
        <Head title="Enter Email" />

        <Form
            :action="'/check-email'"
            method="post"
            class="flex flex-col gap-6"
            v-slot="{ errors, processing }"
        >
            <div class="grid gap-2">
                <Label for="email">Email address</Label>
                <Input
                    id="email"
                    type="email"
                    name="email"
                    required
                    autofocus
                    autocomplete="email"
                    placeholder="you@example.com"
                />
                <InputError :message="errors.email" />
            </div>

            <Button type="submit" class="w-full" :disabled="processing">
                <Spinner v-if="processing" />
                Continue
            </Button>
        </Form>
    </AuthBase>
</template>
