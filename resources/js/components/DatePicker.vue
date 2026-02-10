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
    modelValue?: string; // for v-model usage (YYYY-MM-DD format)
    name?: string; // for normal form usage
    placeholder?: string;
    minYear?: number; // minimum year in the year selector dropdown
    maxYear?: number; // maximum year in the year selector dropdown
    minDate?: string; // minimum selectable date (YYYY-MM-DD format)
    maxDate?: string; // maximum selectable date (YYYY-MM-DD format)
    disabled?: boolean; // whether the date picker is disabled
    disabledDates?: string[]; // array of disabled dates (YYYY-MM-DD format)
    isDateUnavailable?: (date: DateValue) => boolean; // custom function to disable dates
}

const props = withDefaults(defineProps<Props>(), {
    placeholder: 'Pick a date',
    disabled: false,
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

// Convert string minDate to DateValue
const minValue = computed<DateValue | undefined>(() => {
    if (!props.minDate) return undefined;
    try {
        return parseDate(props.minDate);
    } catch {
        return undefined;
    }
});

// Convert string maxDate to DateValue
const maxValue = computed<DateValue | undefined>(() => {
    if (!props.maxDate) return undefined;
    try {
        return parseDate(props.maxDate);
    } catch {
        return undefined;
    }
});

// Handle date unavailability logic
const isDateUnavailableInternal = computed(() => {
    return (date: DateValue) => {
        // Custom unavailability function takes precedence
        if (props.isDateUnavailable && props.isDateUnavailable(date)) {
            return true;
        }

        // Check against disabled dates array
        if (props.disabledDates && props.disabledDates.length > 0) {
            const year = date.year.toString().padStart(4, '0');
            const month = date.month.toString().padStart(2, '0');
            const day = date.day.toString().padStart(2, '0');
            const dateString = `${year}-${month}-${day}`;

            if (props.disabledDates.includes(dateString)) {
                return true;
            }
        }

        return false;
    };
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
                    :disabled="disabled"
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
                <DatePickerCalendar
                    v-model="dateValue"
                    :min-year="minYear"
                    :max-year="maxYear"
                    :min-value="minValue"
                    :max-value="maxValue"
                    :is-date-unavailable="isDateUnavailableInternal"
                    initial-focus
                />
            </PopoverContent>
        </Popover>
    </div>
</template>
