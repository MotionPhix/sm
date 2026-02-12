<script setup lang="ts">
import ConfirmDialog from '@/components/ConfirmDialog.vue'
import HeadingSmall from '@/components/HeadingSmall.vue'
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert'
import { Badge } from '@/components/ui/badge'
import { Button } from '@/components/ui/button'
import { Empty, EmptyDescription, EmptyHeader, EmptyMedia, EmptyTitle } from '@/components/ui/empty'
import {
    Table,
    TableBody,
    TableCell,
    TableEmpty,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table'
import { useAdminSettingsNavigation } from '@/composables/useAdminSettingsNavigation'
import { useBreadcrumbs } from '@/composables/useBreadcrumbs'
import { useConfirm } from '@/composables/useConfirm'
import AdminSettingsLayout from '@/layouts/AdminSettingsLayout.vue'
import AppLayout from '@/layouts/AppLayout.vue'
import { create as plansCreate, destroy as plansDestroy, edit as plansEdit } from '@/routes/admin/settings/assessment-plans'
import { Head, router, usePage } from '@inertiajs/vue3'
import { ModalLink } from '@inertiaui/modal-vue'
import { AlertCircle, Check, ClipboardList, Edit2, Plus, Trash2 } from 'lucide-vue-next'
import { computed } from 'vue'

interface Term {
    id: number
    name: string
}

interface Subject {
    id: number
    name: string
    code: string
}

interface AssessmentPlan {
    id: number
    name: string
    ordering: number
    max_score: number
    weight: number
    is_active: boolean
    term: Term | null
    subject: Subject | null
}

interface PaginatedData<T> {
    data: T[]
    current_page: number
    last_page: number
    total: number
}

const props = defineProps<{
    assessmentPlans: PaginatedData<AssessmentPlan>
    terms: Term[]
    subjects: Subject[]
    academicYear: { id: number; name: string } | null
}>()

const page = usePage()
const flash = computed(() => page.props.flash as { success?: string; error?: string })
const { adminSettingsBreadcrumbs } = useBreadcrumbs()
const { adminSettingsNavItems } = useAdminSettingsNavigation()

const breadcrumbs = adminSettingsBreadcrumbs('Assessment Plans')

const { isOpen, title, description, confirmText, variant, confirm, handleConfirm, handleCancel } = useConfirm()

const confirmDelete = async (plan: AssessmentPlan) => {
    const confirmed = await confirm({
        title: 'Delete Assessment Plan',
        description: `Are you sure you want to delete "${plan.name}"? This action cannot be undone.`,
        confirmText: 'Delete',
        cancelText: 'Cancel',
        variant: 'destructive',
    })

    if (confirmed) {
        router.delete(plansDestroy(plan.id).url, {
            preserveScroll: true,
        })
    }
}

// Group assessment plans by term then subject
const groupedPlans = computed(() => {
    const groups: Record<string, { term: Term; subjects: Record<string, { subject: Subject; plans: AssessmentPlan[] }> }> = {}

    for (const plan of props.assessmentPlans.data) {
        const termKey = plan.term?.id?.toString() ?? 'none'
        const termName = plan.term?.name ?? 'No Term'

        if (!groups[termKey]) {
            groups[termKey] = { term: plan.term ?? { id: 0, name: termName }, subjects: {} }
        }

        const subjectKey = plan.subject?.id?.toString() ?? 'none'
        if (!groups[termKey].subjects[subjectKey]) {
            groups[termKey].subjects[subjectKey] = { subject: plan.subject ?? { id: 0, name: 'No Subject', code: '—' }, plans: [] }
        }

        groups[termKey].subjects[subjectKey].plans.push(plan)
    }

    return groups
})
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Assessment Plans" />

        <template #act>
            <Button :as="ModalLink" :href="plansCreate().url">
                <Plus class="mr-2 h-4 w-4" />
                Add Assessment Plan
            </Button>
        </template>

        <AdminSettingsLayout
            title="School Settings"
            description="Configure your school's academic structure"
            :items="adminSettingsNavItems">
            <div class="space-y-6">
                <HeadingSmall
                    title="Assessment Plans"
                    description="Define assessment components (exams, tests, assignments) for each subject per term"
                />

                <!-- Academic Year Notice -->
                <Alert v-if="!academicYear"
                    class="border-amber-200 bg-amber-50 dark:border-amber-800 dark:bg-amber-900/30">
                    <AlertCircle class="h-4 w-4 text-amber-600" />
                    <AlertTitle class="text-amber-800 dark:text-amber-200">No Active Academic Year</AlertTitle>
                    <AlertDescription class="text-amber-700 dark:text-amber-300">
                        Please set an active academic year before creating assessment plans.
                    </AlertDescription>
                </Alert>

                <!-- Error Alert -->
                <Alert v-if="flash?.error"
                    class="border-red-200 bg-red-50 dark:border-red-800 dark:bg-red-900/30">
                    <AlertCircle class="h-4 w-4 text-red-600" />
                    <AlertTitle class="text-red-800 dark:text-red-200">Error</AlertTitle>
                    <AlertDescription class="text-red-700 dark:text-red-300">
                        {{ flash.error }}
                    </AlertDescription>
                </Alert>

                <!-- Success Alert -->
                <Alert v-if="flash?.success"
                    class="border-green-200 bg-green-50 dark:border-green-800 dark:bg-green-900/30">
                    <Check class="h-4 w-4 text-green-600" />
                    <AlertTitle class="text-green-800 dark:text-green-200">Success</AlertTitle>
                    <AlertDescription class="text-green-700 dark:text-green-300">
                        {{ flash.success }}
                    </AlertDescription>
                </Alert>

                <!-- Assessment Plans Table -->
                <div class="rounded-lg border bg-card">
                    <div class="border-b px-6 py-4">
                        <div class="flex items-center gap-2">
                            <ClipboardList class="h-5 w-5 text-muted-foreground" />
                            <h2 class="text-lg font-semibold">Assessment Plans</h2>
                            <span v-if="academicYear" class="text-sm text-muted-foreground">
                                — {{ academicYear.name }}
                            </span>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Term</TableHead>
                                    <TableHead>Subject</TableHead>
                                    <TableHead>Assessment Name</TableHead>
                                    <TableHead class="text-center">Order</TableHead>
                                    <TableHead class="text-center">Max Score</TableHead>
                                    <TableHead class="text-center">Weight (%)</TableHead>
                                    <TableHead class="text-center">Status</TableHead>
                                    <TableHead class="text-right" />
                                </TableRow>
                            </TableHeader>
                            <TableBody v-if="assessmentPlans.data.length > 0">
                                <TableRow v-for="plan in assessmentPlans.data" :key="plan.id">
                                    <TableCell>
                                        {{ plan.term?.name ?? '—' }}
                                    </TableCell>
                                    <TableCell>
                                        <div class="flex items-center gap-2">
                                            <Badge variant="outline">{{ plan.subject?.code ?? '—' }}</Badge>
                                            <span>{{ plan.subject?.name ?? '—' }}</span>
                                        </div>
                                    </TableCell>
                                    <TableCell class="font-medium">
                                        {{ plan.name }}
                                    </TableCell>
                                    <TableCell class="text-center">
                                        {{ plan.ordering }}
                                    </TableCell>
                                    <TableCell class="text-center">
                                        {{ plan.max_score }}
                                    </TableCell>
                                    <TableCell class="text-center">
                                        {{ plan.weight }}%
                                    </TableCell>
                                    <TableCell class="text-center">
                                        <Badge :variant="plan.is_active ? 'default' : 'secondary'">
                                            {{ plan.is_active ? 'Active' : 'Inactive' }}
                                        </Badge>
                                    </TableCell>
                                    <TableCell class="text-right">
                                        <div class="flex justify-end gap-2">
                                            <Button variant="ghost" size="sm" :as="ModalLink"
                                                :href="plansEdit(plan.id).url">
                                                <Edit2 class="h-4 w-4" />
                                            </Button>
                                            <Button variant="ghost" size="sm" @click="confirmDelete(plan)">
                                                <Trash2 class="h-4 w-4 text-destructive" />
                                            </Button>
                                        </div>
                                    </TableCell>
                                </TableRow>
                            </TableBody>

                            <TableEmpty v-if="assessmentPlans.data.length === 0" :colspan="8">
                                <Empty>
                                    <EmptyHeader>
                                        <EmptyMedia>
                                            <ClipboardList :size="64" />
                                        </EmptyMedia>
                                        <EmptyTitle>No assessment plans defined</EmptyTitle>
                                        <EmptyDescription>
                                            Create assessment plans to define how students will be evaluated (e.g., Mid-Term Test, End of Term Exam).
                                        </EmptyDescription>
                                    </EmptyHeader>
                                    <Button :as="ModalLink" :href="plansCreate().url">
                                        <Plus class="mr-2 h-4 w-4" />
                                        Add Your First Assessment Plan
                                    </Button>
                                </Empty>
                            </TableEmpty>
                        </Table>
                    </div>
                </div>

                <!-- Info Card -->
                <div class="rounded-lg border bg-blue-50 p-4 dark:bg-blue-900/20">
                    <div class="flex">
                        <div class="shrink-0">
                            <ClipboardList class="h-5 w-5 text-blue-600 dark:text-blue-400" />
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-blue-800 dark:text-blue-200">Assessment Plans</h3>
                            <p class="mt-1 text-sm text-blue-700 dark:text-blue-300">
                                Assessment plans define how students are evaluated per subject per term. Each plan has a maximum score and a weight that determines its contribution to the final grade. Ensure weights for each subject within a term sum to 100%.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <ConfirmDialog :open="isOpen" :title="title" :description="description" :confirm-text="confirmText"
                :variant="variant" @confirm="handleConfirm" @cancel="handleCancel"
                @update:open="(val) => !val && handleCancel()" />
        </AdminSettingsLayout>
    </AppLayout>
</template>
