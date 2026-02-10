<script setup lang="ts">
import { computed } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import StatCard from '@/components/StatCard.vue'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
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
    DollarSign,
    TrendingUp,
    AlertCircle,
    Clock,
    FileText,
    CreditCard,
    Bell,
    ArrowRight,
    Receipt,
    Banknote,
    Smartphone,
    Wallet,
} from 'lucide-vue-next'
import { index as invoicesIndex, create as invoicesCreate, show as invoicesShow } from '@/actions/App/Http/Controllers/Bursar/InvoiceController'
import { show as paymentShow } from '@/actions/App/Http/Controllers/Bursar/PaymentController'

interface Stats {
    totalInvoiced: number
    totalCollected: number
    totalOutstanding: number
    collectionRate: number
    overdueCount: number
    overdueAmount: number
    invoiceCount: number
    paymentCount: number
}

interface RecentInvoice {
    id: number
    uuid: string
    invoice_number: string
    total_amount: number
    paid_amount: number
    status: string
    issue_date: string
    due_date: string
    student: {
        id: number
        first_name: string
        last_name: string
        admission_number: string
    }
}

interface RecentPayment {
    id: number
    uuid: string
    amount: number
    payment_method: string
    payment_date: string
    student: {
        id: number
        first_name: string
        last_name: string
        admission_number: string
    }
    invoice: {
        id: number
        uuid: string
        invoice_number: string
    }
}

interface CollectionByMethod {
    payment_method: string
    total: number
    count: number
}

const props = defineProps<{
    user: { name: string }
    stats: Stats
    recentInvoices: RecentInvoice[]
    recentPayments: RecentPayment[]
    collectionByMethod: CollectionByMethod[]
    activeAcademicYear: { id: number; name: string } | null
    activeTerm: { id: number; name: string } | null
}>()

const formatCurrency = (amount: number) => {
    if (amount >= 1000000) {
        return `MK ${(amount / 1000000).toFixed(2)}M`
    }
    if (amount >= 1000) {
        return `MK ${(amount / 1000).toFixed(1)}K`
    }
    return `MK ${amount.toLocaleString()}`
}

const formatFullCurrency = (amount: number) => {
    return `MK ${Number(amount).toLocaleString('en-MW', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`
}

const formatDate = (dateStr: string) => {
    return new Date(dateStr).toLocaleDateString('en-ZA', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    })
}

const getStatusBadgeVariant = (status: string) => {
    switch (status) {
        case 'paid': return 'default'
        case 'overdue': return 'destructive'
        case 'partially_paid': return 'secondary'
        case 'issued': return 'outline'
        case 'cancelled': return 'secondary'
        default: return 'outline'
    }
}

