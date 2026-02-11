<script setup lang="ts">
import Heading from '@/components/Heading.vue'
import { Button } from '@/components/ui/button'
import { Empty, EmptyDescription, EmptyHeader, EmptyTitle } from '@/components/ui/empty'
import { Field, FieldLabel } from '@/components/ui/field'
import { Input } from '@/components/ui/input'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { Spinner } from '@/components/ui/spinner'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table'
import AppLayout from '@/layouts/AppLayout.vue'
import type { BreadcrumbItemType } from '@/types'
import { Head, Link, router } from '@inertiajs/vue3'
import { Loader } from 'lucide-vue-next'
import { computed, onMounted, ref } from 'vue'

const props = defineProps<{
  date: string
  classes: any[]
  streams: any[]
  academicYear: any
}>()

const breadcrumbs: BreadcrumbItemType[] = [
  { title: 'Attendance', href: '/teacher/attendance' },
]

const selectedDate = ref(props.date)
const selectedClassId = ref<string | number>('')
const selectedStreamId = ref<string | number>('')
const roster = ref<any[]>([])
const existing = ref<Record<number, any>>({})
const loading = ref(false)
const errorMsg = ref('')

const classStreams = computed(() => {
  const cid = Number(selectedClassId.value)
  if (!cid) return props.streams
  return props.streams.filter((s: any) => !('school_class_id' in s) || s.school_class_id === cid)
})

const fetchRoster = async () => {
  errorMsg.value = ''
  if (!selectedClassId.value || !selectedDate.value) return
  loading.value = true
  try {
    const params = new URLSearchParams({
      class_id: String(selectedClassId.value),
      date: selectedDate.value,
    })
    if (selectedStreamId.value) params.set('stream_id', String(selectedStreamId.value))
    const res = await fetch(`/teacher/attendance/roster?${params.toString()}`, { headers: { 'Accept': 'application/json' } })
    if (!res.ok) throw new Error('Failed to load roster')
    const data = await res.json()
    roster.value = data.students || []
    existing.value = data.existing || {}
  } catch (e: any) {
    errorMsg.value = e?.message || 'Error loading roster'
  } finally {
    loading.value = false
  }
}

onMounted(fetchRoster)
</script>

<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <Head title="Attendance" />

    <template #act>
        <Link
            href="/teacher/attendance/history"
            :as="Button" variant="outline">
            History
        </Link>

        <Button
          :disabled="loading || !selectedClassId || roster.length === 0"
          @click="router.visit('/teacher/attendance/record', { method: 'get', data: { date: selectedDate, class_id: selectedClassId, stream_id: selectedStreamId } })"
        >
          Record
        </Button>
    </template>

    <div class="space-y-6">
        <Heading
            title="Attendance"
            description="Select a class, stream and date to record daily attendance."
        />

      <div class="p-6">
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-4">
          <Field>
            <FieldLabel>Date</FieldLabel>
            <Input
              v-model="selectedDate"
              type="date"
              @change="fetchRoster"
            />
          </Field>

          <Field>
            <FieldLabel>Class</FieldLabel>
            <Select
              v-model="selectedClassId"
              @change="fetchRoster">
              <SelectTrigger>
                <SelectValue placeholder="Select class" />
              </SelectTrigger>

              <SelectContent>
                <SelectItem v-for="c in classes" :key="c.id" :value="c.id">
                    {{ c.name }}
                </SelectItem>
              </SelectContent>
            </Select>
          </Field>

          <Field>
            <FieldLabel>Stream</FieldLabel>
            <Select
              v-model="selectedStreamId"
              @change="fetchRoster">
              <SelectTrigger>
                <SelectValue placeholder="Select stream" />
              </SelectTrigger>

              <SelectContent>
                <SelectItem
                    v-for="s in classStreams"
                    :key="s.id" :value="s.id">
                    {{ s.name }}
                </SelectItem>
            </SelectContent>
            </Select>
          </Field>

          <div class="flex items-end justify-end">
            <Button size="icon-lg" :disabled="loading" @click="fetchRoster">
              <Spinner v-if="loading" />
              <Loader v-else />
            </Button>
          </div>
        </div>
      </div>

      <div v-if="errorMsg" class="rounded-lg border border-destructive/50 bg-destructive/10 p-4 text-sm text-destructive">
        {{ errorMsg }}
      </div>

      <div class="rounded-lg border bg-card">
        <div class="overflow-x-auto">
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead>Student</TableHead>
                <TableHead>Status</TableHead>
                <TableHead>Remarks</TableHead>
              </TableRow>
            </TableHeader>

            <TableBody>
              <TableRow v-for="st in roster" :key="st.id">
                <TableCell>{{ st.last_name }}, {{ st.first_name }}</TableCell>
                <TableCell>
                  <span
                    class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
                    :class="{
                      'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300': existing[st.id]?.status === 'present',
                      'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300': existing[st.id]?.status === 'absent',
                      'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300': existing[st.id]?.status === 'late',
                      'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300': existing[st.id]?.status === 'excused',
                      'bg-muted text-muted-foreground': !existing[st.id]?.status,
                    }"
                    v-text="existing[st.id]?.status || '—'"
                  />
                </TableCell>

                <TableCell>
                    {{ existing[st.id]?.remarks || '' }}
                </TableCell>
              </TableRow>

              <TableRow v-if="roster.length === 0">
                <TableCell colspan="3" class="py-12">
                    <Empty>
                        <EmptyHeader>
                            <EmptyTitle>
                                No students loaded
                            </EmptyTitle>

                            <EmptyDescription>
                                Select a class and date to view the roster
                            </EmptyDescription>
                        </EmptyHeader>
                    </Empty>
                </TableCell>
              </TableRow>
            </TableBody>
          </Table>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
