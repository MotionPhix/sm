<script setup lang="ts">
import InputError from '@/components/InputError.vue'
import FileUploader from '@/components/FileUploader.vue'
import {
    ModalFooter,
    ModalHeader,
    ModalRoot,
    ModalScrollable,
} from '@/components/modal'
import { Button } from '@/components/ui/button'
import { Field, FieldGroup, FieldLabel } from '@/components/ui/field'
import { Input } from '@/components/ui/input'
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select'
import { Spinner } from '@/components/ui/spinner'
import { update } from '@/routes/admin/settings/school-profile'
import { Head, useForm } from '@inertiajs/vue3'
import { Modal } from '@inertiaui/modal-vue'
import { ref } from 'vue'

interface SchoolData {
    id: number
    name: string
    code?: string
    email?: string
    phone?: string
    type?: string
    district?: string
    country?: string
    logo_url?: string | null
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

const form = useForm({
    _method: 'put' as const,
    name: props.school.name,
    code: props.school.code ?? '',
    email: props.school.email ?? '',
    phone: props.school.phone ?? '',
    type: props.school.type ?? '',
    district: props.school.district ?? '',
    country: props.school.country ?? 'Malawi',
    logo: null as File | null,
})

const submit = () => {
    form.post(update().url, {
        onSuccess: () => {
            editModal.value?.close()
        },
    })
}
</script>

<template>
    <Modal
        ref="editModal"
        v-slot="{ close }"
        max-width="xl"
        :close-explicitly="true"
        :close-button="false">
        <Head title="Edit School Profile" />

        <ModalRoot>
            <ModalHeader
                title="Edit School Profile"
                description="Update your school's information"
            />

            <ModalScrollable>
                <form
                    ref="editForm"
                    class="inert:opacity-50 inert:pointer-events-none"
                    :options="{ preserveScroll: true }">

                    <!-- Logo Upload -->
                    <div class="mb-6">
                        <FileUploader
                            v-model="form.logo"
                            label="School Logo"
                            :accept="['image/jpeg', 'image/png', 'image/svg+xml']"
                            max-file-size="2MB"
                            :max-files="1"
                            collection-name="logo"
                            :image-preview-height="120"
                            :existing-media="props.school.logo_url ? [{ id: 1, url: props.school.logo_url, name: 'logo' }] : []"
                            :error="form.errors.logo"
                        />
                    </div>

                    <!-- Basic Information -->
                    <div class="mb-4">
                        <h3 class="text-sm font-semibold text-muted-foreground mb-3">Basic Information</h3>
                    </div>

                    <FieldGroup>
                        <Field :data-invalid="form.errors.name">
                            <FieldLabel for="edit-name">School Name *</FieldLabel>
                            <Input
                                id="edit-name"
                                v-model="form.name"
                                type="text"
                                class="bg-background" />
                            <InputError class="mt-1" :message="form.errors.name" />
                        </Field>

                        <div class="grid grid-cols-2 gap-4">
                            <Field :data-invalid="form.errors.code">
                                <FieldLabel for="edit-code">School Code</FieldLabel>
                                <Input
                                    id="edit-code"
                                    v-model="form.code"
                                    type="text"
                                    placeholder="Optional"
                                    class="bg-background"
                                />

                                <p class="mt-1 text-xs text-muted-foreground">
                                    Official registration code
                                </p>
                                <InputError class="mt-1" :message="form.errors.code" />
                            </Field>

                            <Field :data-invalid="form.errors.type">
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
                                <InputError class="mt-1" :message="form.errors.type" />
                            </Field>
                        </div>
                    </FieldGroup>

                    <!-- Contact Information -->
                    <div class="mb-4 mt-6 pt-4 border-t">
                        <h3 class="text-sm font-semibold text-muted-foreground mb-3">Contact Information</h3>
                    </div>

                    <FieldGroup class="grid grid-cols-2 gap-4">
                        <Field :data-invalid="form.errors.email">
                            <FieldLabel for="edit-email">Email Address</FieldLabel>
                            <Input
                                id="edit-email"
                                v-model="form.email"
                                type="email"
                                placeholder="school@example.com"
                                class="bg-background" />
                            <InputError class="mt-1" :message="form.errors.email" />
                        </Field>

                        <Field :data-invalid="form.errors.phone">
                            <FieldLabel for="edit-phone">Phone Number</FieldLabel>
                            <Input
                                id="edit-phone"
                                v-model="form.phone"
                                type="tel"
                                placeholder="+265 xxx xxx xxx"
                                class="bg-background" />
                            <InputError class="mt-1" :message="form.errors.phone" />
                        </Field>
                    </FieldGroup>

                    <!-- Location -->
                    <div class="mb-4 mt-6 pt-4 border-t">
                        <h3 class="text-sm font-semibold text-muted-foreground mb-3">Location</h3>
                    </div>

                    <FieldGroup class="grid grid-cols-2 gap-4">
                        <Field :data-invalid="form.errors.district">
                            <FieldLabel for="edit-district">District</FieldLabel>
                            <Input
                                id="edit-district"
                                v-model="form.district"
                                type="text"
                                placeholder="e.g., Lilongwe"
                                class="bg-background" />
                            <InputError class="mt-1" :message="form.errors.district" />
                        </Field>

                        <Field :data-invalid="form.errors.country">
                            <FieldLabel for="edit-country">Country</FieldLabel>
                            <Input
                                id="edit-country"
                                v-model="form.country"
                                type="text"
                                placeholder="e.g., Malawi"
                                class="bg-background" />
                            <InputError class="mt-1" :message="form.errors.country" />
                        </Field>
                    </FieldGroup>
                </form>
            </ModalScrollable>

            <ModalFooter>
                <Button type="button" variant="outline" @click="close">
                    Cancel
                </Button>

                <Button
                    @click="submit"
                    :disabled="form.processing">
                    <Spinner v-if="form.processing" />
                    {{ form.processing ? 'Updating...' : 'Update Profile' }}
                </Button>
            </ModalFooter>
        </ModalRoot>
    </Modal>
</template>
