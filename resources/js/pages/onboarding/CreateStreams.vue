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

interface StreamItem {
    name: string
    id?: number
}

const page = usePage()
const existingStreams = computed(() => page.props.existingStreams || [])
const hasExistingStreams = computed(() => existingStreams.value.length > 0)

const streams = ref<StreamItem[]>(
    existingStreams.value.length > 0 
        ? existingStreams.value 
        : [{ name: '' }, { name: '' }]
)

const streamErrors = ref<Record<number, Record<string, string>>>({})
const generalError = ref<string>('')

const addStreamField = () => {
    streams.value.push({ name: '' })
}

const removeStreamField = (index: number) => {
    streams.value.splice(index, 1)
    delete streamErrors.value[index]
}

const onSubmit = () => {
    streamErrors.value = {}
    generalError.value = ''

    // Send all streams (keep existing, filter out completely empty new ones)
    // Include streams with id (existing) and with name (new)
    const filledStreams = streams.value.filter(s => s.id || s.name.trim())

    const payload = {
        streams: filledStreams.map(s => ({
            id: s.id,
            name: s.name,
        }))
    }

    router.post('/onboarding/streams', payload, {
        onError: (errors: any) => {
            // Field-level errors
            if (errors.streams && typeof errors.streams === 'object') {
                streamErrors.value = errors.streams
            } else if (typeof errors.streams === 'string') {
                // General error about streams array itself
                generalError.value = errors.streams
                toast.error(errors.streams)
            }

            // Other general errors for individual items like "streams.0.name"
            Object.entries(errors).forEach(([key, value]) => {
                const match = key.match(/^streams\.(\d+)\.name$/)
                if (match) {
                    const index = parseInt(match[1])
                    if (!streamErrors.value[index]) {
                        streamErrors.value[index] = {}
                    }
                    streamErrors.value[index].name = value as string
                } else if (typeof value === 'string') {
                    toast.error(value as string)
                }
            })
        },
    })
}

const goBack = () => {
    router.get('/onboarding/classes')
}
</script>

<template>
    <AuthBase
        title="3. Add Streams"
        description="Define the streams/divisions in your school (e.g., A, B, C)"
    >
        <Head title="Add Streams" />

        <form @submit.prevent="onSubmit" class="space-y-6">
            <FieldGroup>
                <div class="space-y-4">
                    <div
                        v-for="(stream, index) in streams"
                        :key="index"
                        class="flex gap-2 items-end"
                    >
                        <Field
                            :data-invalid="streamErrors[index]?.name"
                            class="flex-1"
                        >
                            <FieldLabel :for="`stream-name-${index}`">
                                Stream name
                            </FieldLabel>
                            <Input
                                :id="`stream-name-${index}`"
                                v-model="stream.name"
                                name="name"
                                placeholder="e.g., A, B, C or Science, Arts"
                            />
                            <InputError :message="streamErrors[index]?.name" />
                        </Field>

                        <Button
                            v-if="streams.length > 1"
                            type="button"
                            variant="ghost"
                            size="icon"
                            @click="removeStreamField(index)"
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
                    @click="addStreamField"
                    class="mt-2"
                >
                    <Plus class="w-4 h-4 mr-2" />
                    Add stream
                </Button>
            </FieldGroup>

            <Separator class="my-6" />

            <div class="flex gap-2">
                <Button
                    type="button"
                    variant="outline"
                    @click="goBack"
                    :disabled="hasExistingStreams"
                    :title="hasExistingStreams ? 'Cannot go back after streams have been created. Edit in settings later.' : ''"
                    class="flex-1"
                >
                    <ChevronLeft class="w-4 h-4 mr-2" />
                    Back
                </Button>
                <Button type="submit" class="flex-1 h-10">
                    Continue
                    <ArrowRight class="w-4 h-4 ml-2" />
                </Button>
            </div>
        </form>
    </AuthBase>
</template>
