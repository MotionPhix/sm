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
import { update } from '@/routes/admin/settings/terms'
import { ref } from 'vue'
import { Spinner } from '@/components/ui/spinner'

interface Term {
    id: number
    name: string
    sequence: number
    starts_on: string
    ends_on: string
    is_active: boolean
}

const props = defineProps<{
    term: Term
}>()

const editForm = ref()
const editModal = ref()

const form = ref({
    id: props.term.id,
    name: props.term.name,
    sequence: props.term.sequence,
    starts_on: props.term.starts_on,
    ends_on: props.term.ends_on,
    is_active: props.term.is_active,
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
        <Head title="Edit Term" />

        <ModalRoot>
            <ModalHeader title="Edit Term" description="Update the term details." />

            <ModalScrollable>
                <Form ref="editForm" v-bind="{ url: update(form.id).url, method: 'put', data: form }"
                    v-slot="{ errors, processing }" @success="handleSuccess"
                    :options="{ preserveScroll: true }">
                    <FieldGroup class="grid grid-cols-2 gap-4">
                        <Field :data-invalid="errors.name">
                            <FieldLabel for="edit-name">Name</FieldLabel>
                            <Input id="edit-name" v-model="form.name" type="text" />
                            <InputError class="mt-1" :message="errors.name" />
                        </Field>
                        <Field :data-invalid="errors.sequence">
                            <FieldLabel for="edit-sequence">Sequence</FieldLabel>
                            <Input id="edit-sequence" v-model.number="form.sequence" type="number" min="1"
                                max="6" />
                            <InputError class="mt-1" :message="errors.sequence" />
                        </Field>
                    </FieldGroup>
                    <FieldGroup class="grid grid-cols-2 gap-4">
                        <Field :data-invalid="errors.starts_on">
                            <FieldLabel for="edit-starts">Start Date</FieldLabel>
                            <DatePicker v-model="form.starts_on" name="starts_on" placeholder="Pick start date" />
                            <InputError class="mt-1" :message="errors.starts_on" />
                        </Field>
                        <Field :data-invalid="errors.ends_on">
                            <FieldLabel for="edit-ends">End Date</FieldLabel>
                            <DatePicker v-model="form.ends_on" name="ends_on" placeholder="Pick end date" />
                            <InputError class="mt-1" :message="errors.ends_on" />
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
