<script setup lang="ts">
import DatePickerCalendar from '@/components/DatePickerCalendar.vue';
import { Button } from '@/components/ui/button';
import {
    Popover,
    PopoverContent,
    PopoverTrigger,
} from '@/components/ui/popover';
import { cn } from '@/lib/utils';
import type { DateValue } from '@internationalized/date';
import {
    DateFormatter,
    getLocalTimeZone,
    parseDate,
} from '@internationalized/date';
import { CalendarIcon } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';

interface Props {
    modelValue?: string; // for v-model usage
    name?: string; // for normal form usage
    placeholder?: string;
}

const props = withDefaults(defineProps<Props>(), {
    placeholder: 'Pick a date',
});

const emit = defineEmits<{
    'update:modelValue': [value: string];
}>();

// Internal string value backing both modes (v-model or uncontrolled)
const innerValue = ref<string>(props.modelValue ?? '');

// Keep innerValue in sync when parent controls modelValue
watch(
    () => props.modelValue,
    (val) => {
        if (val !== undefined && val !== innerValue.value) {
            innerValue.value = val ?? '';
        }
    },
);

const df = new DateFormatter('en-US', {
    dateStyle: 'long',
});

const dateValue = computed<DateValue | undefined>({
    get: () => {
        if (!innerValue.value) return undefined;
        try {
            return parseDate(innerValue.value);
        } catch {
            return undefined;
        }
    },
    set: (value) => {
        if (!value) {
            innerValue.value = '';
            emit('update:modelValue', '');
            return;
        }

        const year = value.year.toString().padStart(4, '0');
        const month = value.month.toString().padStart(2, '0');
        const day = value.day.toString().padStart(2, '0');

        const formatted = `${year}-${month}-${day}`;
        innerValue.value = formatted;
        emit('update:modelValue', formatted);
    },
});
</script>

<template>
    <div class="w-full">
        <!-- Hidden input for normal HTML forms -->
        <input v-if="name" type="hidden" :name="name" :value="innerValue" />

        <Popover>
            <PopoverTrigger as-child>
                <Button
                    variant="outline"
                    :class="
                        cn(
                            'w-full justify-start text-left font-normal',
                            !dateValue && 'text-muted-foreground',
                        )
                    ">
                    <CalendarIcon class="mr-2 h-4 w-4" />
                    {{
                        dateValue
                            ? df.format(dateValue.toDate(getLocalTimeZone()))
                            : placeholder
                    }}
                </Button>
            </PopoverTrigger>
            <PopoverContent class="w-auto p-0">
                <DatePickerCalendar v-model="dateValue" initial-focus />
            </PopoverContent>
        </Popover>
    </div>
</template>
