<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3'
import { computed, watch } from 'vue'
import AppLayout from '@/layouts/AppLayout.vue'
import Heading from '@/components/Heading.vue'
import { Button } from '@/components/ui/button'
import { Label } from '@/components/ui/label'
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select'
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card'
import { Checkbox } from '@/components/ui/checkbox'
import { Badge } from '@/components/ui/badge'
import InputError from '@/components/InputError.vue'

interface Student {
    id: number
    first_name: string
    last_name: string
    admission_number: string
    active_enrollment?: {
        classroom?: {
            class: { name: string }
            display_name: string
        }
    }
}

interface AcademicYear {
    id: number
    name: string
}

interface Term {
    id: number
    name: string
}

const props = defineProps<{
    students: Student[]
    academicYears: AcademicYear[]
    terms: Term[]
}>()

const breadcrumbs = [
    { title: 'Dashboard', href: '/bursar/dashboard' },
    { title: 'Invoices', href: '/bursar/invoices' },
    { title: 'Generate Invoice', href: '#' },
]

const form = useForm({
    student_id: '',
    academic_year_id: '',
    term_id: '',
    prorate: false,
})

const selectedStudent = computed(() => {
    return props.students.find(s => s.id === parseInt(form.student_id))
})

const selectedAcademicYear = computed(() => {
    return props.academicYears.find(y => y.id === parseInt(form.academic_year_id))
})

const selectedTerm = computed(() => {
    return props.terms.find(t => t.id === parseInt(form.term_id))
})

function generateInvoice() {
    form.post('/bursar/invoices')
}

