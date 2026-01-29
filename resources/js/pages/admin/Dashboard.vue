<script setup lang="ts">
import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'
import Heading from '@/components/Heading.vue'
import AppLayout from '@/layouts/AppLayout.vue'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Link } from '@inertiajs/vue3'
import {
    Users,
    Calendar,
    BookOpen,
    GraduationCap,
    AlertCircle,
    Check,
    Clock,
    TrendingUp,
    Award,
    FileText,
    Bell,
} from 'lucide-vue-next'
import * as admin from '@/routes/admin'

const page = usePage()
const school = computed(() => page.props.auth.user?.activeSchool)
const stats = computed(() =>
    page.props.stats as {
        staffCount: number
        academicYearsCount: number
        termsCount: number
        admissionCyclesCount: number
    }
)
const setupStatus = computed(
    () =>
        page.props.setupStatus as Record<
            string,
            {
                completed: boolean
                inProgress?: boolean
                href?: string | null
            }
        >
)

const statCards = computed(() => [
    {
        title: 'Staff Members',
        value: stats.value.staffCount,
        icon: Users,
        color: 'from-blue-500 to-blue-600',
        bgColor: 'bg-blue-50 dark:bg-blue-950',
        trend: '+5%',
    },
    {
        title: 'Academic Years',
        value: stats.value.academicYearsCount,
        icon: Calendar,
        color: 'from-purple-500 to-purple-600',
        bgColor: 'bg-purple-50 dark:bg-purple-950',
        trend: stats.value.academicYearsCount > 0 ? 'Active' : 'None',
    },
    {
        title: 'Terms',
        value: stats.value.termsCount,
        icon: BookOpen,
        color: 'from-amber-500 to-amber-600',
        bgColor: 'bg-amber-50 dark:bg-amber-950',
        trend: stats.value.termsCount > 0 ? 'Configured' : 'Pending',
    },
    {
        title: 'Admission Cycles',
        value: stats.value.admissionCyclesCount,
        icon: GraduationCap,
        color: 'from-emerald-500 to-emerald-600',
        bgColor: 'bg-emerald-50 dark:bg-emerald-950',
        trend: stats.value.admissionCyclesCount > 0 ? 'Open' : 'Closed',
    },
])

const setupSteps = [
    {
        key: 'schoolProfile',
        title: 'School Profile',
        description: 'School name, type, location, and contact details',
    },
    {
        key: 'academicCalendar',
        title: 'Academic Calendar',
        description: 'Academic years and terms',
    },
    {
        key: 'classesAndStreams',
        title: 'Classes & Streams',
        description: 'Define class levels and streams',
    },
    {
        key: 'subjects',
        title: 'Subjects',
        description: 'Configure subjects for each class',
    },
    {
        key: 'staffAssignments',
        title: 'Staff Assignments',
        description: 'Assign teachers to subjects and streams',
    },
    {
        key: 'gradingScheme',
        title: 'Grading Scheme',
        description: 'Define grading scales and promotion rules',
    },
]

const getStepStatus = (key: string) => {
    const status = setupStatus.value[key]
    if (status.completed) return 'completed'
    if (status.inProgress) return 'in-progress'
    return 'pending'
}

const recentNotifications = [
    {
        id: 1,
        title: 'Staff Invitation Sent',
        description: 'Invitation sent to john.teacher@school.edu',
        time: '2 hours ago',
        icon: Users,
    },
    {
        id: 2,
        title: 'Academic Year Created',
        description: '2024/2025 academic year is now active',
        time: '1 day ago',
        icon: Calendar,
    },
    {
        id: 3,
        title: 'Terms Configured',
        description: 'All 3 terms have been set up successfully',
        time: '2 days ago',
        icon: Check,
    },
]
</script>

