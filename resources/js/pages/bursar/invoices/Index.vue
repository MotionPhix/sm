<script setup lang="ts">
import { Head, router, usePage } from '@inertiajs/vue3'
import { ref, computed } from 'vue'
import AppLayout from '@/layouts/AppLayout.vue'
import Heading from '@/components/Heading.vue'
import { Button } from '@/components/ui/button'
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table'
import { Input } from '@/components/ui/input'
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select'
import { Badge } from '@/components/ui/badge'
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card'
import { create as createRoute, show as showRoute } from '@/routes/bursar/invoices'

interface Invoice {
    id: number
    uuid: string
    invoice_number: string
    issue_date: string
    due_date: string
    total_amount: string
    paid_amount: string
    status: 'draft' | 'issued' | 'partially_paid' | 'paid' | 'overdue' | 'cancelled'
    outstanding_balance: string
    formatted_total: string
    formatted_paid: string
    formatted_outstanding: string
    student: {
        id: number
        first_name: string
        last_name: string
        admission_number: string
    }
    academic_year: {
        id: number
        name: string
    }
    term?: {
        id: number
        name: string
    }
}

interface Stats {
    total_invoiced: string
    total_paid: string
    overdue_count: number
    unpaid_count: number
}

interface AcademicYear {
    id: number
    name: string
}

interface Term {
    id: number
    name: string
}

const props = defineProps<{
    invoices: {
        data: Invoice[]
        total: number
        per_page: number
        current_page: number
        last_page: number
    }
    stats: Stats
    academicYears: AcademicYear[]
    terms: Term[]
    filters: {
        search?: string
        status?: string
        academic_year_id?: string
        term_id?: string
    }
}>()

const page = usePage()
const flash = computed(() => page.props.flash as { success?: string; error?: string })

const breadcrumbs = [
    { title: 'Dashboard', href: '/bursar/dashboard' },
    { title: 'Invoices', href: '#' },
]

const search = ref(props.filters.search || '')
const selectedStatus = ref(props.filters.status || 'all')
const selectedAcademicYear = ref(props.filters.academic_year_id || 'all')
const selectedTerm = ref(props.filters.term_id || 'all')

function updateFilters() {
    router.get('/bursar/invoices', {
        search: search.value || undefined,
        status: selectedStatus.value !== 'all' ? selectedStatus.value : undefined,
        academic_year_id: selectedAcademicYear.value !== 'all' ? selectedAcademicYear.value : undefined,
        term_id: selectedTerm.value !== 'all' ? selectedTerm.value : undefined,
    }, {
        preserveState: true,
        replace: true,
    })
}

function clearFilters() {
    search.value = ''
    selectedStatus.value = 'all'
    selectedAcademicYear.value = 'all'
    selectedTerm.value = 'all'
    updateFilters()
}

function formatDate(dateString: string): string {
    return new Date(dateString).toLocaleDateString('en-ZA', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    })
}

function getStatusBadgeVariant(status: Invoice['status']): 'default' | 'secondary' | 'destructive' | 'outline' {
    switch (status) {
        case 'paid':
            return 'default'
        case 'issued':
            return 'secondary'
        case 'partially_paid':
            return 'outline'
        case 'overdue':
            return 'destructive'
        case 'cancelled':
            return 'destructive'
        default:
            return 'outline'
    }
}

function getStatusLabel(status: Invoice['status']): string {
    return status
        .split('_')
        .map(word => word.charAt(0).toUpperCase() + word.slice(1))
        .join(' ')
}

const hasActiveFilters = computed(() => {
    return search.value || selectedStatus.value !== 'all' ||
        selectedAcademicYear.value !== 'all' || selectedTerm.value !== 'all'
})

