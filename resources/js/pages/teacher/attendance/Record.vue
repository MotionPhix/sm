<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3'
import { reactive, onMounted, ref, computed } from 'vue'
import { Button } from '@/components/ui/button'
import AppLayout from '@/layouts/AppLayout.vue'
import Heading from '@/components/Heading.vue'
import type { BreadcrumbItemType } from '@/types'
import { Calendar, GraduationCap, Users, CheckCircle } from 'lucide-vue-next'

interface Student {
    id: number
    first_name: string
    last_name: string
    middle_name?: string
    admission_number: string
}

interface ExistingRecord {
    id: number
    student_id: number
    status: AttendanceStatus
    remarks: string | null
}

type AttendanceStatus = 'present' | 'absent' | 'late' | 'excused'

interface AttendanceEntry {
    student_id: number
    status: AttendanceStatus
    remarks: string
}

const props = defineProps<{
    date: string
    classId: number
    className: string
    streamId: number | null
    streamName: string | null
    students: Student[]
    existing: Record<number, ExistingRecord>
}>()

const breadcrumbs: BreadcrumbItemType[] = [
    { title: 'Attendance', href: '/teacher/attendance' },
    { title: 'Record', href: '#' },
]

const attendanceRecords = reactive<Record<number, AttendanceEntry>>({})
const submitting = ref(false)
const successMessage = ref('')
const errorMessage = ref('')

