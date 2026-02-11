<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import Heading from '@/components/Heading.vue'
import { dashboard as teacherDashboard } from '@/routes/teacher'
import { Megaphone } from 'lucide-vue-next'

const props = defineProps<{
    school: { name: string }
}>()
</script>

<template>
    <AppLayout
        :breadcrumbs="[
            { title: 'Teacher', href: '' },
            { title: 'Dashboard', href: teacherDashboard().url },
            { title: 'Announcements', href: '#' },
        ]">
        <Head title="Announcements" />

        <div class="flex flex-col space-y-6 max-w-6xl">
            <Heading
                title="Announcements"
                description="School-wide announcements and notifications"
            />

            <div class="rounded-lg border bg-card">
                <div class="flex flex-col items-center justify-center py-16 text-center">
                    <Megaphone class="h-12 w-12 text-muted-foreground" />
                    <h3 class="mt-4 font-medium">No announcements yet</h3>
                    <p class="mt-1 text-sm text-muted-foreground">
                        Announcements from {{ school.name }} will appear here
                    </p>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
