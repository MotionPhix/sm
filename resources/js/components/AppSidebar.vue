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
import { type NavItem } from '@/types';
import { BookOpen, Folder } from 'lucide-vue-next';
import { usePage } from '@inertiajs/vue3'
import { navigation } from '@/navigation'
import SchoolSwitcher from '@/components/SchoolSwitcher.vue'
import { computed } from 'vue'

const page = usePage()

const mainNavItems = computed(() => {
  const role = page.props.auth?.user?.role
  return role && navigation[role] ? navigation[role] : []
})

const footerNavItems: NavItem[] = [
    {
        title: 'Github Repo',
        href: 'https://github.com/laravel/vue-starter-kit',
        icon: Folder,
    },
    {
        title: 'Documentation',
        href: 'https://laravel.com/docs/starter-kits#vue',
        icon: BookOpen,
    },
];
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
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
