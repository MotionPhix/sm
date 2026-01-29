<script setup lang="ts">
import { Head, Form } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import AdminSettingsLayout from '@/layouts/AdminSettingsLayout.vue'
import HeadingSmall from '@/components/HeadingSmall.vue'
import InputError from '@/components/InputError.vue'
import DatePicker from '@/components/DatePicker.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Badge } from '@/components/ui/badge'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'

import { store } from '@/routes/admin/settings/academic-year'
import { useBreadcrumbs } from '@/composables/useBreadcrumbs'
import { useAdminSettingsNavigation } from '@/composables/useAdminSettingsNavigation'
import { Calendar } from 'lucide-vue-next'
import { computed, ref } from 'vue'
import { Spinner } from '@/components/ui/spinner'

defineProps<{
    academicYears: Array<{
        id: number
        name: string
        starts_at: string
        ends_at: string
        is_current: boolean
    }>
}>()

const { adminSettingsBreadcrumbs } = useBreadcrumbs()
const { adminSettingsNavItems } = useAdminSettingsNavigation()
const formRef = ref()

const breadcrumbs = adminSettingsBreadcrumbs('Academic Years')
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">

        <Head title="Academic Years" />

        <template #act>
            <Button 
                @click="formRef?.submit"
                :disabled="formRef?.processing">
                <Spinner v-if="formRef?.processing" />
                {{ formRef?.processing ? 'Creating...' : 'Create & Set Current' }}
            </Button>
        </template>

        <AdminSettingsLayout title="School Settings"
            description="Configure your school's academic calendar and other settings" :items="adminSettingsNavItems">
            <div class="space-y-6">
                <HeadingSmall title="Academic Years"
                    description="Manage school academic calendar. Only one academic year can be active." />

                <!-- Create Academic Year Form -->
                <Card>
                    <CardHeader>
                        <CardTitle>Create New Academic Year</CardTitle>
                    </CardHeader>

                    <CardContent>
                        <Form v-bind="store.form()" ref="formRef" class="space-y-6" v-slot="{ errors }">
                            <div class="grid gap-2">
                                <Label for="name">Academic Year Name</Label>
                                <Input id="name" name="name" placeholder="e.g., 2024/2025" required />
                                <InputError class="mt-2" :message="errors.name" />
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div class="grid gap-2">
                                    <Label for="starts_at">Start Date</Label>
                                    <DatePicker name="starts_at" placeholder="Pick a start date" />
                                    <InputError class="mt-2" :message="errors.starts_at" />
                                </div>

                                <div class="grid gap-2">
                                    <Label for="ends_at">End Date</Label>
                                    <DatePicker name="ends_at" placeholder="Pick an end date" />
                                    <InputError class="mt-2" :message="errors.ends_at" />
                                </div>
                            </div>
                        </Form>
                    </CardContent>
                </Card>

                <!-- Existing Academic Years -->
                <Card v-if="academicYears.length > 0">
                    <CardHeader>
                        <div class="flex items-center gap-2">
                            <Calendar class="h-5 w-5" />
                            <CardTitle>Academic Years</CardTitle>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-3">
                            <div v-for="year in academicYears" :key="year.id"
                                class="flex items-center justify-between p-4 rounded-lg border hover:bg-accent/50 transition-colors">
                                <div>
                                    <p class="font-medium">{{ year.name }}</p>
                                    <p class="text-sm text-muted-foreground">
                                        {{ new Date(year.starts_at).toLocaleDateString() }} →
                                        {{ new Date(year.ends_at).toLocaleDateString() }}
                                    </p>
                                </div>

                                <Badge v-if="year.is_current" class="bg-green-600">
                                    Current
                                </Badge>
                                <Badge v-else variant="secondary">Locked</Badge>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Empty State -->
                <Card v-else>
                    <CardContent class="pt-6">
                        <div class="text-center py-8">
                            <Calendar class="h-12 w-12 text-muted-foreground mx-auto mb-4" />
                            <p class="text-muted-foreground">No academic years created yet</p>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </AdminSettingsLayout>        
    </AppLayout>
</template>
