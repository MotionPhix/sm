<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarSeparator,
} from '@/components/ui/sidebar';

import SchoolSwitcher from '@/components/SchoolSwitcher.vue';
import { navigation } from '@/navigation';
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage()

const mainNavItems = computed(() => {
  const role = page.props.auth?.user?.role
  return role && navigation[role] ? navigation[role] : []
})
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SchoolSwitcher />
        </SidebarHeader>

        <SidebarSeparator />

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter />

            <SidebarSeparator />

            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
