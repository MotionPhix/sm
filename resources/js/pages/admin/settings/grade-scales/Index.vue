<script setup lang="ts">
import { Head, router, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'
import { Button } from '@/components/ui/button'
import AppLayout from '@/layouts/AppLayout.vue'
import AdminSettingsLayout from '@/layouts/AdminSettingsLayout.vue'
import HeadingSmall from '@/components/HeadingSmall.vue'
import { Plus, Trash2, Edit2, BarChart3, Check, AlertCircle } from 'lucide-vue-next'
import { Badge } from '@/components/ui/badge'
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert'
import { ModalLink } from '@inertiaui/modal-vue'
import ConfirmDialog from '@/components/ConfirmDialog.vue'
import { useConfirm } from '@/composables/useConfirm'
import { useBreadcrumbs } from '@/composables/useBreadcrumbs'
import { useAdminSettingsNavigation } from '@/composables/useAdminSettingsNavigation'
import { create as scalesCreate, edit as scalesEdit, destroy as scalesDestroy } from '@/routes/admin/settings/grade-scales'
import { Empty, EmptyDescription, EmptyHeader, EmptyMedia, EmptyTitle } from '@/components/ui/empty'

interface GradeScaleStep {
    id: number
    min_percent: number
    max_percent: number
    grade: string
    comment: string | null
    ordering: number
}

interface GradeScale {
    id: number
    name: string
    description: string | null
    steps: GradeScaleStep[]
}

const props = defineProps<{
    gradeScales: GradeScale[]
}>()

const page = usePage()
const flash = computed(() => page.props.flash as { success?: string; error?: string })
const { adminSettingsBreadcrumbs } = useBreadcrumbs()
const { adminSettingsNavItems } = useAdminSettingsNavigation()

const breadcrumbs = adminSettingsBreadcrumbs('Grade Scales')

const { isOpen, title, description, confirmText, variant, confirm, handleConfirm, handleCancel } = useConfirm()

const confirmDelete = async (scale: GradeScale) => {
    const confirmed = await confirm({
        title: 'Delete Grading Scale',
        description: `Are you sure you want to delete "${scale.name}"? This action cannot be undone.`,
        confirmText: 'Delete',
        cancelText: 'Cancel',
        variant: 'destructive',
    })

    if (confirmed) {
        router.delete(scalesDestroy(scale.id).url, {
            preserveScroll: true,
        })
    }
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Grading Scales" />

        <template #act>
            <Button :as="ModalLink" :href="scalesCreate().url">
                <Plus class="mr-2 h-4 w-4" />
                Add Grading Scale
            </Button>
        </template>

        <AdminSettingsLayout
            title="School Settings"
            description="Configure your school's academic structure"
            :items="adminSettingsNavItems">
            <div class="space-y-6">
                <HeadingSmall
                    title="Grading Scales"
                    description="Define how percentage scores map to letter grades"
                />

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

                <!-- Grade Scales Cards -->
                <div v-if="gradeScales.length > 0" class="space-y-4">
                    <div v-for="scale in gradeScales" :key="scale.id"
                        class="rounded-lg border bg-card">
                        <div class="border-b px-6 py-4">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <BarChart3 class="h-5 w-5 text-muted-foreground" />
                                    <h2 class="text-lg font-semibold">{{ scale.name }}</h2>
                                </div>
                                <div class="flex gap-2">
                                    <Button variant="ghost" size="sm" :as="ModalLink"
                                        :href="scalesEdit(scale.id).url">
                                        <Edit2 class="h-4 w-4" />
                                    </Button>
                                    <Button variant="ghost" size="sm" @click="confirmDelete(scale)">
                                        <Trash2 class="h-4 w-4 text-destructive" />
                                    </Button>
                                </div>
                            </div>
                            <p v-if="scale.description" class="mt-1 text-sm text-muted-foreground">
                                {{ scale.description }}
                            </p>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="border-b bg-muted/50">
                                        <th class="px-4 py-2 text-left font-medium text-muted-foreground">Grade</th>
                                        <th class="px-4 py-2 text-left font-medium text-muted-foreground">Range</th>
                                        <th class="px-4 py-2 text-left font-medium text-muted-foreground">Comment</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="step in scale.steps" :key="step.id" class="border-b last:border-0">
                                        <td class="px-4 py-2">
                                            <Badge variant="outline" class="font-mono">{{ step.grade }}</Badge>
                                        </td>
                                        <td class="px-4 py-2">
                                            {{ step.min_percent }}% — {{ step.max_percent }}%
                                        </td>
                                        <td class="px-4 py-2 text-muted-foreground">
                                            {{ step.comment ?? '—' }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-else class="rounded-lg border bg-card">
                    <div class="border-b px-6 py-4">
                        <div class="flex items-center gap-2">
                            <BarChart3 class="h-5 w-5 text-muted-foreground" />
                            <h2 class="text-lg font-semibold">Grading Scales</h2>
                        </div>
                    </div>
                    <div class="p-6">
                        <Empty>
                            <EmptyHeader>
                                <EmptyMedia>
                                    <BarChart3 :size="64" />
                                </EmptyMedia>
                                <EmptyTitle>No grading scales defined</EmptyTitle>
                                <EmptyDescription>
                                    Create a grading scale to define how percentage scores translate to letter grades (e.g., A, B, C).
                                </EmptyDescription>
                            </EmptyHeader>
                            <Button :as="ModalLink" :href="scalesCreate().url">
                                <Plus class="mr-2 h-4 w-4" />
                                Add Your First Grading Scale
                            </Button>
                        </Empty>
                    </div>
                </div>

                <!-- Info Card -->
                <div class="rounded-lg border bg-blue-50 p-4 dark:bg-blue-900/20">
                    <div class="flex">
                        <div class="shrink-0">
                            <BarChart3 class="h-5 w-5 text-blue-600 dark:text-blue-400" />
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-blue-800 dark:text-blue-200">Grading Scales</h3>
                            <p class="mt-1 text-sm text-blue-700 dark:text-blue-300">
                                Grading scales map percentage ranges to letter grades. When teachers enter marks, the system uses this scale to automatically calculate letter grades and comments for report cards.
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
