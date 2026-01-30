<script setup lang="ts">
import { Head, router, usePage } from '@inertiajs/vue3'
import { ref, computed } from 'vue'
import AppLayout from '@/layouts/AppLayout.vue'
import Heading from '@/components/Heading.vue'
import { Button } from '@/components/ui/button'
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table'
import { Input } from '@/components/ui/input'
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select'
import { Badge } from '@/components/ui/badge'
import { create as createRoute, show as showRoute } from '@/routes/registrar/students'
import { form as enrollFormRoute } from '@/routes/registrar/students/enroll'

interface Student {
    id: number
    admission_number: string
    first_name: string
    middle_name?: string
    last_name: string
    gender: string
    date_of_birth: string
    admission_date: string
    active_enrollment?: {
        classroom?: {
            class?: { name: string }
            stream?: { name: string }
            display_name: string
        }
    }
}

interface SchoolClass {
    id: number
    name: string
}

interface Stream {
    id: number
    name: string
}

const props = defineProps<{
    students: {
        data: Student[]
        total: number
        per_page: number
        current_page: number
        last_page: number
    }
    classes: SchoolClass[]
    streams: Stream[]
    filters: {
        search?: string
        class_id?: string
        stream_id?: string
        status?: string
    }
}>()

const page = usePage()
const flash = computed(() => page.props.flash as { success?: string; error?: string })

const breadcrumbs = [
    { title: 'Dashboard', href: '/registrar/dashboard' },
    { title: 'Students', href: '#' },
]

const search = ref(props.filters.search || '')
const selectedClass = ref(props.filters.class_id || 'all')
const selectedStream = ref(props.filters.stream_id || 'all')
const selectedStatus = ref(props.filters.status || 'all')

function updateFilters() {
    router.get('/registrar/students', {
        search: search.value || undefined,
        class_id: selectedClass.value !== 'all' ? selectedClass.value : undefined,
        stream_id: selectedStream.value !== 'all' ? selectedStream.value : undefined,
        status: selectedStatus.value !== 'all' ? selectedStatus.value : undefined,
    }, {
        preserveState: true,
        replace: true,
    })
}

function clearFilters() {
    search.value = ''
    selectedClass.value = 'all'
    selectedStream.value = 'all'
    selectedStatus.value = 'all'
    updateFilters()
}

function getStudentName(student: Student): string {
    const parts = [student.first_name]
    if (student.middle_name) parts.push(student.middle_name)
    parts.push(student.last_name)
    return parts.join(' ')
}

function getGenderLabel(gender: string): string {
    return gender === 'male' ? 'Male' : 'Female'
}

function formatDate(dateString: string): string {
    return new Date(dateString).toLocaleDateString('en-ZA', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    })
}

const hasActiveFilters = computed(() => {
    return search.value || selectedClass.value !== 'all' ||
        selectedStream.value !== 'all' || selectedStatus.value !== 'all'
})
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Students" />

        <article class="p-5">
            <Heading title="Students" description="Manage enrolled students" />

            <div class="mt-6 flex flex-col gap-4">
                <!-- Filters -->
                <div class="flex flex-wrap items-center gap-3">
                    <Input
                        v-model="search"
                        placeholder="Search by name or admission number..."
                        class="max-w-xs"
                        @input="updateFilters"
                    />

                    <Select v-model="selectedClass" @update:model-value="updateFilters">
                        <SelectTrigger class="w-[150px]">
                            <SelectValue placeholder="All Classes" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">All Classes</SelectItem>
                            <SelectItem
                                v-for="cls in classes"
                                :key="cls.id"
                                :value="String(cls.id)">
                                {{ cls.name }}
                            </SelectItem>
                        </SelectContent>
                    </Select>

                    <Select v-model="selectedStream" @update:model-value="updateFilters">
                        <SelectTrigger class="w-[150px]">
                            <SelectValue placeholder="All Streams" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">All Streams</SelectItem>
                            <SelectItem
                                v-for="stream in streams"
                                :key="stream.id"
                                :value="String(stream.id)">
                                {{ stream.name }}
                            </SelectItem>
                        </SelectContent>
                    </Select>

                    <Select v-model="selectedStatus" @update:model-value="updateFilters">
                        <SelectTrigger class="w-[150px]">
                            <SelectValue placeholder="All Status" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">All Status</SelectItem>
                            <SelectItem value="active">Active</SelectItem>
                            <SelectItem value="inactive">Inactive</SelectItem>
                        </SelectContent>
                    </Select>

                    <Button
                        v-if="hasActiveFilters"
                        variant="outline"
                        size="sm"
                        @click="clearFilters">
                        Clear filters
                    </Button>
                </div>

                <!-- Actions -->
                <div class="flex justify-end">
                    <Button :href="createRoute().url">
                        Add Student
                    </Button>
                </div>

                <!-- Students Table -->
                <div class="rounded-lg border">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Admission #</TableHead>
                                <TableHead>Name</TableHead>
                                <TableHead>Gender</TableHead>
                                <TableHead>Date of Birth</TableHead>
                                <TableHead>Current Class</TableHead>
                                <TableHead>Admission Date</TableHead>
                                <TableHead class="text-right">Actions</TableHead>
                            </TableRow>
                        </TableHeader>

                        <TableBody>
                            <TableRow
                                v-for="student in students.data"
                                :key="student.id">
                                <TableCell class="font-mono text-sm">
                                    {{ student.admission_number }}
                                </TableCell>

                                <TableCell>
                                    <div class="font-medium">
                                        {{ getStudentName(student) }}
                                    </div>
                                </TableCell>

                                <TableCell>
                                    <Badge variant="outline">
                                        {{ getGenderLabel(student.gender) }}
                                    </Badge>
                                </TableCell>

                                <TableCell>
                                    {{ formatDate(student.date_of_birth) }}
                                </TableCell>

                                <TableCell>
                                    <span v-if="student.active_enrollment?.classroom">
                                        {{ student.active_enrollment.classroom.display_name }}
                                    </span>
                                    <span v-else class="text-muted-foreground">
                                        Not enrolled
                                    </span>
                                </TableCell>

                                <TableCell>
                                    {{ formatDate(student.admission_date) }}
                                </TableCell>

                                <TableCell class="text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <Button
                                            variant="outline"
                                            size="sm"
                                            :href="showRoute(student).url">
                                            View
                                        </Button>
                                        <Button
                                            variant="outline"
                                            size="sm"
                                            :href="enrollFormRoute(student).url">
                                            Enroll
                                        </Button>
                                    </div>
                                </TableCell>
                            </TableRow>

                            <TableRow v-if="students.data.length === 0">
                                <TableCell colspan="7" class="p-6 text-center text-muted-foreground">
                                    No students found
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>

                <!-- Pagination -->
                <div v-if="students.last_page > 1" class="flex items-center justify-between">
                    <p class="text-sm text-muted-foreground">
                        Showing {{ students.data.length }} of {{ students.total }} students
                    </p>
                    <div class="flex gap-2">
                        <Button
                            variant="outline"
                            size="sm"
                            :disabled="students.current_page === 1"
                            @click="router.get('/registrar/students', { ...filters, page: students.current_page - 1 })">
                            Previous
                        </Button>
                        <Button
                            variant="outline"
                            size="sm"
                            :disabled="students.current_page === students.last_page"
                            @click="router.get('/registrar/students', { ...filters, page: students.current_page + 1 })">
                            Next
                        </Button>
                    </div>
                </div>
            </div>
        </article>
    </AppLayout>
</template>
