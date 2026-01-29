<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3'
import { Modal } from '@inertiaui/modal-vue'
import InputError from '@/components/InputError.vue'
import {
    ModalFooter,
    ModalHeader,
    ModalRoot,
    ModalScrollable,
} from '@/components/modal'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Field, FieldGroup, FieldLabel } from '@/components/ui/field'
import { Textarea } from '@/components/ui/textarea'
import { RadioGroup, RadioGroupItem } from '@/components/ui/radio-group'
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select'
import { store } from '@/routes/admin/settings/fee-structures'
import { ref, computed } from 'vue'
import { Spinner } from '@/components/ui/spinner'
import { Plus, Trash2, Info } from 'lucide-vue-next'

interface AcademicYear {
    id: number
    name: string
}

interface SchoolClass {
    id: number
    name: string
    order: number
}

interface Term {
    id: number
    name: string
}

interface FeeItem {
    id: number
    name: string
    code: string
}

const props = defineProps<{
    academicYear: AcademicYear
    classes: SchoolClass[]
    terms: Term[]
    feeItems: FeeItem[]
}>()

const createForm = ref()
const createModal = ref()

// Grouping strategy: 'individual', 'primary-secondary', or 'custom'
const groupingStrategy = ref('individual')

const form = ref({
    academic_year_id: props.academicYear.id,
    grouping_strategy: 'individual',
    school_class_ids: [] as string[],
    term_id: null as string | null,
    fee_items: [{ fee_item_id: '', amount: '', quantity: 1, notes: '' }],
})

// Determine primary vs secondary classes based on typical Malawi structure
// Primary: usually Form 1-4, Secondary: Form 5-6 or similar
const primaryClasses = computed(() => 
    props.classes.filter((c) => c.order <= 4)
)

const secondaryClasses = computed(() => 
    props.classes.filter((c) => c.order > 4)
)

const getSelectedClasses = (): SchoolClass[] => {
    if (groupingStrategy.value === 'primary-secondary') {
        if (form.value.school_class_ids.includes('primary')) {
            return primaryClasses.value
        } else if (form.value.school_class_ids.includes('secondary')) {
            return secondaryClasses.value
        }
        return []
    } else if (groupingStrategy.value === 'individual') {
        return props.classes.filter((c) => form.value.school_class_ids.includes(String(c.id)))
    }
    return []
}

const updateGroupingStrategy = (strategy: string) => {
    groupingStrategy.value = strategy
    form.value.grouping_strategy = strategy
    form.value.school_class_ids = []
}

const handleSuccess = () => {
    createModal.value?.close()
    createForm.value?.resetAndClearErrors()
}

const addFeeItem = () => {
    form.value.fee_items.push({ fee_item_id: '', amount: '', quantity: 1, notes: '' })
}

const removeFeeItem = (index: number) => {
    if (form.value.fee_items.length > 1) {
        form.value.fee_items.splice(index, 1)
    }
}

const toggleClass = (classId: string) => {
    const index = form.value.school_class_ids.indexOf(classId)
    if (index > -1) {
        form.value.school_class_ids.splice(index, 1)
    } else {
        form.value.school_class_ids.push(classId)
    }
}

const toggleGroup = (group: 'primary' | 'secondary') => {
    const index = form.value.school_class_ids.indexOf(group)
    if (index > -1) {
        form.value.school_class_ids.splice(index, 1)
    } else {
        form.value.school_class_ids.push(group)
    }
}

