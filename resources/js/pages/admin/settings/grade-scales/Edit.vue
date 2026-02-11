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
import { update } from '@/routes/admin/settings/grade-scales'
import { ref } from 'vue'
import { Spinner } from '@/components/ui/spinner'
import { Plus, Trash2 } from 'lucide-vue-next'

interface GradeScaleStep {
    id: number
    min_percent: number
    max_percent: number
    grade: string
    comment: string | null
    ordering: number
}

interface GradeScale {
    id: number
    name: string
    description: string | null
    steps: GradeScaleStep[]
}

interface StepForm {
    min_percent: number | ''
    max_percent: number | ''
    grade: string
    comment: string
}

const props = defineProps<{
    gradeScale: GradeScale
}>()

const editForm = ref()
const editModal = ref()

const form = ref({
    name: props.gradeScale.name,
    description: props.gradeScale.description ?? '',
    steps: props.gradeScale.steps.map(s => ({
        min_percent: s.min_percent as number | '',
        max_percent: s.max_percent as number | '',
        grade: s.grade,
        comment: s.comment ?? '',
    })) as StepForm[],
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
    editModal.value?.close()
}
</script>

<template>
    <Modal
        ref="editModal"
        v-slot="{ close }"
        max-width="lg"
        :close-explicitly="true"
        :close-button="false">
        <Head :title="`Edit ${props.gradeScale.name}`" />

        <ModalRoot>
            <ModalHeader :title="`Edit: ${props.gradeScale.name}`"
                description="Update grading scale and its steps" />

            <ModalScrollable>
                <Form ref="editForm" v-bind="{ url: update(props.gradeScale.id).url, method: 'put', data: form }"
                    v-slot="{ errors, processing }" @success="handleSuccess"
                    :options="{ preserveScroll: true }">
                    <FieldGroup>
                        <Field :data-invalid="errors.name">
                            <FieldLabel for="edit-name">Scale Name *</FieldLabel>
                            <Input
                                id="edit-name"
                                v-model="form.name"
                                type="text"
                                class="bg-background" />
                            <InputError class="mt-1" :message="errors.name" />
                        </Field>
                    </FieldGroup>

                    <FieldGroup>
                        <Field>
                            <FieldLabel for="edit-description">Description</FieldLabel>
                            <Textarea
                                id="edit-description"
                                v-model="form.description"
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
                            {{ processing ? 'Updating...' : 'Update Grading Scale' }}
                        </Button>
                    </ModalFooter>
                </Form>
            </ModalScrollable>
        </ModalRoot>
    </Modal>
</template>
