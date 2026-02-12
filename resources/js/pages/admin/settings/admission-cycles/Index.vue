<script setup lang="ts">
import ConfirmDialog from '@/components/ConfirmDialog.vue'
import HeadingSmall from '@/components/HeadingSmall.vue'
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert'
import { Badge } from '@/components/ui/badge'
import { Button } from '@/components/ui/button'
import { useAdminSettingsNavigation } from '@/composables/useAdminSettingsNavigation'
import { useConfirm } from '@/composables/useConfirm'
import { useRoleRoutes } from '@/composables/useRoleRoutes'
import AdminSettingsLayout from '@/layouts/AdminSettingsLayout.vue'
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, router, usePage } from '@inertiajs/vue3'
import { ModalLink } from '@inertiaui/modal-vue'
import { Calendar, Check, Clock, Edit2, GraduationCap, Plus, Trash2 } from 'lucide-vue-next'
import { computed, ref } from 'vue'
// Wayfinder routes - imported directly for type safety
import { Empty, EmptyDescription, EmptyHeader, EmptyMedia, EmptyTitle } from '@/components/ui/empty'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table'
import { useBreadcrumbs } from '@/composables/useBreadcrumbs'
import { create as admissionCyclesCreate, destroy as admissionCyclesDestroy, edit as admissionCyclesEdit } from '@/routes/admin/settings/admission-cycles'

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
const { adminSettingsNavItems } = useAdminSettingsNavigation()

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

        <template #act>
            <Button
                :as="ModalLink"
                :href="admissionCyclesCreate().url">
                <Plus />
                New Cycle
            </Button>
        </template>

        <AdminSettingsLayout
            title="School Settings"
            description="Configure your school's academic calendar and other settings"
            :items="adminSettingsNavItems">
            <div class="space-y-6">
                <HeadingSmall
                    title="Admission Cycles"
                    description="Manage admission windows for different classes and intake periods"
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

                <!-- Admission Cycles Table -->
                <div class="rounded-lg border bg-card">
                    <div class="border-b px-6 py-4">
                        <div class="flex items-center gap-2">
                            <GraduationCap class="h-5 w-5 text-muted-foreground" />
                            <h2 class="text-lg font-semibold">Admission Cycles</h2>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>
                                        Name
                                    </TableHead>

                                    <TableHead>
                                        Target Class
                                    </TableHead>

                                    <TableHead>
                                        Duration
                                    </TableHead>

                                    <TableHead>
                                        Max Intake
                                    </TableHead>

                                    <TableHead>
                                        Status
                                    </TableHead>

                                    <TableHead class="text-right" />
                                </TableRow>
                            </TableHeader>

                            <TableBody>
                                <TableRow v-for="cycle in admissionCycles" :key="cycle.id">
                                    <TableCell>
                                        {{ cycle.name }}
                                    </TableCell>

                                    <TableCell>
                                        <Badge variant="outline">{{ cycle.target_class }}</Badge>
                                    </TableCell>

                                    <TableCell>
                                        <div class="flex items-center gap-1">
                                            <Calendar class="h-3.5 w-3.5" />
                                            {{ formatDate(cycle.starts_at) }} → {{ formatDate(cycle.ends_at) }}
                                        </div>
                                    </TableCell>

                                    <TableCell>
                                        {{ cycle.max_intake ? `${cycle.max_intake} students` : 'Unlimited' }}
                                    </TableCell>

                                    <TableCell>
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
                                    </TableCell>

                                    <TableCell class="text-right">
                                        <div class="flex justify-end gap-2">
                                            <Button variant="ghost" size="sm" :as="ModalLink"
                                                :href="admissionCyclesEdit(cycle.id).url">
                                                <Edit2 class="h-4 w-4" />
                                            </Button>
                                            <Button variant="ghost" size="sm" @click="confirmDelete(cycle)">
                                                <Trash2 class="h-4 w-4 text-destructive" />
                                            </Button>
                                        </div>
                                    </TableCell>
                                </TableRow>

                                <TableRow v-if="admissionCycles.length === 0">
                                    <TableCell colspan="6" class="py-12">
                                        <Empty>
                                            <EmptyHeader>
                                                <EmptyMedia>
                                                    <GraduationCap class="h-12 w-12 text-muted-foreground" />
                                                </EmptyMedia>

                                                <EmptyTitle>
                                                    No Admission Cycles
                                                </EmptyTitle>

                                                <EmptyDescription>
                                                    Create an admission cycle to start accepting applications for a specific class.
                                                </EmptyDescription>
                                            </EmptyHeader>

                                            <Button
                                                :as="ModalLink"
                                                :href="admissionCyclesCreate().url">
                                                <Plus />
                                                Create First Cycle
                                            </Button>
                                        </Empty>
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
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
