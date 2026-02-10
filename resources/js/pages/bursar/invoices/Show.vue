<script setup lang="ts">
import { Head, router, usePage } from '@inertiajs/vue3'
import { ref, computed } from 'vue'
import AppLayout from '@/layouts/AppLayout.vue'
import Heading from '@/components/Heading.vue'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table'
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card'
import { Separator } from '@/components/ui/separator'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select'
import { Textarea } from '@/components/ui/textarea'

interface InvoiceItem {
    id: number
    description: string
    quantity: number
    unit_price: string
    amount: string
    formatted_amount: string
    formatted_unit_price: string
    fee_item: {
        id: number
        name: string
        code: string
    }
}

interface Payment {
    id: number
    uuid: string
    amount: string
    formatted_amount: string
    payment_date: string
    payment_method: string
    payment_method_label: string
    reference_number?: string
    notes?: string
    recorded_by: {
        id: number
        first_name: string
        last_name: string
    }
}

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
    notes?: string
    cancelled_at?: string
    cancellation_reason?: string
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
    school: {
        id: number
        name: string
    }
    items: InvoiceItem[]
    payments: Payment[]
    issued_by: {
        id: number
        first_name: string
        last_name: string
    }
}

const props = defineProps<{
    invoice: Invoice
}>()

const page = usePage()
const flash = computed(() => page.props.flash as { success?: string; error?: string })

const breadcrumbs = [
    { title: 'Dashboard', href: '/bursar/dashboard' },
    { title: 'Invoices', href: '/bursar/invoices' },
    { title: props.invoice.invoice_number, href: '#' },
]

const showPaymentModal = ref(false)
const showCancelModal = ref(false)

const paymentForm = ref({
    amount: '',
    payment_method: 'cash',
    payment_date: new Date().toISOString().split('T')[0],
    reference_number: '',
    notes: '',
})

const cancelForm = ref({
    reason: '',
})