const formatCurrency = (amount: string | number): string => {
    if (!amount) return ''
    const num = typeof amount === 'string' ? parseFloat(amount) : amount
    if (isNaN(num)) return ''
    return 'MK ' + num.toLocaleString('en-ZA', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
}

const calculateTotal = (index: number): string => {
    const item = form.value.fee_items[index]
    if (!item.amount) return ''
    const amount = parseFloat(item.amount) * (item.quantity || 1)
    return formatCurrency(amount)
}

const areSelectedClassesValid = computed(() => {
    if (groupingStrategy.value === 'primary-secondary') {
        return form.value.school_class_ids.length > 0
    } else if (groupingStrategy.value === 'individual') {
        return form.value.school_class_ids.length > 0
    }
    return true
})
</script>

<template>
    <Modal
        ref="createModal"
        v-slot="{ close }"
        max-width="3xl"
        :close-explicitly="true"
        :close-button="false">
        <Head title="Assign Fees to Classes" />

        <ModalRoot>
            <ModalHeader title="Assign Fees to Classes"
                description="Select how to group classes and assign fees" />

            <ModalScrollable>
                <Form ref="createForm" v-bind="{ url: store().url, method: 'post', data: form }"
                    v-slot="{ errors, processing }" @success="handleSuccess"
                    :options="{ preserveScroll: true }">
                    
                    <!-- Grouping Strategy Selection -->
                    <FieldGroup class="space-y-4 border-b pb-6">
                        <div>
                            <FieldLabel>How would you like to assign fees? *</FieldLabel>
                            <p class="text-xs text-muted-foreground mt-1 mb-4">Choose whether to set fees per individual class or use predefined groupings</p>
                        </div>

                        <!-- Per Class Option -->
                        <div class="flex items-start gap-3 p-4 rounded-lg border border-muted hover:border-primary/50 cursor-pointer"
                            @click="updateGroupingStrategy('individual')">
                            <RadioGroupItem 
                                :id="'strategy-individual'"
                                :value="'individual'"
                                :checked="groupingStrategy === 'individual'"
                                class="mt-1" />
                            <div class="flex-1">
                                <label :for="'strategy-individual'" class="font-medium text-sm cursor-pointer">
                                    Per Individual Class
                                </label>
                                <p class="text-xs text-muted-foreground mt-1">
                                    Set different fees for each class. Useful when classes have significantly different fee structures.
                                </p>
                            </div>
                        </div>

                        <!-- Primary & Secondary Option -->
                        <div class="flex items-start gap-3 p-4 rounded-lg border border-muted hover:border-primary/50 cursor-pointer"
                            @click="updateGroupingStrategy('primary-secondary')">
                            <RadioGroupItem 
                                :id="'strategy-groups'"
                                :value="'primary-secondary'"
                                :checked="groupingStrategy === 'primary-secondary'"
                                class="mt-1" />
                            <div class="flex-1">
                                <label :for="'strategy-groups'" class="font-medium text-sm cursor-pointer">
                                    Primary & Secondary Grouping
                                </label>
                                <p class="text-xs text-muted-foreground mt-1">
                                    Group fees by Primary (Form 1-4) and Secondary (Form 5+) classes. Saves time when most classes in a level share the same fees.
                                </p>
                            </div>
                        </div>
                    </FieldGroup>

                    <!-- Class Selection -->
                    <FieldGroup class="space-y-4 border-b py-6">
                        <div>
                            <FieldLabel>Select Classes *</FieldLabel>
                        </div>

                        <!-- Individual Class Selection -->
                        <div v-if="groupingStrategy === 'individual'" class="space-y-2">
                            <div class="grid grid-cols-2 gap-3">
                                <label v-for="klass in classes" :key="klass.id" 
                                    class="flex items-center gap-2 p-3 rounded-lg border border-muted hover:border-primary/50 cursor-pointer"
                                    :class="form.school_class_ids.includes(String(klass.id)) ? 'border-primary bg-primary/5' : ''">
                                    <input 
                                        type="checkbox"
                                        :checked="form.school_class_ids.includes(String(klass.id))"
                                        @change="toggleClass(String(klass.id))"
                                        class="rounded" />
                                    <span class="text-sm font-medium">{{ klass.name }}</span>
                                </label>
                            </div>
                            <InputError v-if="errors['school_class_ids']" :message="errors['school_class_ids']" />
                        </div>

                        <!-- Primary & Secondary Group Selection -->
                        <div v-else-if="groupingStrategy === 'primary-secondary'" class="space-y-4">
                            <!-- Primary Classes -->
                            <div>
                                <h4 class="text-sm font-semibold mb-2 text-muted-foreground">Primary Classes</h4>
                                <div class="space-y-2">
                                    <label class="flex items-center gap-3 p-3 rounded-lg border border-muted hover:border-primary/50 cursor-pointer"
                                        :class="form.school_class_ids.includes('primary') ? 'border-primary bg-primary/5' : ''">
                                        <input 
                                            type="checkbox"
                                            :checked="form.school_class_ids.includes('primary')"
                                            @change="toggleGroup('primary')"
                                            class="rounded" />
                                        <div class="flex-1">
                                            <span class="text-sm font-medium">Apply to all Primary Classes</span>
                                            <p class="text-xs text-muted-foreground">
                                                {{ primaryClasses.map(c => c.name).join(', ') }}
                                            </p>
                                        </div>
                                    </label>
                                </div>
                            </div>

                            <!-- Secondary Classes -->
                            <div v-if="secondaryClasses.length > 0">
                                <h4 class="text-sm font-semibold mb-2 text-muted-foreground">Secondary Classes</h4>
                                <div class="space-y-2">
                                    <label class="flex items-center gap-3 p-3 rounded-lg border border-muted hover:border-primary/50 cursor-pointer"
                                        :class="form.school_class_ids.includes('secondary') ? 'border-primary bg-primary/5' : ''">
                                        <input 
                                            type="checkbox"
                                            :checked="form.school_class_ids.includes('secondary')"
                                            @change="toggleGroup('secondary')"
                                            class="rounded" />
                                        <div class="flex-1">
                                            <span class="text-sm font-medium">Apply to all Secondary Classes</span>
                                            <p class="text-xs text-muted-foreground">
                                                {{ secondaryClasses.map(c => c.name).join(', ') }}
                                            </p>
                                        </div>
                                    </label>
                                </div>
                            </div>
                            <InputError v-if="errors['school_class_ids']" :message="errors['school_class_ids']" />
                        </div>
                    </FieldGroup>

                    <!-- Term Selection -->
                    <FieldGroup class="border-b py-6">
                        <Field :data-invalid="errors['term_id']">
                            <FieldLabel for="create-term">Term (Optional)</FieldLabel>
                            <Select v-model="form.term_id">
                                <SelectTrigger id="create-term" class="bg-background">
                                    <SelectValue placeholder="All year (leave empty)" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="">All Year</SelectItem>
                                    <SelectItem v-for="term in terms" :key="term.id" :value="String(term.id)">
                                        {{ term.name }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <InputError class="mt-1" :message="errors['term_id']" />
                        </Field>
                    </FieldGroup>

                    <!-- Fee Items Section -->
                    <div class="space-y-4 py-6">
                        <div class="flex items-center justify-between">
                            <h3 class="text-sm font-semibold">Fee Items</h3>
                            <Button 
                                type="button" 
                                variant="outline" 
                                size="sm"
                                @click="addFeeItem"
                                class="gap-1">
                                <Plus class="h-4 w-4" />
                                Add Item
                            </Button>
                        </div>

                        <!-- Info Banner -->
                        <div class="flex gap-2 p-3 rounded-lg bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-900">
                            <Info class="h-4 w-4 text-blue-600 dark:text-blue-400 flex-shrink-0 mt-0.5" />
                            <p class="text-xs text-blue-700 dark:text-blue-300">
                                These fees will be applied to {{ getSelectedClasses().length > 0 ? getSelectedClasses().map(c => c.name).join(' & ') : 'selected classes' }}.
                            </p>
                        </div>

                        <div v-for="(item, index) in form.fee_items" :key="index" class="rounded-lg border p-4 bg-muted/30">
                            <div class="grid grid-cols-12 gap-3 items-start">
                                <!-- Fee Item Select -->
                                <div class="col-span-5">
                                    <Field :data-invalid="errors[`fee_items.${index}.fee_item_id`]">
                                        <FieldLabel :for="`fee-item-${index}`">Fee Item *</FieldLabel>
                                        <Select v-model="item.fee_item_id">
                                            <SelectTrigger :id="`fee-item-${index}`" class="bg-background">
                                                <SelectValue placeholder="Select fee" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectItem v-for="fee in feeItems" :key="fee.id" :value="String(fee.id)">
                                                    {{ fee.name }} ({{ fee.code }})
                                                </SelectItem>
                                            </SelectContent>
                                        </Select>
                                        <InputError class="mt-1" :message="errors[`fee_items.${index}.fee_item_id`]" />
                                    </Field>
                                </div>

                                <!-- Amount -->
                                <div class="col-span-3">
                                    <Field :data-invalid="errors[`fee_items.${index}.amount`]">
                                        <FieldLabel :for="`amount-${index}`">Amount (MK) *</FieldLabel>
                                        <Input 
                                            :id="`amount-${index}`"
                                            v-model="item.amount"
                                            type="number"
                                            step="0.01"
                                            min="0"
                                            placeholder="0.00"
                                            class="bg-background" />
                                        <InputError class="mt-1" :message="errors[`fee_items.${index}.amount`]" />
                                    </Field>
                                </div>

                                <!-- Quantity -->
                                <div class="col-span-2">
                                    <Field :data-invalid="errors[`fee_items.${index}.quantity`]">
                                        <FieldLabel :for="`qty-${index}`">Qty</FieldLabel>
                                        <Input 
                                            :id="`qty-${index}`"
                                            v-model.number="item.quantity"
                                            type="number"
                                            min="1"
                                            :value="item.quantity || 1"
                                            class="bg-background" />
                                        <InputError class="mt-1" :message="errors[`fee_items.${index}.quantity`]" />
                                    </Field>
                                </div>

                                <!-- Delete Button -->
                                <div class="col-span-2 pt-7">
                                    <Button 
                                        v-if="form.fee_items.length > 1"
                                        type="button"
                                        variant="ghost"
                                        size="sm"
                                        @click="removeFeeItem(index)"
                                        class="w-full">
                                        <Trash2 class="h-4 w-4 text-destructive" />
                                    </Button>
                                </div>
                            </div>

                            <!-- Notes -->
                            <div class="mt-3">
                                <Field>
                                    <FieldLabel :for="`notes-${index}`">Notes</FieldLabel>
                                    <Textarea 
                                        :id="`notes-${index}`"
                                        v-model="item.notes"
                                        placeholder="Optional notes"
                                        class="bg-background"
                                        rows="2" />
                                </Field>
                            </div>

                            <!-- Total Display -->
                            <div class="mt-3 text-right">
                                <p class="text-sm text-muted-foreground">
                                    Total: <span class="font-semibold text-foreground">{{ calculateTotal(index) || 'MK 0.00' }}</span>
                                </p>
                            </div>
                        </div>

                        <InputError v-if="errors['fee_items']" :message="errors['fee_items']" />
                    </div>

                    <ModalFooter>
                        <Button type="button" variant="outline" @click="close">
                            Cancel
                        </Button>
                        <Button type="submit" :disabled="processing || !areSelectedClassesValid">
                            <Spinner v-if="processing" />
                            {{ processing ? 'Assigning...' : 'Assign Fees' }}
                        </Button>
                    </ModalFooter>
                </Form>
            </ModalScrollable>
        </ModalRoot>
    </Modal>
</template>
