<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import Heading from '@/components/Heading.vue';
import {
    Table,
    TableBody, TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import ApplicantStatusBadge from '@/pages/registrar/admissions/components/ApplicantStatusBadge.vue';
import { Button } from '@/components/ui/button';

import { create as createRoute, update as updateRoute } from '@/routes/registrar/admissions'
import { router } from '@inertiajs/vue3';

import { ModalLink } from '@inertiaui/modal-vue';

defineProps<{
    applicants: any[];
    cycles: any[];
}>();

function updateStatus(id: number, status: string) {
    router.put(updateRoute(id), { status }, {
        preserveScroll: true,
        preserveState: false,
    })
}
</script>

<template>
    <AppLayout>
        <article class="p-5">
            <Heading title="Admissions" description="Manage student applications" />

            <div class="mb-6 flex justify-end">
                <Button
                    :href="createRoute().url"
                    :as="ModalLink">
                    Add applicant
                </Button>
            </div>

            <div class="rounded-lg border">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Name</TableHead>
                            <TableHead>Cycle</TableHead>
                            <TableHead>Status</TableHead>
                            <TableHead class="text-right" />
                        </TableRow>
                    </TableHeader>

                    <TableBody>
                        <TableRow
                            v-for="applicant in applicants"
                            :key="applicant.id">
                            <TableCell>
                                {{ applicant.first_name }} {{ applicant.last_name }}
                            </TableCell>

                            <TableCell>
                                {{ applicant.admission_cycle?.name }}
                            </TableCell>

                            <TableCell>
                                <ApplicantStatusBadge :status="applicant.status" />
                            </TableCell>

                            <TableCell class="text-right">
                                <div class="flex items-center gap-x-2">
                                    <Button
                                        size="sm"
                                        variant="outline"
                                        @click="updateStatus(a.id, 'admitted')">
                                        Admit
                                    </Button>

                                    <Button
                                        size="sm"
                                        variant="destructive"
                                        @click="updateStatus(a.id, 'rejected')">
                                        Reject
                                    </Button>
                                </div>
                            </TableCell>
                        </TableRow>

                        <TableRow v-if="applicants.length === 0">
                            <TableCell
                                colspan="4"
                                class="p-6 text-center text-muted-foreground">
                                No applicants yet
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>
        </article>
    </AppLayout>
</template>
