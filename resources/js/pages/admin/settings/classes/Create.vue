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
import { store } from '@/routes/admin/settings/classes'
import { ref } from 'vue'
import { Spinner } from '@/components/ui/spinner'

const props = defineProps<{
    nextOrder: number
}>()

const createForm = ref()
const createModal = ref()

const form = ref({
    name: '',
    order: props.nextOrder,
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
        <Head title="Add Class" />

        <ModalRoot>
            <ModalHeader title="Add Class"
                description="Create a new class level for your school" />

            <ModalScrollable>
                <Form ref="createForm" v-bind="{ url: store().url, method: 'post', data: form }"
                    v-slot="{ errors, processing }" @success="handleSuccess"
                    :options="{ preserveScroll: true }">
                    <FieldGroup>
                        <Field :data-invalid="errors.name">
                            <FieldLabel for="create-name">Class Name *</FieldLabel>
                            <Input 
                                id="create-name" 
                                v-model="form.name" 
                                type="text" 
                                placeholder="e.g., Form 1, Standard 3"
                                class="bg-background" />
                            <InputError class="mt-1" :message="errors.name" />
                        </Field>
                    </FieldGroup>

                    <FieldGroup>
                        <Field :data-invalid="errors.order">
                            <FieldLabel for="create-order">Order *</FieldLabel>
                            <Input 
                                id="create-order" 
                                v-model.number="form.order" 
                                type="number"
                                min="1"
                                class="bg-background" />
                            <p class="mt-1 text-xs text-muted-foreground">
                                Determines the sequence of classes (lower numbers appear first)
                            </p>
                            <InputError class="mt-1" :message="errors.order" />
                        </Field>
                    </FieldGroup>

                    <ModalFooter>
                        <Button type="button" variant="outline" @click="close">
                            Cancel
                        </Button>
                        <Button type="submit" :disabled="processing">
                            <Spinner v-if="processing" />
                            {{ processing ? 'Creating...' : 'Create Class' }}
                        </Button>
                    </ModalFooter>
                </Form>
            </ModalScrollable>
        </ModalRoot>
    </Modal>
</template>
