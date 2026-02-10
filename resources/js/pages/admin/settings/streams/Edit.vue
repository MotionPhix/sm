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
import { update } from '@/routes/admin/settings/streams'
import { ref } from 'vue'
import { Spinner } from '@/components/ui/spinner'

interface Stream {
    id: number
    name: string
}

const props = defineProps<{
    stream: Stream
}>()

const editForm = ref()
const editModal = ref()

const form = ref({
    name: props.stream.name,
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
        <Head :title="`Edit ${props.stream.name}`" />

        <ModalRoot>
            <ModalHeader :title="`Edit Stream: ${props.stream.name}`"
                description="Update stream details" />

            <ModalScrollable>
                <Form ref="editForm" v-bind="{ url: update(props.stream.id).url, method: 'put', data: form }"
                    v-slot="{ errors, processing }" @success="handleSuccess"
                    :options="{ preserveScroll: true }">
                    <FieldGroup>
                        <Field :data-invalid="errors.name">
                            <FieldLabel for="edit-name">Stream Name *</FieldLabel>
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
                            {{ processing ? 'Updating...' : 'Update Stream' }}
                        </Button>
                    </ModalFooter>
                </Form>
            </ModalScrollable>
        </ModalRoot>
    </Modal>
</template>
