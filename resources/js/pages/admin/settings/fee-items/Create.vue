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
import { store } from '@/routes/admin/settings/fee-items'
import { ref } from 'vue'
import { Spinner } from '@/components/ui/spinner'

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
        max-width="md"
        :close-explicitly="true"
        :close-button="false">
        <Head title="Create Fee Item" />

        <ModalRoot>
            <ModalHeader title="Create Fee Item"
                description="Define a new fee category to charge students" />

            <ModalScrollable>
                <Form ref="createForm" v-bind="{ url: store().url, method: 'post', data: form }"
                    v-slot="{ errors, processing }" @success="handleSuccess"
                    :options="{ preserveScroll: true }">
                    <FieldGroup>
                        <Field :data-invalid="errors.name">
                            <FieldLabel for="create-name">Fee Item Name *</FieldLabel>
                            <Input 
                                id="create-name" 
                                v-model="form.name" 
                                type="text" 
                                placeholder="e.g., Tuition, Exam Fees, Development Levy"
                                class="bg-background" />
                            <InputError class="mt-1" :message="errors.name" />
                        </Field>
                    </FieldGroup>

                    <FieldGroup class="grid grid-cols-2 gap-4">
                        <Field :data-invalid="errors.code">
                            <FieldLabel for="create-code">Code *</FieldLabel>
                            <Input 
                                id="create-code" 
                                v-model="form.code" 
                                type="text" 
                                placeholder="e.g., TUI"
                                class="uppercase bg-background"
                                maxlength="10"
                                @blur="normalizeCode" />
                            <InputError class="mt-1" :message="errors.code" />
                        </Field>

                        <Field :data-invalid="errors.category">
                            <FieldLabel for="create-category">Category *</FieldLabel>
                            <Select v-model="form.category">
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
                                id="is-mandatory" 
                                v-model:checked="form.is_mandatory" />
                            <label 
                                for="is-mandatory"
                                class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70 cursor-pointer">
                                Mandatory fee for all students
                            </label>
                        </div>
                        <div class="flex items-center space-x-2">
                            <Checkbox 
                                id="is-active" 
                                v-model:checked="form.is_active" />
                            <label 
                                for="is-active"
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
                            {{ processing ? 'Creating...' : 'Create Fee Item' }}
                        </Button>
                    </ModalFooter>
                </Form>
            </ModalScrollable>
        </ModalRoot>
    </Modal>
</template>
