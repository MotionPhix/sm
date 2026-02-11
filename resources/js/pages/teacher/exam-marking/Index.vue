<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import Heading from '@/components/Heading.vue'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { dashboard as teacherDashboard } from '@/routes/teacher'
import { index as gradebookIndex } from '@/routes/teacher/gradebook'
import { Button } from '@/components/ui/button'
import { PenLine, BookOpen } from 'lucide-vue-next'

interface Assignment {
    id: number
    classroom: {
        school_class: { name: string } | null
        stream: { name: string } | null
    } | null
    subject: { name: string; code: string } | null
}

const props = defineProps<{
    academicYear: { id: number; name: string } | null
    assignments: Assignment[]
}>()
</script>

<template>
    <AppLayout
        :breadcrumbs="[
            { title: 'Teacher', href: '' },
            { title: 'Dashboard', href: teacherDashboard().url },
            { title: 'Exam Marking', href: '#' },
        ]">
        <Head title="Exam Marking" />

        <div class="flex flex-col space-y-6 max-w-6xl">
            <Heading
                title="Exam Marking"
                :description="`Enter and manage exam marks for your assigned subjects${academicYear ? ` — ${academicYear.name}` : ''}`"
            />

            <div v-if="assignments.length === 0" class="rounded-lg border bg-card">
                <div class="flex flex-col items-center justify-center py-12 text-center">
                    <PenLine class="h-12 w-12 text-muted-foreground" />
                    <p class="mt-4 text-sm text-muted-foreground">
                        No class assignments found. Contact your administrator to be assigned to classes.
                    </p>
                </div>
            </div>

            <div v-else class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <Card v-for="assignment in assignments" :key="assignment.id">
                    <CardHeader class="pb-3">
                        <div class="flex items-center gap-3">
                            <div class="rounded-lg bg-primary/10 p-2">
                                <BookOpen class="h-4 w-4 text-primary" />
                            </div>
                            <div>
                                <CardTitle class="text-base">
                                    {{ assignment.subject?.name ?? 'Unknown Subject' }}
                                </CardTitle>
                                <p class="text-xs text-muted-foreground">
                                    {{ assignment.subject?.code ?? '' }}
                                </p>
                            </div>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <p class="text-sm text-muted-foreground mb-4">
                            {{ assignment.classroom?.school_class?.name ?? 'Unknown' }}
                            <span v-if="assignment.classroom?.stream">
                                — {{ assignment.classroom.stream.name }}
                            </span>
                        </p>
                        <Link :href="gradebookIndex().url">
                            <Button variant="outline" size="sm" class="w-full">
                                <PenLine class="mr-2 h-3.5 w-3.5" />
                                Enter Marks
                            </Button>
                        </Link>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
