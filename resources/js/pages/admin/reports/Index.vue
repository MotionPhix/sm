<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import Heading from '@/components/Heading.vue'
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card'
import { dashboard as adminDashboard } from '@/routes/admin'
import { Users, DollarSign, ClipboardCheck, TrendingUp, TrendingDown, Percent } from 'lucide-vue-next'

const props = defineProps<{
    stats: {
        totalStudents: number
        totalFeeExpected: number
        totalFeeCollected: number
        totalFeePending: number
        attendanceRate: number
    }
}>()

const formatCurrency = (amount: number): string => {
    return 'MK ' + amount.toLocaleString('en-ZA', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
}

const collectionRate = props.stats.totalFeeExpected > 0
    ? Math.round((props.stats.totalFeeCollected / props.stats.totalFeeExpected) * 100)
    : 0
</script>

<template>
    <AppLayout
        :breadcrumbs="[
            { title: 'Admin', href: '' },
            { title: 'Dashboard', href: adminDashboard().url },
            { title: 'Reports', href: '#' },
        ]">
        <Head title="Reports" />

        <div class="flex flex-col space-y-6 max-w-6xl">
            <Heading
                title="Reports Overview"
                description="Key analytics and reports across your school"
            />

            <!-- Summary KPIs -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
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
                        <CardTitle class="text-sm font-medium text-muted-foreground">Fee Collected</CardTitle>
                        <TrendingUp class="h-4 w-4 text-green-500" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ formatCurrency(stats.totalFeeCollected) }}</div>
                        <p class="text-xs text-muted-foreground mt-1">{{ collectionRate }}% collection rate</p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between pb-2">
                        <CardTitle class="text-sm font-medium text-muted-foreground">Outstanding Fees</CardTitle>
                        <TrendingDown class="h-4 w-4 text-red-500" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ formatCurrency(stats.totalFeePending) }}</div>
                        <p class="text-xs text-muted-foreground mt-1">of {{ formatCurrency(stats.totalFeeExpected) }} expected</p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between pb-2">
                        <CardTitle class="text-sm font-medium text-muted-foreground">Attendance Rate</CardTitle>
                        <Percent class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.attendanceRate }}%</div>
                    </CardContent>
                </Card>
            </div>

            <!-- Report Categories -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <Card class="border-dashed">
                    <CardHeader>
                        <div class="flex items-center gap-2">
                            <Users class="h-5 w-5 text-muted-foreground" />
                            <CardTitle>Enrollment</CardTitle>
                        </div>
                        <CardDescription>Student enrollment trends, admissions, transfers, and withdrawals</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <p class="text-xs text-muted-foreground">Detailed enrollment reports coming soon</p>
                    </CardContent>
                </Card>

                <Card class="border-dashed">
                    <CardHeader>
                        <div class="flex items-center gap-2">
                            <ClipboardCheck class="h-5 w-5 text-muted-foreground" />
                            <CardTitle>Attendance</CardTitle>
                        </div>
                        <CardDescription>Daily attendance summaries, chronic absenteeism, and class trends</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <p class="text-xs text-muted-foreground">Detailed attendance reports coming soon</p>
                    </CardContent>
                </Card>

                <Card class="border-dashed">
                    <CardHeader>
                        <div class="flex items-center gap-2">
                            <DollarSign class="h-5 w-5 text-muted-foreground" />
                            <CardTitle>Finance</CardTitle>
                        </div>
                        <CardDescription>Income tracking, outstanding balances, and collection summaries</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <p class="text-xs text-muted-foreground">Detailed finance reports coming soon</p>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
