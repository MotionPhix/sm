<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3'
import { Modal } from '@inertiaui/modal-vue'
import InputError from '@/components/InputError.vue'
import {
    ModalFooter,
    ModalHeader,
    ModalRoot,
    ModalScrollable,
} from '@/components/modal'
import { Button } from '@/components/ui/button'
import { Field, FieldGroup, FieldLabel } from '@/components/ui/field'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { update } from '@/routes/admin/settings/teacher-assignments'
import { ref } from 'vue'
import { Spinner } from '@/components/ui/spinner'

interface Teacher {
    id: number
    name: string
    email: string
}

interface Classroom {
    id: number
    school_class: { id: number; name: string } | null
    stream: { id: number; name: string } | null
}

interface Subject {
    id: number
    name: string
    code: string
}

interface Assignment {
    id: number
    user_id: number
    class_stream_assignment_id: number
    subject_id: number
    teacher: Teacher | null
    classroom: Classroom | null
    subject: Subject | null
}

const props = defineProps<{
    assignment: Assignment
    teachers: Teacher[]
    classrooms: Classroom[]
    subjects: Subject[]
}>()

const editForm = ref()
const editModal = ref()

const form = ref({
    user_id: props.assignment.user_id as string | number,
    class_stream_assignment_id: props.assignment.class_stream_assignment_id as string | number,
    subject_id: props.assignment.subject_id as string | number,
})

const handleSuccess = () => {
    editModal.value?.close()
}

const classroomName = (classroom: Classroom) => {
    const className = classroom.school_class?.name ?? 'Unknown'
    const streamName = classroom.stream?.name
    return streamName ? `${className} - ${streamName}` : className
}
</script>

<template>
    <Modal
        ref="editModal"
        v-slot="{ close }"
        max-width="md"
        :close-explicitly="true"
        :close-button="false">
        <Head title="Edit Teacher Assignment" />

        <ModalRoot>
            <ModalHeader title="Edit Teacher Assignment"
                description="Update this teacher assignment" />

            <ModalScrollable>
                <Form ref="editForm" v-bind="{ url: update(props.assignment.id).url, method: 'put', data: form }"
                    v-slot="{ errors, processing }" @success="handleSuccess"
                    :options="{ preserveScroll: true }">
                    <FieldGroup>
                        <Field :data-invalid="errors.user_id">
                            <FieldLabel for="edit-teacher">Teacher *</FieldLabel>
                            <Select v-model="form.user_id">
                                <SelectTrigger id="edit-teacher" class="bg-background">
                                    <SelectValue placeholder="Select a teacher" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="teacher in teachers" :key="teacher.id" :value="teacher.id">
                                        {{ teacher.name }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <InputError class="mt-1" :message="errors.user_id" />
                        </Field>
                    </FieldGroup>

                    <FieldGroup>
                        <Field :data-invalid="errors.class_stream_assignment_id">
                            <FieldLabel for="edit-classroom">Classroom *</FieldLabel>
                            <Select v-model="form.class_stream_assignment_id">
                                <SelectTrigger id="edit-classroom" class="bg-background">
                                    <SelectValue placeholder="Select a classroom" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="classroom in classrooms" :key="classroom.id" :value="classroom.id">
                                        {{ classroomName(classroom) }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <InputError class="mt-1" :message="errors.class_stream_assignment_id" />
                        </Field>
                    </FieldGroup>

                    <FieldGroup>
                        <Field :data-invalid="errors.subject_id">
                            <FieldLabel for="edit-subject">Subject *</FieldLabel>
                            <Select v-model="form.subject_id">
                                <SelectTrigger id="edit-subject" class="bg-background">
                                    <SelectValue placeholder="Select a subject" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="subject in subjects" :key="subject.id" :value="subject.id">
                                        {{ subject.code }} - {{ subject.name }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <InputError class="mt-1" :message="errors.subject_id" />
                        </Field>
                    </FieldGroup>

                    <ModalFooter>
                        <Button type="button" variant="outline" @click="close">
                            Cancel
                        </Button>
                        <Button type="submit" :disabled="processing">
                            <Spinner v-if="processing" />
                            {{ processing ? 'Updating...' : 'Update Assignment' }}
                        </Button>
                    </ModalFooter>
                </Form>
            </ModalScrollable>
        </ModalRoot>
    </Modal>
</template>
