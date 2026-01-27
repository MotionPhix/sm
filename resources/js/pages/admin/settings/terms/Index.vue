<script setup lang="ts">
import { Head, router, useForm, usePage } from '@inertiajs/vue3'
import { ref, computed } from 'vue'
import { Button } from '@/components/ui/button'
import AppLayout from '@/layouts/AppLayout.vue'
import Heading from '@/components/Heading.vue'
import type { BreadcrumbItemType } from '@/types'
import { Plus, Trash2, Edit2, Sparkles, Calendar, Check } from 'lucide-vue-next'
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog'
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert'

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

const breadcrumbs: BreadcrumbItemType[] = [
    { title: 'Settings', href: '/admin/settings' },
    { title: 'Terms', href: '/admin/settings/terms' },
]

const page = usePage()
const flash = computed(() => page.props.flash as { success?: string; error?: string })

const showAddDialog = ref(false)
const showEditDialog = ref(false)
const showDeleteDialog = ref(false)
const termToDelete = ref<Term | null>(null)

const form = useForm({
    name: '',
    sequence: props.terms.length + 1,
    starts_on: '',
    ends_on: '',
})

const editForm = useForm({
    id: 0,
    name: '',
    sequence: 1,
    starts_on: '',
    ends_on: '',
    is_active: true,
})

const openAddDialog = () => {
    form.reset()
    form.sequence = props.terms.length + 1
    form.name = `Term ${form.sequence}`
    showAddDialog.value = true
}

const openEditDialog = (term: Term) => {
    editForm.id = term.id
    editForm.name = term.name
    editForm.sequence = term.sequence
    editForm.starts_on = term.starts_on
    editForm.ends_on = term.ends_on
    editForm.is_active = term.is_active
    showEditDialog.value = true
}

const confirmDelete = (term: Term) => {
    termToDelete.value = term
    showDeleteDialog.value = true
}

const submitAdd = () => {
    form.post('/admin/settings/terms', {
        preserveScroll: true,
        onSuccess: () => {
            showAddDialog.value = false
            form.reset()
        },
    })
}

const submitEdit = () => {
    editForm.put(`/admin/settings/terms/${editForm.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            showEditDialog.value = false
        },
    })
}

const deleteTerm = () => {
    if (!termToDelete.value) return
    router.delete(`/admin/settings/terms/${termToDelete.value.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            showDeleteDialog.value = false
            termToDelete.value = null
        },
    })
}

