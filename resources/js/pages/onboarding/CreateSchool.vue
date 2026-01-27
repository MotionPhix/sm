<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { Form, Head } from '@inertiajs/vue3'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import InputError from '@/components/InputError.vue'
import AuthBase from '@/layouts/AuthLayout.vue'
import { store } from '@/routes/onboarding/school-setup'
import { Field, FieldGroup, FieldLabel } from '@/components/ui/field'
import { Separator } from '@/components/ui/separator'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { Spinner } from '@/components/ui/spinner'
import { ArrowRight } from 'lucide-vue-next'

const schoolTypes = ref<Array<{ value: string; label: string }>>([])
const countries = ref<Array<{ value: string; label: string }>>([])
const regions = ref<Array<{ value: string; label: string }>>([])
const districts = ref<Array<{ value: string; label: string }>>([])

const selectedCountry = ref('')
const selectedRegion = ref('')
const isLoading = ref(true)

onMounted(async () => {
    try {
        const [typesRes, countriesRes] = await Promise.all([
            fetch('/api/onboarding/school-types'),
            fetch('/api/onboarding/countries'),
        ])

        schoolTypes.value = (await typesRes.json()).data
        countries.value = (await countriesRes.json()).data
    } catch (error) {
        console.error('Failed to load onboarding data:', error)
    } finally {
        isLoading.value = false
    }
})

const onCountryChange = async (country: string) => {
    selectedCountry.value = country
    selectedRegion.value = ''
    districts.value = []
    regions.value = []

    if (!country) return

    try {
        const response = await fetch(
            `/api/onboarding/regions?country=${encodeURIComponent(country)}`
        )
        regions.value = (await response.json()).data
    } catch (error) {
        console.error('Failed to load regions:', error)
    }
}

const onRegionChange = async (region: string) => {
    selectedRegion.value = region
    districts.value = []

    if (!selectedCountry.value || !region) return

    try {
        const response = await fetch(
            `/api/onboarding/districts?country=${encodeURIComponent(selectedCountry.value)}&region=${encodeURIComponent(region)}`
        )
        districts.value = (await response.json()).data
    } catch (error) {
        console.error('Failed to load districts:', error)
    }
}
</script>

<template>
    <AuthBase
        title="1. Create your school"
        description="Set up your school to begin managing students and staff"
    >
        <Head title="Create School" />

        <Form
            v-bind="store.form()"
            v-slot="{ errors, processing }"
        >
            <FieldGroup>
                <Field :data-invalid="errors.name">
                    <FieldLabel for="school_name">School name</FieldLabel>
                    <Input
                        id="school_name"
                        name="name"
                        placeholder="e.g., St. John's Primary School"
                        required
                    />
                    <InputError :message="errors.name" />
                </Field>

                <Field :data-invalid="errors.type">
                    <FieldLabel>School type</FieldLabel>

                    <Select name="type">
                        <SelectTrigger class="w-full">
                            <SelectValue placeholder="Pick a school type" />
                        </SelectTrigger>

                        <SelectContent>
                            <SelectItem
                                v-for="schoolType in schoolTypes"
                                :key="schoolType.value"
                                :value="schoolType.value" >
                                {{ schoolType.label }}
                            </SelectItem>
                        </SelectContent>
                    </Select>

                    <InputError :message="errors.type" />
                </Field>

                <Field :data-invalid="errors.country">
                    <FieldLabel>Country</FieldLabel>

                    <Select
                        :model-value="selectedCountry"
                        name="country"
                        @update:model-value="onCountryChange">
                        <SelectTrigger class="w-full">
                            <SelectValue placeholder="Select country" />
                        </SelectTrigger>

                        <SelectContent>
                            <SelectItem
                                v-for="country in countries"
                                :key="country.value"
                                :value="country.value">
                                {{ country.label }}
                            </SelectItem>
                        </SelectContent>
                    </Select>

                    <InputError :message="errors.country" />
                </Field>

                <Field :data-invalid="errors.region">
                    <FieldLabel>Region</FieldLabel>

                    <Select
                        :model-value="selectedRegion"
                        name="region"
                        @update:model-value="onRegionChange"
                        :disabled="!selectedCountry">
                        <SelectTrigger class="w-full">
                            <SelectValue
                                :placeholder="
                                    !selectedCountry
                                        ? 'Select country first'
                                        : 'Select region'
                                "
                            />
                        </SelectTrigger>

                        <SelectContent>
                            <SelectItem
                                v-for="region in regions"
                                :key="region.value"
                                :value="region.value"
                            >
                                {{ region.label }}
                            </SelectItem>
                        </SelectContent>
                    </Select>

                    <InputError :message="errors.region" />
                </Field>

                <Field :data-invalid="errors.district">
                    <FieldLabel>District</FieldLabel>

                    <Select
                        name="district"
                        :disabled="!selectedRegion"
                    >
                        <SelectTrigger class="w-full">
                            <SelectValue
                                :placeholder="
                                    !selectedRegion
                                        ? 'Select region first'
                                        : 'Select district'
                                "
                            />
                        </SelectTrigger>

                        <SelectContent>
                            <SelectItem
                                v-for="district in districts"
                                :key="district.value"
                                :value="district.value"
                            >
                                {{ district.label }}
                            </SelectItem>
                        </SelectContent>
                    </Select>

                    <InputError :message="errors.district" />
                </Field>

                <Field :data-invalid="errors.email">
                    <FieldLabel for="email">Email (optional)</FieldLabel>
                    <Input
                        id="email"
                        name="email"
                        type="email"
                        placeholder="school@example.com"
                    />
                    <InputError :message="errors.email" />
                </Field>

                <Field :data-invalid="errors.phone">
                    <FieldLabel for="phone">Phone (optional)</FieldLabel>
                    <Input
                        id="phone"
                        name="phone"
                        type="tel"
                        placeholder="+265 1 234 5678"
                    />
                    <InputError :message="errors.phone" />
                </Field>
            </FieldGroup>

            <Separator class="my-6" />

            <Button
                class="h-12 w-full"
                type="submit"
                :disabled="processing || isLoading">
                <Spinner v-if="processing" />
                {{ processing ? 'Creating...' : 'Continue' }}
                <ArrowRight />
            </Button>
        </Form>
    </AuthBase>
</template>
