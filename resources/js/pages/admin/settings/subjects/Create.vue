<script setup lang="ts">
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
import { store } from '@/routes/admin/settings/subjects'
import { Form, Head } from '@inertiajs/vue3'
import { Modal } from '@inertiaui/modal-vue'
import { ref } from 'vue'

const createForm = ref()
const createModal = ref()

const form = ref({
    name: '',
    code: '',
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
        max-width="md"
        :close-explicitly="true"
        :close-button="false">
        <Head title="Add Subject" />

        <ModalRoot>
            <ModalHeader title="Add Subject"
                description="Create a new subject for your school" />

            <ModalScrollable>
                <Form ref="createForm" v-bind="{ url: store().url, method: 'post', data: form }"
                    v-slot="{ errors, processing }" @success="handleSuccess"
                    :options="{ preserveScroll: true }">
                    <FieldGroup>
                        <Field :data-invalid="errors.code">
                            <FieldLabel for="create-code">Subject Code *</FieldLabel>
                            <Input
                                id="create-code"
                                v-model="form.code"
                                type="text"
                                placeholder="e.g., MATH, ENG, SCI"
                                class="uppercase bg-background"
                                maxlength="10"
                                @blur="normalizeCode"
                            />

                            <p class="mt-1 text-xs text-muted-foreground">
                                A unique identifier for the subject (typically 3-4 letters)
                            </p>

                            <InputError class="mt-1" :message="errors.code" />
                        </Field>
                    </FieldGroup>

                    <FieldGroup>
                        <Field :data-invalid="errors.name">
                            <FieldLabel for="create-name">Subject Name *</FieldLabel>
                            <Input
                                id="create-name"
                                v-model="form.name"
                                type="text"
                                placeholder="e.g., Mathematics, English Language"
                                class="bg-background" />
                            <InputError class="mt-1" :message="errors.name" />
                        </Field>
                    </FieldGroup>

                    <ModalFooter>
                        <Button type="button" variant="outline" @click="close">
                            Cancel
                        </Button>
                        <Button type="submit" :disabled="processing">
                            <Spinner v-if="processing" />
                            {{ processing ? 'Creating...' : 'Create Subject' }}
                        </Button>
                    </ModalFooter>
                </Form>
            </ModalScrollable>
        </ModalRoot>
    </Modal>
</template>
