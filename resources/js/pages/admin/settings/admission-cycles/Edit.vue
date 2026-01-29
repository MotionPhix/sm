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
import { update } from '@/routes/admin/settings/admission-cycles'
import { ref } from 'vue'
import { Spinner } from '@/components/ui/spinner'

interface AdmissionCycle {
    id: number
    name: string
    starts_at: string
    ends_at: string
    target_class: string
    max_intake: number | null
    is_active: boolean
}

const props = defineProps<{
    admissionCycle: AdmissionCycle
}>()

const editForm = ref()
const editModal = ref()

const form = ref({
    id: props.admissionCycle.id,
    name: props.admissionCycle.name,
    starts_at: props.admissionCycle.starts_at,
    ends_at: props.admissionCycle.ends_at,
    target_class: props.admissionCycle.target_class,
    max_intake: props.admissionCycle.max_intake,
})

const handleSuccess = () => {
    editModal.value?.close()
    editForm.value?.resetAndClearErrors()
}
</script>

<template>
    <Modal
        ref="editModal"
        v-slot="{ close }"
        max-width="md"
        :close-explicitly="true"
        :close-button="false">
        <Head title="Edit Admission Cycle" />

        <ModalRoot>
            <ModalHeader title="Edit Admission Cycle" description="Update the admission cycle details." />

            <ModalScrollable>
                <Form ref="editForm" v-bind="{ url: update(form.id).url, method: 'put', data: form }"
                    v-slot="{ errors, processing }" @success="handleSuccess"
                    :options="{ preserveScroll: true }">
                    <FieldGroup>
                        <Field :data-invalid="errors.name">
                            <FieldLabel for="edit-name">Cycle Name</FieldLabel>
                            <Input id="edit-name" v-model="form.name" type="text" />
                            <InputError class="mt-1" :message="errors.name" />
                        </Field>

                        <Field :data-invalid="errors.target_class">
                            <FieldLabel for="edit-class">Target Class</FieldLabel>
                            <Input id="edit-class" v-model="form.target_class" type="text" />
                            <InputError class="mt-1" :message="errors.target_class" />
                        </Field>
                    </FieldGroup>

                    <FieldGroup class="grid grid-cols-2 gap-4">
                        <Field :data-invalid="errors.starts_at">
                            <FieldLabel for="edit-starts">Start Date</FieldLabel>
                            <DatePicker v-model="form.starts_at" name="starts_at"
                                placeholder="Pick start date" />
                            <InputError class="mt-1" :message="errors.starts_at" />
                        </Field>

                        <Field :data-invalid="errors.ends_at">
                            <FieldLabel for="edit-ends">End Date</FieldLabel>
                            <DatePicker v-model="form.ends_at" name="ends_at" placeholder="Pick end date" />
                            <InputError class="mt-1" :message="errors.ends_at" />
                        </Field>
                    </FieldGroup>

                    <FieldGroup>
                        <Field :data-invalid="errors.max_intake">
                            <FieldLabel for="edit-intake">Maximum Intake (Optional)</FieldLabel>
                            <Input id="edit-intake" v-model.number="form.max_intake" type="number" min="1"
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
                            {{ processing ? 'Saving...' : 'Save Changes' }}
                        </Button>
                    </ModalFooter>
                </Form>
            </ModalScrollable>
        </ModalRoot>
    </Modal>
</template>