const generateDefaults = () => {
    router.post('/admin/settings/terms/generate-defaults', {}, {
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

        <div class="space-y-6">
            <div class="flex items-center justify-between">
                <Heading
                    :title="`Terms — ${academicYear?.name}`"
                    description="Define term dates for the current academic year. Malawian schools typically use a 3-term structure."
                />
                <div class="flex gap-2">
                    <Button v-if="terms.length === 0" variant="outline" @click="generateDefaults">
                        <Sparkles class="mr-2 h-4 w-4" />
                        Generate 3-Term Default
                    </Button>
                    <Button @click="openAddDialog">
                        <Plus class="mr-2 h-4 w-4" />
                        Add Term
                    </Button>
                </div>
            </div>

            <!-- Success Alert -->
            <Alert v-if="flash?.success" class="border-green-200 bg-green-50 dark:border-green-800 dark:bg-green-900/30">
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
                    <table class="min-w-full">
                        <thead>
                            <tr class="border-b bg-muted/50">
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">#</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Start Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">End Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            <tr v-for="term in terms" :key="term.id" class="hover:bg-muted/30">
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-muted-foreground">
                                    {{ term.sequence }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 font-medium">
                                    {{ term.name }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm">
                                    {{ formatDate(term.starts_on) }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm">
                                    {{ formatDate(term.ends_on) }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4">
                                    <span
                                        class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
                                        :class="term.is_active ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300' : 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300'"
                                    >
                                        {{ term.is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-right">
                                    <div class="flex justify-end gap-2">
                                        <Button variant="ghost" size="sm" @click="openEditDialog(term)">
                                            <Edit2 class="h-4 w-4" />
                                        </Button>
                                        <Button variant="ghost" size="sm" @click="confirmDelete(term)">
                                            <Trash2 class="h-4 w-4 text-destructive" />
                                        </Button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="terms.length === 0">
                                <td colspan="6" class="px-6 py-12 text-center">
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
                                        <Button @click="openAddDialog">
                                            <Plus class="mr-2 h-4 w-4" />
                                            Add Term
                                        </Button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
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

        <!-- Add Term Dialog -->
        <Dialog v-model:open="showAddDialog">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Add New Term</DialogTitle>
                    <DialogDescription>
                        Create a new term for the {{ academicYear?.name }} academic year.
                    </DialogDescription>
                </DialogHeader>

                <form @submit.prevent="submitAdd" class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="mb-1 block text-sm font-medium">Name</label>
                            <input
                                v-model="form.name"
                                type="text"
                                class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2"
                                placeholder="Term 1"
                            />
                            <p v-if="form.errors.name" class="mt-1 text-xs text-destructive">{{ form.errors.name }}</p>
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium">Sequence</label>
                            <input
                                v-model.number="form.sequence"
                                type="number"
                                min="1"
                                max="6"
                                class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2"
                            />
                            <p v-if="form.errors.sequence" class="mt-1 text-xs text-destructive">{{ form.errors.sequence }}</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="mb-1 block text-sm font-medium">Start Date</label>
                            <input
                                v-model="form.starts_on"
                                type="date"
                                class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2"
                            />
                            <p v-if="form.errors.starts_on" class="mt-1 text-xs text-destructive">{{ form.errors.starts_on }}</p>
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium">End Date</label>
                            <input
                                v-model="form.ends_on"
                                type="date"
                                class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2"
                            />
                            <p v-if="form.errors.ends_on" class="mt-1 text-xs text-destructive">{{ form.errors.ends_on }}</p>
                        </div>
                    </div>

                    <DialogFooter>
                        <Button type="button" variant="outline" @click="showAddDialog = false">Cancel</Button>
                        <Button type="submit" :disabled="form.processing">
                            {{ form.processing ? 'Saving...' : 'Add Term' }}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>

        <!-- Edit Term Dialog -->
        <Dialog v-model:open="showEditDialog">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Edit Term</DialogTitle>
                    <DialogDescription>
                        Update the term details.
                    </DialogDescription>
                </DialogHeader>

                <form @submit.prevent="submitEdit" class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="mb-1 block text-sm font-medium">Name</label>
                            <input
                                v-model="editForm.name"
                                type="text"
                                class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2"
                            />
                            <p v-if="editForm.errors.name" class="mt-1 text-xs text-destructive">{{ editForm.errors.name }}</p>
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium">Sequence</label>
                            <input
                                v-model.number="editForm.sequence"
                                type="number"
                                min="1"
                                max="6"
                                class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2"
                            />
                            <p v-if="editForm.errors.sequence" class="mt-1 text-xs text-destructive">{{ editForm.errors.sequence }}</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="mb-1 block text-sm font-medium">Start Date</label>
                            <input
                                v-model="editForm.starts_on"
                                type="date"
                                class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2"
                            />
                            <p v-if="editForm.errors.starts_on" class="mt-1 text-xs text-destructive">{{ editForm.errors.starts_on }}</p>
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium">End Date</label>
                            <input
                                v-model="editForm.ends_on"
                                type="date"
                                class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2"
                            />
                            <p v-if="editForm.errors.ends_on" class="mt-1 text-xs text-destructive">{{ editForm.errors.ends_on }}</p>
                        </div>
                    </div>

                    <DialogFooter>
                        <Button type="button" variant="outline" @click="showEditDialog = false">Cancel</Button>
                        <Button type="submit" :disabled="editForm.processing">
                            {{ editForm.processing ? 'Saving...' : 'Save Changes' }}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>

        <!-- Delete Confirmation Dialog -->
        <Dialog v-model:open="showDeleteDialog">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Delete Term</DialogTitle>
                    <DialogDescription>
                        Are you sure you want to delete "{{ termToDelete?.name }}"? This action cannot be undone.
                    </DialogDescription>
                </DialogHeader>

                <DialogFooter>
                    <Button variant="outline" @click="showDeleteDialog = false">Cancel</Button>
                    <Button variant="destructive" @click="deleteTerm">Delete</Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>
