<script setup lang="ts">
import { Head, router, usePage } from '@inertiajs/vue3'
import { ref, computed } from 'vue'
import { Button } from '@/components/ui/button'
import AppLayout from '@/layouts/AppLayout.vue'
import AdminSettingsLayout from '@/layouts/AdminSettingsLayout.vue'
import HeadingSmall from '@/components/HeadingSmall.vue'
import { Plus, Trash2, Edit2, Grid3X3, Check, AlertCircle, Construction } from 'lucide-vue-next'
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
import { create as feeStructuresCreate, edit as feeStructuresEdit, destroy as feeStructuresDestroy } from '@/routes/admin/settings/fee-structures'
import { Empty, EmptyDescription, EmptyHeader, EmptyMedia, EmptyTitle } from '@/components/ui/empty'

interface FeeItem {
    id: number
    name: string
    code: string
}

interface Term {
    id: number
    name: string
}

interface SchoolClass {
    id: number
    name: string
}

interface FeeStructure {
    id: number
    school_class_id: number
    term_id?: number
    fee_item_id: number
    amount: string
    quantity: number
    is_active: boolean
    schoolClass: SchoolClass
    term?: Term
    feeItem: FeeItem
}

interface AcademicYear {
    id: number
    name: string
}

const props = defineProps<{
    academicYear: AcademicYear
    feeStructures: {
        data: FeeStructure[]
        total: number
        per_page: number
        current_page: number
    }
}>()

const page = usePage()
const flash = computed(() => page.props.flash as { success?: string; error?: string })
const { adminSettingsBreadcrumbs } = useBreadcrumbs()
const { adminSettingsNavItems } = useAdminSettingsNavigation()

const breadcrumbs = adminSettingsBreadcrumbs('Fee Structures')

const { isOpen, title, description, confirmText, variant, confirm, handleConfirm, handleCancel } = useConfirm()
const structureToDelete = ref<FeeStructure | null>(null)

const confirmDelete = async (structure: FeeStructure) => {
    const confirmed = await confirm({
        title: 'Delete Fee Structure',
        description: `Are you sure you want to remove "${structure.feeItem.name}" from ${structure.schoolClass.name}? This action cannot be undone.`,
        confirmText: 'Delete',
        cancelText: 'Cancel',
        variant: 'destructive',
    })

    if (confirmed) {
        router.delete(feeStructuresDestroy(structure.id).url, {
            preserveScroll: true,
        })
    }
}

