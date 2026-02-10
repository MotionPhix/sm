<script setup lang="ts">
import { computed } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import { Separator } from '@/components/ui/separator'
import {
    ArrowLeft,
    Receipt,
    FileText,
    CreditCard,
    Calendar,
    User,
    Hash,
    Banknote,
    Smartphone,
    Wallet,
} from 'lucide-vue-next'
import { show as invoiceShow } from '@/actions/App/Http/Controllers/Bursar/InvoiceController'
import { dashboard } from '@/routes/bursar'
import { index as invoicesIndex } from '@/actions/App/Http/Controllers/Bursar/InvoiceController'

interface Payment {
    id: number
    uuid: string
    amount: string
    payment_date: string
    payment_method: string
    reference_number: string | null
    notes: string | null
    formatted_amount: string
    payment_method_label: string
    invoice: {
        id: number
        uuid: string
        invoice_number: string
        total_amount: string
        paid_amount: string
        outstanding_balance: number
        status: string
        status_label: string
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
        term: {
            id: number
            name: string
        } | null
    }
    student: {
        id: number
        first_name: string
        last_name: string
        admission_number: string
    }
    recorded_by: {
        id: number
        name: string
    }
}

const props = defineProps<{
    payment: Payment
}>()

const breadcrumbs = [
    { title: 'Dashboard', href: dashboard.url() },
    { title: 'Invoices', href: invoicesIndex.url() },
    { title: props.payment.invoice.invoice_number, href: invoiceShow.url(props.payment.invoice.uuid) },
    { title: 'Payment', href: '#' },
]

const formatDate = (dateStr: string) => {
    return new Date(dateStr).toLocaleDateString('en-ZA', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    })
}

const getStatusBadgeVariant = (status: string) => {
    switch (status) {
        case 'paid': return 'default' as const
        case 'overdue': return 'destructive' as const
        case 'partially_paid': return 'secondary' as const
        case 'issued': return 'outline' as const
        default: return 'outline' as const
    }
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

const getStatusLabel = (status: string) => {
    return status.replace(/_/g, ' ').replace(/\b\w/g, (l: string) => l.toUpperCase())
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="`Payment - ${payment.invoice.invoice_number}`" />

        <div class="max-w-3xl mx-auto space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">Payment Receipt</h1>
                    <p class="text-muted-foreground">
                        Payment for invoice {{ payment.invoice.invoice_number }}
                    </p>
                </div>
                <Link :href="invoiceShow.url(payment.invoice.uuid)">
                    <Button variant="outline">
                        <ArrowLeft class="h-4 w-4 mr-2" />
                        Back to Invoice
                    </Button>
                </Link>
            </div>

            <!-- Payment Amount Card -->
            <div class="rounded-xl bg-gradient-to-br from-purple-500 to-indigo-600 p-6 text-white">
                <div class="flex items-center gap-4">
                    <div class="bg-white/20 p-4 rounded-xl">
                        <Receipt class="w-8 h-8" />
                    </div>
                    <div>
                        <p class="text-purple-100 text-sm">Payment Amount</p>
                        <p class="text-4xl font-bold mt-1">{{ payment.formatted_amount }}</p>
                        <p class="text-purple-200 text-sm mt-1">
                            {{ payment.payment_method_label }} &middot; {{ formatDate(payment.payment_date) }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Payment Details -->
            <Card>
                <CardHeader>
                    <CardTitle class="flex items-center gap-2">
                        <Receipt class="h-5 w-5 text-purple-600" />
                        Payment Details
                    </CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <div class="space-y-1">
                            <div class="flex items-center gap-2 text-muted-foreground text-sm">
                                <CreditCard class="h-4 w-4" />
                                Payment Method
                            </div>
                            <p class="font-medium flex items-center gap-2">
                                <component :is="getMethodIcon(payment.payment_method)" class="h-4 w-4 text-muted-foreground" />
                                {{ payment.payment_method_label }}
                            </p>
                        </div>

                        <div class="space-y-1">
                            <div class="flex items-center gap-2 text-muted-foreground text-sm">
                                <Calendar class="h-4 w-4" />
                                Payment Date
                            </div>
                            <p class="font-medium">{{ formatDate(payment.payment_date) }}</p>
                        </div>

                        <div v-if="payment.reference_number" class="space-y-1">
                            <div class="flex items-center gap-2 text-muted-foreground text-sm">
                                <Hash class="h-4 w-4" />
                                Reference Number
                            </div>
                            <p class="font-medium font-mono">{{ payment.reference_number }}</p>
                        </div>

                        <div class="space-y-1">
                            <div class="flex items-center gap-2 text-muted-foreground text-sm">
                                <User class="h-4 w-4" />
                                Recorded By
                            </div>
                            <p class="font-medium">{{ payment.recorded_by.name }}</p>
                        </div>
                    </div>

                    <!-- Notes -->
                    <div v-if="payment.notes" class="mt-6">
                        <Separator class="mb-4" />
                        <div class="space-y-1">
                            <p class="text-sm text-muted-foreground">Notes</p>
                            <p class="text-sm">{{ payment.notes }}</p>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Student Info -->
            <Card>
                <CardHeader>
                    <CardTitle class="flex items-center gap-2">
                        <User class="h-5 w-5 text-blue-600" />
                        Student
                    </CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="flex items-center gap-4">
                        <div class="h-12 w-12 rounded-full bg-blue-100 dark:bg-blue-900 flex items-center justify-center">
                            <User class="h-6 w-6 text-blue-600 dark:text-blue-400" />
                        </div>
                        <div>
                            <p class="font-medium">{{ payment.student.first_name }} {{ payment.student.last_name }}</p>
                            <p class="text-sm text-muted-foreground">{{ payment.student.admission_number }}</p>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Linked Invoice -->
            <Card>
                <CardHeader>
                    <CardTitle class="flex items-center gap-2">
                        <FileText class="h-5 w-5 text-teal-600" />
                        Invoice Summary
                    </CardTitle>
                </CardHeader>
                <CardContent>
                    <Link
                        :href="invoiceShow.url(payment.invoice.uuid)"
                        class="block p-4 rounded-lg border hover:bg-accent/50 transition-colors"
                    >
                        <div class="flex items-center justify-between mb-3">
                            <div>
                                <p class="font-medium">{{ payment.invoice.invoice_number }}</p>
                                <p class="text-sm text-muted-foreground">
                                    {{ payment.invoice.academic_year?.name }}
                                    <span v-if="payment.invoice.term"> &middot; {{ payment.invoice.term.name }}</span>
                                </p>
                            </div>
                            <Badge :variant="getStatusBadgeVariant(payment.invoice.status)">
                                {{ getStatusLabel(payment.invoice.status) }}
                            </Badge>
                        </div>
                        <Separator class="my-3" />
                        <div class="grid grid-cols-3 gap-4 text-sm">
                            <div>
                                <p class="text-muted-foreground">Total</p>
                                <p class="font-medium">{{ payment.invoice.formatted_total }}</p>
                            </div>
                            <div>
                                <p class="text-muted-foreground">Paid</p>
                                <p class="font-medium text-green-600">{{ payment.invoice.formatted_paid }}</p>
                            </div>
                            <div>
                                <p class="text-muted-foreground">Outstanding</p>
                                <p class="font-medium text-orange-600">{{ payment.invoice.formatted_outstanding }}</p>
                            </div>
                        </div>
                    </Link>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
