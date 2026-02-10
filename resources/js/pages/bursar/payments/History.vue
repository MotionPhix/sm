<script setup lang="ts">
import { ref, computed } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import Heading from '@/components/Heading.vue'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select'
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table'
import {
    DollarSign,
    Receipt,
    Filter,
    X,
    User,
    GraduationCap,
    CreditCard,
    Banknote,
    FileText,
    Smartphone,
    Wallet,
} from 'lucide-vue-next'
import { show as paymentShow } from '@/actions/App/Http/Controllers/Bursar/PaymentController'
import { show as invoiceShow } from '@/actions/App/Http/Controllers/Bursar/InvoiceController'
import { dashboard } from '@/routes/bursar'

interface Student {
    id: number
    first_name: string
    last_name: string
    admission_number: string
    active_enrollment: {
        classroom: {
            class: {
                name: string
            }
        }
    } | null
}

interface Payment {
    id: number
    uuid: string
    amount: string
    payment_date: string
    payment_method: string
    reference_number: string | null
    formatted_amount: string
    payment_method_label: string
    invoice: {
        id: number
        uuid: string
        invoice_number: string
    }
    recorded_by: {
        id: number
        name: string
    }
}

interface PaginatedPayments {
    data: Payment[]
    links: { url: string | null; label: string; active: boolean }[]
    current_page: number
    last_page: number
    total: number
    from: number | null
    to: number | null
}

const props = defineProps<{
    student: Student
    payments: PaginatedPayments
    totalPaid: number
    filters: {
        from_date?: string
        to_date?: string
        payment_method?: string
    }
}>()

const breadcrumbs = [
    { title: 'Dashboard', href: dashboard.url() },
    { title: `${props.student.first_name} ${props.student.last_name}`, href: '#' },
    { title: 'Payment History', href: '#' },
]

const fromDate = ref(props.filters.from_date || '')
const toDate = ref(props.filters.to_date || '')
const paymentMethod = ref(props.filters.payment_method || '')

const hasActiveFilters = computed(() => {
    return fromDate.value || toDate.value || paymentMethod.value
})

const updateFilters = () => {
    router.get(
        window.location.pathname,
        {
            from_date: fromDate.value || undefined,
            to_date: toDate.value || undefined,
            payment_method: paymentMethod.value || undefined,
        },
        { preserveState: true, preserveScroll: true }
    )
}

const clearFilters = () => {
    fromDate.value = ''
    toDate.value = ''
    paymentMethod.value = ''
    router.get(window.location.pathname, {}, { preserveState: true, preserveScroll: true })
}

const formatDate = (dateStr: string) => {
    return new Date(dateStr).toLocaleDateString('en-ZA', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    })
}

const formatCurrency = (amount: number | string) => {
    return `MK ${Number(amount).toLocaleString('en-MW', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`
}

const getMethodIcon = (method: string) => {
    switch (method) {
        case 'cash': return Banknote
        case 'bank_transfer': return CreditCard
        case 'mobile_money': return Smartphone
        case 'cheque': return FileText
        case 'card': return CreditCard
        default: return Wallet
    }
}

