<script setup lang="ts">
import { Head, router, usePage } from '@inertiajs/vue3'
import { ref, computed } from 'vue'
import { Button } from '@/components/ui/button'
import AppLayout from '@/layouts/AppLayout.vue'
import AdminSettingsLayout from '@/layouts/AdminSettingsLayout.vue'
import HeadingSmall from '@/components/HeadingSmall.vue'
import InputError from '@/components/InputError.vue'
import type { BreadcrumbItemType } from '@/types'
import { Plus, Trash2, Edit2, Sparkles, Calendar, Check } from 'lucide-vue-next'
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
import { useRoleRoutes } from '@/composables/useRoleRoutes'
import { useAdminSettingsNavigation } from '@/composables/useAdminSettingsNavigation'
// Wayfinder routes - imported directly for type safety
import { create as termsCreate, edit as termsEdit, destroy as termsDestroy, generateDefaults as termsGenerateDefaults } from '@/routes/admin/settings/terms'
import { useBreadcrumbs } from '@/composables/useBreadcrumbs'

interface Term {
    id: number
    name: string
    sequence: number
    starts_on: string
    ends_on: string
    is_active: boolean
}

interface AcademicYear {
    id: number
    name: string
    starts_on: string
    ends_on: string
}

const props = defineProps<{
    academicYear: AcademicYear
    terms: Term[]
}>()

const page = usePage()

const { adminSettingsBreadcrumbs } = useBreadcrumbs()
const { adminSettingsNavItems } = useAdminSettingsNavigation()

const breadcrumbs = adminSettingsBreadcrumbs('Terms')

const flash = computed(() => page.props.flash as { success?: string; error?: string })

const { isOpen, title, description, confirmText, variant, confirm, handleConfirm, handleCancel } = useConfirm()
const { canAccessAdmin } = useRoleRoutes()
const termToDelete = ref<Term | null>(null)

const confirmDelete = async (term: Term) => {
    const confirmed = await confirm({
        title: 'Delete Term',
        description: `Are you sure you want to delete "${term.name}"? This action cannot be undone.`,
        confirmText: 'Delete',
        cancelText: 'Cancel',
        variant: 'destructive',
    })

    if (confirmed) {
        router.delete(termsDestroy(term.id).url, {
            preserveScroll: true,
        })
    }
}

const generateDefaults = () => {
    router.post(termsGenerateDefaults().url, {}, {
        preserveScroll: true,
    })
}

const formatDate = (dateStr: string) => {
    if (!dateStr) return ''
    const date = new Date(dateStr)
    return date.toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' })
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">

        <Head title="Terms" />

        <template #act>
            <Button v-if="terms.length === 0" variant="outline" @click="generateDefaults">
                <Sparkles class="mr-2 h-4 w-4" />
                Generate 3-Term Default
            </Button>

            <Button :as="ModalLink" :href="termsCreate().url">
                <Plus class="mr-2 h-4 w-4" />
                Add Term
            </Button>
        </template>

        <AdminSettingsLayout title="School Settings"
            description="Configure your school's academic calendar and other settings" :items="adminSettingsNavItems">
            <div class="space-y-6">
                <div class="flex items-center justify-between">
                    <HeadingSmall 
                        :title="`Terms — ${academicYear?.name}`"
                        description="Define term dates for the current academic year. Malawian schools typically use a 3-term structure." 
                    />
                    
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

                <!-- Terms Table -->
                <div class="rounded-lg border bg-card">
                    <div class="border-b px-6 py-4">
                        <div class="flex items-center gap-2">
                            <Calendar class="h-5 w-5 text-muted-foreground" />
                            <h2 class="text-lg font-semibold">Academic Terms</h2>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>#</TableHead>
                                    <TableHead>Name</TableHead>
                                    <TableHead>Start Date</TableHead>
                                    <TableHead>End Date</TableHead>
                                    <TableHead>Status</TableHead>
                                    <TableHead class="text-right">Actions</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody v-if="terms.length > 0">
                                <TableRow v-for="term in terms" :key="term.id">
                                    <TableCell class="text-muted-foreground">
                                        {{ term.sequence }}
                                    </TableCell>
                                    <TableCell class="font-medium">
                                        {{ term.name }}
                                    </TableCell>
                                    <TableCell>
                                        {{ formatDate(term.starts_on) }}
                                    </TableCell>
                                    <TableCell>
                                        {{ formatDate(term.ends_on) }}
                                    </TableCell>
                                    <TableCell>
                                        <span
                                            class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
                                            :class="term.is_active ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300' : 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300'">
                                            {{ term.is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </TableCell>
                                    <TableCell class="text-right">
                                        <div class="flex justify-end gap-2">
                                            <Button variant="ghost" size="sm" :as="ModalLink"
                                                :href="termsEdit(term.id).url">
                                                <Edit2 class="h-4 w-4" />
                                            </Button>
                                            <Button variant="ghost" size="sm" @click="confirmDelete(term)">
                                                <Trash2 class="h-4 w-4 text-destructive" />
                                            </Button>
                                        </div>
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                        <TableEmpty v-if="terms.length === 0">
                            <Calendar class="mx-auto h-12 w-12 text-muted-foreground" />
                            <h3 class="mt-4 text-sm font-medium">No terms defined</h3>
                            <p class="mt-2 text-sm text-muted-foreground">
                                Get started by adding terms or generating the default 3-term structure.
                            </p>
                            <div class="mt-4 flex justify-center gap-2">
                                <Button variant="outline" @click="generateDefaults">
                                    <Sparkles class="mr-2 h-4 w-4" />
                                    Generate Defaults
                                </Button>
                                <Button :as="ModalLink" :href="termsCreate().url">
                                    <Plus class="mr-2 h-4 w-4" />
                                    Add Term
                                </Button>
                            </div>
                        </TableEmpty>
                    </div>
                </div>

                <!-- Info Card -->
                <div class="rounded-lg border bg-blue-50 p-4 dark:bg-blue-900/20">
                    <div class="flex">
                        <div class="shrink-0">
                            <Calendar class="h-5 w-5 text-blue-600 dark:text-blue-400" />
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-blue-800 dark:text-blue-200">Malawi School Calendar</h3>
                            <p class="mt-1 text-sm text-blue-700 dark:text-blue-300">
                                The default 3-term structure follows the Malawi academic calendar:
                                Term 1 (Sept-Dec), Term 2 (Jan-Apr), Term 3 (May-Jul).
                                You can customize the dates to match your school's specific schedule.
                            </p>
                        </div>
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
