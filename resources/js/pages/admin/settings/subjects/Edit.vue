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
import { update } from '@/routes/admin/settings/subjects'
import { ref } from 'vue'
import { Spinner } from '@/components/ui/spinner'

interface Subject {
    id: number
    name: string
    code: string
}

const props = defineProps<{
    subject: Subject
}>()

const editForm = ref()
const editModal = ref()

const form = ref({
    name: props.subject.name,
    code: props.subject.code,
})

const handleSuccess = () => {
    editModal.value?.close()
}

const normalizeCode = () => {
    form.value.code = form.value.code.toUpperCase().replace(/[^A-Z0-9]/g, '')
}
</script>

<template>
    <Modal
        ref="editModal"
        v-slot="{ close }"
        max-width="md"
        :close-explicitly="true"
        :close-button="false">
        <Head :title="`Edit ${props.subject.name}`" />

        <ModalRoot>
            <ModalHeader :title="`Edit Subject: ${props.subject.name}`"
                description="Update subject details" />

            <ModalScrollable>
                <Form ref="editForm" v-bind="{ url: update(props.subject.id).url, method: 'put', data: form }"
                    v-slot="{ errors, processing }" @success="handleSuccess"
                    :options="{ preserveScroll: true }">
                    <FieldGroup>
                        <Field :data-invalid="errors.code">
                            <FieldLabel for="edit-code">Subject Code *</FieldLabel>
                            <Input 
                                id="edit-code" 
                                v-model="form.code" 
                                type="text" 
                                class="uppercase bg-background"
                                maxlength="10"
                                @blur="normalizeCode" />
                            <InputError class="mt-1" :message="errors.code" />
                        </Field>
                    </FieldGroup>

                    <FieldGroup>
                        <Field :data-invalid="errors.name">
                            <FieldLabel for="edit-name">Subject Name *</FieldLabel>
                            <Input 
                                id="edit-name" 
                                v-model="form.name" 
                                type="text" 
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
                            {{ processing ? 'Updating...' : 'Update Subject' }}
                        </Button>
                    </ModalFooter>
                </Form>
            </ModalScrollable>
        </ModalRoot>
    </Modal>
</template>
