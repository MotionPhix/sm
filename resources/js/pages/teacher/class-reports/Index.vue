<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import Heading from '@/components/Heading.vue'
import { Badge } from '@/components/ui/badge'
import { Button } from '@/components/ui/button'
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select'
import { dashboard as teacherDashboard } from '@/routes/teacher'
import { show as classReportShow } from '@/routes/teacher/class-reports'
import { FileText, BookOpen, ArrowRight } from 'lucide-vue-next'
import { Empty, EmptyDescription, EmptyHeader, EmptyMedia, EmptyTitle } from '@/components/ui/empty'
import { ref } from 'vue'

interface SchoolClass {
    id: number
    name: string
}

interface Stream {
    id: number
    name: string
}

interface Subject {
    id: number
    name: string
    code: string
}

interface Classroom {
    id: number
    school_class: SchoolClass | null
    stream: Stream | null
}

interface ClassroomGroup {
    classroom_id: number
    classroom: Classroom
    subjects: Subject[]
}

interface Term {
    id: number
    name: string
}

const props = defineProps<{
    academicYear: { id: number; name: string } | null
    classrooms: ClassroomGroup[]
    terms: Term[]
}>()

const selectedTermId = ref<string>(props.terms.length > 0 ? String(props.terms[0].id) : '')

const classroomName = (classroom: Classroom): string => {
    const base = classroom.school_class?.name ?? 'Unknown'
    return classroom.stream ? `${base} - ${classroom.stream.name}` : base
}
</script>

<template>
    <AppLayout
        :breadcrumbs="[
            { title: 'Teacher', href: '' },
            { title: 'Dashboard', href: teacherDashboard().url },
            { title: 'Class Reports', href: '#' },
        ]">
        <Head title="Class Reports" />

        <div class="space-y-6">
            <div class="flex items-center justify-between">
                <Heading
                    title="Class Reports"
                    :description="`View and generate reports for your assigned classes${academicYear ? ` — ${academicYear.name}` : ''}`"
                />

                <div v-if="terms.length > 0" class="w-48">
                    <Select v-model="selectedTermId">
                        <SelectTrigger>
                            <SelectValue placeholder="Select Term" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem v-for="term in terms" :key="term.id" :value="String(term.id)">
                                {{ term.name }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                </div>
            </div>

            <!-- No Academic Year -->
            <div v-if="!academicYear" class="rounded-lg border bg-card p-8 text-center">
                <p class="text-muted-foreground">No active academic year. Please contact your administrator.</p>
            </div>

            <!-- Classrooms Grid -->
            <div v-else-if="classrooms.length > 0" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <div
                    v-for="group in classrooms"
                    :key="group.classroom_id"
                    class="rounded-lg border bg-card transition-shadow hover:shadow-md">
                    <div class="p-6">
                        <div class="flex items-start justify-between">
                            <div class="flex items-center gap-3">
                                <div class="rounded-lg bg-primary/10 p-3">
                                    <BookOpen class="h-5 w-5 text-primary" />
                                </div>
                                <div>
                                    <h3 class="font-semibold">{{ classroomName(group.classroom) }}</h3>
                                    <p class="text-sm text-muted-foreground">
                                        {{ group.subjects.length }} {{ group.subjects.length === 1 ? 'subject' : 'subjects' }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="mt-3 flex flex-wrap gap-1.5">
                            <Badge v-for="subject in group.subjects" :key="subject.id" variant="outline">
                                {{ subject.code || subject.name }}
                            </Badge>
                        </div>

                        <div v-if="selectedTermId" class="mt-4">
                            <Link
                                :href="classReportShow.url({
                                    query: {
                                        class_stream_assignment_id: group.classroom_id,
                                        term_id: Number(selectedTermId),
                                    }
                                })">
                                <Button variant="default" class="w-full gap-2" size="sm">
                                    <FileText class="h-4 w-4" />
                                    View Report
                                    <ArrowRight class="ml-auto h-4 w-4" />
                                </Button>
                            </Link>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-else class="rounded-lg border bg-card p-6">
                <Empty>
                    <EmptyHeader>
                        <EmptyMedia>
                            <BookOpen :size="64" />
                        </EmptyMedia>
                        <EmptyTitle>No class assignments</EmptyTitle>
                        <EmptyDescription>
                            You haven't been assigned to any classes yet. Please contact your administrator.
                        </EmptyDescription>
                    </EmptyHeader>
                </Empty>
            </div>
        </div>
    </AppLayout>
</template>
