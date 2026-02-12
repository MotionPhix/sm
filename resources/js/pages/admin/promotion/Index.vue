<script setup lang="ts">
import Heading from '@/components/Heading.vue'
import { Badge } from '@/components/ui/badge'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import AppLayout from '@/layouts/AppLayout.vue'
import { dashboard as adminDashboard } from '@/routes/admin'
import { Head } from '@inertiajs/vue3'
import { AlertCircle, ArrowUpRight } from 'lucide-vue-next'

interface ClassInfo {
    id: number
    name: string
    order: number
    student_count: number
}

const props = defineProps<{
    activeYear: { id: number; name: string } | null
    classes: ClassInfo[]
}>()

const totalStudents = props.classes.reduce((sum, c) => sum + c.student_count, 0)
</script>

<template>
    <AppLayout
        :breadcrumbs="[
            { title: 'Admin', href: '' },
            { title: 'Dashboard', href: adminDashboard().url },
            { title: 'Promotion', href: '#' },
        ]">
        <Head title="Promotion" />

        <div class="flex flex-col space-y-6 max-w-6xl">
            <Heading
                title="Student Promotion"
                description="Review class distribution and promote students to the next academic level"
            />

            <!-- Info Banner -->
            <div class="flex items-start gap-3 p-4 rounded-lg border border-amber-200 bg-amber-50 dark:border-amber-900 dark:bg-amber-900/20">
                <AlertCircle class="h-5 w-5 text-amber-600 dark:text-amber-400 shrink-0 mt-0.5" />
                <div>
                    <p class="text-sm font-medium text-amber-800 dark:text-amber-200">Promotion runner coming soon</p>
                    <p class="text-sm text-amber-700 dark:text-amber-300 mt-1">
                        This feature will allow you to preview and execute bulk promotions between classes at the end of an academic year.
                    </p>
                </div>
            </div>

            <!-- Current Distribution -->
            <Card>
                <CardHeader>
                    <CardTitle>Current Class Distribution</CardTitle>
                    <CardDescription>
                        {{ activeYear?.name ?? 'No active year' }} — {{ totalStudents }} total students
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <div v-if="classes.length === 0" class="text-center text-muted-foreground py-8">
                        No classes configured yet. Set up classes in Settings first.
                    </div>

                    <div v-else class="space-y-4">
                        <div v-for="(klass, idx) in classes" :key="klass.id" class="flex items-center gap-4">
                            <div class="w-32 shrink-0 flex items-center gap-2">
                                <span class="text-sm font-medium">{{ klass.name }}</span>
                                <Badge variant="secondary" class="text-xs">{{ klass.student_count }}</Badge>
                            </div>

                            <div class="flex-1 flex items-center gap-2">
                                <div class="flex-1 h-8 bg-muted rounded overflow-hidden">
                                    <div
                                        class="h-full bg-primary/80 rounded transition-all"
                                        :style="{
                                            width: totalStudents > 0
                                                ? `${(klass.student_count / totalStudents) * 100}%`
                                                : '0%',
                                        }"
                                    />
                                </div>

                                <ArrowUpRight
                                    v-if="idx < classes.length - 1"
                                    class="h-4 w-4 text-muted-foreground shrink-0"
                                />
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Action -->
            <div class="flex justify-end">
                <Button disabled size="lg">
                    Run Promotion (preview)
                </Button>
            </div>
        </div>
    </AppLayout>
</template>
