<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import Heading from '@/components/Heading.vue'
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card'
import { Badge } from '@/components/ui/badge'
import { dashboard as adminDashboard } from '@/routes/admin'
import { CreditCard, Sparkles, Shield, Zap } from 'lucide-vue-next'

defineProps<{
    school: { name: string }
}>()
</script>

<template>
    <AppLayout
        :breadcrumbs="[
            { title: 'Admin', href: '' },
            { title: 'Dashboard', href: adminDashboard().url },
            { title: 'Billing', href: '#' },
        ]">
        <Head title="Billing" />

        <div class="flex flex-col space-y-6 max-w-4xl">
            <Heading
                title="Billing & Subscription"
                description="Manage your platform subscription and payment details"
            />

            <!-- Current Plan -->
            <Card>
                <CardHeader>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <CreditCard class="h-5 w-5 text-muted-foreground" />
                            <div>
                                <CardTitle>Current Plan</CardTitle>
                                <CardDescription>Your school's active subscription</CardDescription>
                            </div>
                        </div>
                        <Badge variant="secondary">Free Tier</Badge>
                    </div>
                </CardHeader>
                <CardContent>
                    <p class="text-sm text-muted-foreground">
                        You are currently on the free tier. Subscription plans and pricing will be available soon.
                    </p>
                </CardContent>
            </Card>

            <!-- Upcoming Plans Preview -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <Card class="border-dashed">
                    <CardHeader>
                        <div class="flex items-center gap-2">
                            <Zap class="h-5 w-5 text-muted-foreground" />
                            <CardTitle class="text-base">Starter</CardTitle>
                        </div>
                        <CardDescription>Essential tools for small schools</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <p class="text-xs text-muted-foreground">Pricing coming soon</p>
                    </CardContent>
                </Card>

                <Card class="border-dashed">
                    <CardHeader>
                        <div class="flex items-center gap-2">
                            <Sparkles class="h-5 w-5 text-muted-foreground" />
                            <CardTitle class="text-base">Professional</CardTitle>
                        </div>
                        <CardDescription>Advanced features for growing schools</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <p class="text-xs text-muted-foreground">Pricing coming soon</p>
                    </CardContent>
                </Card>

                <Card class="border-dashed">
                    <CardHeader>
                        <div class="flex items-center gap-2">
                            <Shield class="h-5 w-5 text-muted-foreground" />
                            <CardTitle class="text-base">Enterprise</CardTitle>
                        </div>
                        <CardDescription>Full suite for large institutions</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <p class="text-xs text-muted-foreground">Pricing coming soon</p>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
