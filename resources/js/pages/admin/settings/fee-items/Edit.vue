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
import { Textarea } from '@/components/ui/textarea'
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select'
import { Checkbox } from '@/components/ui/checkbox'
import { update } from '@/routes/admin/settings/fee-items'
import { ref } from 'vue'
import { Spinner } from '@/components/ui/spinner'

interface FeeItem {
    id: number
    name: string
    code: string
    category: string
    description?: string
    is_mandatory: boolean
    is_active: boolean
}

interface Categories {
    [key: string]: string
}

const props = defineProps<{
    feeItem: FeeItem
    categories: Categories
}>()

const editForm = ref()
const editModal = ref()

const form = ref({
    name: props.feeItem.name,
    code: props.feeItem.code,
    category: props.feeItem.category,
    description: props.feeItem.description || '',
    is_mandatory: props.feeItem.is_mandatory,
    is_active: props.feeItem.is_active,
})

const handleSuccess = () => {
    editModal.value?.close()
    editForm.value?.resetAndClearErrors()
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
        <Head title="Edit Fee Item" />

        <ModalRoot>
            <ModalHeader title="Edit Fee Item"
                :description="`Update details for ${feeItem.name}`" />

            <ModalScrollable>
                <Form ref="editForm" v-bind="{ url: update(feeItem.id).url, method: 'put', data: form }"
                    v-slot="{ errors, processing }" @success="handleSuccess"
                    :options="{ preserveScroll: true }">
                    <FieldGroup>
                        <Field :data-invalid="errors.name">
                            <FieldLabel for="edit-name">Fee Item Name *</FieldLabel>
                            <Input 
                                id="edit-name" 
                                v-model="form.name" 
                                type="text" 
                                placeholder="e.g., Tuition, Exam Fees"
                                class="bg-background" />
                            <InputError class="mt-1" :message="errors.name" />
                        </Field>
                    </FieldGroup>

                    <FieldGroup class="grid grid-cols-2 gap-4">
                        <Field :data-invalid="errors.code">
                            <FieldLabel for="edit-code">Code *</FieldLabel>
                            <Input 
                                id="edit-code" 
                                v-model="form.code" 
                                type="text" 
                                placeholder="e.g., TUI"
                                class="uppercase bg-background"
                                maxlength="10"
                                @blur="normalizeCode" />
                            <InputError class="mt-1" :message="errors.code" />
                        </Field>

                        <Field :data-invalid="errors.category">
                            <FieldLabel for="edit-category">Category *</FieldLabel>
                            <Select v-model="form.category">
                                <SelectTrigger id="edit-category" class="bg-background">
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
                            <FieldLabel for="edit-description">Description</FieldLabel>
                            <Textarea 
                                id="edit-description" 
                                v-model="form.description" 
                                placeholder="Additional details about this fee"
                                class="bg-background"
                                rows="3" />
                            <InputError class="mt-1" :message="errors.description" />
                        </Field>
                    </FieldGroup>

                    <FieldGroup class="space-y-3">
                        <div class="flex items-center space-x-2">
                            <Checkbox 
                                id="edit-is-mandatory" 
                                v-model:checked="form.is_mandatory" />
                            <label 
                                for="edit-is-mandatory"
                                class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70 cursor-pointer">
                                Mandatory fee for all students
                            </label>
                        </div>
                        <div class="flex items-center space-x-2">
                            <Checkbox 
                                id="edit-is-active" 
                                v-model:checked="form.is_active" />
                            <label 
                                for="edit-is-active"
                                class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70 cursor-pointer">
                                Active (available for use)
                            </label>
                        </div>
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
