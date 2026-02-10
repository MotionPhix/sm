<script setup lang="ts">
import InputError from '@/components/InputError.vue'
import {
    ModalFooter,
    ModalHeader,
    ModalRoot,
    ModalScrollable,
} from '@/components/modal'
import { Button } from '@/components/ui/button'
import { Checkbox } from '@/components/ui/checkbox'
import { Field, FieldContent, FieldDescription, FieldGroup, FieldLabel, FieldTitle } from '@/components/ui/field'
import { Input } from '@/components/ui/input'
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select'
import { Spinner } from '@/components/ui/spinner'
import { Textarea } from '@/components/ui/textarea'
import { store } from '@/routes/admin/settings/fee-items'
import { Form, Head } from '@inertiajs/vue3'
import { Modal } from '@inertiaui/modal-vue'
import { ref } from 'vue'

interface Categories {
    [key: string]: string
}

defineProps<{
    categories: Categories
}>()

const createForm = ref()
const createModal = ref()

const form = ref({
    name: '',
    code: '',
    category: 'tuition',
    description: '',
    is_mandatory: true,
    is_active: true,
})

const handleSuccess = () => {
    createModal.value?.close()
    createForm.value?.resetAndClearErrors()
}

const normalizeCode = () => {
    form.value.code = form.value.code.toUpperCase().replace(/[^A-Z0-9]/g, '')
}
</script>

<template>
    <Modal
        ref="createModal"
        v-slot="{ close }"
        max-width="lg"
        :close-explicitly="true"
        :close-button="false">
        <Head title="Create Fee Item" />

        <ModalRoot>
            <ModalHeader
                title="Create Fee Item"
                description="Define a new fee category to charge students"
            />

            <ModalScrollable>
                <Form
                    ref="createForm"
                    v-bind="{ url: store().url, method: 'post', data: form }"
                    v-slot="{ errors }" @success="handleSuccess"
                    :options="{ preserveScroll: true }">
                    <FieldGroup>

                        <FieldGroup>
                            <Field :data-invalid="errors.name">
                                <FieldLabel for="create-name">Fee Item Name *</FieldLabel>
                                <Input
                                    id="create-name"
                                    name="name"
                                    type="text"
                                    placeholder="e.g., Tuition, Exam Fees, Development Levy"
                                    class="bg-background"
                                />

                                <InputError class="mt-1" :message="errors.name" />
                            </Field>
                        </FieldGroup>

                        <FieldGroup class="grid grid-cols-2 gap-4">
                            <Field :data-invalid="errors.code">
                                <FieldLabel for="create-code">Code *</FieldLabel>
                                <Input
                                    id="create-code"
                                    name="code"
                                    type="text"
                                    placeholder="e.g., TUI"
                                    class="uppercase bg-background"
                                    maxlength="10"
                                    @blur="normalizeCode" />
                                <InputError class="mt-1" :message="errors.code" />
                            </Field>

                            <Field :data-invalid="errors.category">
                                <FieldLabel for="create-category">Category *</FieldLabel>
                                <Select name="category">
                                    <SelectTrigger id="create-category" class="bg-background">
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem v-for="(label, value) in categories" :key="value" :value="value">
                                            {{ label }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                <InputError class="mt-1" :message="errors.category" />
                            </Field>
                        </FieldGroup>

                        <FieldGroup>
                            <Field :data-invalid="errors.description">
                                <FieldLabel for="create-description">Description</FieldLabel>
                                <Textarea
                                    id="create-description"
                                    name="description"
                                    placeholder="Additional details about this fee"
                                    class="bg-background"
                                    rows="3" />
                                <InputError class="mt-1" :message="errors.description" />
                            </Field>
                        </FieldGroup>

                        <FieldGroup>
                            <FieldLabel for="is-mandatory">
                                <Field orientation="horizontal">
                                    <FieldContent>
                                        <FieldTitle>Mandatory</FieldTitle>
                                        <FieldDescription>
                                            The fee will be automatically applied to all students and cannot be waived.
                                        </FieldDescription>
                                    </FieldContent>

                                    <Checkbox
                                        id="is-mandatory"
                                        class="size-5"
                                        value="1"
                                        name="is_mandatory"
                                    />
                                </Field>
                            </FieldLabel>

                            <FieldLabel for="is-active">
                                <Field orientation="horizontal">
                                    <FieldContent>
                                        <FieldTitle>Activate</FieldTitle>
                                        <FieldDescription>
                                            Activate this fee item so it is available for use.
                                        </FieldDescription>
                                    </FieldContent>

                                    <Checkbox
                                        id="is-active"
                                        class="size-5"
                                        value="1"
                                        name="is_active"
                                    />
                                </Field>
                            </FieldLabel>
                        </FieldGroup>

                    </FieldGroup>
                </Form>
            </ModalScrollable>

            <ModalFooter>
                <Button
                    type="button"
                    variant="outline"
                    @click="close">
                    Cancel
                </Button>

                <Button
                    type="submit"
                    @click="createForm?.submit"
                    :disabled="createForm?.processing">
                    <Spinner v-if="createForm?.processing" />
                    {{ createForm?.processing ? 'Creating...' : 'Create Fee Item' }}
                </Button>
            </ModalFooter>
        </ModalRoot>
    </Modal>
</template>
