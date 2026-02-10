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
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select'
import { update } from '@/routes/admin/settings/school-profile'
import { ref  } from 'vue'
import { Spinner } from '@/components/ui/spinner'

interface SchoolData {
    id: number
    name: string
    code?: string
    email?: string
    phone?: string
    type?: string
    district?: string
    country?: string
}

interface SchoolTypeOption {
    value: string
    label: string
}

const props = defineProps<{
    school: SchoolData
    schoolTypes: SchoolTypeOption[]
}>()

const editForm = ref()
const editModal = ref()

const form = ref({
    name: props.school.name,
    code: props.school.code ?? '',
    email: props.school.email ?? '',
    phone: props.school.phone ?? '',
    type: props.school.type ?? '',
    district: props.school.district ?? '',
    country: props.school.country ?? 'Malawi',
})

const handleSuccess = () => {
    editModal.value?.close()
}
</script>

<template>
    <Modal
        ref="editModal"
        v-slot="{ close }"
        max-width="lg"
        :close-explicitly="true"
        :close-button="false">
        <Head title="Edit School Profile" />

        <ModalRoot>
            <ModalHeader title="Edit School Profile"
                description="Update your school's information" />

            <ModalScrollable>
                <Form ref="editForm" v-bind="{ url: update().url, method: 'put', data: form }"
                    v-slot="{ errors, processing }" @success="handleSuccess"
                    :options="{ preserveScroll: true }">
                    
                    <!-- Basic Information -->
                    <div class="mb-4">
                        <h3 class="text-sm font-semibold text-muted-foreground mb-3">Basic Information</h3>
                    </div>

                    <FieldGroup>
                        <Field :data-invalid="errors.name">
                            <FieldLabel for="edit-name">School Name *</FieldLabel>
                            <Input 
                                id="edit-name" 
                                v-model="form.name" 
                                type="text" 
                                class="bg-background" />
                            <InputError class="mt-1" :message="errors.name" />
                        </Field>
                    </FieldGroup>

                    <FieldGroup class="grid grid-cols-2 gap-4">
                        <Field :data-invalid="errors.code">
                            <FieldLabel for="edit-code">School Code</FieldLabel>
                            <Input 
                                id="edit-code" 
                                v-model="form.code" 
                                type="text" 
                                placeholder="Optional"
                                class="bg-background" />
                            <p class="mt-1 text-xs text-muted-foreground">
                                Official registration code
                            </p>
                            <InputError class="mt-1" :message="errors.code" />
                        </Field>

                        <Field :data-invalid="errors.type">
                            <FieldLabel for="edit-type">School Type</FieldLabel>
                            <Select v-model="form.type">
                                <SelectTrigger id="edit-type" class="bg-background">
                                    <SelectValue placeholder="Select school type" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem
                                        v-for="type in props.schoolTypes"
                                        :key="type.value"
                                        :value="type.value">
                                        {{ type.label }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <InputError class="mt-1" :message="errors.type" />
                        </Field>
                    </FieldGroup>

                    <!-- Contact Information -->
                    <div class="mb-4 mt-6 pt-4 border-t">
                        <h3 class="text-sm font-semibold text-muted-foreground mb-3">Contact Information</h3>
                    </div>

                    <FieldGroup class="grid grid-cols-2 gap-4">
                        <Field :data-invalid="errors.email">
                            <FieldLabel for="edit-email">Email Address</FieldLabel>
                            <Input 
                                id="edit-email" 
                                v-model="form.email" 
                                type="email" 
                                placeholder="school@example.com"
                                class="bg-background" />
                            <InputError class="mt-1" :message="errors.email" />
                        </Field>

                        <Field :data-invalid="errors.phone">
                            <FieldLabel for="edit-phone">Phone Number</FieldLabel>
                            <Input 
                                id="edit-phone" 
                                v-model="form.phone" 
                                type="tel" 
                                placeholder="+265 xxx xxx xxx"
                                class="bg-background" />
                            <InputError class="mt-1" :message="errors.phone" />
                        </Field>
                    </FieldGroup>

                    <!-- Location -->
                    <div class="mb-4 mt-6 pt-4 border-t">
                        <h3 class="text-sm font-semibold text-muted-foreground mb-3">Location</h3>
                    </div>

                    <FieldGroup class="grid grid-cols-2 gap-4">
                        <Field :data-invalid="errors.district">
                            <FieldLabel for="edit-district">District</FieldLabel>
                            <Input 
                                id="edit-district" 
                                v-model="form.district" 
                                type="text" 
                                placeholder="e.g., Lilongwe"
                                class="bg-background" />
                            <InputError class="mt-1" :message="errors.district" />
                        </Field>

                        <Field :data-invalid="errors.country">
                            <FieldLabel for="edit-country">Country</FieldLabel>
                            <Input 
                                id="edit-country" 
                                v-model="form.country" 
                                type="text" 
                                placeholder="e.g., Malawi"
                                class="bg-background" />
                            <InputError class="mt-1" :message="errors.country" />
                        </Field>
                    </FieldGroup>

                    <ModalFooter>
                        <Button type="button" variant="outline" @click="close">
                            Cancel
                        </Button>
                        <Button type="submit" :disabled="processing">
                            <Spinner v-if="processing" />
                            {{ processing ? 'Updating...' : 'Update Profile' }}
                        </Button>
                    </ModalFooter>
                </Form>
            </ModalScrollable>
        </ModalRoot>
    </Modal>
</template>
