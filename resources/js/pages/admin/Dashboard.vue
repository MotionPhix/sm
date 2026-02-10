<script setup lang="ts">
import { computed } from 'vue'
import { Head, usePage, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Progress } from '@/components/ui/progress'
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
    DollarSign,
    UserCheck,
    ArrowRight,
    Settings,
    Layers,
    ClipboardList,
    UserPlus,
} from 'lucide-vue-next'
import * as admin from '@/routes/admin'

interface Stats {
    staffCount: number
    academicYearsCount: number
    termsCount: number
    admissionCyclesCount: number
    classesCount: number
    streamsCount: number
    subjectsCount: number
    totalStudents: number
    maleStudents: number
    femaleStudents: number
    openAdmissionCycles: number
    pendingApplicants: number
}

interface FeeStats {
    totalExpected: number
    totalCollected: number
    totalPending: number
    collectionRate: number
}

interface SetupStatus {
    [key: string]: {
        completed: boolean
        inProgress?: boolean
        href?: string | null
    }
}

const page = usePage()
const school = computed(() => page.props.auth.user?.activeSchool)
const user = computed(() => page.props.user as { name: string; email: string })
const stats = computed(() => page.props.stats as Stats)
const feeStats = computed(() => page.props.feeStats as FeeStats)
const activeAcademicYear = computed(() => page.props.activeAcademicYear as { id: number; name: string } | null)
const activeTerm = computed(() => page.props.activeTerm as { id: number; name: string; weekOfTerm: number | null; totalWeeks: number | null } | null)
const studentsByClass = computed(() => page.props.studentsByClass as { name: string; count: number }[])
const setupStatus = computed(() => page.props.setupStatus as SetupStatus)

const formatCurrency = (amount: number) => {
    if (amount >= 1000000) {
        return `MK ${(amount / 1000000).toFixed(2)}M`
    }
    if (amount >= 1000) {
        return `MK ${(amount / 1000).toFixed(1)}K`
    }
    return `MK ${amount.toLocaleString()}`
}

const completedSetupSteps = computed(() =>
    Object.values(setupStatus.value).filter((s) => s.completed).length
)
const totalSetupSteps = computed(() => Object.keys(setupStatus.value).length)
const setupProgress = computed(() =>
    Math.round((completedSetupSteps.value / totalSetupSteps.value) * 100)
)

const setupSteps = [
    {
        key: 'schoolProfile',
        title: 'School Profile',
        description: 'School name, type, location, and contact details',
        icon: Settings,
    },
    {
        key: 'academicCalendar',
        title: 'Academic Calendar',
        description: 'Academic years and terms',
        icon: Calendar,
    },
    {
        key: 'classesAndStreams',
        title: 'Classes & Streams',
        description: 'Define class levels and streams',
        icon: Layers,
    },
    {
        key: 'subjects',
        title: 'Subjects',
        description: 'Configure subjects for each class',
        icon: BookOpen,
    },
    {
        key: 'staffAssignments',
        title: 'Staff Assignments',
        description: 'Assign teachers to subjects and streams',
        icon: Users,
    },
    {
        key: 'gradingScheme',
        title: 'Grading Scheme',
        description: 'Define grading scales and promotion rules',
        icon: Award,
    },
]

const getStepStatus = (key: string) => {
    const status = setupStatus.value[key]
    if (status.completed) return 'completed'
    if (status.inProgress) return 'in-progress'
    return 'pending'
}

const maxStudentsInClass = computed(() =>
    Math.max(...studentsByClass.value.map((c) => c.count), 1)
)
</script>

