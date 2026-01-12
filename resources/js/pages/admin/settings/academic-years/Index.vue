<script setup lang="ts">
import { Head, Form } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import HeadingSmall from '@/components/HeadingSmall.vue'
import InputError from '@/components/InputError.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Badge } from '@/components/ui/badge'

import { store } from '@/routes/admin/settings/academic-year'

defineProps<{
    academicYears: Array<{
        id: number
        name: string
        starts_at: string
        ends_at: string
        is_current: boolean
    }>
}>()
</script>

<template>
    <AppLayout>
        <Head title="Academic Years" />

        <div class="space-y-6">
            <HeadingSmall
                title="Academic Years"
                description="Manage school academic calendar. Only one academic year can be active."
            />

            <!-- Create Academic Year -->
            <Form
                v-bind="store.form()"
                class="grid gap-4 max-w-lg"
                v-slot="{ errors, processing }">
                <div class="grid gap-2">
                    <Label>Name</Label>
                    <Input name="name" placeholder="2024/2025" />
                    <InputError :message="errors.name" />
                </div>

                <div class="grid gap-2">
                    <Label>Starts At</Label>
                    <Input type="date" name="starts_at" />
                    <InputError :message="errors.starts_at" />
                </div>

                <div class="grid gap-2">
                    <Label>Ends At</Label>
                    <Input type="date" name="ends_at" />
                    <InputError :message="errors.ends_at" />
                </div>

                <Button :disabled="processing">
                    Create & Set Current
                </Button>
            </Form>

            <!-- Existing Academic Years -->
            <div class="border rounded-lg divide-y">
                <div
                    v-for="year in academicYears"
                    :key="year.id"
                    class="flex items-center justify-between p-4"
                >
                    <div>
                        <p class="font-medium">{{ year.name }}</p>
                        <p class="text-sm text-muted-foreground">
                            {{ year.starts_at }} → {{ year.ends_at }}
                        </p>
                    </div>

                    <Badge v-if="year.is_current">Current</Badge>
                    <Badge v-else variant="secondary">Locked</Badge>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
