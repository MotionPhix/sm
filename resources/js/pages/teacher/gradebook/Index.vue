<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import Heading from '@/components/Heading.vue'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select'
import { BookOpen, ClipboardList, ArrowRight } from 'lucide-vue-next'
import { show as gradebookShow } from '@/routes/teacher/gradebook'
import { ref } from 'vue'
import { Empty, EmptyDescription, EmptyHeader, EmptyMedia, EmptyTitle } from '@/components/ui/empty'

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
    class: SchoolClass
    stream: Stream | null
}

interface Assignment {
    id: number
    classroom: Classroom
    subject: Subject
}

interface Term {
    id: number
    name: string
}

const props = defineProps<{
    assignments: Assignment[]
    terms: Term[]
    academicYear: { id: number; name: string } | null
}>()

const selectedTermId = ref<string>(props.terms.length > 0 ? String(props.terms[0].id) : '')

const getClassroomLabel = (classroom: Classroom): string => {
    const base = classroom.class?.name ?? 'Unknown'
    return classroom.stream ? `${base} - ${classroom.stream.name}` : base
}
</script>

<template>
    <AppLayout>
        <Head title="Gradebook" />

        <div class="space-y-6">
            <div class="flex items-center justify-between">
                <Heading
                    title="Gradebook"
                    description="Enter and manage grades for your assigned classes and subjects"
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

            <!-- Assignments Grid -->
            <div v-else-if="assignments.length > 0" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <div
                    v-for="assignment in assignments"
                    :key="assignment.id"
                    class="rounded-lg border bg-card transition-shadow hover:shadow-md">
                    <div class="p-6">
                        <div class="flex items-start justify-between">
                            <div class="flex items-center gap-3">
                                <div class="rounded-lg bg-primary/10 p-3">
                                    <BookOpen class="h-5 w-5 text-primary" />
                                </div>
                                <div>
                                    <h3 class="font-semibold">{{ assignment.subject.name }}</h3>
                                    <p class="text-sm text-muted-foreground">
                                        {{ getClassroomLabel(assignment.classroom) }}
                                    </p>
                                </div>
                            </div>
                            <Badge variant="outline">{{ assignment.subject.code }}</Badge>
                        </div>

                        <div v-if="selectedTermId" class="mt-4 flex gap-2">
                            <Link
                                :href="gradebookShow.url({
                                    query: {
                                        class_stream_assignment_id: assignment.classroom.id,
                                        subject_id: assignment.subject.id,
                                        term_id: Number(selectedTermId),
                                    }
                                })"
                                class="flex-1">
                                <Button variant="default" class="w-full gap-2" size="sm">
                                    <ClipboardList class="h-4 w-4" />
                                    Enter Grades
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
                            You haven't been assigned to any classes with subjects yet. Please contact your administrator.
                        </EmptyDescription>
                    </EmptyHeader>
                </Empty>
            </div>
        </div>
    </AppLayout>
</template>
