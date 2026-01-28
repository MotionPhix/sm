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

const onSubmit = () => {
    classErrors.value = {}
    generalError.value = ''

    // Send all classes (existing with id, new without id)
    // Backend will only process new classes
    const payload = {
        classes: classes.value.map(c => ({
            id: c.id,
            name: c.name,
            order: c.order,
        }))
    }

    router.post('/onboarding/classes', payload, {
        onError: (errors: any) => {
            // Field-level errors
            if (errors.classes && typeof errors.classes === 'object') {
                classErrors.value = errors.classes
            } else if (typeof errors.classes === 'string') {
                // General error about classes array itself
                generalError.value = errors.classes
                toast.error(errors.classes)
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
                } else if (typeof value === 'string') {
                    toast.error(value as string)
                }
            })
        },
    })
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
    </AuthBase>
</template>