<template>
    <AppLayout>
        <Head title="Dashboard" />

        <div class="space-y-6">
            <!-- Welcome Banner -->
            <div
                class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-green-600 via-emerald-600 to-teal-600 p-6 lg:p-8 text-white">
                <div class="relative z-10">
                    <h2 class="text-2xl lg:text-3xl font-bold mb-2">
                        Welcome back, {{ school?.name }}!
                    </h2>
                    <p class="text-green-100 mb-4">
                        Here's what's happening at your school today.
                    </p>
                    <div class="flex flex-wrap gap-4">
                        <div v-if="activeAcademicYear" class="bg-white/20 backdrop-blur-sm rounded-lg px-4 py-2">
                            <p class="text-sm text-green-100">Academic Year</p>
                            <p class="font-bold">{{ activeAcademicYear.name }}</p>
                        </div>
                        <div v-if="activeTerm" class="bg-white/20 backdrop-blur-sm rounded-lg px-4 py-2">
                            <p class="text-sm text-green-100">Current Term</p>
                            <p class="font-bold">{{ activeTerm.name }}</p>
                        </div>
                        <div v-if="activeTerm?.weekOfTerm" class="bg-white/20 backdrop-blur-sm rounded-lg px-4 py-2">
                            <p class="text-sm text-green-100">Week</p>
                            <p class="font-bold">{{ activeTerm.weekOfTerm }} of {{ activeTerm.totalWeeks }}</p>
                        </div>
                        <div v-if="!activeAcademicYear" class="bg-white/20 backdrop-blur-sm rounded-lg px-4 py-2">
                            <p class="text-sm text-green-100">Status</p>
                            <p class="font-bold">Setup in Progress</p>
                        </div>
                    </div>
                </div>
                <!-- Decorative circles -->
                <div
                    class="absolute right-0 top-0 w-64 h-64 bg-white/10 rounded-full -translate-y-1/2 translate-x-1/4">
                </div>
                <div class="absolute right-20 bottom-0 w-32 h-32 bg-white/10 rounded-full translate-y-1/2"></div>
            </div>

            <!-- Primary KPI Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Total Students -->
                <div class="rounded-xl bg-gradient-to-br from-blue-500 to-blue-600 p-6 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-blue-100 text-sm">Total Students</p>
                            <p class="text-3xl font-bold mt-1">{{ stats.totalStudents }}</p>
                        </div>
                        <div class="bg-white/20 p-3 rounded-lg">
                            <Users class="w-6 h-6" />
                        </div>
                    </div>
                    <p class="text-sm text-blue-100 mt-2">
                        {{ stats.maleStudents }} Male, {{ stats.femaleStudents }} Female
                    </p>
                </div>

                <!-- Staff Members -->
                <div class="rounded-xl bg-gradient-to-br from-purple-500 to-purple-600 p-6 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-purple-100 text-sm">Staff Members</p>
                            <p class="text-3xl font-bold mt-1">{{ stats.staffCount }}</p>
                        </div>
                        <div class="bg-white/20 p-3 rounded-lg">
                            <GraduationCap class="w-6 h-6" />
                        </div>
                    </div>
                    <p class="text-sm text-purple-100 mt-2">
                        {{ stats.totalStudents > 0 && stats.staffCount > 0
                            ? `1:${Math.round(stats.totalStudents / stats.staffCount)} ratio`
                            : 'Manage your team' }}
                    </p>
                </div>

                <!-- Fee Collection -->
                <div class="rounded-xl bg-gradient-to-br from-green-500 to-green-600 p-6 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-green-100 text-sm">Fee Collected</p>
                            <p class="text-3xl font-bold mt-1">{{ formatCurrency(feeStats.totalCollected) }}</p>
                        </div>
                        <div class="bg-white/20 p-3 rounded-lg">
                            <DollarSign class="w-6 h-6" />
                        </div>
                    </div>
                    <p class="text-sm text-green-100 mt-2">
                        {{ feeStats.collectionRate }}% collection rate
                    </p>
                </div>

                <!-- Pending Fees -->
                <div class="rounded-xl bg-gradient-to-br from-orange-500 to-orange-600 p-6 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-orange-100 text-sm">Pending Fees</p>
                            <p class="text-3xl font-bold mt-1">{{ formatCurrency(feeStats.totalPending) }}</p>
                        </div>
                        <div class="bg-white/20 p-3 rounded-lg">
                            <Clock class="w-6 h-6" />
                        </div>
                    </div>
                    <p class="text-sm text-orange-100 mt-2">
                        {{ formatCurrency(feeStats.totalExpected) }} expected
                    </p>
                </div>
            </div>

            <!-- Secondary Stats Row -->
            <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-6 gap-4">
                <Card class="text-center">
                    <CardContent class="pt-6">
                        <div
                            class="mx-auto h-12 w-12 rounded-full bg-blue-100 dark:bg-blue-900 flex items-center justify-center mb-3">
                            <Layers class="h-6 w-6 text-blue-600 dark:text-blue-400" />
                        </div>
                        <p class="text-2xl font-bold">{{ stats.classesCount }}</p>
                        <p class="text-xs text-muted-foreground">Classes</p>
                    </CardContent>
                </Card>

                <Card class="text-center">
                    <CardContent class="pt-6">
                        <div
                            class="mx-auto h-12 w-12 rounded-full bg-purple-100 dark:bg-purple-900 flex items-center justify-center mb-3">
                            <ClipboardList class="h-6 w-6 text-purple-600 dark:text-purple-400" />
                        </div>
                        <p class="text-2xl font-bold">{{ stats.streamsCount }}</p>
                        <p class="text-xs text-muted-foreground">Streams</p>
                    </CardContent>
                </Card>

                <Card class="text-center">
                    <CardContent class="pt-6">
                        <div
                            class="mx-auto h-12 w-12 rounded-full bg-amber-100 dark:bg-amber-900 flex items-center justify-center mb-3">
                            <BookOpen class="h-6 w-6 text-amber-600 dark:text-amber-400" />
                        </div>
                        <p class="text-2xl font-bold">{{ stats.subjectsCount }}</p>
                        <p class="text-xs text-muted-foreground">Subjects</p>
                    </CardContent>
                </Card>

                <Card class="text-center">
                    <CardContent class="pt-6">
                        <div
                            class="mx-auto h-12 w-12 rounded-full bg-teal-100 dark:bg-teal-900 flex items-center justify-center mb-3">
                            <Calendar class="h-6 w-6 text-teal-600 dark:text-teal-400" />
                        </div>
                        <p class="text-2xl font-bold">{{ stats.termsCount }}</p>
                        <p class="text-xs text-muted-foreground">Terms</p>
                    </CardContent>
                </Card>

                <Card class="text-center">
                    <CardContent class="pt-6">
                        <div
                            class="mx-auto h-12 w-12 rounded-full bg-emerald-100 dark:bg-emerald-900 flex items-center justify-center mb-3">
                            <UserPlus class="h-6 w-6 text-emerald-600 dark:text-emerald-400" />
                        </div>
                        <p class="text-2xl font-bold">{{ stats.openAdmissionCycles }}</p>
                        <p class="text-xs text-muted-foreground">Open Admissions</p>
                    </CardContent>
                </Card>

                <Card class="text-center">
                    <CardContent class="pt-6">
                        <div
                            class="mx-auto h-12 w-12 rounded-full bg-red-100 dark:bg-red-900 flex items-center justify-center mb-3">
                            <FileText class="h-6 w-6 text-red-600 dark:text-red-400" />
                        </div>
                        <p class="text-2xl font-bold">{{ stats.pendingApplicants }}</p>
                        <p class="text-xs text-muted-foreground">Pending Apps</p>
                    </CardContent>
                </Card>
            </div>

            <!-- Main Content Grid -->
            <div class="grid gap-6 lg:grid-cols-3">
                <!-- Left Column (2/3) -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Setup Progress (only show if not complete) -->
                    <Card v-if="setupProgress < 100">
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
                                <div class="text-right">
                                    <p class="text-2xl font-bold text-green-600">{{ setupProgress }}%</p>
                                    <p class="text-xs text-muted-foreground">
                                        {{ completedSetupSteps }}/{{ totalSetupSteps }} complete
                                    </p>
                                </div>
                            </div>
                            <Progress :model-value="setupProgress" class="mt-4" />
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
                                    <!-- Status Icon -->
                                    <div :class="[
                                        'flex h-10 w-10 shrink-0 items-center justify-center rounded-full',
                                        getStepStatus(step.key) === 'completed'
                                            ? 'bg-green-100 dark:bg-green-900'
                                            : getStepStatus(step.key) === 'in-progress'
                                                ? 'bg-blue-100 dark:bg-blue-900'
                                                : 'bg-gray-100 dark:bg-gray-800',
                                    ]">
                                        <Check v-if="getStepStatus(step.key) === 'completed'"
                                            class="h-5 w-5 text-green-600" />
                                        <Clock v-else-if="getStepStatus(step.key) === 'in-progress'"
                                            class="h-5 w-5 text-blue-600" />
                                        <component v-else :is="step.icon" class="h-5 w-5 text-gray-400" />
                                    </div>

                                    <!-- Content -->
                                    <div class="flex-1 min-w-0">
                                        <p class="font-medium text-sm">{{ step.title }}</p>
                                        <p class="text-xs text-muted-foreground">{{ step.description }}</p>
                                    </div>

                                    <!-- Arrow -->
                                    <ArrowRight v-if="setupStatus[step.key]?.href"
                                        class="h-4 w-4 text-muted-foreground" />
                                </Link>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Student Distribution by Class -->
                    <Card v-if="studentsByClass.length > 0">
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2">
                                <Users class="h-5 w-5 text-blue-600" />
                                Student Distribution by Class
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="space-y-3">
                                <div v-for="classData in studentsByClass" :key="classData.name"
                                    class="flex items-center gap-3">
                                    <span class="w-24 text-sm text-muted-foreground truncate">{{ classData.name
                                        }}</span>
                                    <div class="flex-1 bg-gray-100 dark:bg-gray-800 rounded-full h-6 overflow-hidden">
                                        <div class="h-full rounded-full bg-gradient-to-r from-blue-500 to-blue-600 transition-all duration-500"
                                            :style="{ width: `${(classData.count / maxStudentsInClass) * 100}%` }" />
                                    </div>
                                    <span class="w-10 text-sm font-medium text-right">{{ classData.count }}</span>
                                </div>

                                <div v-if="stats.totalStudents === 0"
                                    class="text-center py-8 text-muted-foreground">
                                    <Users class="h-12 w-12 mx-auto mb-3 opacity-50" />
                                    <p>No students enrolled yet</p>
                                    <p class="text-sm">Students will appear here once enrolled</p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- School Overview (when setup is complete) -->
                    <Card v-if="setupProgress === 100">
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2">
                                <Award class="h-5 w-5 text-green-600" />
                                School Overview
                            </CardTitle>
                            <CardDescription>Your school is fully configured and ready</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div class="grid grid-cols-2 gap-4">
                                <div class="p-4 rounded-lg bg-green-50 dark:bg-green-950">
                                    <p class="text-xs font-medium text-muted-foreground">Active Year</p>
                                    <p class="text-2xl font-bold mt-1">
                                        {{ activeAcademicYear?.name || 'Not Set' }}
                                    </p>
                                </div>
                                <div class="p-4 rounded-lg bg-blue-50 dark:bg-blue-950">
                                    <p class="text-xs font-medium text-muted-foreground">Current Term</p>
                                    <p class="text-2xl font-bold mt-1">
                                        {{ activeTerm?.name || 'Not Set' }}
                                    </p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Right Column (1/3) -->
                <div class="space-y-6">
                    <!-- Quick Actions -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="text-base">Quick Actions</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-2">
                            <Link :href="admin.default.staff.index.url()" class="block">
                                <Button variant="outline" class="w-full justify-start gap-3">
                                    <Users class="h-4 w-4" />
                                    Manage Staff
                                </Button>
                            </Link>
                            <Link :href="admin.default.settings.classes.index.url()" class="block">
                                <Button variant="outline" class="w-full justify-start gap-3">
                                    <Layers class="h-4 w-4" />
                                    Manage Classes
                                </Button>
                            </Link>
                            <Link :href="admin.default.settings.subjects.index.url()" class="block">
                                <Button variant="outline" class="w-full justify-start gap-3">
                                    <BookOpen class="h-4 w-4" />
                                    Manage Subjects
                                </Button>
                            </Link>
                            <Link :href="admin.default.settings.admissionCycles.index.url()" class="block">
                                <Button variant="outline" class="w-full justify-start gap-3">
                                    <UserPlus class="h-4 w-4" />
                                    Admissions
                                </Button>
                            </Link>
                            <Link :href="admin.default.settings.schoolProfile.show.url()" class="block">
                                <Button variant="outline" class="w-full justify-start gap-3">
                                    <Settings class="h-4 w-4" />
                                    School Profile
                                </Button>
                            </Link>
                        </CardContent>
                    </Card>

                    <!-- Alerts & Notifications -->
                    <Card v-if="stats.pendingApplicants > 0 || feeStats.totalPending > 0">
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2 text-base">
                                <Bell class="h-4 w-4 text-amber-600" />
                                Attention Needed
                            </CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-3">
                            <div v-if="stats.pendingApplicants > 0"
                                class="flex items-center gap-3 p-3 bg-amber-50 dark:bg-amber-950 rounded-lg">
                                <div
                                    class="h-8 w-8 rounded-full bg-amber-100 dark:bg-amber-900 flex items-center justify-center shrink-0">
                                    <FileText class="h-4 w-4 text-amber-600" />
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm font-medium">{{ stats.pendingApplicants }} pending applications
                                    </p>
                                    <p class="text-xs text-muted-foreground">Review and process</p>
                                </div>
                            </div>

                            <div v-if="feeStats.totalPending > 0"
                                class="flex items-center gap-3 p-3 bg-orange-50 dark:bg-orange-950 rounded-lg">
                                <div
                                    class="h-8 w-8 rounded-full bg-orange-100 dark:bg-orange-900 flex items-center justify-center shrink-0">
                                    <DollarSign class="h-4 w-4 text-orange-600" />
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm font-medium">{{ formatCurrency(feeStats.totalPending) }}
                                        outstanding fees</p>
                                    <p class="text-xs text-muted-foreground">{{ feeStats.collectionRate }}% collected
                                    </p>
                                </div>
                            </div>

                            <div v-if="setupProgress < 100"
                                class="flex items-center gap-3 p-3 bg-blue-50 dark:bg-blue-950 rounded-lg">
                                <div
                                    class="h-8 w-8 rounded-full bg-blue-100 dark:bg-blue-900 flex items-center justify-center shrink-0">
                                    <Settings class="h-4 w-4 text-blue-600" />
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm font-medium">Setup incomplete</p>
                                    <p class="text-xs text-muted-foreground">{{ setupProgress }}% complete</p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Term Progress (if active term) -->
                    <Card v-if="activeTerm?.weekOfTerm">
                        <CardHeader>
                            <CardTitle class="text-base">Term Progress</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="space-y-3">
                                <div class="flex justify-between text-sm">
                                    <span class="text-muted-foreground">Week {{ activeTerm.weekOfTerm }}</span>
                                    <span class="font-medium">of {{ activeTerm.totalWeeks }}</span>
                                </div>
                                <Progress
                                    :model-value="(activeTerm.weekOfTerm / (activeTerm.totalWeeks || 1)) * 100" />
                                <p class="text-xs text-muted-foreground text-center">
                                    {{ activeTerm.totalWeeks! - activeTerm.weekOfTerm }} weeks remaining
                                </p>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