const paymentMethods = [
    { value: 'cash', label: 'Cash' },
    { value: 'bank_transfer', label: 'Bank Transfer' },
    { value: 'mobile_money', label: 'Mobile Money' },
    { value: 'cheque', label: 'Cheque' },
    { value: 'card', label: 'Card' },
    { value: 'other', label: 'Other' },
]
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="`Payment History - ${student.first_name} ${student.last_name}`" />

        <div class="space-y-6">
            <Heading
                :title="`Payment History`"
                :description="`${student.first_name} ${student.last_name} (${student.admission_number})`"
            />

            <!-- Student Info + Total Paid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <Card>
                    <CardContent class="pt-6">
                        <div class="flex items-center gap-4">
                            <div class="h-12 w-12 rounded-full bg-blue-100 dark:bg-blue-900 flex items-center justify-center shrink-0">
                                <User class="h-6 w-6 text-blue-600 dark:text-blue-400" />
                            </div>
                            <div>
                                <p class="font-medium">{{ student.first_name }} {{ student.last_name }}</p>
                                <p class="text-sm text-muted-foreground">{{ student.admission_number }}</p>
                                <div v-if="student.active_enrollment" class="flex items-center gap-1 mt-1">
                                    <GraduationCap class="h-3 w-3 text-muted-foreground" />
                                    <span class="text-xs text-muted-foreground">{{ student.active_enrollment.classroom.class.name }}</span>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <div class="rounded-xl bg-gradient-to-br from-green-500 to-green-600 p-6 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-green-100 text-sm">Total Paid</p>
                            <p class="text-3xl font-bold mt-1">{{ formatCurrency(totalPaid) }}</p>
                        </div>
                        <div class="bg-white/20 p-3 rounded-lg">
                            <DollarSign class="w-6 h-6" />
                        </div>
                    </div>
                    <p class="text-sm text-green-100 mt-2">
                        {{ payments.total }} payment{{ payments.total !== 1 ? 's' : '' }} recorded
                    </p>
                </div>
            </div>

            <!-- Filters -->
            <Card>
                <CardHeader>
                    <div class="flex items-center justify-between">
                        <CardTitle class="flex items-center gap-2 text-base">
                            <Filter class="h-4 w-4" />
                            Filters
                        </CardTitle>
                        <Button
                            v-if="hasActiveFilters"
                            variant="ghost"
                            size="sm"
                            @click="clearFilters"
                        >
                            <X class="h-4 w-4 mr-1" />
                            Clear
                        </Button>
                    </div>
                </CardHeader>
                <CardContent>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div class="space-y-2">
                            <Label for="from_date">From Date</Label>
                            <Input
                                id="from_date"
                                type="date"
                                v-model="fromDate"
                                @change="updateFilters"
                            />
                        </div>
                        <div class="space-y-2">
                            <Label for="to_date">To Date</Label>
                            <Input
                                id="to_date"
                                type="date"
                                v-model="toDate"
                                @change="updateFilters"
                            />
                        </div>
                        <div class="space-y-2">
                            <Label>Payment Method</Label>
                            <Select v-model="paymentMethod" @update:model-value="updateFilters">
                                <SelectTrigger>
                                    <SelectValue placeholder="All methods" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem
                                        v-for="method in paymentMethods"
                                        :key="method.value"
                                        :value="method.value"
                                    >
                                        {{ method.label }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Payments Table -->
            <Card>
                <CardContent class="pt-6">
                    <div v-if="payments.data.length > 0" class="rounded-lg border">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Date</TableHead>
                                    <TableHead>Invoice</TableHead>
                                    <TableHead>Amount</TableHead>
                                    <TableHead>Method</TableHead>
                                    <TableHead>Reference</TableHead>
                                    <TableHead>Recorded By</TableHead>
                                    <TableHead class="w-[80px]"></TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow
                                    v-for="payment in payments.data"
                                    :key="payment.id"
                                >
                                    <TableCell>{{ formatDate(payment.payment_date) }}</TableCell>
                                    <TableCell>
                                        <Link
                                            :href="invoiceShow.url(payment.invoice.uuid)"
                                            class="text-blue-600 hover:underline font-medium"
                                        >
                                            {{ payment.invoice.invoice_number }}
                                        </Link>
                                    </TableCell>
                                    <TableCell class="font-medium">{{ payment.formatted_amount }}</TableCell>
                                    <TableCell>
                                        <div class="flex items-center gap-2">
                                            <component :is="getMethodIcon(payment.payment_method)" class="h-4 w-4 text-muted-foreground" />
                                            {{ payment.payment_method_label }}
                                        </div>
                                    </TableCell>
                                    <TableCell class="text-muted-foreground font-mono text-xs">
                                        {{ payment.reference_number || '—' }}
                                    </TableCell>
                                    <TableCell class="text-muted-foreground">{{ payment.recorded_by.name }}</TableCell>
                                    <TableCell>
                                        <Link :href="paymentShow.url(payment.uuid)">
                                            <Button variant="ghost" size="sm">View</Button>
                                        </Link>
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>

                    <!-- Empty State -->
                    <div v-else class="text-center py-12 text-muted-foreground">
                        <Receipt class="h-12 w-12 mx-auto mb-3 opacity-50" />
                        <p class="font-medium">No payments found</p>
                        <p class="text-sm mt-1">
                            <template v-if="hasActiveFilters">
                                Try adjusting your filters
                            </template>
                            <template v-else>
                                No payments have been recorded for this student
                            </template>
                        </p>
                    </div>

                    <!-- Pagination -->
                    <div
                        v-if="payments.last_page > 1"
                        class="flex items-center justify-between mt-4 pt-4 border-t"
                    >
                        <p class="text-sm text-muted-foreground">
                            Showing {{ payments.from }} to {{ payments.to }} of {{ payments.total }} payments
                        </p>
                        <div class="flex gap-2">
                            <Link
                                v-for="link in payments.links"
                                :key="link.label"
                                :href="link.url || '#'"
                                preserve-state
                                preserve-scroll
                            >
                                <Button
                                    variant="outline"
                                    size="sm"
                                    :disabled="!link.url"
                                    :class="{ 'bg-primary text-primary-foreground': link.active }"
                                    v-html="link.label"
                                />
                            </Link>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
