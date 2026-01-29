<script setup lang="ts">
import { Head, router, usePage } from '@inertiajs/vue3'
import { ref, computed } from 'vue'
import { Button } from '@/components/ui/button'
import AppLayout from '@/layouts/AppLayout.vue'
import AdminSettingsLayout from '@/layouts/AdminSettingsLayout.vue'
import HeadingSmall from '@/components/HeadingSmall.vue'
import InputError from '@/components/InputError.vue'
import type { BreadcrumbItemType } from '@/types'
import { Plus, Trash2, Edit2, GraduationCap, Calendar, Check, Clock } from 'lucide-vue-next'
import { Badge } from '@/components/ui/badge'
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert'
import { ModalLink } from '@inertiaui/modal-vue'
import ConfirmDialog from '@/components/ConfirmDialog.vue'
import { useConfirm } from '@/composables/useConfirm'
import { useRoleRoutes } from '@/composables/useRoleRoutes'
// Wayfinder routes - imported directly for type safety
import { create as admissionCyclesCreate, edit as admissionCyclesEdit, destroy as admissionCyclesDestroy } from '@/routes/admin/settings/admission-cycles'
import { index as academicYearsIndex } from '@/routes/admin/settings/academic-year'
import { index as termsIndex } from '@/routes/admin/settings/terms'
import { useBreadcrumbs } from '@/composables/useBreadcrumbs'

interface AdmissionCycle {
    id: number
    name: string
    starts_at: string
    ends_at: string
    target_class: string
    max_intake: number | null
    is_active: boolean
}

const props = defineProps<{
    admissionCycles: AdmissionCycle[]
}>()

const page = usePage()
const flash = computed(() => page.props.flash as { success?: string; error?: string })

const { adminSettingsBreadcrumbs } = useBreadcrumbs()

const navItems = [
    {
        title: 'Academic Years',
        href: academicYearsIndex().url,
    },
    {
        title: 'Terms',
        href: termsIndex().url,
    },
    {
        title: 'Admission Cycles',
        href: '#',
    },
]

const breadcrumbs = adminSettingsBreadcrumbs('Admission Cycles')

const { isOpen, title, description, confirmText, variant, confirm, handleConfirm, handleCancel } = useConfirm()
const { canAccessAdmin } = useRoleRoutes()
const cycleToDelete = ref<AdmissionCycle | null>(null)

const confirmDelete = async (cycle: AdmissionCycle) => {
    const confirmed = await confirm({
        title: 'Delete Admission Cycle',
        description: `Are you sure you want to delete "${cycle.name}"? This action cannot be undone.`,
        confirmText: 'Delete',
        cancelText: 'Cancel',
        variant: 'destructive',
    })

    if (confirmed) {
        router.delete(admissionCyclesDestroy(cycle.id).url, {
            preserveScroll: true,
        })
    }
}

const formatDate = (dateStr: string) => {
    if (!dateStr) return ''
    const date = new Date(dateStr)
    return date.toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' })
}

const isActive = (cycle: AdmissionCycle) => {
    const now = new Date()
    const start = new Date(cycle.starts_at)
    const end = new Date(cycle.ends_at)
    return now >= start && now <= end
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">

        <Head title="Admission Cycles" />

        <AdminSettingsLayout title="School Settings"
            description="Configure your school's academic calendar and other settings" :items="navItems">
            <div class="space-y-6">
                <div class="flex items-center justify-between">
                    <HeadingSmall title="Admission Cycles"
                        description="Manage admission windows for different classes and intake periods" />
                    <Button :as="ModalLink" :href="admissionCyclesCreate().url">
                        <Plus class="mr-2 h-4 w-4" />
                        New Cycle
                    </Button>
                </div>

                <!-- Success Alert -->
                <Alert v-if="flash?.success"
                    class="border-green-200 bg-green-50 dark:border-green-800 dark:bg-green-900/30">
                    <Check class="h-4 w-4 text-green-600" />
                    <AlertTitle class="text-green-800 dark:text-green-200">Success</AlertTitle>
                    <AlertDescription class="text-green-700 dark:text-green-300">
                        {{ flash.success }}
                    </AlertDescription>
                </Alert>

                <!-- Admission Cycles Table -->
                <div class="rounded-lg border bg-card">
                    <div class="border-b px-6 py-4">
                        <div class="flex items-center gap-2">
                            <GraduationCap class="h-5 w-5 text-muted-foreground" />
                            <h2 class="text-lg font-semibold">Admission Cycles</h2>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full">
                            <thead>
                                <tr class="border-b bg-muted/50">
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Name
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Target
                                        Class</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">
                                        Duration</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Max
                                        Intake</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Status
                                    </th>
                                    <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider">
                                        Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                <tr v-for="cycle in admissionCycles" :key="cycle.id" class="hover:bg-muted/30">
                                    <td class="whitespace-nowrap px-6 py-4 font-medium">
                                        {{ cycle.name }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm">
                                        <Badge variant="outline">{{ cycle.target_class }}</Badge>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-muted-foreground">
                                        <div class="flex items-center gap-1">
                                            <Calendar class="h-3.5 w-3.5" />
                                            {{ formatDate(cycle.starts_at) }} → {{ formatDate(cycle.ends_at) }}
                                        </div>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm">
                                        {{ cycle.max_intake ? `${cycle.max_intake} students` : 'Unlimited' }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4">
                                        <div v-if="isActive(cycle)" class="flex items-center gap-2">
                                            <Clock class="h-4 w-4 text-amber-600" />
                                            <span
                                                class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300">
                                                Active
                                            </span>
                                        </div>
                                        <span v-else
                                            class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300">
                                            Inactive
                                        </span>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-right">
                                        <div class="flex justify-end gap-2">
                                            <Button variant="ghost" size="sm" :as="ModalLink"
                                                :href="admissionCyclesEdit(cycle.id).url">
                                                <Edit2 class="h-4 w-4" />
                                            </Button>
                                            <Button variant="ghost" size="sm" @click="confirmDelete(cycle)">
                                                <Trash2 class="h-4 w-4 text-destructive" />
                                            </Button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="admissionCycles.length === 0">
                                    <td colspan="6" class="px-6 py-12 text-center">
                                        <GraduationCap class="mx-auto h-12 w-12 text-muted-foreground" />
                                        <h3 class="mt-4 text-sm font-medium">No admission cycles</h3>
                                        <p class="mt-2 text-sm text-muted-foreground">
                                            Create an admission cycle to start accepting applications for a specific
                                            class.
                                        </p>
                                        <Button class="mt-4" :as="ModalLink" :href="admissionCyclesCreate().url">
                                            <Plus class="mr-2 h-4 w-4" />
                                            Create First Cycle
                                        </Button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Delete Confirmation Dialog -->
            <ConfirmDialog :open="isOpen" :title="title" :description="description" :confirm-text="confirmText"
                :variant="variant" @confirm="handleConfirm" @cancel="handleCancel"
                @update:open="(val) => !val && handleCancel()" />
            </AdminSettingsLayout>
    </AppLayout>
</template>
