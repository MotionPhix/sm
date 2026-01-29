<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { usePage } from '@inertiajs/vue3';
import {
    SidebarGroup,
    SidebarGroupContent,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { Settings, CreditCard } from 'lucide-vue-next';
import { index as academicYearsIndex } from '@/routes/admin/settings/academic-year';
import { index as termsIndex } from '@/routes/admin/settings/terms';

const page = usePage();

// Define footer navigation items with routes
const footerItems = [
    {
        title: 'Settings',
        href: academicYearsIndex().url,
        icon: Settings,
        isActive: () => page.url.startsWith('/admin/settings'),
    },
    {
        title: 'Billing',
        href: termsIndex().url,
        icon: CreditCard,
        isActive: () => page.url.startsWith('/admin/billing'),
    },
];

interface Props {
    class?: string;
}

defineProps<Props>();
</script>

<template>
    <SidebarGroup
        :class="`group-data-[collapsible=icon]:p-0 ${$props.class || ''}`"
    >
        <SidebarGroupContent>
            <SidebarMenu>
                <SidebarMenuItem v-for="item in footerItems" :key="item.title">
                    <SidebarMenuButton
                        :class="{
                            'text-neutral-900 dark:text-neutral-50': item.isActive(),
                            'text-neutral-600 hover:text-neutral-800 dark:text-neutral-300 dark:hover:text-neutral-100': !item.isActive(),
                        }"
                        as-child
                    >
                        <Link :href="item.href">
                            <component :is="item.icon" />
                            <span>{{ item.title }}</span>
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarGroupContent>
    </SidebarGroup>
</template>
