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

        <form @submit.prevent="onSubmit" class="space-y-6">
            <FieldGroup>
                <div class="space-y-4">
                    <div
                        v-for="(subject, index) in subjects"
                        :key="index"
                        class="grid grid-cols-3 gap-2 items-end"
                    >
                        <Field
                            :data-invalid="subjectErrors[index]?.name"
                            class="col-span-2"
                        >
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
                            :data-invalid="subjectErrors[index]?.code"
                        >
                            <FieldLabel :for="`subject-code-${index}`">
                                Code
                            </FieldLabel>
                            <Input
                                :id="`subject-code-${index}`"
                                v-model="subject.code"
                                name="code"
                                placeholder="e.g., MTH"
                                required
                            />
                            <InputError :message="subjectErrors[index]?.code" />
                        </Field>

                        <Button
                            v-if="subjects.length > 1"
                            type="button"
                            variant="ghost"
                            size="icon"
                            @click="removeSubjectField(index)"
                            class="h-10 w-10 col-span-1"
                        >
                            <Trash2 class="w-4 h-4" />
                        </Button>
                        <div v-else class="w-10" />
                    </div>
                </div>

                <Button
                    type="button"
                    variant="outline"
                    size="sm"
                    @click="addSubjectField"
                    class="mt-2"
                >
                    <Plus class="w-4 h-4 mr-2" />
                    Add subject
                </Button>
            </FieldGroup>

            <Separator class="my-6" />

            <div class="flex gap-2">
                <Button
                    type="button"
                    variant="outline"
                    @click="goBack"
                    :disabled="hasExistingSubjects"
                    :title="hasExistingSubjects ? 'Cannot go back after subjects have been created. Edit in settings later.' : ''"
                    class="flex-1"
                >
                    <ChevronLeft class="w-4 h-4 mr-2" />
                    Back
                </Button>
                <Button type="submit" class="flex-1 h-10">
                    Complete setup
                    <ArrowRight class="w-4 h-4 ml-2" />
                </Button>
            </div>
        </form>
    </AuthBase>
</template>
