<script setup lang="ts">
import { computed } from 'vue'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Badge } from '@/components/ui/badge'
import { Separator } from '@/components/ui/separator'

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

interface AcademicYear {
    id: number
    name: string
}

const props = defineProps<{
    enrollments: Enrollment[]
    currentAcademicYear?: AcademicYear
}>()

function formatDate(dateString: string): string {
    if (!dateString) return 'N/A'
    return new Date(dateString).toLocaleDateString('en-ZA', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    })
}

const sortedEnrollments = computed(() => {
    return [...props.enrollments].sort((a, b) => {
        // Active enrollment first
        if (a.is_active && !b.is_active) return -1
        if (!a.is_active && b.is_active) return 1
        // Then by date descending
        return new Date(b.created_at).getTime() - new Date(a.created_at).getTime()
    })
})

const activeEnrollment = computed(() => {
    return props.enrollments.find(e => e.is_active)
})
</script>

<template>
    <div class="space-y-4">
        <!-- Current Enrollment Status -->
        <Card v-if="activeEnrollment">
            <CardHeader class="pb-2">
                <CardTitle class="text-lg flex items-center gap-2">
                    Current Enrollment
                    <Badge variant="default">Active</Badge>
                </CardTitle>
            </CardHeader>
            <CardContent>
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-2xl font-bold">{{ activeEnrollment.classroom.display_name }}</p>
                        <p class="text-muted-foreground">Enrolled since {{ formatDate(activeEnrollment.created_at) }}</p>
                    </div>
                </div>
            </CardContent>
        </Card>

        <!-- Enrollment History Timeline -->
        <Card>
            <CardHeader>
                <CardTitle class="text-lg">Enrollment History</CardTitle>
            </CardHeader>
            <CardContent>
                <div v-if="sortedEnrollments.length > 0" class="relative">
                    <!-- Timeline line -->
                    <div class="absolute left-4 top-0 bottom-0 w-0.5 bg-border"></div>

                    <div class="space-y-6">
                        <div
                            v-for="(enrollment, index) in sortedEnrollments"
                            :key="enrollment.id"
                            class="relative pl-10">
                            <!-- Timeline dot -->
                            <div
                                class="absolute left-2.5 top-1 w-3 h-3 rounded-full"
                                :class="enrollment.is_active ? 'bg-green-500' : 'bg-muted-foreground'"
                            ></div>

                            <div class="flex flex-col">
                                <div class="flex items-center gap-2">
                                    <span class="font-medium">{{ enrollment.classroom.display_name }}</span>
                                    <Badge
                                        v-if="enrollment.is_active"
                                        variant="default"
                                        class="text-xs">
                                        Current
                                    </Badge>
                                    <Badge
                                        v-else
                                        variant="outline"
                                        class="text-xs">
                                        Former
                                    </Badge>
                                </div>

                                <p class="text-sm text-muted-foreground">
                                    Enrolled: {{ formatDate(enrollment.created_at) }}
                                </p>

                                <p v-if="enrollment.transferred_out_at" class="text-sm text-muted-foreground">
                                    Left: {{ formatDate(enrollment.transferred_out_at) }}
                                </p>

                                <p v-if="enrollment.transfer_reason" class="mt-1 text-sm">
                                    Reason: {{ enrollment.transfer_reason }}
                                </p>
                            </div>

                            <Separator v-if="index < sortedEnrollments.length - 1" class="mt-4" />
                        </div>
                    </div>
                </div>

                <p v-else class="text-muted-foreground text-center py-6">
                    No enrollment history available
                </p>
            </CardContent>
        </Card>
    </div>
</template>
