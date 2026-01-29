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
import DatePicker from '@/components/DatePicker.vue'
import { store } from '@/routes/admin/settings/admission-cycles'
import { ref } from 'vue'
import { Spinner } from '@/components/ui/spinner'

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
                    v-slot="{ errors, processing }" @success="handleSuccess"
                    :options="{ preserveScroll: true }">
                    <FieldGroup>
                        <Field :data-invalid="errors.name">
                            <FieldLabel for="create-name">Cycle Name</FieldLabel>
                            <Input id="create-name" v-model="form.name" type="text"
                                placeholder="e.g., 2024 Intake A" />
                            <InputError class="mt-1" :message="errors.name" />
                        </Field>

                        <Field :data-invalid="errors.target_class">
                            <FieldLabel for="create-class">Target Class</FieldLabel>
                            <Input id="create-class" v-model="form.target_class" type="text"
                                placeholder="e.g., Form 1" />
                            <InputError class="mt-1" :message="errors.target_class" />
                        </Field>
                    </FieldGroup>

                    <FieldGroup class="grid grid-cols-2 gap-4">
                        <Field :data-invalid="errors.starts_at">
                            <FieldLabel for="create-starts">Start Date</FieldLabel>
                            <DatePicker v-model="form.starts_at" name="starts_at"
                                placeholder="Pick start date" />
                            <InputError class="mt-1" :message="errors.starts_at" />
                        </Field>

                        <Field :data-invalid="errors.ends_at">
                            <FieldLabel for="create-ends">End Date</FieldLabel>
                            <DatePicker v-model="form.ends_at" name="ends_at" placeholder="Pick end date" />
                            <InputError class="mt-1" :message="errors.ends_at" />
                        </Field>
                    </FieldGroup>

                    <FieldGroup>
                        <Field :data-invalid="errors.max_intake">
                            <FieldLabel for="create-intake">Maximum Intake (Optional)</FieldLabel>
                            <Input id="create-intake" v-model.number="form.max_intake" type="number" min="1"
                                placeholder="Leave empty for unlimited" />
                            <InputError class="mt-1" :message="errors.max_intake" />
                        </Field>
                    </FieldGroup>

                    <ModalFooter>
                        <Button type="button" variant="outline" @click="close">
                            Cancel
                        </Button>
                        <Button type="submit" :disabled="processing">
                            <Spinner v-if="processing" />
                            {{ processing ? 'Creating...' : 'Create Cycle' }}
                        </Button>
                    </ModalFooter>
                </Form>
            </ModalScrollable>
        </ModalRoot>
    </Modal>
</template>
