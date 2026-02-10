<script setup lang="ts">
import DatePicker from '@/components/DatePicker.vue'
import InputError from '@/components/InputError.vue'
import {
    ModalFooter,
    ModalHeader,
    ModalRoot,
    ModalScrollable,
} from '@/components/modal'
import { Button } from '@/components/ui/button'
import { Field, FieldGroup, FieldLabel } from '@/components/ui/field'
import { Input } from '@/components/ui/input'
import { Spinner } from '@/components/ui/spinner'
import { store } from '@/routes/admin/settings/admission-cycles'
import { Form, Head } from '@inertiajs/vue3'
import { Modal } from '@inertiaui/modal-vue'
import { ref } from 'vue'

const createForm = ref()
const createModal = ref()

const form = ref({
    name: '',
    starts_at: '',
    ends_at: '',
    target_class: '',
    max_intake: null as number | null,
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
        <Head title="Add Admission Cycle" />

        <ModalRoot>
            <ModalHeader title="Create Admission Cycle"
                description="Create a new admission cycle for accepting applications." />

            <ModalScrollable>
                <Form ref="createForm" v-bind="{ url: store().url, method: 'post', data: form }"
                    v-slot="{ errors }" @success="handleSuccess"
                    :options="{ preserveScroll: true }">
                    <FieldGroup>
                        <FieldGroup>
                            <Field :data-invalid="errors.name">
                                <FieldLabel for="create-name">Cycle Name</FieldLabel>
                                <Input
                                    id="create-name"
                                    name="name"
                                    type="text"
                                    placeholder="e.g., 2024 Intake A"
                                />

                                <InputError class="mt-1" :message="errors.name" />
                            </Field>

                            <Field :data-invalid="errors.target_class">
                                <FieldLabel for="create-class">Target Class</FieldLabel>

                                <Input
                                    id="create-class"
                                    name="target_class"
                                    type="text"
                                    placeholder="e.g., Form 1"
                                />

                                <InputError class="mt-1" :message="errors.target_class" />
                            </Field>
                        </FieldGroup>

                        <FieldGroup class="grid grid-cols-2 gap-4">
                            <Field :data-invalid="errors.starts_at">
                                <FieldLabel for="create-starts">Start Date</FieldLabel>
                                <DatePicker
                                    name="starts_at"
                                    placeholder="Pick start date"
                                    :min-year="new Date().getFullYear() - 1"
                                    :max-year="new Date().getFullYear() + 1"
                                />

                                <InputError class="mt-1" :message="errors.starts_at" />
                            </Field>

                            <Field :data-invalid="errors.ends_at">
                                <FieldLabel for="create-ends">End Date</FieldLabel>
                                <DatePicker
                                    name="ends_at"
                                    placeholder="Pick end date"
                                    :min-year="new Date().getFullYear() - 1"
                                    :max-year="new Date().getFullYear() + 1"
                                />
                                <InputError class="mt-1" :message="errors.ends_at" />
                            </Field>
                        </FieldGroup>

                        <FieldGroup>
                            <Field :data-invalid="errors.max_intake">
                                <FieldLabel for="create-intake">
                                    Maximum Intake (Optional)
                                </FieldLabel>

                                <Input
                                    id="create-intake"
                                    name="max_intake"
                                    type="number" min="1"
                                    placeholder="Leave empty for unlimited"
                                />

                                <InputError class="mt-1" :message="errors.max_intake" />
                            </Field>
                        </FieldGroup>
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
                    {{ createForm?.processing ? 'Creating...' : 'Create Cycle' }}
                </Button>
            </ModalFooter>
        </ModalRoot>
    </Modal>
</template>
