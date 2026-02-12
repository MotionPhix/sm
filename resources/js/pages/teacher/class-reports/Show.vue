<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import Heading from '@/components/Heading.vue'
import { Badge } from '@/components/ui/badge'
import { Button } from '@/components/ui/button'
import { Progress } from '@/components/ui/progress'
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs'
import { dashboard as teacherDashboard } from '@/routes/teacher'
import { index as classReportsIndex } from '@/routes/teacher/class-reports'
import {
    ArrowLeft,
    Users,
    TrendingUp,
    CalendarCheck,
    BookOpen,
} from 'lucide-vue-next'
import { computed } from 'vue'

interface SchoolClass {
    id: number
    name: string
}

interface Stream {
    id: number
    name: string
}

interface Classroom {
    id: number
    school_class: SchoolClass | null
    stream: Stream | null
}

interface Term {
    id: number
    name: string
}

interface GradeDistribution {
    grade: string
    comment: string | null
    count: number
}

interface SubjectAnalysis {
    subject_id: number
    subject_name: string
    subject_code: string
    average_score: number
    pass_rate: number
    total_graded: number
    total_students: number
    grade_distribution: GradeDistribution[]
}

interface SubjectScore {
    subject_id: number
    percentage: number | null
}

interface StudentPerformance {
    student_id: number
    student_name: string
    admission_number: string
    gender: string
    subject_scores: Record<string, SubjectScore>
    percentage: number
    grade: string | null
    comment: string | null
    rank: number
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

interface AttendanceStats {
    rate: number
    present: number
    absent: number
    late: number
    excused: number
    total_records: number
    total_days: number
}

const props = defineProps<{
    classroom: Classroom
    term: Term
    totalStudents: number
    overallAverage: number
    attendanceStats: AttendanceStats
    subjectAnalysis: SubjectAnalysis[]
    studentPerformance: StudentPerformance[]
    gradeScale: GradeScale | null
}>()

const classroomLabel = computed(() => {
    const base = props.classroom.school_class?.name ?? 'Unknown'
    return props.classroom.stream ? `${base} - ${props.classroom.stream.name}` : base
})

const subjectIds = computed(() => props.subjectAnalysis.map(s => s.subject_id))

const highestScore = computed(() => {
    if (props.studentPerformance.length === 0) return 0
    return Math.max(...props.studentPerformance.map(s => s.percentage))
})

const lowestScore = computed(() => {
    if (props.studentPerformance.length === 0) return 0
    return Math.min(...props.studentPerformance.map(s => s.percentage))
})

const maleCount = computed(() =>
    props.studentPerformance.filter(s => s.gender === 'male').length,
)

const femaleCount = computed(() =>
    props.studentPerformance.filter(s => s.gender === 'female').length,
)

const getGradeBadgeVariant = (grade: string | null): 'default' | 'secondary' | 'destructive' | 'outline' => {
    if (!grade) return 'outline'
    if (['A', 'A+', 'A-'].includes(grade)) return 'default'
    if (['F', 'U'].includes(grade)) return 'destructive'
    return 'secondary'
}

const getPerformanceColor = (value: number): string => {
    if (value >= 75) return 'text-green-600 dark:text-green-400'
    if (value >= 50) return 'text-yellow-600 dark:text-yellow-400'
    return 'text-red-600 dark:text-red-400'
}

const getPerformanceStatus = (passRate: number): string => {
    if (passRate >= 75) return 'Excellent'
    if (passRate >= 50) return 'Good'
    if (passRate >= 25) return 'Fair'
    return 'Needs Improvement'
}

const getStatusVariant = (passRate: number): 'default' | 'secondary' | 'destructive' | 'outline' => {
    if (passRate >= 75) return 'default'
    if (passRate >= 50) return 'secondary'
    return 'destructive'
}
</script>

<template>
    <AppLayout
        :breadcrumbs="[
            { title: 'Teacher', href: '' },
            { title: 'Dashboard', href: teacherDashboard().url },
            { title: 'Class Reports', href: classReportsIndex().url },
            { title: classroomLabel, href: '#' },
        ]">
        <Head :title="`Class Report — ${classroomLabel}`" />

        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <div class="mb-2">
                        <Link :href="classReportsIndex().url">
                            <Button variant="ghost" size="sm">
                                <ArrowLeft class="mr-1 h-4 w-4" />
                                Back to Class Reports
                            </Button>
                        </Link>
                    </div>
                    <Heading
                        :title="`Class Report: ${classroomLabel}`"
                        :description="`${term.name} performance summary`"
                    />
                </div>
            </div>

            <!-- KPI Cards -->
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-lg border bg-card p-4">
                    <div class="flex items-center gap-3">
                        <div class="rounded-lg bg-blue-100 p-2 dark:bg-blue-900/30">
                            <Users class="h-5 w-5 text-blue-600 dark:text-blue-400" />
                        </div>
                        <div>
                            <p class="text-sm text-muted-foreground">Students</p>
                            <p class="text-2xl font-semibold">{{ totalStudents }}</p>
                            <p class="text-xs text-muted-foreground">
                                {{ maleCount }}M / {{ femaleCount }}F
                            </p>
                        </div>
                    </div>
                </div>

                <div class="rounded-lg border bg-card p-4">
                    <div class="flex items-center gap-3">
                        <div class="rounded-lg bg-green-100 p-2 dark:bg-green-900/30">
                            <TrendingUp class="h-5 w-5 text-green-600 dark:text-green-400" />
                        </div>
                        <div>
                            <p class="text-sm text-muted-foreground">Class Average</p>
                            <p class="text-2xl font-semibold" :class="getPerformanceColor(overallAverage)">
                                {{ overallAverage }}%
                            </p>
                            <p class="text-xs text-muted-foreground">
                                High {{ highestScore }}% / Low {{ lowestScore }}%
                            </p>
                        </div>
                    </div>
                </div>

                <div class="rounded-lg border bg-card p-4">
                    <div class="flex items-center gap-3">
                        <div class="rounded-lg bg-purple-100 p-2 dark:bg-purple-900/30">
                            <CalendarCheck class="h-5 w-5 text-purple-600 dark:text-purple-400" />
                        </div>
                        <div>
                            <p class="text-sm text-muted-foreground">Attendance</p>
                            <p class="text-2xl font-semibold" :class="getPerformanceColor(attendanceStats.rate)">
                                {{ attendanceStats.rate }}%
                            </p>
                            <p class="text-xs text-muted-foreground">
                                {{ attendanceStats.total_days }} days recorded
                            </p>
                        </div>
                    </div>
                </div>

                <div class="rounded-lg border bg-card p-4">
                    <div class="flex items-center gap-3">
                        <div class="rounded-lg bg-orange-100 p-2 dark:bg-orange-900/30">
                            <BookOpen class="h-5 w-5 text-orange-600 dark:text-orange-400" />
                        </div>
                        <div>
                            <p class="text-sm text-muted-foreground">Subjects</p>
                            <p class="text-2xl font-semibold">{{ subjectAnalysis.length }}</p>
                            <p class="text-xs text-muted-foreground">
                                {{ subjectAnalysis.filter(s => s.total_graded > 0).length }} with grades
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabs -->
            <Tabs default-value="subjects" class="space-y-4">
                <TabsList>
                    <TabsTrigger value="subjects">Subject Analysis</TabsTrigger>
                    <TabsTrigger value="students">Student Performance</TabsTrigger>
                    <TabsTrigger value="attendance">Attendance</TabsTrigger>
                </TabsList>

                <!-- Subject Analysis Tab -->
                <TabsContent value="subjects">
                    <div class="rounded-lg border bg-card">
                        <div class="border-b px-6 py-4">
                            <h2 class="font-semibold">Subject Performance</h2>
                            <p class="text-sm text-muted-foreground">Average scores, pass rates, and grade distribution by subject</p>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="border-b bg-muted/50">
                                        <th class="px-4 py-3 text-left font-medium text-muted-foreground">Subject</th>
                                        <th class="px-4 py-3 text-center font-medium text-muted-foreground">Avg Score</th>
                                        <th class="px-4 py-3 text-center font-medium text-muted-foreground">Pass Rate</th>
                                        <th class="hidden px-4 py-3 text-left font-medium text-muted-foreground md:table-cell">Performance</th>
                                        <th class="px-4 py-3 text-center font-medium text-muted-foreground">Graded</th>
                                        <th class="px-4 py-3 text-center font-medium text-muted-foreground">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="subject in subjectAnalysis" :key="subject.subject_id" class="border-b last:border-0">
                                        <td class="px-4 py-3">
                                            <div class="font-medium">{{ subject.subject_name }}</div>
                                            <div v-if="subject.subject_code" class="text-xs text-muted-foreground">
                                                {{ subject.subject_code }}
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            <span class="font-semibold" :class="getPerformanceColor(subject.average_score)">
                                                {{ subject.average_score }}%
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            <span :class="getPerformanceColor(subject.pass_rate)">
                                                {{ subject.pass_rate }}%
                                            </span>
                                        </td>
                                        <td class="hidden px-4 py-3 md:table-cell">
                                            <div class="flex items-center gap-3">
                                                <Progress :model-value="subject.average_score" class="h-2 flex-1" />
                                                <span class="w-10 text-right text-xs text-muted-foreground">{{ subject.average_score }}%</span>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 text-center text-muted-foreground">
                                            {{ subject.total_graded }} / {{ subject.total_students }}
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            <Badge :variant="getStatusVariant(subject.pass_rate)">
                                                {{ getPerformanceStatus(subject.pass_rate) }}
                                            </Badge>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div v-if="subjectAnalysis.length === 0" class="px-6 py-8 text-center text-muted-foreground">
                            No subject data available for this term.
                        </div>
                    </div>

                    <!-- Grade Distribution -->
                    <div v-if="gradeScale && subjectAnalysis.some(s => s.grade_distribution.length > 0)" class="mt-4 rounded-lg border bg-card">
                        <div class="border-b px-6 py-4">
                            <h2 class="font-semibold">Grade Distribution</h2>
                            <p class="text-sm text-muted-foreground">Number of students per grade level for each subject</p>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="border-b bg-muted/50">
                                        <th class="px-4 py-3 text-left font-medium text-muted-foreground">Subject</th>
                                        <th
                                            v-for="step in gradeScale.steps"
                                            :key="step.id"
                                            class="px-3 py-3 text-center font-medium text-muted-foreground">
                                            {{ step.grade }}
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="subject in subjectAnalysis.filter(s => s.grade_distribution.length > 0)" :key="subject.subject_id" class="border-b last:border-0">
                                        <td class="px-4 py-2 font-medium">{{ subject.subject_name }}</td>
                                        <td
                                            v-for="step in gradeScale.steps"
                                            :key="step.id"
                                            class="px-3 py-2 text-center">
                                            <span
                                                v-if="subject.grade_distribution.find(g => g.grade === step.grade)?.count"
                                                class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-primary/10 text-xs font-medium">
                                                {{ subject.grade_distribution.find(g => g.grade === step.grade)?.count }}
                                            </span>
                                            <span v-else class="text-muted-foreground/40">—</span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </TabsContent>

                <!-- Student Performance Tab -->
                <TabsContent value="students">
                    <div class="rounded-lg border bg-card">
                        <div class="border-b px-6 py-4">
                            <h2 class="font-semibold">Student Rankings</h2>
                            <p class="text-sm text-muted-foreground">
                                Individual student performance across all subjects
                            </p>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="border-b bg-muted/50">
                                        <th class="w-12 px-4 py-3 text-center font-medium text-muted-foreground">#</th>
                                        <th class="px-4 py-3 text-left font-medium text-muted-foreground">Adm. No.</th>
                                        <th class="px-4 py-3 text-left font-medium text-muted-foreground">Student Name</th>
                                        <th
                                            v-for="subject in subjectAnalysis"
                                            :key="subject.subject_id"
                                            class="px-3 py-3 text-center font-medium text-muted-foreground">
                                            <span class="hidden lg:inline">{{ subject.subject_code || subject.subject_name }}</span>
                                            <span class="lg:hidden">{{ (subject.subject_code || subject.subject_name).slice(0, 3) }}</span>
                                        </th>
                                        <th class="px-4 py-3 text-center font-medium text-muted-foreground">Avg %</th>
                                        <th class="px-4 py-3 text-center font-medium text-muted-foreground">Grade</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="student in studentPerformance" :key="student.student_id" class="border-b last:border-0">
                                        <td class="px-4 py-2 text-center font-semibold">{{ student.rank }}</td>
                                        <td class="px-4 py-2">
                                            <Badge variant="outline">{{ student.admission_number }}</Badge>
                                        </td>
                                        <td class="px-4 py-2 font-medium">{{ student.student_name }}</td>
                                        <td
                                            v-for="subject in subjectAnalysis"
                                            :key="subject.subject_id"
                                            class="px-3 py-2 text-center">
                                            <template v-if="student.subject_scores[subject.subject_id]?.percentage !== null && student.subject_scores[subject.subject_id]?.percentage !== undefined">
                                                <span :class="getPerformanceColor(student.subject_scores[subject.subject_id].percentage!)">
                                                    {{ student.subject_scores[subject.subject_id].percentage }}
                                                </span>
                                            </template>
                                            <span v-else class="text-muted-foreground/40">—</span>
                                        </td>
                                        <td class="px-4 py-2 text-center">
                                            <span class="font-semibold" :class="getPerformanceColor(student.percentage)">
                                                {{ student.percentage }}%
                                            </span>
                                        </td>
                                        <td class="px-4 py-2 text-center">
                                            <Badge :variant="getGradeBadgeVariant(student.grade)">
                                                {{ student.grade ?? '—' }}
                                            </Badge>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div v-if="studentPerformance.length === 0" class="px-6 py-8 text-center text-muted-foreground">
                            No student performance data available for this term.
                        </div>
                    </div>
                </TabsContent>

                <!-- Attendance Tab -->
                <TabsContent value="attendance">
                    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                        <div class="rounded-lg border bg-card p-4">
                            <p class="text-sm text-muted-foreground">Present</p>
                            <p class="text-2xl font-semibold text-green-600 dark:text-green-400">
                                {{ attendanceStats.present }}
                            </p>
                        </div>
                        <div class="rounded-lg border bg-card p-4">
                            <p class="text-sm text-muted-foreground">Absent</p>
                            <p class="text-2xl font-semibold text-red-600 dark:text-red-400">
                                {{ attendanceStats.absent }}
                            </p>
                        </div>
                        <div class="rounded-lg border bg-card p-4">
                            <p class="text-sm text-muted-foreground">Late</p>
                            <p class="text-2xl font-semibold text-yellow-600 dark:text-yellow-400">
                                {{ attendanceStats.late }}
                            </p>
                        </div>
                        <div class="rounded-lg border bg-card p-4">
                            <p class="text-sm text-muted-foreground">Excused</p>
                            <p class="text-2xl font-semibold text-blue-600 dark:text-blue-400">
                                {{ attendanceStats.excused }}
                            </p>
                        </div>
                    </div>

                    <div v-if="attendanceStats.total_records > 0" class="mt-4 rounded-lg border bg-card p-6">
                        <h3 class="mb-3 font-semibold">Attendance Rate</h3>
                        <div class="flex items-center gap-4">
                            <Progress :model-value="attendanceStats.rate" class="h-3 flex-1" />
                            <span class="text-lg font-semibold" :class="getPerformanceColor(attendanceStats.rate)">
                                {{ attendanceStats.rate }}%
                            </span>
                        </div>
                        <p class="mt-2 text-sm text-muted-foreground">
                            Based on {{ attendanceStats.total_records }} records across {{ attendanceStats.total_days }} school days
                        </p>
                    </div>

                    <div v-else class="mt-4 rounded-lg border bg-card px-6 py-8 text-center text-muted-foreground">
                        No attendance records for this term yet.
                    </div>
                </TabsContent>
            </Tabs>

            <!-- Grading Scale Reference -->
            <div v-if="gradeScale" class="rounded-lg border bg-card">
                <div class="border-b px-6 py-3">
                    <h3 class="text-sm font-semibold text-muted-foreground">Grading Scale: {{ gradeScale.name }}</h3>
                </div>
                <div class="flex flex-wrap gap-3 px-6 py-3">
                    <div v-for="step in gradeScale.steps" :key="step.id" class="flex items-center gap-1.5 text-xs">
                        <Badge variant="outline" class="font-mono">{{ step.grade }}</Badge>
                        <span class="text-muted-foreground">{{ step.min_percent }}–{{ step.max_percent }}%</span>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
