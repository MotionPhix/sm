<script setup lang="ts">
import { Head, router, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'
import { Button } from '@/components/ui/button'
import AppLayout from '@/layouts/AppLayout.vue'
import AdminSettingsLayout from '@/layouts/AdminSettingsLayout.vue'
import HeadingSmall from '@/components/HeadingSmall.vue'
import { Plus, Trash2, Edit2, Users, Check, AlertCircle } from 'lucide-vue-next'
import { Badge } from '@/components/ui/badge'
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert'
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
    TableEmpty,
} from '@/components/ui/table'
import { ModalLink } from '@inertiaui/modal-vue'
import ConfirmDialog from '@/components/ConfirmDialog.vue'
import { useConfirm } from '@/composables/useConfirm'
import { useBreadcrumbs } from '@/composables/useBreadcrumbs'
import { useAdminSettingsNavigation } from '@/composables/useAdminSettingsNavigation'
import { create as assignmentsCreate, edit as assignmentsEdit, destroy as assignmentsDestroy } from '@/routes/admin/settings/teacher-assignments'
import { Empty, EmptyDescription, EmptyHeader, EmptyMedia, EmptyTitle } from '@/components/ui/empty'

interface Assignment {
    id: number
    teacher: { id: number; name: string; email: string } | null
    classroom: {
        id: number
        school_class: { id: number; name: string } | null
        stream: { id: number; name: string } | null
    } | null
    subject: { id: number; name: string; code: string } | null
}

const props = defineProps<{
    assignments: Assignment[]
}>()

const page = usePage()
const flash = computed(() => page.props.flash as { success?: string; error?: string })
const { adminSettingsBreadcrumbs } = useBreadcrumbs()
const { adminSettingsNavItems } = useAdminSettingsNavigation()

const breadcrumbs = adminSettingsBreadcrumbs('Teacher Assignments')

const { isOpen, title, description, confirmText, variant, confirm, handleConfirm, handleCancel } = useConfirm()

const classroomName = (assignment: Assignment) => {
    const className = assignment.classroom?.school_class?.name ?? 'Unknown'
    const streamName = assignment.classroom?.stream?.name
    return streamName ? `${className} - ${streamName}` : className
}

const confirmDelete = async (assignment: Assignment) => {
    const confirmed = await confirm({
        title: 'Delete Assignment',
        description: `Are you sure you want to remove ${assignment.teacher?.name ?? 'this teacher'} from ${classroomName(assignment)} (${assignment.subject?.name ?? 'Unknown'})? This action cannot be undone.`,
        confirmText: 'Delete',
        cancelText: 'Cancel',
        variant: 'destructive',
    })

    if (confirmed) {
        router.delete(assignmentsDestroy(assignment.id).url, {
            preserveScroll: true,
        })
    }
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Teacher Assignments" />

        <template #act>
            <Button :as="ModalLink" :href="assignmentsCreate().url">
                <Plus class="mr-2 h-4 w-4" />
                Add Assignment
            </Button>
        </template>

        <AdminSettingsLayout
            title="School Settings"
            description="Configure your school's academic structure"
            :items="adminSettingsNavItems">
            <div class="space-y-6">
                <HeadingSmall
                    title="Teacher Assignments"
                    description="Assign teachers to classes and subjects"
                />

                <Alert v-if="flash?.error"
                    class="border-red-200 bg-red-50 dark:border-red-800 dark:bg-red-900/30">
                    <AlertCircle class="h-4 w-4 text-red-600" />
                    <AlertTitle class="text-red-800 dark:text-red-200">Error</AlertTitle>
                    <AlertDescription class="text-red-700 dark:text-red-300">
                        {{ flash.error }}
                    </AlertDescription>
                </Alert>

                <Alert v-if="flash?.success"
                    class="border-green-200 bg-green-50 dark:border-green-800 dark:bg-green-900/30">
                    <Check class="h-4 w-4 text-green-600" />
                    <AlertTitle class="text-green-800 dark:text-green-200">Success</AlertTitle>
                    <AlertDescription class="text-green-700 dark:text-green-300">
                        {{ flash.success }}
                    </AlertDescription>
                </Alert>

                <div class="rounded-lg border bg-card">
                    <div class="border-b px-6 py-4">
                        <div class="flex items-center gap-2">
                            <Users class="h-5 w-5 text-muted-foreground" />
                            <h2 class="text-lg font-semibold">Teacher Assignments</h2>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Teacher</TableHead>
                                    <TableHead>Classroom</TableHead>
                                    <TableHead>Subject</TableHead>
                                    <TableHead class="text-right">Actions</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody v-if="assignments.length > 0">
                                <TableRow v-for="assignment in assignments" :key="assignment.id">
                                    <TableCell class="font-medium">
                                        {{ assignment.teacher?.name ?? 'Unknown' }}
                                    </TableCell>
                                    <TableCell>
                                        {{ classroomName(assignment) }}
                                    </TableCell>
                                    <TableCell>
                                        <Badge variant="outline">{{ assignment.subject?.code ?? '—' }}</Badge>
                                        {{ assignment.subject?.name ?? 'Unknown' }}
                                    </TableCell>
                                    <TableCell class="text-right">
                                        <div class="flex justify-end gap-2">
                                            <Button variant="ghost" size="sm" :as="ModalLink"
                                                :href="assignmentsEdit(assignment.id).url">
                                                <Edit2 class="h-4 w-4" />
                                            </Button>
                                            <Button variant="ghost" size="sm" @click="confirmDelete(assignment)">
                                                <Trash2 class="h-4 w-4 text-destructive" />
                                            </Button>
                                        </div>
                                    </TableCell>
                                </TableRow>
                            </TableBody>

                            <TableEmpty v-if="assignments.length === 0" :colspan="4">
                                <Empty>
                                    <EmptyHeader>
                                        <EmptyMedia>
                                            <Users :size="64" />
                                        </EmptyMedia>
                                        <EmptyTitle>No teacher assignments</EmptyTitle>
                                        <EmptyDescription>
                                            Assign teachers to classes and subjects to get started.
                                        </EmptyDescription>
                                    </EmptyHeader>
                                    <Button :as="ModalLink" :href="assignmentsCreate().url">
                                        <Plus class="mr-2 h-4 w-4" />
                                        Add First Assignment
                                    </Button>
                                </Empty>
                            </TableEmpty>
                        </Table>
                    </div>
                </div>

                <div class="rounded-lg border bg-blue-50 p-4 dark:bg-blue-900/20">
                    <div class="flex">
                        <div class="shrink-0">
                            <Users class="h-5 w-5 text-blue-600 dark:text-blue-400" />
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-blue-800 dark:text-blue-200">Teacher Assignments</h3>
                            <p class="mt-1 text-sm text-blue-700 dark:text-blue-300">
                                Assign teachers to specific class-stream combinations and subjects. Each assignment links a teacher to a classroom and subject for the current academic year.
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
