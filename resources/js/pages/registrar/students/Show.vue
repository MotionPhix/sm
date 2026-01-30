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
import { edit as editRoute } from '@/routes/registrar/students'
import { form as enrollFormRoute } from '@/routes/registrar/students/enroll'
import { form as transferFormRoute } from '@/routes/registrar/students/transfer'
import EnrollmentHistory from '@/pages/registrar/students/components/EnrollmentHistory.vue'

interface Student {
    id: number
    admission_number: string
    first_name: string
    middle_name?: string
    last_name: string
    gender: string
    date_of_birth: string
    admission_date: string
    national_id?: string
    withdrawn_at?: string
    withdrawal_reason?: string
    active_enrollment?: {
        classroom?: {
            class?: { name: string }
            stream?: { name: string }
            display_name: string
        }
    }
    enrollments: Enrollment[]
}

interface Enrollment {
    id: number
    is_active: boolean
    created_at: string
    classroom: {
        class: { name: string }
        stream?: { name: string }
        display_name: string
    }
    transferred_out_at?: string
    transfer_reason?: string
}

interface Guardian {
    id: number
    first_name: string
    last_name: string
    phone?: string
    email?: string
    relationship?: string
}

interface AcademicYear {
    id: number
    name: string
}

const props = defineProps<{
    student: Student
    guardians: Guardian[]
    currentAcademicYear?: AcademicYear
}>()

const page = usePage()
const flash = computed(() => page.props.flash as { success?: string; error?: string })

const breadcrumbs = [
    { title: 'Dashboard', href: '/registrar/dashboard' },
    { title: 'Students', href: '/registrar/students' },
    { title: props.student.first_name + ' ' + props.student.last_name, href: '#' },
]

const showWithdrawDialog = ref(false)
const withdrawReason = ref('')
const withdrawDate = ref(new Date().toISOString().split('T')[0])
const activeTab = ref('details')

function getStudentName(): string {
    const parts = [props.student.first_name]
    if (props.student.middle_name) parts.push(props.student.middle_name)
    parts.push(props.student.last_name)
    return parts.join(' ')
}

function formatDate(dateString: string): string {
    if (!dateString) return 'N/A'
    return new Date(dateString).toLocaleDateString('en-ZA', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    })
}

function confirmWithdraw() {
    router.post(`/registrar/students/${props.student.id}/withdraw`, {
        reason: withdrawReason.value,
        withdrawal_date: withdrawDate.value,
    }, {
        preserveScroll: true,
        onSuccess: () => {
            showWithdrawDialog.value = false
            withdrawReason.value = ''
        },
    })
}

