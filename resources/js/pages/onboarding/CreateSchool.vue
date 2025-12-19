<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import InputError from '@/components/InputError.vue'
import AuthBase from '@/layouts/AuthLayout.vue'
import { store } from '@/routes/onboarding/school-setup';
import { Field, FieldGroup, FieldLabel } from '@/components/ui/field'
import { Separator } from '@/components/ui/separator'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'

const schoolTypes = [
    { value: 'public_primary', label: 'Public Primary School' },
    { value: 'private_primary', label: 'Private Primary School' },
    { value: 'public_secondary', label: 'Public Secondary School' },
    { value: 'private_secondary', label: 'Private Secondary School' },
    { value: 'combined_private', label: 'Combined Private School' },
    { value: 'combined_public', label: 'Combined Public School' }
]
</script>

<template>
    <AuthBase
        title="Create your school"
        description="Set up your school to begin managing students and staff"
    >
        <Head title="Create School" />

        <Form
            v-bind="store.form()"
            v-slot="{ errors, processing }">
            <FieldGroup>
                <Field :data-invalid="errors.name">
                    <FieldLabel for="school_name">School name</FieldLabel>
                    <Input id="school_name" name="name" required />
                    <InputError :message="errors.name" />
                </Field>
                
                <Field :data-invalid="errors.type">
                    <FieldLabel>School type</FieldLabel>
                    
                    <Select 
                        name="type">
                        <SelectTrigger 
                            class="w-full">
                            <SelectValue 
                                placeholder="Pick a school type" 
                            />
                        </SelectTrigger>

                        <SelectContent>
                            <SelectItem 
                                :value="schoolType.value" 
                                v-for="schoolType in schoolTypes" 
                                :key="schoolType.value">
                                {{ schoolType.label }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    
                    <InputError :message="errors.type" />
                </Field>
                
                <Field :data-invalid="errors.country">
                    <FieldLabel for="country">Country</FieldLabel>
                    <Input id="country" name="country" />
                    <InputError :message="errors.country" />
                </Field>
                
                <Field :data-invalid="errors.state">
                    <FieldLabel for="state">Region</FieldLabel>
                    <Input id="state" name="state" />
                    <InputError :message="errors.state" />
                </Field>
                
                <Field :data-invalid="errors.district">
                    <FieldLabel for="district">District</FieldLabel>
                    <Input id="district" name="district" />
                    <InputError :message="errors.district" />
                </Field>
                
                <Field :data-invalid="errors.email">
                    <FieldLabel for="email">Email</FieldLabel>
                    <Input id="email" name="email" />
                    <InputError :message="errors.email" />
                </Field>
            </FieldGroup>

            <Separator class="my-6" />

            <Button type="submit" class="w-full" :disabled="processing">
                {{ processing ? 'Creating...' : 'Create School' }}
            </Button>
        </form>
    </AuthBase>
</template>
