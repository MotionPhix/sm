<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import Heading from '@/components/Heading.vue'
import { Button } from '@/components/ui/button'
import { Calendar, ClipboardCheck, Users, BookOpen, Clock, TrendingUp } from 'lucide-vue-next'

interface QuickStat {
    label: string
    value: string | number
    icon: typeof Calendar
    color: string
}

const props = defineProps<{
    assignedClasses?: number
    todayAttendance?: number
    totalStudents?: number
    upcomingPeriods?: number
}>()

const quickStats: QuickStat[] = [
    {
        label: 'Assigned Classes',
        value: props.assignedClasses ?? 0,
        icon: BookOpen,
        color: 'bg-blue-100 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400',
    },
    {
        label: 'Total Students',
        value: props.totalStudents ?? 0,
        icon: Users,
        color: 'bg-green-100 text-green-600 dark:bg-green-900/30 dark:text-green-400',
    },
    {
        label: 'Today\'s Attendance',
        value: props.todayAttendance ?? 0,
        icon: ClipboardCheck,
        color: 'bg-amber-100 text-amber-600 dark:bg-amber-900/30 dark:text-amber-400',
    },
    {
        label: 'Periods Today',
        value: props.upcomingPeriods ?? 0,
        icon: Clock,
        color: 'bg-purple-100 text-purple-600 dark:bg-purple-900/30 dark:text-purple-400',
    },
]

const quickActions = [
    {
        title: 'Record Attendance',
        description: 'Mark daily attendance for your classes',
        href: '/teacher/attendance',
        icon: ClipboardCheck,
    },
    {
        title: 'View Timetable',
        description: 'Check your weekly teaching schedule',
        href: '/teacher/timetable',
        icon: Calendar,
    },
]
</script>

<template>
    <AppLayout>
        <Head title="Teacher Dashboard" />

        <div class="space-y-6">
            <Heading
                title="Teacher Dashboard"
                description="Welcome back! Here's an overview of your teaching activities."
            />

            <!-- Quick Stats -->
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div
                    v-for="stat in quickStats"
                    :key="stat.label"
                    class="rounded-lg border bg-card p-6"
                >
                    <div class="flex items-center gap-4">
                        <div class="rounded-lg p-3" :class="stat.color">
                            <component :is="stat.icon" class="h-5 w-5" />
                        </div>
                        <div>
                            <p class="text-sm text-muted-foreground">{{ stat.label }}</p>
                            <p class="text-2xl font-semibold">{{ stat.value }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="rounded-lg border bg-card">
                <div class="border-b px-6 py-4">
                    <h2 class="text-lg font-semibold">Quick Actions</h2>
                </div>
                <div class="grid gap-4 p-6 sm:grid-cols-2">
                    <Link
                        v-for="action in quickActions"
                        :key="action.title"
                        :href="action.href"
                        class="flex items-start gap-4 rounded-lg border p-4 transition-colors hover:bg-muted/50"
                    >
                        <div class="rounded-lg bg-primary/10 p-3">
                            <component :is="action.icon" class="h-5 w-5 text-primary" />
                        </div>
                        <div>
                            <h3 class="font-medium">{{ action.title }}</h3>
                            <p class="text-sm text-muted-foreground">{{ action.description }}</p>
                        </div>
                    </Link>
                </div>
            </div>

            <!-- Recent Activity / Upcoming -->
            <div class="grid gap-6 lg:grid-cols-2">
                <div class="rounded-lg border bg-card">
                    <div class="border-b px-6 py-4">
                        <h2 class="text-lg font-semibold">Today's Schedule</h2>
                    </div>
                    <div class="p-6">
                        <div class="flex flex-col items-center justify-center py-8 text-center">
                            <Calendar class="h-12 w-12 text-muted-foreground" />
                            <p class="mt-4 text-sm text-muted-foreground">
                                View your full timetable for today's classes
                            </p>
                            <Link href="/teacher/timetable">
                                <Button variant="outline" class="mt-4">
                                    View Timetable
                                </Button>
                            </Link>
                        </div>
                    </div>
                </div>

                <div class="rounded-lg border bg-card">
                    <div class="border-b px-6 py-4">
                        <h2 class="text-lg font-semibold">Attendance Overview</h2>
                    </div>
                    <div class="p-6">
                        <div class="flex flex-col items-center justify-center py-8 text-center">
                            <TrendingUp class="h-12 w-12 text-muted-foreground" />
                            <p class="mt-4 text-sm text-muted-foreground">
                                Track attendance patterns and history
                            </p>
                            <Link href="/teacher/attendance/history">
                                <Button variant="outline" class="mt-4">
                                    View History
                                </Button>
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
