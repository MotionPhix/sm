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
import { Input } from '@/components/ui/input'
import { Field, FieldGroup, FieldLabel } from '@/components/ui/field'
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select'
import { Checkbox } from '@/components/ui/checkbox'
import { update } from '@/routes/admin/settings/assessment-plans'
import { ref } from 'vue'
import { Spinner } from '@/components/ui/spinner'

interface Term {
    id: number
    name: string
}

interface Subject {
    id: number
    name: string
    code: string
}

interface AssessmentPlan {
    id: number
    name: string
    ordering: number
    max_score: number
    weight: number
    is_active: boolean
    term: Term | null
    subject: Subject | null
    term_id: number
    subject_id: number
}

const props = defineProps<{
    assessmentPlan: AssessmentPlan
    terms: Term[]
    subjects: Subject[]
}>()

const editForm = ref()
const editModal = ref()

const form = ref({
    term_id: String(props.assessmentPlan.term_id),
    subject_id: String(props.assessmentPlan.subject_id),
    name: props.assessmentPlan.name,
    ordering: props.assessmentPlan.ordering,
    max_score: props.assessmentPlan.max_score,
    weight: props.assessmentPlan.weight,
    is_active: props.assessmentPlan.is_active,
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
        <Head :title="`Edit ${props.assessmentPlan.name}`" />

        <ModalRoot>
            <ModalHeader :title="`Edit: ${props.assessmentPlan.name}`"
                description="Update assessment plan details" />

            <ModalScrollable>
                <Form ref="editForm" v-bind="{ url: update(props.assessmentPlan.id).url, method: 'put', data: form }"
                    v-slot="{ errors, processing }" @success="handleSuccess"
                    :options="{ preserveScroll: true }">
                    <FieldGroup>
                        <Field :data-invalid="errors.term_id">
                            <FieldLabel for="edit-term">Term *</FieldLabel>
                            <Select v-model="form.term_id">
                                <SelectTrigger id="edit-term" class="bg-background">
                                    <SelectValue placeholder="Select a term" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="term in terms" :key="term.id" :value="String(term.id)">
                                        {{ term.name }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <InputError class="mt-1" :message="errors.term_id" />
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
                                    <SelectItem v-for="subject in subjects" :key="subject.id" :value="String(subject.id)">
                                        {{ subject.name }} ({{ subject.code }})
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <InputError class="mt-1" :message="errors.subject_id" />
                        </Field>
                    </FieldGroup>

                    <FieldGroup>
                        <Field :data-invalid="errors.name">
                            <FieldLabel for="edit-name">Assessment Name *</FieldLabel>
                            <Input
                                id="edit-name"
                                v-model="form.name"
                                type="text"
                                class="bg-background" />
                            <InputError class="mt-1" :message="errors.name" />
                        </Field>
                    </FieldGroup>

                    <FieldGroup>
                        <div class="grid grid-cols-3 gap-4">
                            <Field :data-invalid="errors.ordering">
                                <FieldLabel for="edit-ordering">Order</FieldLabel>
                                <Input
                                    id="edit-ordering"
                                    v-model.number="form.ordering"
                                    type="number"
                                    min="1"
                                    class="bg-background" />
                                <InputError class="mt-1" :message="errors.ordering" />
                            </Field>

                            <Field :data-invalid="errors.max_score">
                                <FieldLabel for="edit-max-score">Max Score *</FieldLabel>
                                <Input
                                    id="edit-max-score"
                                    v-model.number="form.max_score"
                                    type="number"
                                    min="1"
                                    class="bg-background" />
                                <InputError class="mt-1" :message="errors.max_score" />
                            </Field>

                            <Field :data-invalid="errors.weight">
                                <FieldLabel for="edit-weight">Weight (%) *</FieldLabel>
                                <Input
                                    id="edit-weight"
                                    v-model.number="form.weight"
                                    type="number"
                                    min="1"
                                    max="100"
                                    class="bg-background" />
                                <InputError class="mt-1" :message="errors.weight" />
                            </Field>
                        </div>
                        <p class="text-xs text-muted-foreground">
                            Weights for all assessments of a subject within a term should total 100%.
                        </p>
                    </FieldGroup>

                    <FieldGroup>
                        <label class="flex items-center gap-2">
                            <Checkbox v-model:checked="form.is_active" />
                            <span class="text-sm">Active</span>
                        </label>
                    </FieldGroup>

                    <ModalFooter>
                        <Button type="button" variant="outline" @click="close">
                            Cancel
                        </Button>
                        <Button type="submit" :disabled="processing">
                            <Spinner v-if="processing" />
                            {{ processing ? 'Updating...' : 'Update Assessment Plan' }}
                        </Button>
                    </ModalFooter>
                </Form>
            </ModalScrollable>
        </ModalRoot>
    </Modal>
</template>