const formatCurrency = (amount: string | number): string => {
    const num = typeof amount === 'string' ? parseFloat(amount) : amount
    return 'MK ' + num.toLocaleString('en-ZA', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
}

const getTotalAmount = (structure: FeeStructure): string => {
    const amount = parseFloat(structure.amount) * structure.quantity
    return formatCurrency(amount)
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Fee Structures" />

        <template #act>
            <Button :as="ModalLink" :href="feeStructuresCreate().url">
                <Plus class="mr-2 h-4 w-4" />
                Assign Fees
            </Button>
        </template>

        <AdminSettingsLayout 
            title="School Settings"
            description="Configure your school's academic calendar and other settings" 
            :items="adminSettingsNavItems">
            <div class="space-y-6">
                <div class="flex items-center justify-between">
                    <HeadingSmall 
                        :title="`Fee Structures — ${academicYear?.name}`"
                        description="Assign fees to classes and terms for the current academic year" 
                    />
                </div>

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

                <!-- Fee Structures Table -->
                <div class="rounded-lg border bg-card">
                    <div class="border-b px-6 py-4">
                        <div class="flex items-center gap-2">
                            <Construction class="h-5 w-5 text-muted-foreground" />
                            <h2 class="text-lg font-semibold">Assigned Fees</h2>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Class</TableHead>
                                    <TableHead>Term</TableHead>
                                    <TableHead>Fee Item</TableHead>
                                    <TableHead>Amount</TableHead>
                                    <TableHead>Qty</TableHead>
                                    <TableHead>Total</TableHead>
                                    <TableHead class="text-right" />
                                </TableRow>
                            </TableHeader>
                            <TableBody v-if="feeStructures.data.length > 0">
                                <TableRow v-for="structure in feeStructures.data" :key="structure.id" :class="!structure.is_active && 'opacity-50'">
                                    <TableCell class="font-medium">
                                        {{ structure.schoolClass.name }}
                                    </TableCell>
                                    <TableCell>
                                        <Badge v-if="structure.term" variant="outline">{{ structure.term.name }}</Badge>
                                        <span v-else class="text-sm text-muted-foreground">All Year</span>
                                    </TableCell>
                                    <TableCell>
                                        <div class="flex flex-col gap-0.5">
                                            <span class="font-medium">{{ structure.feeItem.name }}</span>
                                            <span class="text-xs text-muted-foreground">{{ structure.feeItem.code }}</span>
                                        </div>
                                    </TableCell>
                                    <TableCell>
                                        {{ formatCurrency(structure.amount) }}
                                    </TableCell>
                                    <TableCell class="text-center">
                                        {{ structure.quantity }}
                                    </TableCell>
                                    <TableCell class="font-semibold">
                                        {{ getTotalAmount(structure) }}
                                    </TableCell>
                                    <TableCell class="text-right">
                                        <div class="flex justify-end gap-2">
                                            <Button variant="ghost" size="sm" :as="ModalLink"
                                                :href="feeStructuresEdit(structure.id).url">
                                                <Edit2 class="h-4 w-4" />
                                            </Button>
                                            <Button variant="ghost" size="sm" @click="confirmDelete(structure)">
                                                <Trash2 class="h-4 w-4 text-destructive" />
                                            </Button>
                                        </div>
                                    </TableCell>
                                </TableRow>
                            </TableBody>

                            <!-- EMPTY STATE -->
                            <TableEmpty
                                v-if="feeStructures.data.length === 0"
                                :colspan="7">
                                <Empty>
                                    <EmptyHeader>
                                    <EmptyMedia>
                                        <Construction :size="64" />
                                    </EmptyMedia>

                                    <EmptyTitle>
                                        No fee structures assigned
                                    </EmptyTitle>

                                    <EmptyDescription>
                                        Create fee items first, then assign them to classes and terms.
                                    </EmptyDescription>
                                    </EmptyHeader>

                                    <Button
                                    :as="ModalLink"
                                    :href="feeStructuresCreate().url"
                                    >
                                    <Plus class="mr-2 h-4 w-4" />
                                    Assign Fees to Class
                                    </Button>
                                </Empty>
                            </TableEmpty>
                        </Table>
                        
                        <!-- <TableEmpty v-if="feeStructures.data.length === 0">
                            <div class="mx-auto w-full bg-rose-500 col-span-7">
                                <Empty>

                                    <EmptyHeader>
                                        <EmptyMedia>
                                            <Construction :size="64" />
                                        </EmptyMedia>

                                        <EmptyTitle>
                                            No fee structures assigned
                                        </EmptyTitle>   
                                        <EmptyDescription>
                                            Create fee items first, then assign them to classes and terms.
                                        </EmptyDescription>
                                    </EmptyHeader>

                                    <Button 
                                        :as="ModalLink" 
                                        :href="feeStructuresCreate().url">
                                        <Plus class="mr-2 h-4 w-4" />
                                        Assign Fees to Class
                                    </Button>

                                </Empty>
                            </div>
                        </TableEmpty> -->
                    </div>
                </div>

                <!-- Info Card -->
                <div class="rounded-lg border bg-blue-50 p-4 dark:bg-blue-900/20">
                    <div class="flex">
                        <div class="shrink-0">
                            <Grid3X3 class="h-5 w-5 text-blue-600 dark:text-blue-400" />
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-blue-800 dark:text-blue-200">Fee Structures</h3>
                            <p class="mt-1 text-sm text-blue-700 dark:text-blue-300">
                                Assign fees to classes. You can set fees for the entire year or specific terms. Students in those classes will be charged accordingly during invoicing.
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
