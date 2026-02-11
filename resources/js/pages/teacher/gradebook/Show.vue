<script setup lang="ts">
import { Form, Head, Link, usePage } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import Heading from '@/components/Heading.vue'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import { Input } from '@/components/ui/input'
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert'
import { ArrowLeft, Save, Check, AlertCircle, BarChart3 } from 'lucide-vue-next'
import { index as gradebookIndex, store as gradebookStore, summary as gradebookSummary } from '@/routes/teacher/gradebook'
import { ref, computed } from 'vue'
import { Spinner } from '@/components/ui/spinner'

interface SchoolClass {
    id: number
    name: string
}

interface Stream {
    id: number
    name: string
}

interface Subject {
    id: number
    name: string
    code: string
}

interface Classroom {
    id: number
    class: SchoolClass
    stream: Stream | null
}

interface Term {
    id: number
    name: string
}

interface Student {
    id: number
    first_name: string
    last_name: string
    admission_number: string
}

interface AssessmentPlan {
    id: number
    name: string
    max_score: number
    weight: number
    ordering: number
}

interface Grade {
    id: number
    score: number | null
    remarks: string | null
    is_locked: boolean
}

const props = defineProps<{
    classroom: Classroom
    subject: Subject
    term: Term
    students: Student[]
    assessmentPlans: AssessmentPlan[]
    grades: Record<string, Record<string, Grade>>
}>()

const page = usePage()
const flash = computed(() => page.props.flash as { success?: string; error?: string })

const classroomLabel = computed(() => {
    const base = props.classroom.class?.name ?? 'Unknown'
    return props.classroom.stream ? `${base} - ${props.classroom.stream.name}` : base
})

// Track which assessment is currently being saved
const savingAssessmentId = ref<number | null>(null)

// Build form data for each assessment plan
const formsData = ref<Record<number, { grades: Array<{ student_id: number; score: string; remarks: string }> }>>(
    Object.fromEntries(
        props.assessmentPlans.map(plan => [
            plan.id,
            {
                grades: props.students.map(student => ({
                    student_id: student.id,
                    score: props.grades[student.id]?.[plan.id]?.score?.toString() ?? '',
                    remarks: props.grades[student.id]?.[plan.id]?.remarks ?? '',
                })),
            },
        ])
    )
)

const isLocked = (planId: number): boolean => {
    for (const studentGrades of Object.values(props.grades)) {
        if (studentGrades[planId]?.is_locked) {
            return true
        }
    }
    return false
}

const getSubmitData = (planId: number) => ({
    assessment_plan_id: planId,
    class_stream_assignment_id: props.classroom.id,
    grades: formsData.value[planId].grades
        .filter(g => g.score !== '')
        .map(g => ({
            student_id: g.student_id,
            score: parseFloat(g.score),
            remarks: g.remarks || null,
        })),
})
</script>

<template>
    <AppLayout>
        <Head :title="`Gradebook - ${subject.name} - ${classroomLabel}`" />

        <div class="space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <div class="mb-2 flex items-center gap-2">
                        <Link :href="gradebookIndex().url">
                            <Button variant="ghost" size="sm">
                                <ArrowLeft class="mr-1 h-4 w-4" />
                                Back
                            </Button>
                        </Link>
                    </div>
                    <Heading
                        :title="`${subject.name} — ${classroomLabel}`"
                        :description="`Enter grades for ${term.name}`"
                    />
                </div>

                <Link :href="gradebookSummary.url({
                    query: {
                        class_stream_assignment_id: classroom.id,
                        subject_id: subject.id,
                        term_id: term.id,
                    }
                })">
                    <Button variant="outline" class="gap-2">
                        <BarChart3 class="h-4 w-4" />
                        View Summary
                    </Button>
                </Link>
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

            <!-- No Assessment Plans -->
            <div v-if="assessmentPlans.length === 0" class="rounded-lg border bg-card p-8 text-center">
                <p class="text-muted-foreground">
                    No assessment plans have been configured for this subject and term. Please contact your administrator.
                </p>
            </div>

            <!-- Grade Entry Tables - One per Assessment -->
            <div v-for="plan in assessmentPlans" :key="plan.id" class="rounded-lg border bg-card">
                <div class="border-b px-6 py-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <h2 class="text-lg font-semibold">{{ plan.name }}</h2>
                            <Badge variant="outline">Max: {{ plan.max_score }}</Badge>
                            <Badge variant="secondary">Weight: {{ plan.weight }}%</Badge>
                            <Badge v-if="isLocked(plan.id)" variant="destructive">Locked</Badge>
                        </div>
                    </div>
                </div>

                <Form
                    v-bind="{
                        url: gradebookStore().url,
                        method: 'post',
                        data: getSubmitData(plan.id),
                    }"
                    v-slot="{ errors, processing }"
                    :options="{ preserveScroll: true }"
                    @success="savingAssessmentId = null">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b bg-muted/50">
                                    <th class="px-4 py-3 text-left font-medium text-muted-foreground w-12">#</th>
                                    <th class="px-4 py-3 text-left font-medium text-muted-foreground">Adm. No.</th>
                                    <th class="px-4 py-3 text-left font-medium text-muted-foreground">Student Name</th>
                                    <th class="px-4 py-3 text-center font-medium text-muted-foreground w-32">
                                        Score (/ {{ plan.max_score }})
                                    </th>
                                    <th class="px-4 py-3 text-left font-medium text-muted-foreground">Remarks</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(student, studentIndex) in students"
                                    :key="student.id"
                                    class="border-b last:border-0">
                                    <td class="px-4 py-2 text-muted-foreground">{{ studentIndex + 1 }}</td>
                                    <td class="px-4 py-2">
                                        <Badge variant="outline">{{ student.admission_number }}</Badge>
                                    </td>
                                    <td class="px-4 py-2 font-medium">
                                        {{ student.last_name }}, {{ student.first_name }}
                                    </td>
                                    <td class="px-4 py-2">
                                        <Input
                                            v-model="formsData[plan.id].grades[studentIndex].score"
                                            type="number"
                                            :min="0"
                                            :max="plan.max_score"
                                            step="0.01"
                                            class="w-24 text-center mx-auto bg-background"
                                            :disabled="isLocked(plan.id)"
                                            :placeholder="`/${plan.max_score}`" />
                                    </td>
                                    <td class="px-4 py-2">
                                        <Input
                                            v-model="formsData[plan.id].grades[studentIndex].remarks"
                                            type="text"
                                            class="bg-background"
                                            :disabled="isLocked(plan.id)"
                                            placeholder="Optional remarks" />
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div v-if="errors.grades" class="px-6 py-2">
                        <p class="text-sm text-destructive">{{ errors.grades }}</p>
                    </div>

                    <div class="border-t px-6 py-4">
                        <div class="flex justify-end">
                            <Button
                                type="submit"
                                :disabled="processing || isLocked(plan.id)"
                                class="gap-2">
                                <Spinner v-if="processing" />
                                <Save v-else class="h-4 w-4" />
                                {{ processing ? 'Saving...' : 'Save Grades' }}
                            </Button>
                        </div>
                    </div>
                </Form>
            </div>
        </div>
    </AppLayout>
</template>
