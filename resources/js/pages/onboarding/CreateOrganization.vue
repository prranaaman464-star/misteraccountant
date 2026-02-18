<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import AuthBase from '@/layouts/AuthLayout.vue';
</script>

<template>
    <AuthBase
        title="Create your organization"
        description="Set up your organization to get started"
    >
        <Head title="Create Organization" />

        <Form
            :action="'/onboarding/organizations'"
            method="post"
            class="flex flex-col gap-6"
            v-slot="{ errors, processing }"
        >
            <div class="grid gap-6">
                <div class="grid gap-2">
                    <Label for="name">Organization name</Label>
                    <Input
                        id="name"
                        type="text"
                        required
                        autofocus
                        name="name"
                        placeholder="Acme Corp"
                        autocomplete="organization"
                    />
                    <InputError :message="errors.name" />
                </div>

                <div class="grid gap-2">
                    <Label for="email">Email (optional)</Label>
                    <Input
                        id="email"
                        type="email"
                        name="email"
                        placeholder="contact@acme.com"
                    />
                    <InputError :message="errors.email" />
                </div>

                <div class="grid gap-2">
                    <Label for="phone">Phone (optional)</Label>
                    <Input
                        id="phone"
                        type="text"
                        name="phone"
                        placeholder="+1 234 567 8900"
                    />
                    <InputError :message="errors.phone" />
                </div>

                <div class="grid gap-2">
                    <Label for="address">Address (optional)</Label>
                    <Input
                        id="address"
                        type="text"
                        name="address"
                        placeholder="123 Main St"
                    />
                    <InputError :message="errors.address" />
                </div>

                <Button
                    type="submit"
                    class="mt-2 w-full"
                    :disabled="processing"
                >
                    <Spinner v-if="processing" />
                    Continue to select plan
                </Button>
            </div>
        </Form>
    </AuthBase>
</template>
