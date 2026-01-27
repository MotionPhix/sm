<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import { ref, onMounted, computed } from 'vue'
import AppLayout from '@/layouts/AppLayout.vue'
import Heading from '@/components/Heading.vue'
import type { BreadcrumbItemType } from '@/types'
import { CalendarDays, Clock, Users } from 'lucide-vue-next'

interface Stream {
    id: number
    name: string
}

interface SchoolClass {
    id: number
    name: string
    order: number
}

interface Classroom {
    id: number
    school_class_id: number
    stream_id: number | null
    class: SchoolClass | null
    stream: Stream | null
}

interface TeacherAssignment {
    id: number
    user_id: number
    class_stream_assignment_id: number
    schedule_data: Record<string, Record<string, unknown>> | null
    classroom: Classroom | null
}

interface ScheduleSlot {
    assignment: TeacherAssignment
    details: unknown
}

interface WeeklyTimetable {
    [day: string]: {
        [period: string]: ScheduleSlot
    }
}

interface Period {
    key: string
    label: string
    isBreak?: boolean
}

const props = defineProps<{
    teacherAssignments: TeacherAssignment[]
    classes?: SchoolClass[]
}>()

const breadcrumbs: BreadcrumbItemType[] = [
    { title: 'Timetable', href: '/teacher/timetable' },
]

const weeklyTimetable = ref<WeeklyTimetable>({})
const loading = ref(false)
const error = ref('')

const periods: Period[] = [
    { key: 'period_1', label: '8:00 - 8:40', isBreak: false },
    { key: 'period_2', label: '8:40 - 9:20', isBreak: false },
    { key: 'period_3', label: '9:20 - 10:00', isBreak: false },
    { key: 'break_1', label: '10:00 - 10:20', isBreak: true },
    { key: 'period_4', label: '10:20 - 11:00', isBreak: false },
    { key: 'period_5', label: '11:00 - 11:40', isBreak: false },
    { key: 'period_6', label: '11:40 - 12:20', isBreak: false },
    { key: 'lunch', label: '12:20 - 13:20', isBreak: true },
    { key: 'period_7', label: '13:20 - 14:00', isBreak: false },
    { key: 'period_8', label: '14:00 - 14:40', isBreak: false },
    { key: 'period_9', label: '14:40 - 15:20', isBreak: false },
]

const days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'] as const

const dayLabels: Record<typeof days[number], string> = {
    monday: 'Mon',
    tuesday: 'Tue',
    wednesday: 'Wed',
    thursday: 'Thu',
    friday: 'Fri',
    saturday: 'Sat',
}

const formatSchedule = (schedule: Record<string, Record<string, unknown>> | null): string => {
    if (!schedule) return 'No schedule set'

    const formattedDays: string[] = []

    for (const [day, periodData] of Object.entries(schedule)) {
        const periodCount = Object.keys(periodData).length
        const dayCapitalized = day.charAt(0).toUpperCase() + day.slice(1)
        formattedDays.push(`${dayCapitalized}: ${periodCount} period${periodCount !== 1 ? 's' : ''}`)
    }

    return formattedDays.length > 0 ? formattedDays.join(', ') : 'No schedule set'
}

const getSlotClass = (slot: ScheduleSlot | undefined): string => {
    if (!slot) return ''

    const classNames = [
        'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-200',
        'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-200',
        'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-200',
        'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-200',
        'bg-rose-100 text-rose-800 dark:bg-rose-900/30 dark:text-rose-200',
        'bg-cyan-100 text-cyan-800 dark:bg-cyan-900/30 dark:text-cyan-200',
    ]

    const assignmentId = slot.assignment?.classroom?.id ?? 0
    return classNames[assignmentId % classNames.length]
}

const totalAssignedPeriods = computed(() => {
    let count = 0
    for (const day of Object.values(weeklyTimetable.value)) {
        count += Object.keys(day).filter(key => !key.includes('break') && key !== 'lunch').length
    }
    return count
})

const hasScheduleData = computed(() => Object.keys(weeklyTimetable.value).length > 0)

const fetchWeeklyTimetable = async () => {
    loading.value = true
    error.value = ''

    try {
        const response = await fetch('/teacher/timetable/my', {
            headers: { Accept: 'application/json' },
        })

        if (!response.ok) {
            throw new Error('Failed to load timetable')
        }

        const data = await response.json()
        weeklyTimetable.value = data.timetable ?? {}
    } catch (e) {
        error.value = e instanceof Error ? e.message : 'Error loading timetable'
    } finally {
        loading.value = false
    }
}

