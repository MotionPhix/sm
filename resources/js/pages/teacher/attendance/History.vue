<script setup lang="ts">
import Heading from '@/components/Heading.vue'
import { Button } from '@/components/ui/button'
import { Field, FieldGroup, FieldLabel } from '@/components/ui/field'
import { Input } from '@/components/ui/input'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import AppLayout from '@/layouts/AppLayout.vue'
import type { BreadcrumbItemType } from '@/types'
import { Head, router } from '@inertiajs/vue3'
import { Download } from 'lucide-vue-next'
import { computed, reactive } from 'vue'

const props = defineProps<{
  records: { data: any[], links: any[], meta?: any }
  filters: Record<string, any>
  classes: any[]
  streams: any[]
}>()

const breadcrumbs: BreadcrumbItemType[] = [
  { title: 'Attendance', href: '/teacher/attendance' },
  { title: 'History', href: '/teacher/attendance/history' },
]

const f = reactive({
  date_from: props.filters.date_from || '',
  date_to: props.filters.date_to || '',
  class_id: props.filters.class_id || '',
  stream_id: props.filters.stream_id || '',
  status: props.filters.status || '',
})

const apply = () => {
  router.visit('/teacher/attendance/history', { method: 'get', data: f, preserveState: true, preserveScroll: true })
}

const exportCsv = () => {
  const params = new URLSearchParams()
  Object.entries(f).forEach(([k, v]) => { if (v) params.set(k, String(v)) })
  window.location.href = `/teacher/attendance/export?${params.toString()}`
}

const filteredStreams = computed(() => {
  const cid = Number(f.class_id)
  if (!cid) return props.streams
  return props.streams.filter((s: any) => !('school_class_id' in s) || s.school_class_id === cid)
})

const statusTotals = computed(() => {
  const t: Record<string, number> = { present: 0, absent: 0, late: 0, excused: 0 }
  for (const r of props.records.data) {
    if (r.status && r.status in t) t[r.status]++
  }
  return t
})
</script>

<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <Head title="Attendance History" />

    <template #act>
        <Button @click="exportCsv" variant="outline">
          <Download class="mr-2 h-4 w-4" />
          Export CSV
        </Button>

        <Button @click="apply">Apply</Button>
    </template>

    <div class="space-y-6">
        <Heading
            title="Attendance History"
            description="Filter and export attendance records."
        />

      <div class="p-6">
        <FieldGroup class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3">
          <Field>
            <FieldLabel>From</FieldLabel>
            <Input
              v-model="f.date_from"
              type="date"
            />
          </Field>

          <Field>
            <FieldLabel>To</FieldLabel>
            <Input
              v-model="f.date_to"
              type="date"
            />
          </Field>

          <Field>
            <FieldLabel>Class</FieldLabel>
            <Select v-model="f.class_id">
              <SelectTrigger>
                <SelectValue placeholder="All classes" />
              </SelectTrigger>

              <SelectContent>
                <SelectItem :value="null">All</SelectItem>
                <SelectItem v-for="c in classes" :key="c.id" :value="c.id">
                    {{ c.name }}
                </SelectItem>
              </SelectContent>
            </Select>
          </Field>

          <Field>
            <FieldLabel>Stream</FieldLabel>
            <Select v-model="f.stream_id">
              <SelectTrigger>
                <SelectValue placeholder="All streams" />
              </SelectTrigger>

              <SelectContent>
                <SelectItem :value="null">All</SelectItem>
                <SelectItem v-for="s in filteredStreams" :key="s.id" :value="s.id">
                    {{ s.name }}
                </SelectItem>
              </SelectContent>
            </Select>
          </Field>

          <Field>
            <FieldLabel>Status</FieldLabel>
            <Select v-model="f.status">
              <SelectTrigger>
                <SelectValue placeholder="All statuses" />
              </SelectTrigger>

              <SelectContent>
                <SelectItem :value="null">All</SelectItem>
                <SelectItem value="present">Present</SelectItem>
                <SelectItem value="absent">Absent</SelectItem>
                <SelectItem value="late">Late</SelectItem>
                <SelectItem value="excused">Excused</SelectItem>
                </SelectContent>
            </Select>
          </Field>
        </FieldGroup>
      </div>

      <div class="flex flex-wrap items-center gap-4 text-sm">
        <span class="rounded-full bg-green-100 px-3 py-1 text-green-800 dark:bg-green-900/30 dark:text-green-300">
          Present: {{ statusTotals.present }}
        </span>
        <span class="rounded-full bg-red-100 px-3 py-1 text-red-800 dark:bg-red-900/30 dark:text-red-300">
          Absent: {{ statusTotals.absent }}
        </span>
        <span class="rounded-full bg-amber-100 px-3 py-1 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300">
          Late: {{ statusTotals.late }}
        </span>
        <span class="rounded-full bg-blue-100 px-3 py-1 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300">
          Excused: {{ statusTotals.excused }}
        </span>
      </div>

      <div class="rounded-lg border bg-card">
        <div class="overflow-x-auto">
          <table class="min-w-full text-sm">
            <thead>
              <tr class="border-b bg-muted/50">
                <th class="px-4 py-3 text-left font-medium">Date</th>
                <th class="px-4 py-3 text-left font-medium">Student</th>
                <th class="px-4 py-3 text-left font-medium">Status</th>
                <th class="px-4 py-3 text-left font-medium">Remarks</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="r in records.data" :key="r.id" class="border-b last:border-0">
                <td class="px-4 py-3">{{ r.date }}</td>
                <td class="px-4 py-3">{{ r.student?.last_name }}, {{ r.student?.first_name }}</td>
                <td class="px-4 py-3">
                  <span
                    class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
                    :class="{
                      'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300': r.status === 'present',
                      'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300': r.status === 'absent',
                      'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300': r.status === 'late',
                      'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300': r.status === 'excused',
                    }"
                  >
                    {{ r.status }}
                  </span>
                </td>
                <td class="px-4 py-3 text-muted-foreground">{{ r.remarks }}</td>
              </tr>
              <tr v-if="records.data.length === 0">
                <td colspan="4" class="px-4 py-12 text-center text-muted-foreground">
                  <p class="text-sm">No records found</p>
                  <p class="mt-1 text-xs">Adjust filters or record new attendance</p>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
