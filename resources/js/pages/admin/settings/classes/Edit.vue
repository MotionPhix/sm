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
import { update } from '@/routes/admin/settings/classes'
import { ref } from 'vue'
import { Spinner } from '@/components/ui/spinner'

interface SchoolClass {
    id: number
    name: string
    order: number
}

const props = defineProps<{
    schoolClass: SchoolClass
}>()

const editForm = ref()
const editModal = ref()

const form = ref({
    name: props.schoolClass.name,
    order: props.schoolClass.order,
})

const handleSuccess = () => {
    editModal.value?.close()
}
</script>

<template>
    <Modal
        ref="editModal"
        v-slot="{ close }"
        max-width="md"
        :close-explicitly="true"
        :close-button="false">
        <Head :title="`Edit ${props.schoolClass.name}`" />

        <ModalRoot>
            <ModalHeader :title="`Edit ${props.schoolClass.name}`"
                description="Update class details" />

            <ModalScrollable>
                <Form ref="editForm" v-bind="{ url: update(props.schoolClass.id).url, method: 'put', data: form }"
                    v-slot="{ errors, processing }" @success="handleSuccess"
                    :options="{ preserveScroll: true }">
                    <FieldGroup>
                        <Field :data-invalid="errors.name">
                            <FieldLabel for="edit-name">Class Name *</FieldLabel>
                            <Input 
                                id="edit-name" 
                                v-model="form.name" 
                                type="text" 
                                class="bg-background" />
                            <InputError class="mt-1" :message="errors.name" />
                        </Field>
                    </FieldGroup>

                    <FieldGroup>
                        <Field :data-invalid="errors.order">
                            <FieldLabel for="edit-order">Order *</FieldLabel>
                            <Input 
                                id="edit-order" 
                                v-model.number="form.order" 
                                type="number"
                                min="1"
                                class="bg-background" />
                            <p class="mt-1 text-xs text-muted-foreground">
                                Determines the sequence of classes
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
                            {{ processing ? 'Updating...' : 'Update Class' }}
                        </Button>
                    </ModalFooter>
                </Form>
            </ModalScrollable>
        </ModalRoot>
    </Modal>
</template>