function formatCurrency(amount: string): string {
    return 'MK ' + parseFloat(amount).toLocaleString('en-MW', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    })
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Invoices" />

        <article class="p-5">
            <div class="flex items-start justify-between">
                <Heading
                    title="Invoices"
                    description="Manage student invoices and track payments"
                />
                <Button :href="createRoute()">
                    Generate Invoice
                </Button>
            </div>

            <!-- Stats Cards -->
            <div class="mt-6 grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                <Card>
                    <CardHeader class="pb-3">
                        <CardDescription>Total Invoiced</CardDescription>
                        <CardTitle class="text-2xl">
                            {{ formatCurrency(stats.total_invoiced) }}
                        </CardTitle>
                    </CardHeader>
                </Card>

                <Card>
                    <CardHeader class="pb-3">
                        <CardDescription>Total Paid</CardDescription>
                        <CardTitle class="text-2xl">
                            {{ formatCurrency(stats.total_paid) }}
                        </CardTitle>
                    </CardHeader>
                </Card>

                <Card>
                    <CardHeader class="pb-3">
                        <CardDescription>Outstanding</CardDescription>
                        <CardTitle class="text-2xl">
                            {{ formatCurrency((parseFloat(stats.total_invoiced) - parseFloat(stats.total_paid)).toFixed(2)) }}
                        </CardTitle>
                    </CardHeader>
                </Card>

                <Card>
                    <CardHeader class="pb-3">
                        <CardDescription>Overdue</CardDescription>
                        <CardTitle class="text-2xl">
                            {{ stats.overdue_count }}
                        </CardTitle>
                        <p class="text-xs text-muted-foreground">
                            {{ stats.unpaid_count }} unpaid
                        </p>
                    </CardHeader>
                </Card>
            </div>

            <div class="mt-6 flex flex-col gap-4">
                <!-- Filters -->
                <div class="flex flex-wrap items-center gap-3">
                    <Input
                        v-model="search"
                        placeholder="Search by invoice # or student..."
                        class="max-w-xs"
                        @input="updateFilters"
                    />

                    <Select v-model="selectedStatus" @update:model-value="updateFilters">
                        <SelectTrigger class="w-[150px]">
                            <SelectValue placeholder="All Status" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">All Status</SelectItem>
                            <SelectItem value="draft">Draft</SelectItem>
                            <SelectItem value="issued">Issued</SelectItem>
                            <SelectItem value="partially_paid">Partially Paid</SelectItem>
                            <SelectItem value="paid">Paid</SelectItem>
                            <SelectItem value="overdue">Overdue</SelectItem>
                            <SelectItem value="cancelled">Cancelled</SelectItem>
                        </SelectContent>
                    </Select>

                    <Select v-model="selectedAcademicYear" @update:model-value="updateFilters">
                        <SelectTrigger class="w-[180px]">
                            <SelectValue placeholder="All Years" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">All Years</SelectItem>
                            <SelectItem
                                v-for="year in academicYears"
                                :key="year.id"
                                :value="String(year.id)">
                                {{ year.name }}
                            </SelectItem>
                        </SelectContent>
                    </Select>

                    <Select v-model="selectedTerm" @update:model-value="updateFilters">
                        <SelectTrigger class="w-[150px]">
                            <SelectValue placeholder="All Terms" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">All Terms</SelectItem>
                            <SelectItem
                                v-for="term in terms"
                                :key="term.id"
                                :value="String(term.id)">
                                {{ term.name }}
                            </SelectItem>
                        </SelectContent>
                    </Select>

                    <Button
                        v-if="hasActiveFilters"
                        variant="outline"
                        size="sm"
                        @click="clearFilters">
                        Clear filters
                    </Button>
                </div>

                <!-- Invoices Table -->
                <div class="rounded-lg border">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Invoice #</TableHead>
                                <TableHead>Student</TableHead>
                                <TableHead>Issue Date</TableHead>
                                <TableHead>Due Date</TableHead>
                                <TableHead>Academic Year</TableHead>
                                <TableHead>Term</TableHead>
                                <TableHead class="text-right">Amount</TableHead>
                                <TableHead class="text-right">Paid</TableHead>
                                <TableHead class="text-right">Outstanding</TableHead>
                                <TableHead>Status</TableHead>
                                <TableHead class="text-right">Actions</TableHead>
                            </TableRow>
                        </TableHeader>

                        <TableBody>
                            <TableRow
                                v-for="invoice in invoices.data"
                                :key="invoice.id"
                                class="cursor-pointer hover:bg-muted/50"
                                @click="router.visit(showRoute(invoice).url)">
                                <TableCell class="font-mono text-sm font-medium">
                                    {{ invoice.invoice_number }}
                                </TableCell>

                                <TableCell>
                                    <div>
                                        <div class="font-medium">
                                            {{ invoice.student.first_name }} {{ invoice.student.last_name }}
                                        </div>
                                        <div class="text-xs text-muted-foreground">
                                            {{ invoice.student.admission_number }}
                                        </div>
                                    </div>
                                </TableCell>

                                <TableCell>
                                    {{ formatDate(invoice.issue_date) }}
                                </TableCell>

                                <TableCell>
                                    {{ formatDate(invoice.due_date) }}
                                </TableCell>

                                <TableCell>
                                    <span class="text-sm">{{ invoice.academic_year.name }}</span>
                                </TableCell>

                                <TableCell>
                                    <span v-if="invoice.term" class="text-sm">
                                        {{ invoice.term.name }}
                                    </span>
                                    <span v-else class="text-sm text-muted-foreground">
                                        Full Year
                                    </span>
                                </TableCell>

                                <TableCell class="text-right font-medium">
                                    {{ invoice.formatted_total }}
                                </TableCell>

                                <TableCell class="text-right">
                                    {{ invoice.formatted_paid }}
                                </TableCell>

                                <TableCell class="text-right font-medium">
                                    {{ invoice.formatted_outstanding }}
                                </TableCell>

                                <TableCell>
                                    <Badge :variant="getStatusBadgeVariant(invoice.status)">
                                        {{ getStatusLabel(invoice.status) }}
                                    </Badge>
                                </TableCell>

                                <TableCell class="text-right" @click.stop>
                                    <Button
                                        variant="ghost"
                                        size="sm"
                                        :href="showRoute(invoice).url">
                                        View
                                    </Button>
                                </TableCell>
                            </TableRow>

                            <TableRow v-if="invoices.data.length === 0">
                                <TableCell colspan="11" class="p-8 text-center">
                                    <div class="flex flex-col items-center gap-2">
                                        <p class="text-lg font-medium text-muted-foreground">
                                            No invoices found
                                        </p>
                                        <p class="text-sm text-muted-foreground">
                                            Get started by generating your first invoice
                                        </p>
                                    </div>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>

                <!-- Pagination -->
                <div v-if="invoices.last_page > 1" class="flex items-center justify-between">
                    <p class="text-sm text-muted-foreground">
                        Showing {{ invoices.data.length }} of {{ invoices.total }} invoices
                    </p>
                    <div class="flex gap-2">
                        <Button
                            variant="outline"
                            size="sm"
                            :disabled="invoices.current_page === 1"
                            @click="router.get('/bursar/invoices', { ...filters, page: invoices.current_page - 1 })">
                            Previous
                        </Button>
                        <Button
                            variant="outline"
                            size="sm"
                            :disabled="invoices.current_page === invoices.last_page"
                            @click="router.get('/bursar/invoices', { ...filters, page: invoices.current_page + 1 })">
                            Next
                        </Button>
                    </div>
                </div>
            </div>
        </article>
    </AppLayout>
</template>
