<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import { Modal } from '@inertiaui/modal-vue';
import InputError from '@/components/InputError.vue';

import {
    ModalFooter,
    ModalHeader,
    ModalRoot,
    ModalScrollable,
} from '@/components/modal';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';

import { Field, FieldGroup, FieldLabel } from '@/components/ui/field';
import { store } from '@/routes/admin/staff';
import { ref } from 'vue';
import { rand } from '@vueuse/core';
import { Spinner } from '@/components/ui/spinner';

interface Role {
    id: number;
    name: string;
    label: string;
}

defineProps<{
    roles: Role[];
}>();

const inviteForm = ref()
const inviteModal = ref()

const handleSuccess = () => {
    inviteModal.value?.close()
    inviteForm.value?.resetAndClearErrors()
}
</script>

<template>
    <Modal
        ref="inviteModal"
        v-slot="{ close }"
        max-width="md"
        :close-explicitly="true"
        :close-button="false">
        <Head title="Invite Staff" />

        <ModalRoot>
            <ModalHeader
                title="Invite staff member"
                description="Invite a user to join this school"
            />

            <ModalScrollable>
                <Form
                    ref="inviteForm"
                    disableWhileProcessing
                    v-bind="store.form()"
                    v-slot="{ errors, processing }"
                    @success="handleSuccess"
                    :options="{
                        preserveScroll: true,
                        preserveState: false
                    }">
                    <FieldGroup>
                        <Field :data-invalid="errors.name">
                            <FieldLabel for="name">Invitee name</FieldLabel>
                            <Input
                                id="name"
                                name="name"
                                :placeholder="[
                                    'Chiyembekezo Kapatamoyo',
                                    'Chrissy Kamthunzi',
                                    'Moffat Moyo',
                                    'Lameck Patson'
                                ][rand(0, 3)]"
                            />
                            <InputError :message="errors.email" />
                        </Field>

                        <Field :data-invalid="errors.email">
                            <FieldLabel for="email">Email address</FieldLabel>
                            <Input
                                id="email"
                                name="email"
                                type="email"
                                placeholder="staff@example.com"
                            />
                            <InputError :message="errors.email" />
                        </Field>

                        <Field :data-invalid="errors.role_id">
                            <FieldLabel for="role_id">Role</FieldLabel>

                            <Select name="role_id">
                                <SelectTrigger class="w-full">
                                    <SelectValue placeholder="Select role" />
                                </SelectTrigger>

                                <SelectContent>
                                    <SelectItem
                                        v-for="role in roles"
                                        :key="role.id"
                                        :value="String(role.id)">
                                        {{ role.label }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>

                            <InputError :message="errors.role_id" />
                        </Field>
                    </FieldGroup>

                    <div class="flex items-center justify-end gap-4">
                    </div>
                </Form>
            </ModalScrollable>

            <ModalFooter>

                <Button
                    @click="inviteForm?.submit"
                    :disabled="inviteForm?.processing">
                    <Spinner v-if="inviteForm?.processing" />
                    {{ inviteForm?.processing ? 'Processing ...': 'Send invitation' }}
                </Button>

                <Button
                    type="button"
                    variant="outline"
                    @click="close">
                    Cancel
                </Button>
            </ModalFooter>
        </ModalRoot>
    </Modal>
</template>
