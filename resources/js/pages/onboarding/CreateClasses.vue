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

interface ClassItem {
    name: string
    order: string | number
    id?: number
}

const page = usePage()
const existingClasses = computed(() => page.props.existingClasses || [])
const hasExistingClasses = computed(() => existingClasses.value.length > 0)

const classes = ref<ClassItem[]>(
    existingClasses.value.length > 0 
        ? existingClasses.value 
        : [{ name: '', order: '1' }]
)

const classErrors = ref<Record<number, Record<string, string>>>({})
const generalError = ref<string>('')
const bypassPassword = ref('')
const showPasswordPrompt = ref(false)

const addClassField = () => {
    classes.value.push({
        name: '',
        order: String(classes.value.length + 1),
    })
}

const removeClassField = (index: number) => {
    classes.value.splice(index, 1)
    // Reorder
    classes.value.forEach((_, i) => {
        classes.value[i].order = String(i + 1)
    })
    // Remove errors for this index
    delete classErrors.value[index]
}

const submitWithPassword = () => {
    if (!bypassPassword.value) {
        toast.error('Password is required to bypass duplicate check')
        return
    }
    
    showPasswordPrompt.value = false
    submitForm(bypassPassword.value)
}

const submitForm = (password?: string) => {
    classErrors.value = {}
    generalError.value = ''

    // Send all classes (existing with id, new without id)
    // Backend will only process new classes
    const payload = {
        classes: classes.value.map(c => ({
            id: c.id,
            name: c.name,
            order: c.order,
        })),
        ...(password && { bypass_password: password })
    }

    router.post('/onboarding/classes', payload, {
        onError: (errors: any) => {
            // Check if there are duplicate errors that ask for password confirmation
            const hasDuplicateErrors = Object.values(errors).some(
                (msg: any) => typeof msg === 'string' && msg.includes('already exists')
            )

            if (hasDuplicateErrors) {
                // Show password prompt for duplicate confirmation
                showPasswordPrompt.value = true
                toast.error('Some classes already exist. Enter your password to confirm adding them.')
            }

            // Field-level errors
            if (errors.classes && typeof errors.classes === 'object') {
                classErrors.value = errors.classes
            } else if (typeof errors.classes === 'string') {
                // General error about classes array itself
                generalError.value = errors.classes
                if (!hasDuplicateErrors) {
                    toast.error(errors.classes)
                }
            }

            // Other general errors for individual items like "classes.0.name"
            Object.entries(errors).forEach(([key, value]) => {
                const match = key.match(/^classes\.(\d+)\.name$/)
                if (match) {
                    const index = parseInt(match[1])
                    if (!classErrors.value[index]) {
                        classErrors.value[index] = {}
                    }
                    classErrors.value[index].name = value as string
                } else if (key === 'bypass_password' && typeof value === 'string') {
                    toast.error(value as string)
                    bypassPassword.value = ''
                } else if (typeof value === 'string' && key !== 'classes' && !hasDuplicateErrors) {
                    toast.error(value as string)
                }
            })
        },
    })
}

const onSubmit = () => {
    submitForm()
}

const goBack = () => {
    router.get('/onboarding/school-setup')
}
</script>

<template>
    <AuthBase
        title="2. Add Classes"
        description="Define the classes/grades in your school"
    >
        <Head title="Add Classes" />

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
                        Add all classes now
                    </p>
                    <p class="mt-1 text-sm text-amber-700 dark:text-amber-300">
                        Please add all the classes/grades you need in one go. Once you proceed, you won't be able to return to this step to add more classes.
                    </p>
                </div>
            </div>
        </div>

        <form @submit.prevent="onSubmit" class="space-y-6">
            <FieldGroup>
                <div class="space-y-4">
                    <div
                        v-for="(classItem, index) in classes"
                        :key="index"
                        class="flex gap-2 items-end">
                        <Field
                            :data-invalid="classErrors[index]?.name"
                            class="flex-1">
                            <FieldLabel :for="`class-name-${index}`">
                                Class name
                            </FieldLabel>
                            <Input
                                :id="`class-name-${index}`"
                                v-model="classItem.name"
                                name="name"
                                placeholder="e.g., Standard 1, Grade 1, Form 2, Class A"
                                required
                            />
                            <InputError :message="classErrors[index]?.name" />
                        </Field>

                        <Field
                            :data-invalid="classErrors[index]?.order ? true : false"
                            class="w-20">
                            <FieldLabel :for="`class-order-${index}`">
                                Order
                            </FieldLabel>
                            <Input
                                :id="`class-order-${index}`"
                                v-model="classItem.order"
                                name="order"
                                type="number"
                                class="text-center"
                                min="1"
                                required
                            />
                            <InputError :message="classErrors[index]?.order" />
                        </Field>

                        <Button
                            v-if="classes.length > 1"
                            type="button"
                            variant="ghost"
                            size="icon"
                            @click="removeClassField(index)"
                            class="h-10 w-10"
                        >
                            <Trash2 class="w-4 h-4" />
                        </Button>
                    </div>
                </div>

                <Button
                    type="button"
                    variant="outline"
                    size="sm"
                    @click="addClassField"
                    class="mt-2">
                    <Plus class="w-4 h-4 mr-2" />
                    Add class
                </Button>
            </FieldGroup>

            <Separator class="my-6" />

            <div class="flex justify-between items-center">
                <!-- <Button
                    type="button"
                    variant="ghost"
                    @click="goBack"
                    :disabled="hasExistingClasses"
                    :title="hasExistingClasses ? 'Cannot go back after classes have been created. Edit in settings later.' : ''"
                    size="sm">
                    <ChevronLeft />
                    Back
                </Button> -->

                <Button 
                    type="submit"
                    size="lg" class="ml-auto">
                    Continue
                    <ArrowRight class="ml-2" />
                </Button>
            </div>
        </form>

        <!-- Password confirmation modal for duplicate classes -->
        <div v-if="showPasswordPrompt" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
            <div class="bg-white dark:bg-slate-950 rounded-lg p-6 w-full max-w-sm mx-4 space-y-4">
                <h2 class="text-lg font-semibold">
                    Confirm Duplicate Classes
                </h2>

                <p class="text-sm text-slate-600 dark:text-slate-400">
                    Some classes you're trying to add already exist. Enter your password to confirm you want to add them anyway.
                </p>

                <div>
                    <Input
                        v-model="bypassPassword"
                        type="password"
                        placeholder="Enter your password"
                        @keyup.enter="submitWithPassword"
                    />
                </div>

                <div class="flex gap-2">
                    <Button
                        type="button"
                        variant="outline"
                        @click="showPasswordPrompt = false"
                        class="flex-1">
                        Cancel
                    </Button>
                    
                    <Button
                        type="button"
                        @click="submitWithPassword"
                        class="flex-1">
                        Confirm
                    </Button>
                </div>
            </div>
        </div>
    </AuthBase>
</template>
