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
import { update } from '@/routes/admin/settings/teacher-assignments'
import { Form, Head } from '@inertiajs/vue3'
import { Modal } from '@inertiaui/modal-vue'
import { computed, ref, watch } from 'vue'

interface Teacher {
    id: number
    name: string
    email: string
}

interface SchoolClass {
    id: number
    name: string
}

interface Stream {
    id: number
    name: string
}

interface Classroom {
    id: number
    school_class: SchoolClass | null
    stream: Stream | null
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
    school_class_id: (props.assignment.classroom?.school_class?.id ?? '') as string | number,
    stream_id: (props.assignment.classroom?.stream?.id ?? '') as string | number,
    subject_id: props.assignment.subject_id as string | number,
})

const uniqueClasses = computed(() => {
    const classMap = new Map<number, SchoolClass>()
    for (const classroom of props.classrooms) {
        if (classroom.school_class && !classMap.has(classroom.school_class.id)) {
            classMap.set(classroom.school_class.id, classroom.school_class)
        }
    }
    return Array.from(classMap.values())
})

const filteredStreams = computed(() => {
    const classId = Number(form.value.school_class_id)
    if (!classId) return []
    return props.classrooms
        .filter(c => c.school_class?.id === classId && c.stream)
        .map(c => c.stream!)
})

watch(() => form.value.school_class_id, (newVal, oldVal) => {
    if (oldVal !== '') {
        form.value.stream_id = ''
    }
})

const handleSuccess = () => {
    editModal.value?.close()
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

                        <Field :data-invalid="errors.school_class_id">
                            <FieldLabel for="edit-class">Class *</FieldLabel>
                            <Select v-model="form.school_class_id">
                                <SelectTrigger id="edit-class" class="bg-background">
                                    <SelectValue placeholder="Select a class" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="klass in uniqueClasses" :key="klass.id" :value="klass.id">
                                        {{ klass.name }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <InputError class="mt-1" :message="errors.school_class_id" />
                        </Field>

                        <Field :data-invalid="errors.stream_id">
                            <FieldLabel for="edit-stream">Stream *</FieldLabel>
                            <Select v-model="form.stream_id" :disabled="!form.school_class_id">
                                <SelectTrigger id="edit-stream" class="bg-background">
                                    <SelectValue placeholder="Select a stream" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="stream in filteredStreams" :key="stream.id" :value="stream.id">
                                        {{ stream.name }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <InputError class="mt-1" :message="errors.stream_id" />
                        </Field>

                        <Field :data-invalid="errors.subject_id">
                            <FieldLabel for="edit-subject">Subject *</FieldLabel>
                            <Select v-model="form.subject_id">
                                <SelectTrigger id="edit-subject" class="bg-background">
                                    <SelectValue placeholder="Select a subject" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="subject in subjects" :key="subject.id" :value="subject.id">
                                        {{ subject.code ? `${subject.code} - ${subject.name}` : subject.name }}
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