const isWithdrawn = computed(() => !!props.student.withdrawn_at)
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="getStudentName()" />

        <article class="p-5">
            <!-- Header -->
            <div class="flex items-start justify-between">
                <div>
                    <Heading :title="getStudentName()" :description="`Admission #: ${student.admission_number}`" />
                    <div class="mt-2 flex items-center gap-2">
                        <Badge variant="outline">{{ student.gender }}</Badge>
                        <Badge v-if="isWithdrawn" variant="destructive">Withdrawn</Badge>
                        <Badge v-else variant="default">Active</Badge>
                    </div>
                </div>
                <div class="flex gap-2">
                    <Button variant="outline" :href="editRoute(student).url">
                        Edit
                    </Button>
                    <Button
                        v-if="!isWithdrawn"
                        variant="outline"
                        :href="enrollFormRoute(student).url">
                        Enroll in Class
                    </Button>
                    <Button
                        v-if="!isWithdrawn && student.active_enrollment"
                        variant="outline"
                        :href="transferFormRoute(student).url">
                        Transfer
                    </Button>
                    <Button
                        v-if="!isWithdrawn"
                        variant="destructive"
                        @click="showWithdrawDialog = true">
                        Withdraw
                    </Button>
                </div>
            </div>

            <!-- Tab Navigation -->
            <div class="mt-6 flex gap-2 border-b">
                <button
                    class="px-4 py-2 text-sm font-medium transition-colors"
                    :class="activeTab === 'details' ? 'border-b-2 border-primary text-primary' : 'text-muted-foreground'"
                    @click="activeTab = 'details'">
                    Details
                </button>
                <button
                    class="px-4 py-2 text-sm font-medium transition-colors"
                    :class="activeTab === 'enrollment' ? 'border-b-2 border-primary text-primary' : 'text-muted-foreground'"
                    @click="activeTab = 'enrollment'">
                    Enrollment History
                </button>
                <button
                    class="px-4 py-2 text-sm font-medium transition-colors"
                    :class="activeTab === 'guardians' ? 'border-b-2 border-primary text-primary' : 'text-muted-foreground'"
                    @click="activeTab = 'guardians'">
                    Guardians
                </button>
            </div>

            <!-- Tab Content -->
            <div class="mt-4">
                <!-- Details Tab -->
                <div v-if="activeTab === 'details'">
                    <Card>
                        <CardHeader>
                            <CardTitle>Student Information</CardTitle>
                            <CardDescription>Personal and admission details</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                                <div>
                                    <p class="text-sm font-medium text-muted-foreground">First Name</p>
                                    <p>{{ student.first_name }}</p>
                                </div>
                                <div v-if="student.middle_name">
                                    <p class="text-sm font-medium text-muted-foreground">Middle Name</p>
                                    <p>{{ student.middle_name }}</p>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-muted-foreground">Last Name</p>
                                    <p>{{ student.last_name }}</p>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-muted-foreground">Date of Birth</p>
                                    <p>{{ formatDate(student.date_of_birth) }}</p>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-muted-foreground">Gender</p>
                                    <p>{{ student.gender === 'male' ? 'Male' : 'Female' }}</p>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-muted-foreground">National ID</p>
                                    <p>{{ student.national_id || 'Not provided' }}</p>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-muted-foreground">Admission Date</p>
                                    <p>{{ formatDate(student.admission_date) }}</p>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-muted-foreground">Current Class</p>
                                    <p>{{ student.active_enrollment?.classroom?.display_name || 'Not enrolled' }}</p>
                                </div>
                                <div v-if="isWithdrawn">
                                    <p class="text-sm font-medium text-muted-foreground">Withdrawal Date</p>
                                    <p>{{ formatDate(student.withdrawn_at!) }}</p>
                                </div>
                                <div v-if="isWithdrawn && student.withdrawal_reason">
                                    <p class="text-sm font-medium text-muted-foreground">Withdrawal Reason</p>
                                    <p>{{ student.withdrawal_reason }}</p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Enrollment Tab -->
                <div v-if="activeTab === 'enrollment'">
                    <EnrollmentHistory
                        :enrollments="student.enrollments"
                        :current-academic-year="currentAcademicYear"
                    />
                </div>

                <!-- Guardians Tab -->
                <div v-if="activeTab === 'guardians'">
                    <Card>
                        <CardHeader>
                            <CardTitle>Guardians</CardTitle>
                            <CardDescription>Associated guardians for this student</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div v-if="guardians.length > 0" class="rounded-lg border">
                                <Table>
                                    <TableHeader>
                                        <TableRow>
                                            <TableHead>Name</TableHead>
                                            <TableHead>Relationship</TableHead>
                                            <TableHead>Phone</TableHead>
                                            <TableHead>Email</TableHead>
                                        </TableRow>
                                    </TableHeader>
                                    <TableBody>
                                        <TableRow v-for="guardian in guardians" :key="guardian.id">
                                            <TableCell>
                                                {{ guardian.first_name }} {{ guardian.last_name }}
                                            </TableCell>
                                            <TableCell>{{ guardian.relationship || 'N/A' }}</TableCell>
                                            <TableCell>{{ guardian.phone || 'N/A' }}</TableCell>
                                            <TableCell>{{ guardian.email || 'N/A' }}</TableCell>
                                        </TableRow>
                                    </TableBody>
                                </Table>
                            </div>
                            <p v-else class="text-muted-foreground text-center py-6">
                                No guardians associated with this student
                            </p>
                        </CardContent>
                    </Card>
                </div>
            </div>

            <!-- Withdraw Dialog -->
            <div v-if="showWithdrawDialog" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
                <Card class="w-full max-w-md">
                    <CardHeader>
                        <CardTitle>Withdraw Student</CardTitle>
                        <CardDescription>
                            This will mark the student as withdrawn. They can be re-enrolled later.
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div>
                            <label class="text-sm font-medium">Withdrawal Date</label>
                            <input v-model="withdrawDate" type="date" class="mt-1 w-full rounded-md border p-2" />
                        </div>
                        <div>
                            <label class="text-sm font-medium">Reason</label>
                            <textarea
                                v-model="withdrawReason"
                                class="mt-1 w-full rounded-md border p-2"
                                rows="3"
                                placeholder="Enter reason for withdrawal..."
                            ></textarea>
                        </div>
                        <div class="flex justify-end gap-2">
                            <Button variant="outline" @click="showWithdrawDialog = false">
                                Cancel
                            </Button>
                            <Button
                                variant="destructive"
                                :disabled="!withdrawReason"
                                @click="confirmWithdraw">
                                Confirm Withdrawal
                            </Button>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </article>
    </AppLayout>
</template>
