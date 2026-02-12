<script setup lang="ts">
import Heading from '@/components/Heading.vue'
import { Badge } from '@/components/ui/badge'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table'
import AppLayout from '@/layouts/AppLayout.vue'
import { dashboard as adminDashboard } from '@/routes/admin'
import { Head } from '@inertiajs/vue3'
import { GraduationCap, UserCheck, Users } from 'lucide-vue-next'

interface ClassDistribution {
    name: string
    count: number
}

interface RecentEnrollment {
    id: number
    student_name: string
    class: string | null
    stream: string | null
    enrollment_date: string | null
    is_active: boolean
}

const props = defineProps<{
    activeYear: { id: number; name: string } | null
    stats: {
        totalStudents: number
        activeEnrollments: number
    }
    studentsByClass: ClassDistribution[]
    recentEnrollments: RecentEnrollment[]
}>()
</script>

<template>
    <AppLayout
        :breadcrumbs="[
            { title: 'Admin', href: '' },
            { title: 'Dashboard', href: adminDashboard().url },
            { title: 'Enrollment', href: '#' },
        ]">
        <Head title="Enrollment" />

        <div class="flex flex-col space-y-6 max-w-6xl">
            <Heading
                title="Enrollment Overview"
                description="Student enrollment statistics and recent activity"
            />

            <!-- KPI Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between pb-2">
                        <CardTitle class="text-sm font-medium text-muted-foreground">Total Students</CardTitle>
                        <Users class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.totalStudents }}</div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between pb-2">
                        <CardTitle class="text-sm font-medium text-muted-foreground">Active Enrollments</CardTitle>
                        <UserCheck class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.activeEnrollments }}</div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between pb-2">
                        <CardTitle class="text-sm font-medium text-muted-foreground">Academic Year</CardTitle>
                        <GraduationCap class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ activeYear?.name ?? 'Not set' }}</div>
                    </CardContent>
                </Card>
            </div>

            <!-- Students by Class -->
            <Card v-if="studentsByClass.length > 0">
                <CardHeader>
                    <CardTitle>Students by Class</CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="space-y-3">
                        <div v-for="item in studentsByClass" :key="item.name" class="flex items-center gap-3">
                            <span class="text-sm font-medium w-32 shrink-0">{{ item.name }}</span>
                            <div class="flex-1 h-6 bg-muted rounded-full overflow-hidden">
                                <div
                                    class="h-full bg-primary rounded-full transition-all"
                                    :style="{
                                        width: stats.totalStudents > 0
                                            ? `${(item.count / stats.totalStudents) * 100}%`
                                            : '0%',
                                    }"
                                />
                            </div>
                            <span class="text-sm text-muted-foreground w-10 text-right">{{ item.count }}</span>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Recent Enrollments -->
            <div class="rounded-lg border bg-background py-2">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Student</TableHead>
                            <TableHead>Class</TableHead>
                            <TableHead>Stream</TableHead>
                            <TableHead>Enrolled</TableHead>
                            <TableHead>Status</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-if="recentEnrollments.length === 0">
                            <TableCell colspan="5" class="text-center text-muted-foreground py-8">
                                No enrollments recorded yet
                            </TableCell>
                        </TableRow>
                        <TableRow v-for="enrollment in recentEnrollments" :key="enrollment.id">
                            <TableCell class="font-medium">{{ enrollment.student_name }}</TableCell>
                            <TableCell>{{ enrollment.class ?? '—' }}</TableCell>
                            <TableCell>{{ enrollment.stream ?? '—' }}</TableCell>
                            <TableCell class="text-muted-foreground">
                                {{ enrollment.enrollment_date
                                    ? new Date(enrollment.enrollment_date).toLocaleDateString()
                                    : '—' }}
                            </TableCell>
                            <TableCell>
                                <Badge :variant="enrollment.is_active ? 'default' : 'secondary'">
                                    {{ enrollment.is_active ? 'Active' : 'Inactive' }}
                                </Badge>
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>
        </div>
    </AppLayout>
</template>