function formatDate(dateString: string): string {
    return new Date(dateString).toLocaleDateString('en-ZA', {
        year: 'numeric',
        month: 'long',
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

function recordPayment() {
    router.post(`/bursar/invoices/${props.invoice.uuid}/payments`, paymentForm.value, {
        preserveScroll: true,
        onSuccess: () => {
            showPaymentModal.value = false
            paymentForm.value = {
                amount: '',
                payment_method: 'cash',
                payment_date: new Date().toISOString().split('T')[0],
                reference_number: '',
                notes: '',
            }
        },
    })
}

function cancelInvoice() {
    router.post(`/bursar/invoices/${props.invoice.uuid}/cancel`, cancelForm.value, {
        preserveScroll: true,
        onSuccess: () => {
            showCancelModal.value = false
            cancelForm.value = { reason: '' }
        },
    })
}

const canRecordPayment = computed(() => {
    return ['issued', 'partially_paid', 'overdue'].includes(props.invoice.status) &&
        parseFloat(props.invoice.outstanding_balance) > 0
})

const canCancel = computed(() => {
    return ['draft', 'issued'].includes(props.invoice.status) &&
        parseFloat(props.invoice.paid_amount) === 0
})
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="`Invoice ${invoice.invoice_number}`" />

        <article class="p-5">
            <!-- Header -->
            <div class="flex items-start justify-between">
                <div>
                    <Heading
                        :title="`Invoice ${invoice.invoice_number}`"
                        :description="`for ${invoice.formatted_total}`"
                    />
                    <div class="mt-2 flex items-center gap-2">
                        <Badge :variant="getStatusBadgeVariant(invoice.status)">
                            {{ getStatusLabel(invoice.status) }}
                        </Badge>
                        <span class="text-sm text-muted-foreground">
                            Due {{ formatDate(invoice.due_date) }}
                        </span>
                    </div>
                </div>
                <div class="flex gap-2">
                    <Button
                        v-if="canRecordPayment"
                        @click="showPaymentModal = true">
                        Record Payment
                    </Button>
                    <Button
                        v-if="canCancel"
                        variant="destructive"
                        @click="showCancelModal = true">
                        Cancel Invoice
                    </Button>
                </div>
            </div>

            <!-- Invoice Preview Card -->
            <Card class="mt-6">
                <!-- Invoice Header with Gradient Background -->
                <div class="relative overflow-hidden rounded-t-lg bg-gradient-to-br from-slate-800 to-slate-900 p-8 text-white dark:from-slate-900 dark:to-slate-950">
                    <div class="relative z-10 flex items-start justify-between">
                        <div>
                            <h2 class="text-3xl font-bold">Invoice</h2>
                            <p class="mt-1 text-xl font-mono">{{ invoice.invoice_number }}</p>
                        </div>
                        <div class="rounded-lg bg-white/10 p-3 backdrop-blur-sm">
                            <svg class="h-10 w-10" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-7 3c1.93 0 3.5 1.57 3.5 3.5S13.93 13 12 13s-3.5-1.57-3.5-3.5S10.07 6 12 6zm7 13H5v-.23c0-.62.28-1.2.76-1.58C7.47 15.82 9.64 15 12 15s4.53.82 6.24 2.19c.48.38.76.97.76 1.58V19z"/>
                            </svg>
                        </div>
                    </div>
                    <div class="relative z-10 mt-6 grid gap-4 sm:grid-cols-3">
                        <div>
                            <p class="text-sm opacity-75">Date of Issue</p>
                            <p class="mt-1 font-medium">{{ formatDate(invoice.issue_date) }}</p>
                        </div>
                        <div>
                            <p class="text-sm opacity-75">Date Due</p>
                            <p class="mt-1 font-medium">{{ formatDate(invoice.due_date) }}</p>
                        </div>
                        <div>
                            <p class="text-sm opacity-75">Academic Period</p>
                            <p class="mt-1 font-medium">
                                {{ invoice.academic_year.name }}
                                <span v-if="invoice.term"> - {{ invoice.term.name }}</span>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Invoice Body -->
                <CardContent class="p-8">
                    <!-- Amount Due Badge -->
                    <div class="mb-6 rounded-lg bg-slate-50 p-4 dark:bg-slate-900">
                        <p class="text-lg font-bold">{{ invoice.formatted_outstanding }} due {{ formatDate(invoice.due_date) }}</p>
                    </div>

                    <!-- Bill From / Bill To -->
                    <div class="grid gap-8 sm:grid-cols-2">
                        <div>
                            <p class="text-sm font-semibold uppercase tracking-wide text-muted-foreground">
                                Bill From
                            </p>
                            <div class="mt-2 space-y-1">
                                <p class="font-semibold">{{ invoice.school.name }}</p>
                                <p class="text-sm text-muted-foreground">Malawi</p>
                            </div>
                        </div>

                        <div>
                            <p class="text-sm font-semibold uppercase tracking-wide text-muted-foreground">
                                Bill To
                            </p>
                            <div class="mt-2 space-y-1">
                                <p class="font-semibold">
                                    {{ invoice.student.first_name }} {{ invoice.student.last_name }}
                                </p>
                                <p class="text-sm text-muted-foreground">
                                    Student #{{ invoice.student.admission_number }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Line Items -->
                    <div class="mt-8">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Item</TableHead>
                                    <TableHead class="text-center">Qty</TableHead>
                                    <TableHead class="text-right">Unit Price</TableHead>
                                    <TableHead class="text-right">Amount</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-for="item in invoice.items" :key="item.id">
                                    <TableCell>
                                        <div class="font-medium">{{ item.description }}</div>
                                        <div class="text-xs text-muted-foreground">
                                            Code: {{ item.fee_item.code }}
                                        </div>
                                    </TableCell>
                                    <TableCell class="text-center">{{ item.quantity }}</TableCell>
                                    <TableCell class="text-right">{{ item.formatted_unit_price }}</TableCell>
                                    <TableCell class="text-right font-medium">
                                        {{ item.formatted_amount }}
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>

                    <!-- Totals -->
                    <div class="mt-6 flex justify-end">
                        <div class="w-full max-w-xs space-y-2">
                            <div class="flex justify-between text-sm">
                                <span class="text-muted-foreground">Subtotal</span>
                                <span class="font-medium">{{ invoice.formatted_total }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-muted-foreground">Amount Paid</span>
                                <span class="font-medium text-green-600 dark:text-green-400">
                                    {{ invoice.formatted_paid }}
                                </span>
                            </div>
                            <Separator />
                            <div class="flex justify-between text-lg font-bold">
                                <span>Amount Due</span>
                                <span>{{ invoice.formatted_outstanding }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Notes -->
                    <div v-if="invoice.notes" class="mt-6 rounded-lg bg-muted p-4">
                        <p class="text-sm font-semibold">Notes</p>
                        <p class="mt-1 text-sm text-muted-foreground">{{ invoice.notes }}</p>
                    </div>

                    <!-- Cancellation Info -->
                    <div v-if="invoice.cancelled_at" class="mt-6 rounded-lg border-destructive bg-destructive/10 p-4">
                        <p class="font-semibold text-destructive">Invoice Cancelled</p>
                        <p class="mt-1 text-sm text-muted-foreground">
                            Cancelled on {{ formatDate(invoice.cancelled_at) }}
                        </p>
                        <p v-if="invoice.cancellation_reason" class="mt-1 text-sm">
                            Reason: {{ invoice.cancellation_reason }}
                        </p>
                    </div>
                </CardContent>
            </Card>

            <!-- Payment History -->
            <Card v-if="invoice.payments.length > 0" class="mt-6">
                <CardHeader>
                    <CardTitle>Payment History</CardTitle>
                    <CardDescription>All payments recorded for this invoice</CardDescription>
                </CardHeader>
                <CardContent>
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Date</TableHead>
                                <TableHead>Amount</TableHead>
                                <TableHead>Method</TableHead>
                                <TableHead>Reference</TableHead>
                                <TableHead>Recorded By</TableHead>
                                <TableHead>Notes</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="payment in invoice.payments" :key="payment.id">
                                <TableCell>
                                    {{ formatDate(payment.payment_date) }}
                                </TableCell>
                                <TableCell class="font-medium">
                                    {{ payment.formatted_amount }}
                                </TableCell>
                                <TableCell>
                                    <Badge variant="outline">
                                        {{ payment.payment_method_label }}
                                    </Badge>
                                </TableCell>
                                <TableCell>
                                    <span v-if="payment.reference_number" class="font-mono text-sm">
                                        {{ payment.reference_number }}
                                    </span>
                                    <span v-else class="text-muted-foreground">-</span>
                                </TableCell>
                                <TableCell>
                                    {{ payment.recorded_by.first_name }} {{ payment.recorded_by.last_name }}
                                </TableCell>
                                <TableCell>
                                    <span v-if="payment.notes" class="text-sm">
                                        {{ payment.notes }}
                                    </span>
                                    <span v-else class="text-muted-foreground">-</span>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </CardContent>
            </Card>

            <!-- Record Payment Modal -->
            <div
                v-if="showPaymentModal"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
                @click.self="showPaymentModal = false">
                <Card class="w-full max-w-lg">
                    <CardHeader>
                        <CardTitle>Record Payment</CardTitle>
                        <CardDescription>
                            Outstanding balance: {{ invoice.formatted_outstanding }}
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div>
                            <Label for="amount">Amount</Label>
                            <Input
                                id="amount"
                                v-model="paymentForm.amount"
                                type="number"
                                step="0.01"
                                :placeholder="`Max: ${invoice.outstanding_balance}`"
                            />
                        </div>

                        <div>
                            <Label for="payment_method">Payment Method</Label>
                            <Select v-model="paymentForm.payment_method">
                                <SelectTrigger id="payment_method">
                                    <SelectValue />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="cash">Cash</SelectItem>
                                    <SelectItem value="bank_transfer">Bank Transfer</SelectItem>
                                    <SelectItem value="mobile_money">Mobile Money</SelectItem>
                                    <SelectItem value="cheque">Cheque</SelectItem>
                                    <SelectItem value="card">Card</SelectItem>
                                    <SelectItem value="other">Other</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>

                        <div>
                            <Label for="payment_date">Payment Date</Label>
                            <Input
                                id="payment_date"
                                v-model="paymentForm.payment_date"
                                type="date"
                            />
                        </div>

                        <div>
                            <Label for="reference_number">Reference Number (Optional)</Label>
                            <Input
                                id="reference_number"
                                v-model="paymentForm.reference_number"
                                placeholder="Transaction reference"
                            />
                        </div>

                        <div>
                            <Label for="notes">Notes (Optional)</Label>
                            <Textarea
                                id="notes"
                                v-model="paymentForm.notes"
                                placeholder="Additional payment notes"
                                rows="3"
                            />
                        </div>

                        <div class="flex justify-end gap-2 pt-4">
                            <Button variant="outline" @click="showPaymentModal = false">
                                Cancel
                            </Button>
                            <Button
                                :disabled="!paymentForm.amount || parseFloat(paymentForm.amount) <= 0"
                                @click="recordPayment">
                                Record Payment
                            </Button>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Cancel Invoice Modal -->
            <div
                v-if="showCancelModal"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
                @click.self="showCancelModal = false">
                <Card class="w-full max-w-md">
                    <CardHeader>
                        <CardTitle>Cancel Invoice</CardTitle>
                        <CardDescription>
                            This action cannot be undone. Please provide a reason.
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div>
                            <Label for="reason">Cancellation Reason</Label>
                            <Textarea
                                id="reason"
                                v-model="cancelForm.reason"
                                placeholder="Reason for cancellation..."
                                rows="3"
                            />
                        </div>

                        <div class="flex justify-end gap-2 pt-4">
                            <Button variant="outline" @click="showCancelModal = false">
                                Don't Cancel
                            </Button>
                            <Button
                                variant="destructive"
                                :disabled="!cancelForm.reason"
                                @click="cancelInvoice">
                                Confirm Cancellation
                            </Button>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </article>
    </AppLayout>
</template>
