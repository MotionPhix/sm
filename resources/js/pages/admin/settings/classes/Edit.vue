<script setup lang="ts">
import { Form, Head, useForm } from '@inertiajs/vue3'
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
import { Field, FieldDescription, FieldGroup, FieldLabel } from '@/components/ui/field'
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

const form = useForm({
    name: props.schoolClass.name,
    order: props.schoolClass.order,
})

const handleSuccess = () => {
    form.put(update(props.schoolClass.id).url, {
        onSuccess: () => {
            editModal.value?.close()
        }
    })
}

const updateClass = () => {

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
                <form>
                    <FieldGroup>
                        <Field :data-invalid="form.errors.name">
                            <FieldLabel for="edit-name">Class Name *</FieldLabel>
                            <Input 
                                id="edit-name" 
                                v-model="form.name" 
                                type="text" 
                                class="bg-background" 
                            />
                            
                            <InputError class="mt-1" :message="form.errors.name" />
                        </Field>
                        
                        <Field :data-invalid="form.errors.order">
                            <FieldLabel for="edit-order">Order *</FieldLabel>
                            <Input 
                                id="edit-order" 
                                v-model.number="form.order" 
                                type="number"
                                min="1"
                                class="bg-background" />
                            <FieldDescription class="text-xs text-muted-foreground">
                                Determines the sequence of classes
                            </FieldDescription>
                            <InputError :message="form.errors.order" />
                        </Field>
                    </FieldGroup>
                </form>
            </ModalScrollable>

            <ModalFooter>
                <Button type="button" variant="outline" @click="close">
                    Cancel
                </Button>

                <Button 
                    type="submit" 
                    @click="editForm.submit()"
                    :disabled="form.processing">
                    <Spinner v-if="form.processing" />
                    {{ form.processing ? 'Updating...' : 'Update Class' }}
                </Button>
            </ModalFooter>
        </ModalRoot>
    </Modal>
</template>
