<script setup lang="ts">
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
import { Spinner } from '@/components/ui/spinner'
import { store } from '@/routes/admin/settings/teacher-assignments'
import { Form, Head } from '@inertiajs/vue3'
import { Modal } from '@inertiaui/modal-vue'
import { ref } from 'vue'

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

const props = defineProps<{
    teachers: Teacher[]
    classrooms: Classroom[]
    subjects: Subject[]
}>()

const createForm = ref()
const createModal = ref()

const form = ref({
    user_id: '' as string | number,
    class_stream_assignment_id: '' as string | number,
    subject_id: '' as string | number,
})

const handleSuccess = () => {
    createModal.value?.close()
    createForm.value?.resetAndClearErrors()
}

const classroomName = (classroom: Classroom) => {
    const className = classroom.school_class?.name ?? 'Unknown'
    const streamName = classroom.stream?.name
    return streamName ? `${className} - ${streamName}` : className
}
</script>

<template>
    <Modal
        ref="createModal"
        v-slot="{ close }"
        max-width="md"
        :close-explicitly="true"
        :close-button="false">
        <Head title="Add Teacher Assignment" />

        <ModalRoot>
            <ModalHeader
                title="Add Teacher Assignment"
                description="Assign a teacher to a class and subject"
            />

            <ModalScrollable>
                <Form ref="createForm" v-bind="{ url: store().url, method: 'post', data: form }"
                    v-slot="{ errors }" @success="handleSuccess"
                    :options="{ preserveScroll: true }">
                    <FieldGroup>
                        <Field :data-invalid="errors.user_id">
                            <FieldLabel for="create-teacher">Teacher *</FieldLabel>
                            <Select name="user_id">
                                <SelectTrigger id="create-teacher" class="bg-background">
                                    <SelectValue placeholder="Select a teacher" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="teacher in teachers" :key="teacher.id" :value="teacher.id">
                                        {{ teacher.name }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <InputError :message="errors.user_id" />
                        </Field>

                        <Field :data-invalid="errors.class_stream_assignment_id">
                            <FieldLabel for="create-classroom">Classroom *</FieldLabel>
                            <Select name="class_stream_assignment_id">
                                <SelectTrigger id="create-classroom" class="bg-background">
                                    <SelectValue placeholder="Select a classroom" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="classroom in classrooms" :key="classroom.id" :value="classroom.id">
                                        {{ classroomName(classroom) }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <InputError :message="errors.class_stream_assignment_id" />
                        </Field>

                        <Field :data-invalid="errors.subject_id">
                            <FieldLabel for="create-subject">Subject *</FieldLabel>
                            <Select name="subject_id" multiple>
                                <SelectTrigger id="create-subject" class="bg-background">
                                    <SelectValue placeholder="Select a subject" />
                                </SelectTrigger>

                                <SelectContent>
                                    <SelectItem v-for="subject in subjects" :key="subject.id" :value="subject.id">
                                        {{ subject.code }} - {{ subject.name }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <InputError :message="errors.subject_id" />
                        </Field>
                    </FieldGroup>
                </Form>
            </ModalScrollable>

            <ModalFooter>
                <Button type="button" variant="outline" @click="close">
                    Cancel
                </Button>

                <Button type="submit" @click="createForm?.submit()" :disabled="createForm?.processing">
                    <Spinner v-if="createForm?.processing" />
                    {{ createForm?.processing ? 'Assigning...' : 'Assign' }}
                </Button>
            </ModalFooter>
        </ModalRoot>
    </Modal>
</template>
