<script setup lang="ts">
import { Head, router, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'
import { Button } from '@/components/ui/button'
import AppLayout from '@/layouts/AppLayout.vue'
import AdminSettingsLayout from '@/layouts/AdminSettingsLayout.vue'
import HeadingSmall from '@/components/HeadingSmall.vue'
import { Plus, Trash2, Edit2, SplitSquareHorizontal, Check, AlertCircle } from 'lucide-vue-next'
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
import { create as streamsCreate, edit as streamsEdit, destroy as streamsDestroy } from '@/routes/admin/settings/streams'
import { Empty, EmptyDescription, EmptyHeader, EmptyMedia, EmptyTitle } from '@/components/ui/empty'

interface Stream {
    id: number
    name: string
}

const props = defineProps<{
    streams: Stream[]
}>()

const page = usePage()
const flash = computed(() => page.props.flash as { success?: string; error?: string })
const { adminSettingsBreadcrumbs } = useBreadcrumbs()
const { adminSettingsNavItems } = useAdminSettingsNavigation()

const breadcrumbs = adminSettingsBreadcrumbs('Streams')

const { isOpen, title, description, confirmText, variant, confirm, handleConfirm, handleCancel } = useConfirm()

const confirmDelete = async (stream: Stream) => {
    const confirmed = await confirm({
        title: 'Delete Stream',
        description: `Are you sure you want to delete "${stream.name}"? This action cannot be undone.`,
        confirmText: 'Delete',
        cancelText: 'Cancel',
        variant: 'destructive',
    })

    if (confirmed) {
        router.delete(streamsDestroy(stream.id).url, {
            preserveScroll: true,
        })
    }
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Streams" />

        <template #act>
            <Button :as="ModalLink" :href="streamsCreate().url">
                <Plus class="mr-2 h-4 w-4" />
                Add Stream
            </Button>
        </template>

        <AdminSettingsLayout 
            title="School Settings"
            description="Configure your school's academic structure" 
            :items="adminSettingsNavItems">
            <div class="space-y-6">
                <HeadingSmall 
                    title="Streams"
                    description="Manage streams for organizing students within classes (e.g., A, B, C)" 
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

                <!-- Streams Table -->
                <div class="rounded-lg border bg-card">
                    <div class="border-b px-6 py-4">
                        <div class="flex items-center gap-2">
                            <SplitSquareHorizontal class="h-5 w-5 text-muted-foreground" />
                            <h2 class="text-lg font-semibold">Class Streams</h2>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Stream Name</TableHead>
                                    <TableHead class="text-right">Actions</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody v-if="streams.length > 0">
                                <TableRow v-for="stream in streams" :key="stream.id">
                                    <TableCell class="font-medium">
                                        {{ stream.name }}
                                    </TableCell>
                                    <TableCell class="text-right">
                                        <div class="flex justify-end gap-2">
                                            <Button variant="ghost" size="sm" :as="ModalLink"
                                                :href="streamsEdit(stream.id).url">
                                                <Edit2 class="h-4 w-4" />
                                            </Button>
                                            <Button variant="ghost" size="sm" @click="confirmDelete(stream)">
                                                <Trash2 class="h-4 w-4 text-destructive" />
                                            </Button>
                                        </div>
                                    </TableCell>
                                </TableRow>
                            </TableBody>

                            <TableEmpty v-if="streams.length === 0" :colspan="2">
                                <Empty>
                                    <EmptyHeader>
                                        <EmptyMedia>
                                            <SplitSquareHorizontal :size="64" />
                                        </EmptyMedia>
                                        <EmptyTitle>No streams defined</EmptyTitle>
                                        <EmptyDescription>
                                            Get started by adding streams for your school.
                                        </EmptyDescription>
                                    </EmptyHeader>
                                    <Button :as="ModalLink" :href="streamsCreate().url">
                                        <Plus class="mr-2 h-4 w-4" />
                                        Add Your First Stream
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
                            <SplitSquareHorizontal class="h-5 w-5 text-blue-600 dark:text-blue-400" />
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-blue-800 dark:text-blue-200">Streams</h3>
                            <p class="mt-1 text-sm text-blue-700 dark:text-blue-300">
                                Streams are used to divide students within the same class (e.g., Form 1A, Form 1B). Common stream names include A, B, C or East, West, North.
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
