<script setup lang="ts">
import type { DateValue } from '@internationalized/date'
import type { CalendarRootEmits, CalendarRootProps } from 'reka-ui'
import type { HTMLAttributes } from 'vue'
import { getLocalTimeZone, today } from '@internationalized/date'
import { CalendarRoot, useDateFormatter, useForwardPropsEmits } from 'reka-ui'
import { createDecade, createYear, toDate } from 'reka-ui/date'
import { computed, ref, watch } from 'vue'
import { cn } from '@/lib/utils'
import {
  CalendarCell,
  CalendarCellTrigger,
  CalendarGrid,
  CalendarGridBody,
  CalendarGridHead,
  CalendarGridRow,
  CalendarHeadCell,
  CalendarHeader,
  CalendarHeading,
} from '@/components/ui/calendar'
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select'

const props = withDefaults(
  defineProps<CalendarRootProps & {
    class?: HTMLAttributes['class']
    minYear?: number
    maxYear?: number
  }>(),
  {
    modelValue: undefined,
    placeholder() {
      return today(getLocalTimeZone())
    },
    weekdayFormat: 'short',
    minYear: undefined,
    maxYear: undefined,
  }
)
const emits = defineEmits<CalendarRootEmits>()

const delegatedProps = computed(() => {
  const { class: _, placeholder: __, ...delegated } = props

  return delegated
})

// Get the actual current year for year range calculations
const actualCurrentYear = new Date().getFullYear()

// Use a local ref for the placeholder/selected date
// Ensure placeholder respects minYear if provided
const getInitialPlaceholder = () => {
  const initial = props.modelValue || props.placeholder || today(getLocalTimeZone())
  
  // If minYear is set and initial year is less than minYear, adjust to minYear
  if (props.minYear && initial.year < props.minYear) {
    return initial.set({ year: props.minYear })
  }
  
  return initial
}

const placeholderDate = ref<DateValue>(getInitialPlaceholder())

// Watch for external changes to modelValue
watch(() => props.modelValue, (newValue) => {
  if (newValue && newValue.toString() !== placeholderDate.value.toString()) {
    placeholderDate.value = newValue
  }
})

// Emit changes back to parent
watch(placeholderDate, (newValue) => {
  emits('update:modelValue', newValue)
})

const forwarded = useForwardPropsEmits(delegatedProps, emits)

const formatter = useDateFormatter('en')

// Calculate year range for the decade
const yearRange = computed(() => {
  const currentYear = placeholderDate.value.year
  const minYear = props.minYear ?? actualCurrentYear - 100
  const maxYear = props.maxYear ?? actualCurrentYear + 10

  return {
    startIndex: minYear - currentYear,
    endIndex: maxYear - currentYear,
  }
})

// Generate list of available years (absolute values)
const availableYears = computed(() => {
  const minYear = props.minYear ?? actualCurrentYear - 100
  const maxYear = props.maxYear ?? actualCurrentYear + 10
  
  const years = []
  for (let year = minYear; year <= maxYear; year++) {
    years.push(year)
  }
  return years
})
</script>

<template>
  <CalendarRoot
    v-slot="{ date, grid, weekDays }"
    v-model:placeholder="placeholderDate"
    v-bind="forwarded"
    :class="cn('rounded-md border p-3', props.class)">
    <CalendarHeader class="px-0">
      <CalendarHeading class="flex items-center justify-between gap-2 !px-0">
        <Select
          :model-value="placeholderDate.month.toString()"
          @update:model-value="
            (v) => {
              if (!v) return
              const newMonth = Number(v)
              if (newMonth === placeholderDate.month) return
              placeholderDate = placeholderDate.set({ month: newMonth })
            }
          ">
          <SelectTrigger aria-label="Select month">
            <SelectValue placeholder="Select month" />
          </SelectTrigger>

          <SelectContent>
            <SelectItem
              v-for="month in createYear({ dateObj: date })"
              :key="month.toString()"
              :value="month.month.toString()">
              {{ formatter.custom(toDate(month), { month: 'long' }) }}
            </SelectItem>
          </SelectContent>
        </Select>

        <Select
          :model-value="placeholderDate.year.toString()"
          @update:model-value="
            (v) => {
              if (!v) return
              const newYear = Number(v)
              if (newYear === placeholderDate.year) return
              placeholderDate = placeholderDate.set({ year: newYear })
            }
          ">
          <SelectTrigger aria-label="Select year">
            <SelectValue placeholder="Select year" />
          </SelectTrigger>

          <SelectContent>
            <SelectItem
              v-for="year in availableYears"
              :key="year"
              :value="year.toString()">
              {{ year }}
            </SelectItem>
          </SelectContent>
        </Select>
      </CalendarHeading>
    </CalendarHeader>

    <div class="flex flex-col space-y-4 pt-4 sm:flex-row sm:gap-x-4 sm:gap-y-0">
      <CalendarGrid v-for="month in grid" :key="month.value.toString()">
        <CalendarGridHead>
          <CalendarGridRow>
            <CalendarHeadCell v-for="day in weekDays" :key="day">
              {{ day }}
            </CalendarHeadCell>
          </CalendarGridRow>
        </CalendarGridHead>

        <CalendarGridBody class="grid">
          <CalendarGridRow
            v-for="(weekDates, index) in month.rows"
            :key="`weekDate-${index}`"
            class="mt-2">
            <CalendarCell
                class="w-full"
                v-for="weekDate in weekDates"
                :key="weekDate.toString()"
                :date="weekDate">
              <CalendarCellTrigger :day="weekDate" :month="month.value" />
            </CalendarCell>
          </CalendarGridRow>
        </CalendarGridBody>
      </CalendarGrid>
    </div>
  </CalendarRoot>
</template>
