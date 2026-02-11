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
import { store } from '@/routes/admin/settings/assessment-plans'
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

const props = defineProps<{
    terms: Term[]
    subjects: Subject[]
    academicYear: { id: number; name: string } | null
}>()

const createForm = ref()
const createModal = ref()

const form = ref({
    term_id: '' as string,
    subject_id: '' as string,
    name: '',
    ordering: 1,
    max_score: 100,
    weight: 100,
    is_active: true,
})

const handleSuccess = () => {
    createModal.value?.close()
    createForm.value?.resetAndClearErrors()
}
</script>

<template>
    <Modal
        ref="createModal"
        v-slot="{ close }"
        max-width="md"
        :close-explicitly="true"
        :close-button="false">
        <Head title="Add Assessment Plan" />

        <ModalRoot>
            <ModalHeader title="Add Assessment Plan"
                description="Create a new assessment component for a subject" />

            <ModalScrollable>
                <Form ref="createForm" v-bind="{ url: store().url, method: 'post', data: form }"
                    v-slot="{ errors, processing }" @success="handleSuccess"
                    :options="{ preserveScroll: true }">
                    <FieldGroup>
                        <Field :data-invalid="errors.term_id">
                            <FieldLabel for="create-term">Term *</FieldLabel>
                            <Select v-model="form.term_id">
                                <SelectTrigger id="create-term">
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
                        
                        <Field :data-invalid="errors.subject_id">
                            <FieldLabel for="create-subject">Subject *</FieldLabel>
                            <Select v-model="form.subject_id">
                                <SelectTrigger id="create-subject" class="bg-background">
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
                            <FieldLabel for="create-name">Assessment Name *</FieldLabel>
                            <Input
                                id="create-name"
                                v-model="form.name"
                                type="text"
                                placeholder="e.g., Mid-Term Test, End of Term Exam"
                                class="bg-background" />
                            <InputError class="mt-1" :message="errors.name" />
                        </Field>
                    </FieldGroup>

                    <FieldGroup>
                        <div class="grid grid-cols-3 gap-4">
                            <Field :data-invalid="errors.ordering">
                                <FieldLabel for="create-ordering">Order</FieldLabel>
                                <Input
                                    id="create-ordering"
                                    v-model.number="form.ordering"
                                    type="number"
                                    min="1"
                                    class="bg-background" />
                                <InputError class="mt-1" :message="errors.ordering" />
                            </Field>

                            <Field :data-invalid="errors.max_score">
                                <FieldLabel for="create-max-score">Max Score *</FieldLabel>
                                <Input
                                    id="create-max-score"
                                    v-model.number="form.max_score"
                                    type="number"
                                    min="1"
                                    class="bg-background" />
                                <InputError class="mt-1" :message="errors.max_score" />
                            </Field>

                            <Field :data-invalid="errors.weight">
                                <FieldLabel for="create-weight">Weight (%) *</FieldLabel>
                                <Input
                                    id="create-weight"
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
                </Form>
            </ModalScrollable>

            <ModalFooter>
                <Button type="button" variant="outline" @click="close">
                    Cancel
                </Button>

                <Button 
                    type="submit" 
                    @click="createForm?.submit"
                    :disabled="createForm?.processing">
                    <Spinner v-if="createForm?.processing" />
                    {{ createForm?.processing ? 'Creating...' : 'Create Plan' }}
                </Button>
            </ModalFooter>
        </ModalRoot>
    </Modal>
</template>