<template>
    <AppLayout>
        <div class="space-y-6 max-w-7xl">
            <!-- Header -->
            <div>
                <h1 class="text-3xl font-bold">Welcome back, {{ school?.name }}</h1>
                <p class="text-muted-foreground mt-2">School Management Dashboard</p>
            </div>

            <!-- Stats Cards Grid -->
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                <Card v-for="stat in statCards" :key="stat.title" class="relative overflow-hidden">
                    <div :class="[
                        'absolute inset-0 opacity-10',
                        stat.bgColor,
                    ]" />
                    <CardHeader class="relative flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">
                            {{ stat.title }}
                        </CardTitle>
                        <div :class="[
                            'h-10 w-10 rounded-full flex items-center justify-center text-white',
                            `bg-gradient-to-br ${stat.color}`,
                        ]">
                            <component :is="stat.icon" class="h-5 w-5" />
                        </div>
                    </CardHeader>
                    <CardContent class="relative">
                        <div class="text-3xl font-bold">{{ stat.value }}</div>
                        <div
                            class="flex items-center gap-1 text-xs font-medium text-emerald-600 dark:text-emerald-400 mt-2">
                            <TrendingUp class="h-3 w-3" />
                            {{ stat.trend }}
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Main Content Grid -->
            <div class="grid gap-6 lg:grid-cols-3">
                <!-- Left Column (2/3) -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Setup Progress -->
                    <Card>
                        <CardHeader>
                            <div class="flex items-center justify-between">
                                <div>
                                    <CardTitle class="flex items-center gap-2">
                                        <AlertCircle class="h-5 w-5 text-amber-600" />
                                        Setup Progress
                                    </CardTitle>
                                    <CardDescription>
                                        Complete the setup steps to fully configure your school
                                    </CardDescription>
                                </div>
                            </div>
                        </CardHeader>
                        <CardContent>
                            <div class="space-y-3">
                                <Link v-for="step in setupSteps" :key="step.key"
                                    :href="setupStatus[step.key]?.href || '#'" :class="[
                                        'flex items-center gap-3 p-3 rounded-lg border transition-colors',
                                        setupStatus[step.key]?.href
                                            ? 'hover:bg-accent cursor-pointer'
                                            : 'opacity-75',
                                    ]">
                                    <!-- Status Badge -->
                                    <div :class="[
                                        'flex h-8 w-8 shrink-0 items-center justify-center rounded-full text-xs font-semibold text-white',
                                        getStepStatus(step.key) === 'completed'
                                            ? 'bg-green-600'
                                            : getStepStatus(step.key) === 'in-progress'
                                                ? 'bg-blue-600'
                                                : 'bg-gray-400',
                                    ]">
                                        <Check v-if="getStepStatus(step.key) === 'completed'" class="h-4 w-4" />
                                        <Clock v-else-if="getStepStatus(step.key) === 'in-progress'" class="h-4 w-4" />
                                        <span v-else>•</span>
                                    </div>

                                    <!-- Content -->
                                    <div class="flex-1 min-w-0">
                                        <p class="font-medium text-sm">{{ step.title }}</p>
                                        <p class="text-xs text-muted-foreground">{{ step.description }}</p>
                                    </div>

                                    <!-- Status Badge -->
                                    <span :class="[
                                        'text-xs font-semibold px-2 py-1 rounded-full shrink-0',
                                        getStepStatus(step.key) === 'completed'
                                            ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'
                                            : getStepStatus(step.key) === 'in-progress'
                                                ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200'
                                                : 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-200',
                                    ]">
                                        {{
                                            getStepStatus(step.key) === 'completed'
                                                ? '✓ Done'
                                                : getStepStatus(step.key) === 'in-progress'
                                                    ? 'In Progress'
                                        : 'Pending'
                                        }}
                                    </span>
                                </Link>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Key Metrics Section -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2">
                                <Award class="h-5 w-5 text-blue-600" />
                                School Overview
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="grid grid-cols-2 gap-4">
                                <div class="p-4 rounded-lg bg-slate-50 dark:bg-slate-900">
                                    <p class="text-xs font-medium text-muted-foreground">Active Year</p>
                                    <p class="text-2xl font-bold mt-1">
                                        {{ stats.academicYearsCount > 0 ? '2024/2025' : 'Not Set' }}
                                    </p>
                                </div>
                                <div class="p-4 rounded-lg bg-slate-50 dark:bg-slate-900">
                                    <p class="text-xs font-medium text-muted-foreground">Terms</p>
                                    <p class="text-2xl font-bold mt-1">{{ stats.termsCount }}/3</p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Right Column (1/3) -->
                <div class="space-y-6">
                    <!-- Recent Activity -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2 text-base">
                                <Bell class="h-4 w-4" />
                                Recent Activity
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="space-y-4">
                                <div v-for="notification in recentNotifications" :key="notification.id"
                                    class="flex gap-3 pb-4 last:pb-0 last:border-b-0 border-b">
                                    <div
                                        class="h-8 w-8 rounded-full bg-blue-100 dark:bg-blue-900 flex items-center justify-center shrink-0">
                                        <component :is="notification.icon"
                                            class="h-4 w-4 text-blue-600 dark:text-blue-400" />
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium">{{ notification.title }}</p>
                                        <p class="text-xs text-muted-foreground">{{ notification.description }}</p>
                                        <p class="text-xs text-muted-foreground mt-1">{{ notification.time }}</p>
                                    </div>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Quick Links -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="text-base">Quick Links</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="space-y-2">
                                <Link :href="admin.default.staff.index.url()" class="block">
                                    <div class="p-3 rounded-lg hover:bg-accent transition-colors">
                                        <p class="text-sm font-medium">Manage Staff</p>
                                        <p class="text-xs text-muted-foreground">Invite & manage teachers</p>
                                    </div>
                                </Link>
                                <Link :href="admin.default.settings.academicYear.index.url()" class="block">
                                    <div class="p-3 rounded-lg hover:bg-accent transition-colors">
                                        <p class="text-sm font-medium">Academic Setup</p>
                                        <p class="text-xs text-muted-foreground">Years & terms</p>
                                    </div>
                                </Link>
                                <Link :href="admin.default.settings.admissionCycles.index.url()" class="block">
                                    <div class="p-3 rounded-lg hover:bg-accent transition-colors">
                                        <p class="text-sm font-medium">Admissions</p>
                                        <p class="text-xs text-muted-foreground">Manage cycles</p>
                                    </div>
                                </Link>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
