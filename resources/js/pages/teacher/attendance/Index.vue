<script setup lang="ts">
import Heading from '@/components/Heading.vue'
import { Button } from '@/components/ui/button'
import { Empty, EmptyDescription, EmptyHeader, EmptyTitle } from '@/components/ui/empty'
import { Field, FieldLabel } from '@/components/ui/field'
import { Input } from '@/components/ui/input'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table'
import AppLayout from '@/layouts/AppLayout.vue'
import type { BreadcrumbItemType } from '@/types'
import { Head, Link, router } from '@inertiajs/vue3'
import { index as attendanceIndex, record as attendanceRecord, history as attendanceHistory } from '@/actions/App/Http/Controllers/Teacher/AttendanceController'
import { ref, watch } from 'vue'

const props = defineProps<{
    date: string
    classes: any[]
    streams: any[]
    academicYear: any
    roster: any[]
    existing: Record<number, any>
    filters: {
        date: string
        class_id: string | number | null
        stream_id: string | number | null
    }
}>()

const breadcrumbs: BreadcrumbItemType[] = [
    { title: 'Attendance', href: attendanceIndex.url() },
]

const selectedDate = ref(props.filters.date ?? props.date)
const selectedClassId = ref<string | number>(props.filters.class_id ?? '')
const selectedStreamId = ref<string | number>(props.filters.stream_id ?? '')

const updateFilters = () => {
    if (!selectedClassId.value || !selectedDate.value) return

    const query: Record<string, string> = {
        date: selectedDate.value,
        class_id: String(selectedClassId.value),
    }

    if (selectedStreamId.value) {
        query.stream_id = String(selectedStreamId.value)
    }

    router.get(attendanceIndex.url(), query, {
        preserveState: true,
        replace: true,
    })
}

watch(selectedClassId, () => {
    selectedStreamId.value = ''
    updateFilters()
})

watch(selectedStreamId, (val, oldVal) => {
    if (val !== oldVal) {
        updateFilters()
    }
})
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Attendance" />

        <template #act>
            <Link :href="attendanceHistory.url()" :as="Button" variant="outline">
                History
            </Link>

            <Button
                :disabled="!selectedClassId || props.roster.length === 0"
                @click="
                    router.visit(
                        attendanceRecord.url({
                            query: {
                                date: selectedDate,
                                class_id: String(selectedClassId),
                                stream_id: selectedStreamId ? String(selectedStreamId) : undefined,
                            },
                        }),
                    )
                "
            >
                Record
            </Button>
        </template>

        <div class="space-y-6">
            <Heading title="Attendance" description="Select a class, stream and date to record daily attendance." />

            <div class="p-6">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-4">
                    <Field>
                        <FieldLabel>Date</FieldLabel>
                        <Input v-model="selectedDate" type="date" @change="updateFilters" />
                    </Field>

                    <Field>
                        <FieldLabel>Class</FieldLabel>
                        <Select v-model="selectedClassId">
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
                        <Select v-model="selectedStreamId">
                            <SelectTrigger>
                                <SelectValue placeholder="All streams" />
                            </SelectTrigger>

                            <SelectContent>
                                <SelectItem v-for="s in streams" :key="s.id" :value="s.id">
                                    {{ s.name }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </Field>

                    <div class="flex items-end justify-end">
                        <Button :disabled="!selectedClassId" @click="updateFilters"> Load </Button>
                    </div>
                </div>
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
                            <TableRow v-for="st in props.roster" :key="st.id">
                                <TableCell>{{ st.last_name }}, {{ st.first_name }}</TableCell>
                                <TableCell>
                                    <span
                                        class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
                                        :class="{
                                            'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300':
                                                existing[st.id]?.status === 'present',
                                            'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300':
                                                existing[st.id]?.status === 'absent',
                                            'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300':
                                                existing[st.id]?.status === 'late',
                                            'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300':
                                                existing[st.id]?.status === 'excused',
                                            'bg-muted text-muted-foreground': !existing[st.id]?.status,
                                        }"
                                        v-text="existing[st.id]?.status || '—'"
                                    />
                                </TableCell>

                                <TableCell>
                                    {{ existing[st.id]?.remarks || '' }}
                                </TableCell>
                            </TableRow>

                            <TableRow v-if="props.roster.length === 0">
                                <TableCell colspan="3" class="py-12">
                                    <Empty>
                                        <EmptyHeader>
                                            <EmptyTitle> No students loaded </EmptyTitle>

                                            <EmptyDescription> Select a class and date to view the roster </EmptyDescription>
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
