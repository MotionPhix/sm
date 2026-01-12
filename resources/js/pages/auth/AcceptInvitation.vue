<script setup lang="ts">
import { Head, Form } from '@inertiajs/vue3'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import InputError from '@/components/InputError.vue'
import AuthLayout from '@/layouts/AuthLayout.vue'

import { store } from '@/routes/invitations'
import { Field, FieldGroup, FieldLabel } from '@/components/ui/field'

interface Props {
    name: string
    email: string
    school: string
    role: string
    token: string
    userExists: boolean
}

defineProps<Props>()
</script>

<template>
    <AuthLayout>
        <Head title="Accept invitation" />

        <div class="mx-auto space-y-6 w-full">
            <div class="space-y-2">
                <h1 class="text-2xl font-semibold">
                    You've been invited
                </h1>

                <p class="text-muted-foreground">
                    Join <strong>{{ school }}</strong> as a 
                    <strong>{{ role }}</strong>
                </p>
            </div>

            <Form
                v-bind="store.form(token)"
                v-slot="{ processing, errors }"
                :transform="data => ({ ...data, email: email, name: name })">
                <FieldGroup>
                    <!-- Email (always shown, read-only) -->
                    <Field>
                        <FieldLabel>Email</FieldLabel>
                        <Input
                            name="email"
                            :default-value="email"
                            disabled
                        />
                    </Field>

                    <!-- Name (only shown for new users, read-only) -->
                    <Field disabled>
                        <FieldLabel>Name</FieldLabel>
                        <Input
                            name="name"
                            :default-value="name"
                            disabled
                        />
                    </Field>

                    <!-- Password (only for new users) -->
                    <Field v-if="!userExists">
                        <FieldLabel for="password">Password</FieldLabel>
                        <Input
                            id="password"
                            name="password"
                            type="password"
                            autocomplete="new-password"
                        />

                        <InputError :message="errors.password" />
                    </Field>
                </FieldGroup>

                <Button
                    class="w-full mt-8"
                    :disabled="processing">
                    {{ userExists ? 'Join school' : 'Create account & join' }}
                </Button>
            </Form>

            <p class="text-center text-sm text-muted-foreground">
                By continuing, you will be signed in and added to this school.
            </p>
        </div>
    </AuthLayout>
</template>
    