function getStudentName(student: Student): string {
    return `${student.first_name} ${student.last_name}`
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Generate Invoice" />

        <article class="p-5">
            <Heading
                title="Generate Invoice"
                description="Create a new invoice for a student based on their class fee structure"
            />

            <div class="mt-6 grid gap-6 lg:grid-cols-3">
                <!-- Form -->
                <div class="lg:col-span-2">
                    <Card>
                        <CardHeader>
                            <CardTitle>Invoice Details</CardTitle>
                            <CardDescription>
                                Select the student and academic period for this invoice
                            </CardDescription>
                        </CardHeader>
                        <CardContent class="space-y-6">
                            <!-- Student Selection -->
                            <div class="space-y-2">
                                <Label for="student_id">Student *</Label>
                                <Select v-model="form.student_id" required>
                                    <SelectTrigger id="student_id">
                                        <SelectValue placeholder="Select a student" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem
                                            v-for="student in students"
                                            :key="student.id"
                                            :value="String(student.id)">
                                            <div class="flex items-center justify-between gap-4">
                                                <span class="font-medium">
                                                    {{ getStudentName(student) }}
                                                </span>
                                                <span class="text-xs text-muted-foreground">
                                                    {{ student.admission_number }}
                                                </span>
                                            </div>
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                <InputError :message="form.errors.student_id" />

                                <!-- Student Info -->
                                <div v-if="selectedStudent" class="mt-2 rounded-lg bg-muted p-3">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <p class="text-sm font-medium">
                                                {{ getStudentName(selectedStudent) }}
                                            </p>
                                            <p class="text-xs text-muted-foreground">
                                                {{ selectedStudent.admission_number }}
                                            </p>
                                        </div>
                                        <Badge v-if="selectedStudent.active_enrollment" variant="outline">
                                            {{ selectedStudent.active_enrollment.classroom?.display_name }}
                                        </Badge>
                                        <Badge v-else variant="destructive">
                                            Not Enrolled
                                        </Badge>
                                    </div>
                                </div>
                            </div>

                            <!-- Academic Year Selection -->
                            <div class="space-y-2">
                                <Label for="academic_year_id">Academic Year *</Label>
                                <Select v-model="form.academic_year_id" required>
                                    <SelectTrigger id="academic_year_id">
                                        <SelectValue placeholder="Select academic year" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem
                                            v-for="year in academicYears"
                                            :key="year.id"
                                            :value="String(year.id)">
                                            {{ year.name }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                <InputError :message="form.errors.academic_year_id" />
                            </div>

                            <!-- Term Selection -->
                            <div class="space-y-2">
                                <Label for="term_id">Term (Optional)</Label>
                                <Select v-model="form.term_id">
                                    <SelectTrigger id="term_id">
                                        <SelectValue placeholder="Full year (leave blank for full year invoice)" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="">Full Year</SelectItem>
                                        <SelectItem
                                            v-for="term in terms"
                                            :key="term.id"
                                            :value="String(term.id)">
                                            {{ term.name }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                <p class="text-xs text-muted-foreground">
                                    Leave blank to generate invoice for the entire academic year
                                </p>
                                <InputError :message="form.errors.term_id" />
                            </div>

                            <!-- Prorate Option -->
                            <div class="flex items-center space-x-2 rounded-lg border p-4">
                                <Checkbox
                                    id="prorate"
                                    v-model:checked="form.prorate"
                                />
                                <div class="flex-1">
                                    <Label
                                        for="prorate"
                                        class="cursor-pointer font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
                                        Prorate fees for mid-term enrollment
                                    </Label>
                                    <p class="mt-1 text-xs text-muted-foreground">
                                        Adjust fees based on the student's enrollment date within the term
                                    </p>
                                </div>
                            </div>

                            <!-- Form Actions -->
                            <div class="flex justify-end gap-3 pt-4">
                                <Button
                                    variant="outline"
                                    type="button"
                                    @click="router.visit('/bursar/invoices')">
                                    Cancel
                                </Button>
                                <Button
                                    type="button"
                                    :disabled="form.processing || !form.student_id || !form.academic_year_id"
                                    @click="generateInvoice">
                                    Generate Invoice
                                </Button>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Preview/Info Sidebar -->
                <div class="space-y-4">
                    <!-- Summary Card -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="text-lg">Invoice Summary</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-3">
                            <div>
                                <p class="text-sm font-medium text-muted-foreground">Student</p>
                                <p class="text-sm">
                                    {{ selectedStudent ? getStudentName(selectedStudent) : 'Not selected' }}
                                </p>
                            </div>

                            <div>
                                <p class="text-sm font-medium text-muted-foreground">Academic Year</p>
                                <p class="text-sm">
                                    {{ selectedAcademicYear?.name || 'Not selected' }}
                                </p>
                            </div>

                            <div>
                                <p class="text-sm font-medium text-muted-foreground">Term</p>
                                <p class="text-sm">
                                    {{ selectedTerm?.name || 'Full Year' }}
                                </p>
                            </div>

                            <div v-if="selectedStudent && !selectedStudent.active_enrollment">
                                <div class="rounded-lg bg-destructive/10 p-3">
                                    <p class="text-xs font-medium text-destructive">
                                        ⚠️ Student not enrolled
                                    </p>
                                    <p class="mt-1 text-xs text-muted-foreground">
                                        This student doesn't have an active class enrollment
                                    </p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Help Card -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="text-lg">How it works</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-3 text-sm text-muted-foreground">
                            <div class="flex gap-2">
                                <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-primary/10 text-xs font-medium text-primary">
                                    1
                                </span>
                                <p>Select a student with an active class enrollment</p>
                            </div>
                            <div class="flex gap-2">
                                <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-primary/10 text-xs font-medium text-primary">
                                    2
                                </span>
                                <p>Choose the academic year and optionally a specific term</p>
                            </div>
                            <div class="flex gap-2">
                                <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-primary/10 text-xs font-medium text-primary">
                                    3
                                </span>
                                <p>The system will automatically fetch active fee structures for the student's class</p>
                            </div>
                            <div class="flex gap-2">
                                <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-primary/10 text-xs font-medium text-primary">
                                    4
                                </span>
                                <p>Enable proration if the student joined mid-term</p>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </article>
    </AppLayout>
</template>
