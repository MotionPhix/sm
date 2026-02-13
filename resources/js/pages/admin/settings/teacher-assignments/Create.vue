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

const props = defineProps<{
    teachers: Teacher[]
    classrooms: Classroom[]
    subjects: Subject[]
}>()

const createForm = ref()
const createModal = ref()

const form = ref({
    user_id: '' as string | number,
    school_class_id: '' as string | number,
    stream_id: '' as string | number,
    subject_ids: [] as number[],
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

watch(() => form.value.school_class_id, () => {
    form.value.stream_id = ''
})

const handleSuccess = () => {
    createModal.value?.close()
    form.value = {
        user_id: '',
        school_class_id: '',
        stream_id: '',
        subject_ids: [],
    }
    createForm.value?.clearErrors()
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
                description="Assign a teacher to a class, stream, and subjects"
            />

            <ModalScrollable>
                <Form ref="createForm" v-bind="{ url: store().url, method: 'post', data: form }"
                    v-slot="{ errors }" @success="handleSuccess"
                    :options="{ preserveScroll: true }">
                    <FieldGroup>
                        <Field :data-invalid="errors.user_id">
                            <FieldLabel for="create-teacher">Teacher *</FieldLabel>
                            <Select v-model="form.user_id">
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

                        <Field :data-invalid="errors.school_class_id">
                            <FieldLabel for="create-class">Class *</FieldLabel>
                            <Select v-model="form.school_class_id">
                                <SelectTrigger id="create-class" class="bg-background">
                                    <SelectValue placeholder="Select a class" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="klass in uniqueClasses" :key="klass.id" :value="klass.id">
                                        {{ klass.name }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <InputError :message="errors.school_class_id" />
                        </Field>

                        <Field :data-invalid="errors.stream_id">
                            <FieldLabel for="create-stream">Stream *</FieldLabel>
                            <Select v-model="form.stream_id" :disabled="!form.school_class_id">
                                <SelectTrigger id="create-stream" class="bg-background">
                                    <SelectValue placeholder="Select a stream" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="stream in filteredStreams" :key="stream.id" :value="stream.id">
                                        {{ stream.name }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <InputError :message="errors.stream_id" />
                        </Field>

                        <Field :data-invalid="errors.subject_ids || errors['subject_ids.0']">
                            <FieldLabel for="create-subjects">Subjects *</FieldLabel>
                            <Select v-model="form.subject_ids" multiple>
                                <SelectTrigger id="create-subjects" class="bg-background">
                                    <SelectValue placeholder="Select subjects" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="subject in subjects" :key="subject.id" :value="subject.id">
                                        {{ subject.code ? `${subject.code} - ${subject.name}` : subject.name }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <InputError :message="errors.subject_ids || errors['subject_ids.0']" />
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
