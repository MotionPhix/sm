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
import { Textarea } from '@/components/ui/textarea'
import { Field, FieldGroup, FieldLabel } from '@/components/ui/field'
import { store } from '@/routes/admin/settings/grade-scales'
import { ref } from 'vue'
import { Spinner } from '@/components/ui/spinner'
import { Plus, Trash2 } from 'lucide-vue-next'

interface StepForm {
    min_percent: number | ''
    max_percent: number | ''
    grade: string
    comment: string
}

const createForm = ref()
const createModal = ref()

const defaultSteps: StepForm[] = [
    { min_percent: 80, max_percent: 100, grade: 'A', comment: 'Excellent' },
    { min_percent: 60, max_percent: 79, grade: 'B', comment: 'Good' },
    { min_percent: 40, max_percent: 59, grade: 'C', comment: 'Average' },
    { min_percent: 20, max_percent: 39, grade: 'D', comment: 'Below Average' },
    { min_percent: 0, max_percent: 19, grade: 'F', comment: 'Fail' },
]

const form = ref({
    name: '',
    description: '',
    steps: [...defaultSteps.map(s => ({ ...s }))] as StepForm[],
})

const addStep = () => {
    form.value.steps.push({ min_percent: '', max_percent: '', grade: '', comment: '' })
}

const removeStep = (index: number) => {
    if (form.value.steps.length > 1) {
        form.value.steps.splice(index, 1)
    }
}

const handleSuccess = () => {
    createModal.value?.close()
    createForm.value?.resetAndClearErrors()
}
</script>

<template>
    <Modal
        ref="createModal"
        v-slot="{ close }"
        max-width="lg"
        :close-explicitly="true"
        :close-button="false">
        <Head title="Add Grading Scale" />

        <ModalRoot>
            <ModalHeader title="Add Grading Scale"
                description="Define how percentage scores map to letter grades" />

            <ModalScrollable>
                <Form ref="createForm" v-bind="{ url: store().url, method: 'post', data: form }"
                    v-slot="{ errors, processing }" @success="handleSuccess"
                    :options="{ preserveScroll: true }">
                    <FieldGroup>
                        <Field :data-invalid="errors.name">
                            <FieldLabel for="create-name">Scale Name *</FieldLabel>
                            <Input
                                id="create-name"
                                v-model="form.name"
                                type="text"
                                placeholder="e.g., Primary Grading Scale"
                                class="bg-background" />
                            <InputError class="mt-1" :message="errors.name" />
                        </Field>
                    </FieldGroup>

                    <FieldGroup>
                        <Field>
                            <FieldLabel for="create-description">Description</FieldLabel>
                            <Textarea
                                id="create-description"
                                v-model="form.description"
                                placeholder="Optional description"
                                class="bg-background"
                                rows="2" />
                        </Field>
                    </FieldGroup>

                    <!-- Steps -->
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <h3 class="text-sm font-semibold">Grade Steps</h3>
                            <Button
                                type="button"
                                variant="outline"
                                size="sm"
                                @click="addStep"
                                class="gap-1">
                                <Plus class="h-4 w-4" />
                                Add Step
                            </Button>
                        </div>

                        <InputError v-if="errors.steps" :message="errors.steps" />

                        <div
                            v-for="(step, index) in form.steps"
                            :key="index"
                            class="relative rounded-lg border bg-muted/30 p-4">
                            <div class="grid grid-cols-4 gap-3">
                                <Field :data-invalid="errors[`steps.${index}.min_percent`]">
                                    <FieldLabel :for="`min-${index}`">Min %</FieldLabel>
                                    <Input
                                        :id="`min-${index}`"
                                        v-model.number="step.min_percent"
                                        type="number"
                                        min="0"
                                        max="100"
                                        class="bg-background" />
                                    <InputError :message="errors[`steps.${index}.min_percent`]" />
                                </Field>

                                <Field :data-invalid="errors[`steps.${index}.max_percent`]">
                                    <FieldLabel :for="`max-${index}`">Max %</FieldLabel>
                                    <Input
                                        :id="`max-${index}`"
                                        v-model.number="step.max_percent"
                                        type="number"
                                        min="0"
                                        max="100"
                                        class="bg-background" />
                                    <InputError :message="errors[`steps.${index}.max_percent`]" />
                                </Field>

                                <Field :data-invalid="errors[`steps.${index}.grade`]">
                                    <FieldLabel :for="`grade-${index}`">Grade *</FieldLabel>
                                    <Input
                                        :id="`grade-${index}`"
                                        v-model="step.grade"
                                        type="text"
                                        placeholder="A"
                                        maxlength="5"
                                        class="bg-background" />
                                    <InputError :message="errors[`steps.${index}.grade`]" />
                                </Field>

                                <Field>
                                    <FieldLabel :for="`comment-${index}`">Comment</FieldLabel>
                                    <Input
                                        :id="`comment-${index}`"
                                        v-model="step.comment"
                                        type="text"
                                        placeholder="Excellent"
                                        class="bg-background" />
                                </Field>
                            </div>

                            <div v-if="form.steps.length > 1" class="absolute -top-0.5 -right-0.5">
                                <Button
                                    type="button"
                                    variant="destructive"
                                    size="icon-sm"
                                    @click="removeStep(index)">
                                    <Trash2 />
                                </Button>
                            </div>
                        </div>
                    </div>

                    <ModalFooter>
                        <Button type="button" variant="outline" @click="close">
                            Cancel
                        </Button>
                        <Button type="submit" :disabled="processing">
                            <Spinner v-if="processing" />
                            {{ processing ? 'Creating...' : 'Create Grading Scale' }}
                        </Button>
                    </ModalFooter>
                </Form>
            </ModalScrollable>
        </ModalRoot>
    </Modal>
</template>
