<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'
import { Button } from '@/components/ui/button'
import AppLayout from '@/layouts/AppLayout.vue'
import AdminSettingsLayout from '@/layouts/AdminSettingsLayout.vue'
import HeadingSmall from '@/components/HeadingSmall.vue'
import { Edit2, School, Mail, Phone, MapPin, Globe, Building, Check } from 'lucide-vue-next'
import { Badge } from '@/components/ui/badge'
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert'
import { ModalLink } from '@inertiaui/modal-vue'
import { useBreadcrumbs } from '@/composables/useBreadcrumbs'
import { useAdminSettingsNavigation } from '@/composables/useAdminSettingsNavigation'
import { edit as schoolProfileEdit } from '@/routes/admin/settings/school-profile'

interface SchoolData {
    id: number
    name: string
    code?: string
    email?: string
    phone?: string
    type?: string
    district?: string
    country?: string
    is_active: boolean
}

const props = defineProps<{
    school: SchoolData
    schoolTypeLabel?: string
}>()

const page = usePage()
const flash = computed(() => page.props.flash as { success?: string; error?: string })
const { adminSettingsBreadcrumbs } = useBreadcrumbs()
const { adminSettingsNavItems } = useAdminSettingsNavigation()

const breadcrumbs = adminSettingsBreadcrumbs('School Profile')
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="School Profile" />

        <template #act>
            <Button :as="ModalLink" :href="schoolProfileEdit().url">
                <Edit2 class="mr-2 h-4 w-4" />
                Edit Profile
            </Button>
        </template>

        <AdminSettingsLayout 
            title="School Settings"
            description="Configure your school's information and preferences" 
            :items="adminSettingsNavItems">
            <div class="space-y-6">
                <HeadingSmall 
                    title="School Profile"
                    description="View and manage your school's information" 
                />

                <!-- Success Alert -->
                <Alert v-if="flash?.success"
                    class="border-green-200 bg-green-50 dark:border-green-800 dark:bg-green-900/30">
                    <Check class="h-4 w-4 text-green-600" />
                    <AlertTitle class="text-green-800 dark:text-green-200">Success</AlertTitle>
                    <AlertDescription class="text-green-700 dark:text-green-300">
                        {{ flash.success }}
                    </AlertDescription>
                </Alert>

                <!-- Basic Information Card -->
                <div class="rounded-lg border bg-card">
                    <div class="border-b px-6 py-4">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <School class="h-5 w-5 text-muted-foreground" />
                                <h2 class="text-lg font-semibold">Basic Information</h2>
                            </div>
                            <Badge :variant="school.is_active ? 'default' : 'secondary'">
                                {{ school.is_active ? 'Active' : 'Inactive' }}
                            </Badge>
                        </div>
                    </div>
                    <div class="p-6">
                        <dl class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <dt class="text-sm font-medium text-muted-foreground">School Name</dt>
                                <dd class="mt-1 text-base font-semibold">{{ school.name }}</dd>
                            </div>

                            <div v-if="school.code">
                                <dt class="text-sm font-medium text-muted-foreground">School Code</dt>
                                <dd class="mt-1">
                                    <Badge variant="outline">{{ school.code }}</Badge>
                                </dd>
                            </div>

                            <div v-if="props.schoolTypeLabel">
                                <dt class="text-sm font-medium text-muted-foreground flex items-center gap-2">
                                    <Building class="h-4 w-4" />
                                    School Type
                                </dt>
                                <dd class="mt-1 text-base">{{ props.schoolTypeLabel }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>

                <!-- Contact Information Card -->
                <div class="rounded-lg border bg-card">
                    <div class="border-b px-6 py-4">
                        <div class="flex items-center gap-2">
                            <Mail class="h-5 w-5 text-muted-foreground" />
                            <h2 class="text-lg font-semibold">Contact Information</h2>
                        </div>
                    </div>
                    <div class="p-6">
                        <dl class="grid gap-4 sm:grid-cols-2">
                            <div v-if="school.email">
                                <dt class="text-sm font-medium text-muted-foreground flex items-center gap-2">
                                    <Mail class="h-4 w-4" />
                                    Email Address
                                </dt>
                                <dd class="mt-1">
                                    <a :href="`mailto:${school.email}`" class="text-primary hover:underline">
                                        {{ school.email }}
                                    </a>
                                </dd>
                            </div>

                            <div v-if="school.phone">
                                <dt class="text-sm font-medium text-muted-foreground flex items-center gap-2">
                                    <Phone class="h-4 w-4" />
                                    Phone Number
                                </dt>
                                <dd class="mt-1">
                                    <a :href="`tel:${school.phone}`" class="hover:underline">
                                        {{ school.phone }}
                                    </a>
                                </dd>
                            </div>

                            <div v-if="!school.email && !school.phone" class="col-span-2">
                                <p class="text-sm text-muted-foreground">No contact information available</p>
                            </div>
                        </dl>
                    </div>
                </div>

                <!-- Location Card -->
                <div class="rounded-lg border bg-card">
                    <div class="border-b px-6 py-4">
                        <div class="flex items-center gap-2">
                            <MapPin class="h-5 w-5 text-muted-foreground" />
                            <h2 class="text-lg font-semibold">Location</h2>
                        </div>
                    </div>
                    <div class="p-6">
                        <dl class="grid gap-4 sm:grid-cols-2">
                            <div v-if="school.district">
                                <dt class="text-sm font-medium text-muted-foreground flex items-center gap-2">
                                    <MapPin class="h-4 w-4" />
                                    District
                                </dt>
                                <dd class="mt-1 text-base">{{ school.district }}</dd>
                            </div>

                            <div v-if="school.country">
                                <dt class="text-sm font-medium text-muted-foreground flex items-center gap-2">
                                    <Globe class="h-4 w-4" />
                                    Country
                                </dt>
                                <dd class="mt-1 text-base">{{ school.country }}</dd>
                            </div>

                            <div v-if="!school.district && !school.country" class="col-span-2">
                                <p class="text-sm text-muted-foreground">No location information available</p>
                            </div>
                        </dl>
                    </div>
                </div>
            </div>
        </AdminSettingsLayout>
    </AppLayout>
</template>
