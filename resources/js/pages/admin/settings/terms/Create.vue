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
import { store } from '@/routes/admin/settings/terms'
import { ref } from 'vue'
import { Spinner } from '@/components/ui/spinner'

interface AcademicYear {
    id: number
    name: string
    starts_on: string
    ends_on: string
}

defineProps<{
    academicYear: AcademicYear
    termCount: number
}>()

const createForm = ref()
const createModal = ref()

const form = ref({
    name: '',
    sequence: 0,
    starts_on: '',
    ends_on: '',
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
        <Head title="Add Term" />

        <ModalRoot>
            <ModalHeader title="Add New Term"
                :description="`Create a new term for the ${academicYear?.name} academic year.`" />

            <ModalScrollable>
                <Form ref="createForm" v-bind="{ url: store().url, method: 'post', data: form }"
                    v-slot="{ errors, processing }" @success="handleSuccess"
                    :options="{ preserveScroll: true }">
                    <FieldGroup class="grid grid-cols-2 gap-4">
                        <Field :data-invalid="errors.name">
                            <FieldLabel for="create-name">Name</FieldLabel>
                            <Input id="create-name" v-model="form.name" type="text" placeholder="Term 1" />
                            <InputError class="mt-1" :message="errors.name" />
                        </Field>
                        <Field :data-invalid="errors.sequence">
                            <FieldLabel for="create-sequence">Sequence</FieldLabel>
                            <Input id="create-sequence" v-model.number="form.sequence" type="number" min="1"
                                max="6" />
                            <InputError class="mt-1" :message="errors.sequence" />
                        </Field>
                    </FieldGroup>
                    <FieldGroup class="grid grid-cols-2 gap-4">
                        <Field :data-invalid="errors.starts_on">
                            <FieldLabel for="create-starts">Start Date</FieldLabel>
                            <DatePicker v-model="form.starts_on" name="starts_on" placeholder="Pick start date" />
                            <InputError class="mt-1" :message="errors.starts_on" />
                        </Field>
                        <Field :data-invalid="errors.ends_on">
                            <FieldLabel for="create-ends">End Date</FieldLabel>
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
                            {{ processing ? 'Creating...' : 'Add Term' }}
                        </Button>
                    </ModalFooter>
                </Form>
            </ModalScrollable>
        </ModalRoot>
    </Modal>
</template>
