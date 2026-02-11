<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import Heading from '@/components/Heading.vue'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { dashboard as teacherDashboard } from '@/routes/teacher'
import { FileText, BookOpen } from 'lucide-vue-next'

interface Assignment {
    id: number
    classroom: {
        school_class: { name: string } | null
        stream: { name: string } | null
    } | null
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
            { title: 'Class Reports', href: '#' },
        ]">
        <Head title="Class Reports" />

        <div class="flex flex-col space-y-6 max-w-6xl">
            <Heading
                title="Class Reports"
                :description="`View and generate reports for your assigned classes${academicYear ? ` — ${academicYear.name}` : ''}`"
            />

            <div v-if="assignments.length === 0" class="rounded-lg border bg-card">
                <div class="flex flex-col items-center justify-center py-12 text-center">
                    <FileText class="h-12 w-12 text-muted-foreground" />
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
                            <CardTitle class="text-base">
                                {{ assignment.classroom?.school_class?.name ?? 'Unknown' }}
                                <span v-if="assignment.classroom?.stream" class="text-muted-foreground font-normal">
                                    — {{ assignment.classroom.stream.name }}
                                </span>
                            </CardTitle>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div class="flex flex-col items-center justify-center py-4 text-center">
                            <FileText class="h-8 w-8 text-muted-foreground/50" />
                            <p class="mt-2 text-xs text-muted-foreground">
                                Report generation coming soon
                            </p>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
