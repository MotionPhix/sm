<script setup lang="ts">
import { Head, router, Link } from '@inertiajs/vue3'
import { ref, onMounted, computed } from 'vue'
import { Button } from '@/components/ui/button'
import AppLayout from '@/layouts/AppLayout.vue'
import Heading from '@/components/Heading.vue'
import type { BreadcrumbItemType } from '@/types'

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

    <div class="space-y-6">
      <div class="flex items-center justify-between">
        <Heading
          title="Attendance"
          description="Select a class, stream and date to record daily attendance."
        />
        <Link href="/teacher/attendance/history">
          <Button variant="outline">View History</Button>
        </Link>
      </div>

      <div class="rounded-lg border bg-card p-6">
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-4">
          <div>
            <label class="mb-1 block text-sm font-medium">Date</label>
            <input
              v-model="selectedDate"
              type="date"
              class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2"
              @change="fetchRoster"
            />
          </div>
          <div>
            <label class="mb-1 block text-sm font-medium">Class</label>
            <select
              v-model="selectedClassId"
              class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2"
              @change="fetchRoster"
            >
              <option value="">Select class</option>
              <option v-for="c in classes" :key="c.id" :value="c.id">{{ c.name }}</option>
            </select>
          </div>
          <div>
            <label class="mb-1 block text-sm font-medium">Stream</label>
            <select
              v-model="selectedStreamId"
              class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2"
              @change="fetchRoster"
            >
              <option value="">All/None</option>
              <option v-for="s in classStreams" :key="s.id" :value="s.id">{{ s.name }}</option>
            </select>
          </div>
          <div class="flex items-end">
            <Button :disabled="loading" @click="fetchRoster">
              <span v-if="loading">Loading...</span>
              <span v-else>Load roster</span>
            </Button>
          </div>
        </div>
      </div>

      <div v-if="errorMsg" class="rounded-lg border border-destructive/50 bg-destructive/10 p-4 text-sm text-destructive">
        {{ errorMsg }}
      </div>

      <div class="rounded-lg border bg-card">
        <div class="overflow-x-auto">
          <table class="min-w-full text-sm">
            <thead>
              <tr class="border-b bg-muted/50">
                <th class="px-4 py-3 text-left font-medium">Student</th>
                <th class="px-4 py-3 text-left font-medium">Status</th>
                <th class="px-4 py-3 text-left font-medium">Remarks</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="st in roster" :key="st.id" class="border-b last:border-0">
                <td class="px-4 py-3">{{ st.last_name }}, {{ st.first_name }}</td>
                <td class="px-4 py-3">
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
                </td>
                <td class="px-4 py-3 text-muted-foreground">{{ existing[st.id]?.remarks || '' }}</td>
              </tr>
              <tr v-if="roster.length === 0">
                <td colspan="3" class="px-4 py-12 text-center text-muted-foreground">
                  <p class="text-sm">No students loaded</p>
                  <p class="mt-1 text-xs">Select a class and date to view the roster</p>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <div class="flex justify-end">
        <Button
          :disabled="loading || !selectedClassId || roster.length === 0"
          @click="router.visit('/teacher/attendance/record', { method: 'get', data: { date: selectedDate, class_id: selectedClassId, stream_id: selectedStreamId } })"
        >
          Record attendance
        </Button>
      </div>
    </div>
  </AppLayout>
</template>
