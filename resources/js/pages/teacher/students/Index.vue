<script setup lang="ts">
import Heading from '@/components/Heading.vue'
import { Badge } from '@/components/ui/badge'
import { Empty, EmptyDescription, EmptyHeader, EmptyMedia } from '@/components/ui/empty'
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table'
import AppLayout from '@/layouts/AppLayout.vue'
import { dashboard as teacherDashboard } from '@/routes/teacher'
import { Head, Link } from '@inertiajs/vue3'
import { Users } from 'lucide-vue-next'

interface Enrollment {
    id: number
    classroom: {
        school_class: { name: string } | null
        stream: { name: string } | null
    } | null
}

interface Student {
    id: number
    first_name: string
    last_name: string
    admission_number: string | null
    gender: string | null
    enrollments: Enrollment[]
}

interface PaginatedStudents {
    data: Student[]
    links: { url: string | null; label: string; active: boolean }[]
    current_page: number
    last_page: number
    total: number
}

const props = defineProps<{
    students: PaginatedStudents | Student[]
    academicYear: { id: number; name: string } | null
    totalStudents: number
}>()

const studentList = Array.isArray(props.students) ? props.students : props.students.data
const pagination = Array.isArray(props.students) ? null : props.students
</script>

<template>
    <AppLayout
        :breadcrumbs="[
            { title: 'Teacher', href: '' },
            { title: 'Dashboard', href: teacherDashboard().url },
            { title: 'My Students', href: '#' },
        ]">
        <Head title="My Students" />

        <template #act>
            <div class="flex items-center gap-4">
                <Users class="h-4 w-4" />
                <span>
                    {{ totalStudents }} student{{ totalStudents !== 1 ? 's' : '' }}
                </span>
            </div>
        </template>

        <div class="flex flex-col space-y-6 max-w-6xl">
            <Heading
                title="My Students"
                :description="`Students enrolled in your assigned classes${academicYear ? ` for ${academicYear.name}` : ''}`"
            />

            <div class="rounded-lg border bg-background py-2">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Admission #</TableHead>
                            <TableHead>Name</TableHead>
                            <TableHead>Gender</TableHead>
                            <TableHead>Class</TableHead>
                            <TableHead>Stream</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-if="studentList.length === 0">
                            <TableCell colspan="5" class="py-8">
                                <Empty>
                                    <EmptyHeader>
                                        <EmptyMedia>
                                            <Users class="size-12 text-muted-foreground/50" />
                                        </EmptyMedia>

                                        <EmptyDescription>
                                            No students found in your assigned classes
                                        </EmptyDescription>
                                    </EmptyHeader>
                                </Empty>
                            </TableCell>
                        </TableRow>

                        <TableRow v-for="student in studentList" :key="student.id">
                            <TableCell class="font-mono text-sm">{{ student.admission_number ?? '—' }}</TableCell>
                            <TableCell class="font-medium">{{ student.last_name }}, {{ student.first_name }}</TableCell>
                            <TableCell>
                                <Badge v-if="student.gender" variant="outline">
                                    {{ student.gender }}
                                </Badge>
                                <span v-else class="text-muted-foreground">—</span>
                            </TableCell>
                            <TableCell>
                                {{ student.enrollments?.[0]?.classroom?.school_class?.name ?? '—' }}
                            </TableCell>
                            <TableCell>
                                {{ student.enrollments?.[0]?.classroom?.stream?.name ?? '—' }}
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>

            <!-- Pagination -->
            <div v-if="pagination && pagination.last_page > 1" class="flex items-center justify-center gap-1">
                <template v-for="link in pagination.links" :key="link.label">
                    <Link
                        v-if="link.url"
                        :href="link.url"
                        class="rounded-md px-3 py-1.5 text-sm transition-colors"
                        :class="link.active
                            ? 'bg-primary text-primary-foreground'
                            : 'hover:bg-muted'"
                        v-html="link.label"
                    />
                    <span
                        v-else
                        class="rounded-md px-3 py-1.5 text-sm text-muted-foreground"
                        v-html="link.label"
                    />
                </template>
            </div>
        </div>
    </AppLayout>
</template>
