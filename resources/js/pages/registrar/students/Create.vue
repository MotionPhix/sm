<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3'
import { ref } from 'vue'
import AppLayout from '@/layouts/AppLayout.vue'
import Heading from '@/components/Heading.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { store as storeRoute } from '@/routes/registrar/students'

interface Applicant {
    id: number
    first_name: string
    last_name: string
    date_of_birth: string
    gender: string
    admission_cycle?: {
        name: string
    }
}

const props = defineProps<{
    admittedApplicants: Applicant[]
    currentAcademicYear?: { id: number; name: string }
}>()

const breadcrumbs = [
    { title: 'Dashboard', href: '/registrar/dashboard' },
    { title: 'Students', href: '/registrar/students' },
    { title: 'Add Student', href: '#' },
]

const form = ref({
    applicant_id: '',
    first_name: '',
    middle_name: '',
    last_name: '',
    gender: '',
    date_of_birth: '',
    admission_date: new Date().toISOString().split('T')[0],
    national_id: '',
})

const errors = ref<Record<string, string>>({})
const selectedFromApplicant = ref(false)

function selectApplicant(applicant: Applicant) {
    form.value.applicant_id = String(applicant.id)
    form.value.first_name = applicant.first_name
    form.value.last_name = applicant.last_name
    form.value.date_of_birth = applicant.date_of_birth
    form.value.gender = applicant.gender
    selectedFromApplicant.value = true
}

function clearApplicantSelection() {
    form.value.applicant_id = ''
    form.value.first_name = ''
    form.value.last_name = ''
    form.value.date_of_birth = ''
    form.value.gender = ''
    selectedFromApplicant.value = false
}

function submit() {
    router.post(storeRoute().url, form.value, {
        onError: (err) => {
            errors.value = err
        },
    })
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Add Student" />

        <article class="p-5 max-w-3xl mx-auto">
            <Heading title="Add Student" description="Enroll a new student" />

            <form @submit.prevent="submit" class="mt-6 space-y-6">
                <!-- From Applicant Selection -->
                <Card v-if="admittedApplicants.length > 0">
                    <CardHeader>
                        <CardTitle>Enroll from Applicant</CardTitle>
                        <CardDescription>
                            Select an admitted applicant to convert to a student
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div v-if="!selectedFromApplicant" class="space-y-2">
                            <div
                                v-for="applicant in admittedApplicants"
                                :key="applicant.id"
                                class="flex items-center justify-between p-3 border rounded-lg cursor-pointer hover:bg-muted/50 transition-colors"
                                @click="selectApplicant(applicant)">
                                <div>
                                    <p class="font-medium">{{ applicant.first_name }} {{ applicant.last_name }}</p>
                                    <p class="text-sm text-muted-foreground">
                                        {{ applicant.admission_cycle?.name || 'No cycle' }} • DOB: {{ applicant.date_of_birth }}
                                    </p>
                                </div>
                                <Button variant="outline" size="sm">Select</Button>
                            </div>
                            <div v-if="admittedApplicants.length === 0" class="text-center py-4 text-muted-foreground">
                                No admitted applicants available
                            </div>
                        </div>
                        <div v-else class="flex items-center justify-between p-3 bg-muted/50 rounded-lg">
                            <div>
                                <p class="font-medium">
                                    {{ admittedApplicants.find(a => a.id === Number(form.applicant_id))?.first_name }}
                                    {{ admittedApplicants.find(a => a.id === Number(form.applicant_id))?.last_name }}
                                </p>
                                <p class="text-sm text-muted-foreground">Selected applicant</p>
                            </div>
                            <Button variant="outline" size="sm" @click="clearApplicantSelection">
                                Change Selection
                            </Button>
                        </div>
                    </CardContent>
                </Card>

                <!-- Student Details -->
                <Card>
                    <CardHeader>
                        <CardTitle>Student Details</CardTitle>
                        <CardDescription>Enter the student's personal information</CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="grid gap-4 md:grid-cols-2">
                            <div class="space-y-2">
                                <Label for="first_name">First Name *</Label>
                                <Input
                                    id="first_name"
                                    v-model="form.first_name"
                                    :class="{ 'border-destructive': errors.first_name }"
                                />
                                <p v-if="errors.first_name" class="text-sm text-destructive">{{ errors.first_name }}</p>
                            </div>

                            <div class="space-y-2">
                                <Label for="middle_name">Middle Name</Label>
                                <Input id="middle_name" v-model="form.middle_name" />
                            </div>
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
                            <div class="space-y-2">
                                <Label for="last_name">Last Name *</Label>
                                <Input
                                    id="last_name"
                                    v-model="form.last_name"
                                    :class="{ 'border-destructive': errors.last_name }"
                                />
                                <p v-if="errors.last_name" class="text-sm text-destructive">{{ errors.last_name }}</p>
                            </div>

                            <div class="space-y-2">
                                <Label for="national_id">National ID</Label>
                                <Input id="national_id" v-model="form.national_id" />
                            </div>
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
                            <div class="space-y-2">
                                <Label for="gender">Gender *</Label>
                                <Select v-model="form.gender">
                                    <SelectTrigger :class="{ 'border-destructive': errors.gender }">
                                        <SelectValue placeholder="Select gender" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="male">Male</SelectItem>
                                        <SelectItem value="female">Female</SelectItem>
                                    </SelectContent>
                                </Select>
                                <p v-if="errors.gender" class="text-sm text-destructive">{{ errors.gender }}</p>
                            </div>

                            <div class="space-y-2">
                                <Label for="date_of_birth">Date of Birth *</Label>
                                <Input
                                    id="date_of_birth"
                                    type="date"
                                    v-model="form.date_of_birth"
                                    :class="{ 'border-destructive': errors.date_of_birth }"
                                />
                                <p v-if="errors.date_of_birth" class="text-sm text-destructive">{{ errors.date_of_birth }}</p>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <Label for="admission_date">Admission Date *</Label>
                            <Input
                                id="admission_date"
                                type="date"
                                v-model="form.admission_date"
                                :class="{ 'border-destructive': errors.admission_date }"
                            />
                            <p v-if="errors.admission_date" class="text-sm text-destructive">{{ errors.admission_date }}</p>
                        </div>
                    </CardContent>
                </Card>

                <div class="flex justify-end gap-4">
                    <Button variant="outline" type="button" :href="'/registrar/students'">
                        Cancel
                    </Button>
                    <Button type="submit">
                        Create Student
                    </Button>
                </div>
            </form>
        </article>
    </AppLayout>
</template>
