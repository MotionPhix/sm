<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import Heading from '@/components/Heading.vue'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import { ArrowLeft, ClipboardList } from 'lucide-vue-next'
import { index as gradebookIndex, show as gradebookShow } from '@/routes/teacher/gradebook'
import { computed } from 'vue'

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

interface AssessmentPlan {
    id: number
    name: string
    max_score: number
    weight: number
    ordering: number
}

interface GradeScaleStep {
    id: number
    min_percent: number
    max_percent: number
    grade: string
    comment: string | null
}

interface GradeScale {
    id: number
    name: string
    steps: GradeScaleStep[]
}

interface StudentResult {
    student: {
        id: number
        first_name: string
        last_name: string
        admission_number: string
    }
    total: number
    percentage: number
    grade: string | null
    comment: string | null
    rank: number
}

interface Grade {
    id: number
    score: number | null
    remarks: string | null
}

const props = defineProps<{
    classroom: Classroom
    subject: Subject
    term: Term
    results: StudentResult[]
    assessmentPlans: AssessmentPlan[]
    grades: Record<string, Record<string, Grade>>
    gradeScale: GradeScale
}>()

const classroomLabel = computed(() => {
    const base = props.classroom.class?.name ?? 'Unknown'
    return props.classroom.stream ? `${base} - ${props.classroom.stream.name}` : base
})

const classAverage = computed(() => {
    if (props.results.length === 0) return 0
    const sum = props.results.reduce((acc, r) => acc + r.percentage, 0)
    return (sum / props.results.length).toFixed(1)
})

const highestScore = computed(() => {
    if (props.results.length === 0) return 0
    return Math.max(...props.results.map(r => r.percentage)).toFixed(1)
})

const lowestScore = computed(() => {
    if (props.results.length === 0) return 0
    return Math.min(...props.results.map(r => r.percentage)).toFixed(1)
})

const getGradeBadgeVariant = (grade: string | null): 'default' | 'secondary' | 'destructive' | 'outline' => {
    if (!grade) return 'outline'
    if (['A', 'A+', 'A-'].includes(grade)) return 'default'
    if (['F', 'U'].includes(grade)) return 'destructive'
    return 'secondary'
}
</script>

<template>
    <AppLayout>
        <Head :title="`Summary - ${subject.name} - ${classroomLabel}`" />

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
                        :title="`Summary: ${subject.name} — ${classroomLabel}`"
                        :description="`${term.name} results with rankings`"
                    />
                </div>

                <Link :href="gradebookShow.url({
                    query: {
                        class_stream_assignment_id: classroom.id,
                        subject_id: subject.id,
                        term_id: term.id,
                    }
                })">
                    <Button variant="outline" class="gap-2">
                        <ClipboardList class="h-4 w-4" />
                        Enter Grades
                    </Button>
                </Link>
            </div>

            <!-- Stats Overview -->
            <div class="grid gap-4 sm:grid-cols-4">
                <div class="rounded-lg border bg-card p-4">
                    <p class="text-sm text-muted-foreground">Students</p>
                    <p class="text-2xl font-semibold">{{ results.length }}</p>
                </div>
                <div class="rounded-lg border bg-card p-4">
                    <p class="text-sm text-muted-foreground">Class Average</p>
                    <p class="text-2xl font-semibold">{{ classAverage }}%</p>
                </div>
                <div class="rounded-lg border bg-card p-4">
                    <p class="text-sm text-muted-foreground">Highest</p>
                    <p class="text-2xl font-semibold">{{ highestScore }}%</p>
                </div>
                <div class="rounded-lg border bg-card p-4">
                    <p class="text-sm text-muted-foreground">Lowest</p>
                    <p class="text-2xl font-semibold">{{ lowestScore }}%</p>
                </div>
            </div>

            <!-- Grading Scale Reference -->
            <div class="rounded-lg border bg-card">
                <div class="border-b px-6 py-3">
                    <h3 class="text-sm font-semibold text-muted-foreground">Grading Scale: {{ gradeScale.name }}</h3>
                </div>
                <div class="flex flex-wrap gap-3 px-6 py-3">
                    <div v-for="step in gradeScale.steps" :key="step.id"
                        class="flex items-center gap-1.5 text-xs">
                        <Badge variant="outline" class="font-mono">{{ step.grade }}</Badge>
                        <span class="text-muted-foreground">{{ step.min_percent }}–{{ step.max_percent }}%</span>
                    </div>
                </div>
            </div>

            <!-- Results Table -->
            <div class="rounded-lg border bg-card">
                <div class="border-b px-6 py-4">
                    <h2 class="text-lg font-semibold">Results</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b bg-muted/50">
                                <th class="px-4 py-3 text-center font-medium text-muted-foreground w-12">Rank</th>
                                <th class="px-4 py-3 text-left font-medium text-muted-foreground">Adm. No.</th>
                                <th class="px-4 py-3 text-left font-medium text-muted-foreground">Student Name</th>
                                <th v-for="plan in assessmentPlans" :key="plan.id"
                                    class="px-4 py-3 text-center font-medium text-muted-foreground">
                                    <div>{{ plan.name }}</div>
                                    <div class="text-xs font-normal">/ {{ plan.max_score }}</div>
                                </th>
                                <th class="px-4 py-3 text-center font-medium text-muted-foreground">Total (%)</th>
                                <th class="px-4 py-3 text-center font-medium text-muted-foreground">Grade</th>
                                <th class="px-4 py-3 text-left font-medium text-muted-foreground">Comment</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="result in results"
                                :key="result.student.id"
                                class="border-b last:border-0">
                                <td class="px-4 py-2 text-center font-semibold">
                                    {{ result.rank }}
                                </td>
                                <td class="px-4 py-2">
                                    <Badge variant="outline">{{ result.student.admission_number }}</Badge>
                                </td>
                                <td class="px-4 py-2 font-medium">
                                    {{ result.student.last_name }}, {{ result.student.first_name }}
                                </td>
                                <td v-for="plan in assessmentPlans" :key="plan.id"
                                    class="px-4 py-2 text-center">
                                    {{ grades[result.student.id]?.[plan.id]?.score ?? '—' }}
                                </td>
                                <td class="px-4 py-2 text-center font-semibold">
                                    {{ result.percentage.toFixed(1) }}%
                                </td>
                                <td class="px-4 py-2 text-center">
                                    <Badge :variant="getGradeBadgeVariant(result.grade)">
                                        {{ result.grade ?? '—' }}
                                    </Badge>
                                </td>
                                <td class="px-4 py-2 text-muted-foreground">
                                    {{ result.comment ?? '—' }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
