<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';

import { Modal } from '@inertiaui/modal-vue';
import { Form } from '@inertiajs/vue3'

import {
    ModalRoot,
    ModalHeader,
    ModalScrollable,
    ModalFooter,
} from '@/components/modal';
import {
    Field,
    FieldGroup, FieldLabel,
    FieldSeparator,
} from '@/components/ui/field';
import InputError from '@/components/InputError.vue';
import DatePicker from '@/components/DatePicker.vue';

import { store } from '@/routes/registrar/admissions'
import { ref } from 'vue';
import { Spinner } from '@/components/ui/spinner';

defineProps<{
    cycles: Array<{
        id: number;
        name: string;
    }>;
}>();

const applicantForm = ref()
const applicantModal = ref()

const handleSuccess = () => {
    applicantForm.value?.resetAndClearErrors();
    applicantModal.value?.close()
}
</script>

<template>
    <Modal
        :close-button="false"
        :close-explicitly="true"
        v-slot="{ close }"
        ref="applicantModal">
        <ModalRoot>
            <ModalHeader
                title="New applicant"
            />

            <ModalScrollable>
                <Form
                    ref="applicantForm"
                    v-bind="store.form()"
                    v-slot="{ errors, progress }"
                    @success="handleSuccess"
                    disable-while-processing>
                    <FieldGroup>
                        <Field>
                            <FieldLabel>Cycle</FieldLabel>
                            <Select name="admission_cycle_id">
                                <SelectTrigger>
                                    <SelectValue placeholder="Select cycle" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem
                                        v-for="cycle in cycles"
                                        :key="cycle.id"
                                        :value="cycle.id">
                                        {{ cycle.name }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>

                            <InputError :message="errors.admission_cycle_id" />
                        </Field>

                        <Field>
                            <FieldLabel>First name</FieldLabel>
                            <Input name="first_name" />

                            <InputError :message="errors.first_name" />
                        </Field>

                        <Field>
                            <FieldLabel>Last name</FieldLabel>
                            <Input name="last_name" required />

                            <InputError :message="errors.last_name" />
                        </Field>

                        <Field>
                            <FieldLabel>National ID</FieldLabel>
                            <Input name="national_id" />

                            <InputError :message="errors.national_id" />
                        </Field>

                        <Field>
                            <FieldLabel>Date of birth</FieldLabel>
                            <DatePicker placeholder="Pick date of birth" name="date_of_birth" />

                            <InputError :message="errors.date_of_birth" />
                        </Field>

                        <Field>
                            <FieldLabel>Gender</FieldLabel>
                            <Select name="gender">
                                <SelectTrigger>
                                    <SelectValue placeholder="Select gender" />
                                </SelectTrigger>

                                <SelectContent>
                                    <SelectItem
                                        v-for="gender in [
                                            { value: 'male', label: 'Male'},
                                            { value: 'female', label: 'Female'},
                                            { value: 'other', label: 'Other'},
                                        ]"
                                        :key="gender.value"
                                        :value="gender.value">
                                        {{ gender.label }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>

                            <InputError :message="errors.gender" />
                        </Field>
                    </FieldGroup>
                </Form>
            </ModalScrollable>

            <ModalFooter>
                <Button
                    @click="applicantForm?.submit"
                    type="button">
                    <Spinner v-if="applicantForm?.processing" />
                    {{ applicantForm?.processing ? 'Processing ...' : 'Save applicant' }}
                </Button>

                <Button
                    @click="close"
                    type="button"
                    variant="outline">
                    Cancel
                </Button>
            </ModalFooter>
        </ModalRoot>
    </Modal>
</template>
