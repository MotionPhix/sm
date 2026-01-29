<script setup lang="ts">
import { Head, router, usePage } from '@inertiajs/vue3'
import { ref, computed } from 'vue'
import { Button } from '@/components/ui/button'
import AppLayout from '@/layouts/AppLayout.vue'
import AdminSettingsLayout from '@/layouts/AdminSettingsLayout.vue'
import HeadingSmall from '@/components/HeadingSmall.vue'
import InputError from '@/components/InputError.vue'
import { Plus, Trash2, Edit2, DollarSign, Check, AlertCircle, Tag } from 'lucide-vue-next'
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
import { create as feeItemsCreate, edit as feeItemsEdit, destroy as feeItemsDestroy } from '@/routes/admin/settings/fee-items'

interface FeeItem {
    id: number
    name: string
    code: string
    category: string
    description?: string
    is_mandatory: boolean
    is_active: boolean
}

const props = defineProps<{
    feeItems: {
        data: FeeItem[]
        total: number
        per_page: number
        current_page: number
    }
    categories: Record<string, string>
}>()

const page = usePage()
const flash = computed(() => page.props.flash as { success?: string; error?: string })
const { adminSettingsBreadcrumbs } = useBreadcrumbs()
const { adminSettingsNavItems } = useAdminSettingsNavigation()

const breadcrumbs = adminSettingsBreadcrumbs('Fee Items')

const { isOpen, title, description, confirmText, variant, confirm, handleConfirm, handleCancel } = useConfirm()
const itemToDelete = ref<FeeItem | null>(null)

const confirmDelete = async (item: FeeItem) => {
    const confirmed = await confirm({
        title: 'Delete Fee Item',
        description: `Are you sure you want to delete "${item.name}"? This action cannot be undone if it's being used in fee structures.`,
        confirmText: 'Delete',
        cancelText: 'Cancel',
        variant: 'destructive',
    })

    if (confirmed) {
        router.delete(feeItemsDestroy(item.id).url, {
            preserveScroll: true,
        })
    }
}

const getCategoryColor = (category: string): string => {
    const colors: Record<string, string> = {
        tuition: 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300',
        exam: 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300',
        development: 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300',
        extra_curriculum: 'bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-300',
        other: 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300',
    }
    return colors[category] || colors.other
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Fee Items" />

        <template #act>
            <Button :as="ModalLink" :href="feeItemsCreate().url">
                <Plus class="mr-2 h-4 w-4" />
                New Fee Item
            </Button>
        </template>

        <AdminSettingsLayout 
            title="School Settings"
            description="Configure your school's academic calendar and other settings" 
            :items="adminSettingsNavItems">
            <div class="space-y-6">
                <HeadingSmall 
                    title="Fee Items"
                    description="Define fee categories and types charged to students" 
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

                <!-- Fee Items Table -->
                <div class="rounded-lg border bg-card">
                    <div class="border-b px-6 py-4">
                        <div class="flex items-center gap-2">
                            <DollarSign class="h-5 w-5 text-muted-foreground" />
                            <h2 class="text-lg font-semibold">Fee Items</h2>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Name</TableHead>
                                    <TableHead>Code</TableHead>
                                    <TableHead>Category</TableHead>
                                    <TableHead>Type</TableHead>
                                    <TableHead>Status</TableHead>
                                    <TableHead class="text-right">Actions</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody v-if="feeItems.data.length > 0">
                                <TableRow v-for="item in feeItems.data" :key="item.id">
                                    <TableCell class="font-medium">
                                        {{ item.name }}
                                    </TableCell>
                                    <TableCell>
                                        <Badge variant="outline">{{ item.code }}</Badge>
                                    </TableCell>
                                    <TableCell>
                                        <Badge :class="getCategoryColor(item.category)">
                                            {{ categories[item.category] }}
                                        </Badge>
                                    </TableCell>
                                    <TableCell>
                                        <span :class="item.is_mandatory ? 'text-sm font-medium text-foreground' : 'text-sm text-muted-foreground'">
                                            {{ item.is_mandatory ? 'Mandatory' : 'Optional' }}
                                        </span>
                                    </TableCell>
                                    <TableCell>
                                        <span
                                            :class="item.is_active ? 'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300' : 'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300'">
                                            {{ item.is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </TableCell>
                                    <TableCell class="text-right">
                                        <div class="flex justify-end gap-2">
                                            <Button variant="ghost" size="sm" :as="ModalLink"
                                                :href="feeItemsEdit(item.id).url">
                                                <Edit2 class="h-4 w-4" />
                                            </Button>
                                            <Button variant="ghost" size="sm" @click="confirmDelete(item)">
                                                <Trash2 class="h-4 w-4 text-destructive" />
                                            </Button>
                                        </div>
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                        <TableEmpty v-if="feeItems.data.length === 0">
                            <DollarSign class="mx-auto h-12 w-12 text-muted-foreground" />
                            <h3 class="mt-4 text-sm font-medium">No fee items yet</h3>
                            <p class="mt-2 text-sm text-muted-foreground">
                                Create fee item categories that students will be charged for.
                            </p>
                            <Button class="mt-4" :as="ModalLink" :href="feeItemsCreate().url">
                                <Plus class="mr-2 h-4 w-4" />
                                Create First Fee Item
                            </Button>
                        </TableEmpty>
                    </div>
                </div>

                <!-- Info Card -->
                <div class="rounded-lg border bg-blue-50 p-4 dark:bg-blue-900/20">
                    <div class="flex">
                        <div class="shrink-0">
                            <Tag class="h-5 w-5 text-blue-600 dark:text-blue-400" />
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-blue-800 dark:text-blue-200">Fee Items Configuration</h3>
                            <p class="mt-1 text-sm text-blue-700 dark:text-blue-300">
                                Create fee categories first, then assign amounts to classes and terms in the fee structures section.
                                Common items: Tuition, Exam Fees, Development Levy, Sports, etc.
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
