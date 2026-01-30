<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3';
import { ModalLink } from '@inertiaui/modal-vue'

import AppLayout from '@/layouts/AppLayout.vue';
import Heading from '@/components/Heading.vue';

import { dashboard as adminDashboard } from '@/routes/admin';
import { invite as inviteStaff } from '@/routes/admin/staff';

import {
    Table,
    TableBody,
    TableCaption,
    TableCell,
    TableFooter,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';

import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Separator } from '@/components/ui/separator';

interface StaffMember {
    id: number;
    name: string;
    email: string;
    role: string | null;
    is_active: boolean;
}

interface Invitation {
    id: number;
    name?: string;
    email: string;
    created_at: string;
}

const page = usePage();

const staff = page.props.staff as StaffMember[];
const pendingInvitations = page.props.pendingInvitations as Invitation[];
</script>

<template>
    <AppLayout
        :breadcrumbs="[
            { title: 'Admin', href: '' },
            { title: 'Dashboard', href: adminDashboard().url },
            { title: 'Staff', href: '#' },
        ]">
        <Head title="Staff" />

        <!-- Actions -->
        <template #act>
            <Button
                size="sm"
                :as="ModalLink"
                :href="inviteStaff().url">
                Invite member
            </Button>
        </template>

        <div class="flex flex-col space-y-6 max-w-6xl">
            <Heading
                title="Staff members"
                description="Manage staff members for the active school"
            />

            <!-- Staff table -->
            <div class="rounded-lg border bg-background py-2">
                <Table>
                    <TableCaption>
                        Users who belong to <strong>{{ $page.props.auth.user?.activeSchool?.name }}</strong> school
                    </TableCaption>

                    <TableHeader>
                        <TableRow>
                            <TableHead>Name</TableHead>
                            <TableHead>Email</TableHead>
                            <TableHead>Role</TableHead>
                            <TableHead>Status</TableHead>
                        </TableRow>
                    </TableHeader>

                    <TableBody>
                        <TableRow v-if="staff.length === 0">
                            <TableCell
                                colspan="4"
                                class="text-center text-muted-foreground">
                                No staff members yet
                            </TableCell>
                        </TableRow>

                        <TableRow v-for="member in staff" :key="member.id">
                            <TableCell class="font-medium">
                                {{ member.name }}
                            </TableCell>

                            <TableCell>
                                {{ member.email }}
                            </TableCell>

                            <TableCell>
                                <Badge variant="secondary">
                                    {{ member.role ?? '—' }}
                                </Badge>
                            </TableCell>

                            <TableCell>
                                <Badge
                                    :variant="
                                        member.is_active
                                            ? 'default'
                                            : 'destructive'
                                    ">
                                    {{
                                        member.is_active ? 'Active' : 'Inactive'
                                    }}
                                </Badge>
                            </TableCell>
                        </TableRow>
                    </TableBody>

                    <TableFooter>
                        <TableRow>
                            <TableCell colspan="3"> Total </TableCell>
                            <TableCell>
                                {{ staff.length }} Member{{ `${staff.length === 1 ? '' : 's'}` }}
                            </TableCell>
                        </TableRow>
                    </TableFooter>
                </Table>
            </div>

            <!-- Pending invitations -->
            <div v-if="pendingInvitations.length">
                <Separator class="my-6" />


                <Heading
                    title="Pending invitations"
                    description="Invited users who have not yet accepted"
                />

                <div class="rounded-lg border bg-background">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Name</TableHead>
                                <TableHead>Email</TableHead>
                                <TableHead>Invited on</TableHead>
                            </TableRow>
                        </TableHeader>

                        <TableBody>
                            <TableRow
                                v-for="invite in pendingInvitations"
                                :key="invite.id">
                                <TableCell>
                                    {{ invite?.name }}
                                </TableCell>

                                <TableCell>
                                    {{ invite.email }}
                                </TableCell>

                                <TableCell class="text-muted-foreground">
                                    {{
                                        new Date(
                                            invite.created_at,
                                        ).toLocaleDateString()
                                    }}
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
