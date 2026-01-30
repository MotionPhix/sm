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
import { update as updateRoute } from '@/routes/registrar/students'

interface Student {
    id: number
    first_name: string
    middle_name?: string
    last_name: string
    gender: string
    date_of_birth: string
    admission_date: string
    national_id?: string
}

const props = defineProps<{
    student: Student
}>()

const breadcrumbs = [
    { title: 'Dashboard', href: '/registrar/dashboard' },
    { title: 'Students', href: '/registrar/students' },
    { title: props.student.first_name + ' ' + props.student.last_name, href: '#' },
    { title: 'Edit', href: '#' },
]

const form = ref({
    first_name: props.student.first_name,
    middle_name: props.student.middle_name || '',
    last_name: props.student.last_name,
    gender: props.student.gender,
    date_of_birth: props.student.date_of_birth,
    admission_date: props.student.admission_date,
    national_id: props.student.national_id || '',
})

const errors = ref<Record<string, string>>({})

function submit() {
    router.put(updateRoute(props.student).url, form.value, {
        onError: (err) => {
            errors.value = err
        },
    })
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="`Edit ${student.first_name} ${student.last_name}`" />

        <article class="p-5 max-w-3xl mx-auto">
            <Heading :title="`Edit ${student.first_name} ${student.last_name}`" description="Update student information" />

            <form @submit.prevent="submit" class="mt-6 space-y-6">
                <Card>
                    <CardHeader>
                        <CardTitle>Student Details</CardTitle>
                        <CardDescription>Update the student's personal information</CardDescription>
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
                    <Button variant="outline" type="button" :href="`/registrar/students/${student.id}`">
                        Cancel
                    </Button>
                    <Button type="submit">
                        Update Student
                    </Button>
                </div>
            </form>
        </article>
    </AppLayout>
</template>