const getStatusLabel = (status: string) => {
    return status.replace(/_/g, ' ').replace(/\b\w/g, (l) => l.toUpperCase())
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

const getMethodLabel = (method: string) => {
    return method.replace(/_/g, ' ').replace(/\b\w/g, (l) => l.toUpperCase())
}

const maxMethodTotal = computed(() =>
    Math.max(...props.collectionByMethod.map((m) => Number(m.total)), 1)
)
</script>

<template>
    <AppLayout>
        <Head title="Bursar Dashboard" />

        <div class="space-y-6">
            <!-- Welcome Banner -->
            <div
                class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-emerald-600 via-teal-600 to-cyan-600 p-6 lg:p-8 text-white">
                <div class="relative z-10">
                    <h2 class="text-2xl lg:text-3xl font-bold mb-2">
                        Finance Overview
                    </h2>
                    <p class="text-emerald-100 mb-4">
                        Welcome back, {{ user.name }}. Here's your financial summary.
                    </p>
                    <div class="flex flex-wrap gap-4">
                        <div v-if="activeAcademicYear" class="bg-white/20 backdrop-blur-sm rounded-lg px-4 py-2">
                            <p class="text-sm text-emerald-100">Academic Year</p>
                            <p class="font-bold">{{ activeAcademicYear.name }}</p>
                        </div>
                        <div v-if="activeTerm" class="bg-white/20 backdrop-blur-sm rounded-lg px-4 py-2">
                            <p class="text-sm text-emerald-100">Current Term</p>
                            <p class="font-bold">{{ activeTerm.name }}</p>
                        </div>
                        <div class="bg-white/20 backdrop-blur-sm rounded-lg px-4 py-2">
                            <p class="text-sm text-emerald-100">Collection Rate</p>
                            <p class="font-bold">{{ stats.collectionRate }}%</p>
                        </div>
                    </div>
                </div>
                <!-- Decorative circles -->
                <div
                    class="absolute right-0 top-0 w-64 h-64 bg-white/10 rounded-full -translate-y-1/2 translate-x-1/4">
                </div>
                <div class="absolute right-20 bottom-0 w-32 h-32 bg-white/10 rounded-full translate-y-1/2"></div>
            </div>

            <!-- Primary KPI Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <StatCard
                    title="Total Invoiced"
                    :value="formatCurrency(stats.totalInvoiced)"
                    :subtitle="`${stats.invoiceCount} invoices generated`"
                    :icon="FileText"
                    variant="primary"
                />
                <StatCard
                    title="Total Collected"
                    :value="formatCurrency(stats.totalCollected)"
                    :subtitle="`${stats.paymentCount} payments recorded`"
                    :icon="DollarSign"
                    variant="success"
                />
                <StatCard
                    title="Outstanding"
                    :value="formatCurrency(stats.totalOutstanding)"
                    :subtitle="`${stats.collectionRate}% collection rate`"
                    :icon="Clock"
                    variant="warning"
                />
                <StatCard
                    title="Overdue"
                    :value="formatCurrency(stats.overdueAmount)"
                    :subtitle="`${stats.overdueCount} overdue invoices`"
                    :icon="AlertCircle"
                    variant="danger"
                />
            </div>

            <!-- Secondary Stats Row -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <StatCard
                    title="Total Invoices"
                    :value="stats.invoiceCount"
                    :icon="FileText"
                />
                <StatCard
                    title="Total Payments"
                    :value="stats.paymentCount"
                    :icon="Receipt"
                />
                <StatCard
                    title="Overdue Invoices"
                    :value="stats.overdueCount"
                    :icon="AlertCircle"
                />
                <StatCard
                    title="Collection Rate"
                    :value="`${stats.collectionRate}%`"
                    :icon="TrendingUp"
                />
            </div>

            <!-- Main Content Grid -->
            <div class="grid gap-6 lg:grid-cols-3">
                <!-- Left Column (2/3) -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Recent Invoices -->
                    <Card>
                        <CardHeader>
                            <div class="flex items-center justify-between">
                                <div>
                                    <CardTitle class="flex items-center gap-2">
                                        <FileText class="h-5 w-5 text-blue-600" />
                                        Recent Invoices
                                    </CardTitle>
                                    <CardDescription>Latest invoices generated</CardDescription>
                                </div>
                                <Link :href="invoicesIndex.url()">
                                    <Button variant="outline" size="sm">
                                        View All
                                        <ArrowRight class="h-4 w-4 ml-1" />
                                    </Button>
                                </Link>
                            </div>
                        </CardHeader>
                        <CardContent>
                            <div v-if="recentInvoices.length > 0" class="rounded-lg border">
                                <Table>
                                    <TableHeader>
                                        <TableRow>
                                            <TableHead>Invoice #</TableHead>
                                            <TableHead>Student</TableHead>
                                            <TableHead>Amount</TableHead>
                                            <TableHead>Status</TableHead>
                                            <TableHead>Date</TableHead>
                                        </TableRow>
                                    </TableHeader>
                                    <TableBody>
                                        <TableRow
                                            v-for="invoice in recentInvoices"
                                            :key="invoice.id"
                                            class="cursor-pointer hover:bg-accent/50"
                                            @click="$inertia.visit(invoicesShow.url(invoice.uuid))"
                                        >
                                            <TableCell class="font-medium">{{ invoice.invoice_number }}</TableCell>
                                            <TableCell>{{ invoice.student.first_name }} {{ invoice.student.last_name }}</TableCell>
                                            <TableCell>{{ formatFullCurrency(invoice.total_amount) }}</TableCell>
                                            <TableCell>
                                                <Badge :variant="getStatusBadgeVariant(invoice.status)">
                                                    {{ getStatusLabel(invoice.status) }}
                                                </Badge>
                                            </TableCell>
                                            <TableCell class="text-muted-foreground">{{ formatDate(invoice.issue_date) }}</TableCell>
                                        </TableRow>
                                    </TableBody>
                                </Table>
                            </div>
                            <div v-else class="text-center py-8 text-muted-foreground">
                                <FileText class="h-12 w-12 mx-auto mb-3 opacity-50" />
                                <p>No invoices generated yet</p>
                                <p class="text-sm">Create your first invoice to get started</p>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Collection by Payment Method -->
                    <Card v-if="collectionByMethod.length > 0">
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2">
                                <CreditCard class="h-5 w-5 text-purple-600" />
                                Collection by Payment Method
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="space-y-4">
                                <div
                                    v-for="method in collectionByMethod"
                                    :key="method.payment_method"
                                    class="flex items-center gap-4"
                                >
                                    <div class="h-10 w-10 rounded-full bg-purple-100 dark:bg-purple-900 flex items-center justify-center shrink-0">
                                        <component :is="getMethodIcon(method.payment_method)" class="h-5 w-5 text-purple-600 dark:text-purple-400" />
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center justify-between mb-1">
                                            <span class="text-sm font-medium">{{ getMethodLabel(method.payment_method) }}</span>
                                            <span class="text-sm text-muted-foreground">{{ method.count }} payments</span>
                                        </div>
                                        <div class="w-full bg-gray-100 dark:bg-gray-800 rounded-full h-2.5 overflow-hidden">
                                            <div
                                                class="h-full rounded-full bg-gradient-to-r from-purple-500 to-purple-600 transition-all duration-500"
                                                :style="{ width: `${(Number(method.total) / maxMethodTotal) * 100}%` }"
                                            />
                                        </div>
                                    </div>
                                    <span class="text-sm font-bold w-28 text-right">{{ formatFullCurrency(method.total) }}</span>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Right Column (1/3) -->
                <div class="space-y-6">
                    <!-- Quick Actions -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="text-base">Quick Actions</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-2">
                            <Link :href="invoicesCreate.url()" class="block">
                                <Button variant="outline" class="w-full justify-start gap-3">
                                    <FileText class="h-4 w-4" />
                                    Create Invoice
                                </Button>
                            </Link>
                            <Link :href="invoicesIndex.url()" class="block">
                                <Button variant="outline" class="w-full justify-start gap-3">
                                    <DollarSign class="h-4 w-4" />
                                    View All Invoices
                                </Button>
                            </Link>
                        </CardContent>
                    </Card>

                    <!-- Alerts -->
                    <Card v-if="stats.overdueCount > 0 || stats.totalOutstanding > 0">
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2 text-base">
                                <Bell class="h-4 w-4 text-amber-600" />
                                Attention Needed
                            </CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-3">
                            <div
                                v-if="stats.overdueCount > 0"
                                class="flex items-center gap-3 p-3 bg-red-50 dark:bg-red-950 rounded-lg"
                            >
                                <div
                                    class="h-8 w-8 rounded-full bg-red-100 dark:bg-red-900 flex items-center justify-center shrink-0"
                                >
                                    <AlertCircle class="h-4 w-4 text-red-600" />
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm font-medium">{{ stats.overdueCount }} overdue invoices</p>
                                    <p class="text-xs text-muted-foreground">{{ formatCurrency(stats.overdueAmount) }} outstanding</p>
                                </div>
                            </div>

                            <div
                                v-if="stats.totalOutstanding > 0"
                                class="flex items-center gap-3 p-3 bg-orange-50 dark:bg-orange-950 rounded-lg"
                            >
                                <div
                                    class="h-8 w-8 rounded-full bg-orange-100 dark:bg-orange-900 flex items-center justify-center shrink-0"
                                >
                                    <DollarSign class="h-4 w-4 text-orange-600" />
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm font-medium">{{ formatCurrency(stats.totalOutstanding) }} pending</p>
                                    <p class="text-xs text-muted-foreground">{{ stats.collectionRate }}% collected</p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Recent Payments -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="text-base">Recent Payments</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div v-if="recentPayments.length > 0" class="space-y-3">
                                <Link
                                    v-for="payment in recentPayments"
                                    :key="payment.id"
                                    :href="paymentShow.url(payment.uuid)"
                                    class="flex items-center gap-3 p-3 rounded-lg border hover:bg-accent/50 transition-colors"
                                >
                                    <div
                                        class="h-8 w-8 rounded-full bg-green-100 dark:bg-green-900 flex items-center justify-center shrink-0"
                                    >
                                        <component :is="getMethodIcon(payment.payment_method)" class="h-4 w-4 text-green-600 dark:text-green-400" />
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium truncate">{{ payment.student.first_name }} {{ payment.student.last_name }}</p>
                                        <p class="text-xs text-muted-foreground">{{ payment.invoice.invoice_number }}</p>
                                    </div>
                                    <div class="text-right shrink-0">
                                        <p class="text-sm font-bold text-green-600">{{ formatFullCurrency(payment.amount) }}</p>
                                        <p class="text-xs text-muted-foreground">{{ formatDate(payment.payment_date) }}</p>
                                    </div>
                                </Link>
                            </div>
                            <div v-else class="text-center py-6 text-muted-foreground">
                                <Receipt class="h-8 w-8 mx-auto mb-2 opacity-50" />
                                <p class="text-sm">No payments recorded yet</p>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
