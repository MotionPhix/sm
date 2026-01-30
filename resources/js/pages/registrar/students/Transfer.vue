<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3'
import { ref } from 'vue'
import AppLayout from '@/layouts/AppLayout.vue'
import Heading from '@/components/Heading.vue'
import { Button } from '@/components/ui/button'
import { Label } from '@/components/ui/label'
import { Input } from '@/components/ui/input'
import { Textarea } from '@/components/ui/textarea'
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { form as transferRoute } from '@/routes/registrar/students/transfer'

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
    { title: 'Transfer', href: '#' },
]

const form = ref({
    class_stream_assignment_id: '',
    transfer_date: new Date().toISOString().split('T')[0],
    reason: '',
    notes: '',
})

const errors = ref<Record<string, string>>({})

function submit() {
    router.post(transferRoute(props.student).url, form.value, {
        onError: (err) => {
            errors.value = err
        },
    })
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="`Transfer ${student.first_name} ${student.last_name}`" />

        <article class="p-5 max-w-2xl mx-auto">
            <Heading
                :title="`Transfer ${student.first_name} ${student.last_name}`"
                description="Move the student to a different class or stream"
            />

            <form @submit.prevent="submit" class="mt-6 space-y-6">
                <Card>
                    <CardHeader>
                        <CardTitle>Transfer Details</CardTitle>
                        <CardDescription>
                            <template v-if="currentAcademicYear">
                                Academic year: {{ currentAcademicYear.name }}
                            </template>
                            <template v-else>
                                No active academic year found
                            </template>
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="space-y-2">
                            <Label for="class_stream_assignment_id">New Class / Stream *</Label>
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
                            <Label for="transfer_date">Transfer Date *</Label>
                            <Input
                                id="transfer_date"
                                type="date"
                                v-model="form.transfer_date"
                                :class="{ 'border-destructive': errors.transfer_date }"
                            />
                            <p v-if="errors.transfer_date" class="text-sm text-destructive">
                                {{ errors.transfer_date }}
                            </p>
                        </div>

                        <div class="space-y-2">
                            <Label for="reason">Reason for Transfer *</Label>
                            <Textarea
                                id="reason"
                                v-model="form.reason"
                                placeholder="Enter the reason for this transfer..."
                                :class="{ 'border-destructive': errors.reason }"
                            />
                            <p v-if="errors.reason" class="text-sm text-destructive">
                                {{ errors.reason }}
                            </p>
                        </div>

                        <div class="space-y-2">
                            <Label for="notes">Additional Notes (Optional)</Label>
                            <Textarea
                                id="notes"
                                v-model="form.notes"
                                placeholder="Any additional notes about this transfer..."
                            />
                        </div>
                    </CardContent>
                </Card>

                <div class="flex justify-end gap-4">
                    <Button variant="outline" type="button" :href="`/registrar/students/${student.id}`">
                        Cancel
                    </Button>
                    <Button type="submit" :disabled="!form.class_stream_assignment_id || !form.reason">
                        Transfer Student
                    </Button>
                </div>
            </form>
        </article>
    </AppLayout>
</template>
