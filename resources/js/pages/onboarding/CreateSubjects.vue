<script setup lang="ts">
import { ref, computed } from 'vue'
import { Head, usePage } from '@inertiajs/vue3'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import InputError from '@/components/InputError.vue'
import AuthBase from '@/layouts/AuthLayout.vue'
import { Field, FieldGroup, FieldLabel } from '@/components/ui/field'
import { Separator } from '@/components/ui/separator'
import { Plus, Trash2, ArrowRight, ChevronLeft } from 'lucide-vue-next'
import { router } from '@inertiajs/vue3'
import { toast } from 'vue-sonner'

interface SubjectItem {
    name: string
    code: string
    id?: number
}

const page = usePage()
const existingSubjects = computed(() => page.props.existingSubjects || [])
const hasExistingSubjects = computed(() => existingSubjects.value.length > 0)

const subjects = ref<SubjectItem[]>(
    existingSubjects.value.length > 0 
        ? existingSubjects.value 
        : [{ name: '', code: '' }]
)

const subjectErrors = ref<Record<number, Record<string, string>>>({})
const generalError = ref<string>('')

const addSubjectField = () => {
    subjects.value.push({ name: '', code: '' })
}

const removeSubjectField = (index: number) => {
    subjects.value.splice(index, 1)
    delete subjectErrors.value[index]
}

const onSubmit = () => {
    subjectErrors.value = {}
    generalError.value = ''

    // Send all subjects (existing with id, new without id)
    // Backend will only process new subjects
    const payload = {
        subjects: subjects.value.map(s => ({
            id: s.id,
            name: s.name,
            code: s.code,
        }))
    }

    router.post('/onboarding/subjects', payload, {
        onError: (errors: any) => {
            // Field-level errors
            if (errors.subjects && typeof errors.subjects === 'object') {
                subjectErrors.value = errors.subjects
            } else if (typeof errors.subjects === 'string') {
                // General error about subjects array itself
                generalError.value = errors.subjects
                toast.error(errors.subjects)
            }

            // Other general errors for individual items like "subjects.0.name"
            Object.entries(errors).forEach(([key, value]) => {
                const match = key.match(/^subjects\.(\d+)\.(name|code)$/)
                if (match) {
                    const index = parseInt(match[1])
                    const field = match[2]
                    if (!subjectErrors.value[index]) {
                        subjectErrors.value[index] = {}
                    }
                    subjectErrors.value[index][field] = value as string
                } else if (typeof value === 'string') {
                    toast.error(value as string)
                }
            })
        },
    })
}

const goBack = () => {
    router.get('/onboarding/streams')
}
</script>

<template>
    <AuthBase
        title="4. Add Subjects"
        description="Define the subjects taught in your school"
    >
        <Head title="Add Subjects" />

        <!-- Warning banner -->
        <div class="mb-6 rounded-lg border border-amber-200 bg-amber-50 p-4 dark:border-amber-800 dark:bg-amber-950">
            <div class="flex gap-3">
                <div class="mt-0.5 shrink-0">
                    <svg class="h-5 w-5 text-amber-600 dark:text-amber-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-semibold text-amber-800 dark:text-amber-200">
                        Add all subjects now
                    </p>
                    <p class="mt-1 text-sm text-amber-700 dark:text-amber-300">
                        Please add all the subjects you need in one go. Once you complete setup, you can manage additional subjects in the school settings.
                    </p>
                </div>
            </div>
        </div>

        <form @submit.prevent="onSubmit" class="space-y-6">
            <FieldGroup>
                <div class="space-y-4">
                    <div
                        v-for="(subject, index) in subjects"
                        :key="index"
                        class="flex gap-4 items-end w-full">
                        <Field
                            :data-invalid="subjectErrors[index]?.name">
                            <FieldLabel :for="`subject-name-${index}`">
                                Subject name
                            </FieldLabel>
                            <Input
                                :id="`subject-name-${index}`"
                                v-model="subject.name"
                                name="name"
                                placeholder="e.g., Mathematics"
                                required
                            />
                            <InputError :message="subjectErrors[index]?.name" />
                        </Field>

                        <Field
                            class="max-w-[5rem]"
                            :data-invalid="subjectErrors[index]?.code">
                            <FieldLabel :for="`subject-code-${index}`">
                                Code
                            </FieldLabel>
                            <Input
                                :id="`subject-code-${index}`"
                                v-model="subject.code"
                                name="code"
                                placeholder="e.g., MATH, SCI, ENG"
                                required
                            />
                            <InputError :message="subjectErrors[index]?.code" />
                        </Field>

                        <Button
                            v-if="subjects.length > 1"
                            type="button"
                            variant="ghost"
                            size="icon-sm"
                            @click="removeSubjectField(index)">
                            <Trash2 />
                        </Button>
                    </div>
                </div>

                <Button
                    type="button"
                    variant="ghost"
                    size="sm"
                    @click="addSubjectField"
                    class="mt-2">
                    <Plus class="w-4 h-4 mr-2" />
                    Add subject
                </Button>
            </FieldGroup>

            <Separator class="my-6" />

            <div class="flex justify-between">
                <Button
                    type="button"
                    variant="ghost"
                    @click="goBack"
                    size="sm"
                    :disabled="hasExistingSubjects"
                    :title="hasExistingSubjects ? 'Cannot go back after subjects have been created. Edit in settings later.' : ''">
                    <ChevronLeft class="w-4 h-4 mr-2" />
                    Back
                </Button>

                <Button type="submit" class="h-10">
                    Complete setup
                    <ArrowRight class="w-4 h-4 ml-2" />
                </Button>
            </div>
        </form>
    </AuthBase>
</template>
