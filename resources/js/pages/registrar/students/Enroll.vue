<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3'
import { ref } from 'vue'
import AppLayout from '@/layouts/AppLayout.vue'
import Heading from '@/components/Heading.vue'
import { Button } from '@/components/ui/button'
import { Label } from '@/components/ui/label'
import { Input } from '@/components/ui/input'
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { enroll as enrollRoute } from '@/routes/registrar/students'

interface Student {
    id: number
    first_name: string
    last_name: string
}

interface ClassStreamAssignment {
    id: number
    name: string
    current_enrollment: number
}

interface AcademicYear {
    id: number
    name: string
}

const props = defineProps<{
    student: Student
    classStreamAssignments: ClassStreamAssignment[]
    currentAcademicYear?: AcademicYear
}>()

const breadcrumbs = [
    { title: 'Dashboard', href: '/registrar/dashboard' },
    { title: 'Students', href: '/registrar/students' },
    { title: `${props.student.first_name} ${props.student.last_name}`, href: `#` },
    { title: 'Enroll', href: '#' },
]

const form = ref({
    class_stream_assignment_id: '',
    enrollment_date: new Date().toISOString().split('T')[0],
})

const errors = ref<Record<string, string>>({})

function submit() {
    router.post(enrollRoute(props.student).url, form.value, {
        onError: (err) => {
            errors.value = err
        },
    })
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="`Enroll ${student.first_name} ${student.last_name}`" />

        <article class="p-5 max-w-2xl mx-auto">
            <Heading
                :title="`Enroll ${student.first_name} ${student.last_name}`"
                description="Assign the student to a class and stream"
            />

            <form @submit.prevent="submit" class="mt-6 space-y-6">
                <Card>
                    <CardHeader>
                        <CardTitle>Enrollment Details</CardTitle>
                        <CardDescription>
                            <template v-if="currentAcademicYear">
                                Enrolling for academic year: {{ currentAcademicYear.name }}
                            </template>
                            <template v-else>
                                No active academic year found
                            </template>
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="space-y-2">
                            <Label for="class_stream_assignment_id">Class / Stream *</Label>
                            <Select v-model="form.class_stream_assignment_id">
                                <SelectTrigger :class="{ 'border-destructive': errors.class_stream_assignment_id }">
                                    <SelectValue placeholder="Select a class and stream" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem
                                        v-for="assignment in classStreamAssignments"
                                        :key="assignment.id"
                                        :value="String(assignment.id)">
                                        {{ assignment.name }}
                                        <span class="text-muted-foreground ml-2">
                                            ({{ assignment.current_enrollment }} enrolled)
                                        </span>
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <p v-if="errors.class_stream_assignment_id" class="text-sm text-destructive">
                                {{ errors.class_stream_assignment_id }}
                            </p>
                        </div>

                        <div class="space-y-2">
                            <Label for="enrollment_date">Enrollment Date *</Label>
                            <Input
                                id="enrollment_date"
                                type="date"
                                v-model="form.enrollment_date"
                                :class="{ 'border-destructive': errors.enrollment_date }"
                            />
                            <p v-if="errors.enrollment_date" class="text-sm text-destructive">
                                {{ errors.enrollment_date }}
                            </p>
                        </div>
                    </CardContent>
                </Card>

                <div class="flex justify-end gap-4">
                    <Button variant="outline" type="button" :href="`/registrar/students/${student.id}`">
                        Cancel
                    </Button>
                    <Button type="submit" :disabled="!form.class_stream_assignment_id">
                        Enroll Student
                    </Button>
                </div>
            </form>
        </article>
    </AppLayout>
</template>