onMounted(fetchWeeklyTimetable)
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="My Timetable" />

        <div class="space-y-6">
            <div class="flex items-center justify-between">
                <Heading
                    title="My Timetable"
                    description="View your assigned classes and weekly teaching schedule"
                />
                <div v-if="hasScheduleData" class="flex items-center gap-2 rounded-lg border bg-muted/50 px-4 py-2">
                    <Clock class="h-5 w-5 text-muted-foreground" />
                    <span class="text-sm font-medium">
                        {{ totalAssignedPeriods }} periods/week
                    </span>
                </div>
            </div>

            <!-- Error Message -->
            <div v-if="error" class="rounded-lg border border-destructive/50 bg-destructive/10 p-4 text-sm text-destructive">
                {{ error }}
            </div>

            <!-- Classes Assigned Card -->
            <div class="rounded-lg border bg-card">
                <div class="border-b px-6 py-4">
                    <div class="flex items-center gap-2">
                        <Users class="h-5 w-5 text-muted-foreground" />
                        <h2 class="text-lg font-semibold">Classes Assigned</h2>
                    </div>
                </div>

                <div class="p-6">
                    <div v-if="teacherAssignments.length > 0" class="space-y-4">
                        <div
                            v-for="assignment in teacherAssignments"
                            :key="assignment.id"
                            class="flex items-center justify-between rounded-lg border p-4"
                        >
                            <div class="flex items-center gap-3">
                                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-primary/10">
                                    <span class="text-sm font-bold text-primary">
                                        {{ assignment.classroom?.class?.name?.charAt(0) ?? '?' }}
                                    </span>
                                </div>
                                <div>
                                    <h3 class="font-semibold">
                                        {{ assignment.classroom?.class?.name ?? 'Unknown Class' }}
                                        <span v-if="assignment.classroom?.stream" class="text-muted-foreground">
                                            ({{ assignment.classroom.stream.name }})
                                        </span>
                                    </h3>
                                    <p class="text-sm text-muted-foreground">
                                        {{ formatSchedule(assignment.schedule_data) }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-else class="py-8 text-center">
                        <CalendarDays class="mx-auto h-12 w-12 text-muted-foreground" />
                        <h3 class="mt-2 text-sm font-medium">No assigned classes</h3>
                        <p class="mt-1 text-sm text-muted-foreground">
                            You haven't been assigned any classes yet.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Weekly Schedule Grid -->
            <div v-if="hasScheduleData || loading" class="rounded-lg border bg-card">
                <div class="border-b px-6 py-4">
                    <div class="flex items-center gap-2">
                        <CalendarDays class="h-5 w-5 text-muted-foreground" />
                        <h2 class="text-lg font-semibold">Weekly Schedule</h2>
                    </div>
                </div>

                <div class="overflow-x-auto p-6">
                    <!-- Loading State -->
                    <div v-if="loading" class="flex items-center justify-center py-12">
                        <div class="h-8 w-8 animate-spin rounded-full border-4 border-primary/20 border-t-primary"></div>
                        <span class="ml-3 text-muted-foreground">Loading schedule...</span>
                    </div>

                    <!-- Timetable Grid -->
                    <table v-else class="min-w-full">
                        <thead>
                            <tr>
                                <th class="bg-muted/50 px-4 py-3 text-left text-xs font-medium uppercase tracking-wider">
                                    Time
                                </th>
                                <th
                                    v-for="day in days"
                                    :key="day"
                                    class="bg-muted/50 px-4 py-3 text-center text-xs font-medium uppercase tracking-wider"
                                >
                                    {{ dayLabels[day] }}
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            <tr v-for="period in periods" :key="period.key" :class="{ 'bg-muted/30': period.isBreak }">
                                <td class="whitespace-nowrap px-4 py-3 text-sm font-medium">
                                    <div class="flex flex-col">
                                        <span v-if="!period.isBreak" class="text-xs text-muted-foreground">
                                            {{ period.key.replace('_', ' ').replace('period', 'Period') }}
                                        </span>
                                        <span :class="period.isBreak ? 'italic text-muted-foreground' : ''">
                                            {{ period.label }}
                                        </span>
                                    </div>
                                </td>
                                <td
                                    v-for="day in days"
                                    :key="`${day}-${period.key}`"
                                    class="px-2 py-2 text-center"
                                >
                                    <template v-if="period.isBreak">
                                        <span class="text-xs italic text-muted-foreground">
                                            {{ period.key === 'lunch' ? 'Lunch' : 'Break' }}
                                        </span>
                                    </template>
                                    <template v-else-if="weeklyTimetable[day]?.[period.key]">
                                        <div
                                            class="rounded-md px-2 py-1.5 text-xs font-medium"
                                            :class="getSlotClass(weeklyTimetable[day][period.key])"
                                        >
                                            <div class="font-semibold">
                                                {{ weeklyTimetable[day][period.key].assignment.classroom?.class?.name }}
                                            </div>
                                            <div v-if="weeklyTimetable[day][period.key].assignment.classroom?.stream" class="opacity-75">
                                                {{ weeklyTimetable[day][period.key].assignment.classroom?.stream?.name }}
                                            </div>
                                        </div>
                                    </template>
                                    <template v-else>
                                        <span class="text-muted-foreground/50">-</span>
                                    </template>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Empty State for No Schedule -->
            <div v-else-if="!loading && teacherAssignments.length > 0" class="rounded-lg border-2 border-dashed p-8 text-center">
                <CalendarDays class="mx-auto h-12 w-12 text-muted-foreground" />
                <h3 class="mt-4 text-sm font-medium">No schedule configured</h3>
                <p class="mt-2 text-sm text-muted-foreground">
                    Your assigned classes don't have schedule times set yet.
                    Contact your administrator to configure the timetable.
                </p>
            </div>
        </div>
    </AppLayout>
</template>