const statusOptions: { value: AttendanceStatus; label: string; color: string }[] = [
    { value: 'present', label: 'Present', color: 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300' },
    { value: 'absent', label: 'Absent', color: 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300' },
    { value: 'late', label: 'Late', color: 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300' },
    { value: 'excused', label: 'Excused', color: 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300' },
]

const initializeRecords = () => {
    props.students.forEach((student) => {
        const existingRecord = props.existing[student.id]
        attendanceRecords[student.id] = {
            student_id: student.id,
            status: existingRecord?.status ?? 'present',
            remarks: existingRecord?.remarks ?? '',
        }
    })
}

const markAllAs = (status: AttendanceStatus) => {
    Object.keys(attendanceRecords).forEach((id) => {
        attendanceRecords[Number(id)].status = status
    })
}

const attendanceSummary = computed(() => {
    const summary = { present: 0, absent: 0, late: 0, excused: 0 }
    Object.values(attendanceRecords).forEach((record) => {
        summary[record.status]++
    })
    return summary
})

const saveAttendance = () => {
    submitting.value = true
    successMessage.value = ''
    errorMessage.value = ''

    const recordsArray = Object.values(attendanceRecords)

    router.post(
        '/teacher/attendance',
        {
            date: props.date,
            class_id: props.classId,
            stream_id: props.streamId,
            records: recordsArray,
        },
        {
            preserveScroll: true,
            onSuccess: () => {
                successMessage.value = 'Attendance saved successfully!'
                submitting.value = false
            },
            onError: (errors) => {
                errorMessage.value = Object.values(errors).flat().join(', ') || 'Failed to save attendance'
                submitting.value = false
            },
            onFinish: () => {
                submitting.value = false
            },
        }
    )
}

onMounted(initializeRecords)
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Record Attendance" />

        <div class="space-y-6">
            <div>
                <Heading
                    title="Record Attendance"
                    :description="`Recording attendance for ${className}${streamName ? ' - ' + streamName : ''}`"
                />
                <div class="mt-3 flex flex-wrap items-center gap-4 text-sm text-muted-foreground">
                    <div class="flex items-center gap-1.5">
                        <Calendar class="h-4 w-4" />
                        <span>{{ date }}</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <GraduationCap class="h-4 w-4" />
                        <span>{{ className }}</span>
                    </div>
                    <div v-if="streamName" class="flex items-center gap-1.5">
                        <Users class="h-4 w-4" />
                        <span>{{ streamName }}</span>
                    </div>
                </div>
            </div>

            <!-- Success/Error Messages -->
            <div v-if="successMessage" class="flex items-center gap-2 rounded-lg border border-green-200 bg-green-50 p-4 text-green-700 dark:border-green-800 dark:bg-green-900/30 dark:text-green-300">
                <CheckCircle class="h-5 w-5" />
                {{ successMessage }}
            </div>
            <div v-if="errorMessage" class="rounded-lg border border-destructive/50 bg-destructive/10 p-4 text-destructive">
                {{ errorMessage }}
            </div>

            <!-- Quick Actions -->
            <div class="flex flex-wrap items-center justify-between gap-4 rounded-lg border bg-muted/50 p-4">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="text-sm font-medium">Quick mark all:</span>
                    <button
                        v-for="option in statusOptions"
                        :key="option.value"
                        type="button"
                        class="rounded-md px-3 py-1 text-xs font-medium transition-colors hover:opacity-80"
                        :class="option.color"
                        @click="markAllAs(option.value)"
                    >
                        {{ option.label }}
                    </button>
                </div>
                <div class="flex items-center gap-4 text-sm">
                    <span class="text-green-600 dark:text-green-400">Present: {{ attendanceSummary.present }}</span>
                    <span class="text-red-600 dark:text-red-400">Absent: {{ attendanceSummary.absent }}</span>
                    <span class="text-amber-600 dark:text-amber-400">Late: {{ attendanceSummary.late }}</span>
                    <span class="text-blue-600 dark:text-blue-400">Excused: {{ attendanceSummary.excused }}</span>
                </div>
            </div>

            <!-- Attendance Form -->
            <form @submit.prevent="saveAttendance">
                <div class="overflow-hidden rounded-lg border bg-card">
                    <div class="border-b px-6 py-4">
                        <h2 class="text-lg font-semibold">
                            Student Attendance
                            <span class="ml-2 text-sm font-normal text-muted-foreground">
                                ({{ students.length }} students)
                            </span>
                        </h2>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full">
                            <thead>
                                <tr class="border-b bg-muted/50">
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">
                                        #
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">
                                        Student
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">
                                        Status
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">
                                        Remarks
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                <tr v-for="(student, index) in students" :key="student.id" class="hover:bg-muted/30">
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-muted-foreground">
                                        {{ index + 1 }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4">
                                        <div class="flex items-center">
                                            <div class="flex h-8 w-8 items-center justify-center rounded-full bg-muted text-sm font-medium">
                                                {{ student.first_name.charAt(0) }}{{ student.last_name.charAt(0) }}
                                            </div>
                                            <div class="ml-3">
                                                <div class="text-sm font-medium">
                                                    {{ student.last_name }}, {{ student.first_name }}
                                                </div>
                                                <div class="text-xs text-muted-foreground">
                                                    {{ student.admission_number }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4">
                                        <div class="flex gap-1">
                                            <button
                                                v-for="option in statusOptions"
                                                :key="option.value"
                                                type="button"
                                                class="rounded-md px-2.5 py-1.5 text-xs font-medium transition-all"
                                                :class="[
                                                    attendanceRecords[student.id]?.status === option.value
                                                        ? option.color + ' ring-2 ring-offset-1 ring-ring'
                                                        : 'bg-muted text-muted-foreground hover:bg-muted/80',
                                                ]"
                                                @click="attendanceRecords[student.id].status = option.value"
                                            >
                                                {{ option.label }}
                                            </button>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <input
                                            v-model="attendanceRecords[student.id].remarks"
                                            type="text"
                                            placeholder="Optional remarks"
                                            class="w-full rounded-md border border-input bg-background px-3 py-1.5 text-sm ring-offset-background focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2"
                                        />
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Footer Actions -->
                    <div class="flex items-center justify-between border-t bg-muted/30 px-6 py-4">
                        <Button variant="outline" type="button" @click="router.visit('/teacher/attendance')">
                            Cancel
                        </Button>
                        <Button type="submit" :disabled="submitting">
                            <span v-if="submitting">Saving...</span>
                            <span v-else>Save Attendance</span>
                        </Button>
                    </div>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
