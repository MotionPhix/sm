<script setup lang="ts">
import {
    SidebarGroup,
    SidebarGroupContent,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { index as billingIndex } from '@/routes/admin/billing';
import { index as academicYearsIndex } from '@/routes/admin/settings/academic-year';
import { Link, usePage } from '@inertiajs/vue3';
import { CreditCard, Settings } from 'lucide-vue-next';
import { computed } from 'vue';

const page = usePage();

const role = computed(() => page.props.auth?.user?.role as string | undefined);

const adminRoles = ['admin', 'super_admin'];
const financeRoles = ['admin', 'super_admin', 'bursar', 'accountant'];

const footerItems = computed(() => {
    const items: {
        title: string;
        href: string;
        icon: typeof Settings;
        isActive: () => boolean;
    }[] = [];

    if (adminRoles.includes(role.value ?? '')) {
        items.push({
            title: 'Settings',
            href: academicYearsIndex().url,
            icon: Settings,
            isActive: () => page.url.startsWith('/admin/settings'),
        });
    }

    if (financeRoles.includes(role.value ?? '')) {
        items.push({
            title: 'Billing',
            href: billingIndex().url,
            icon: CreditCard,
            isActive: () => page.url.startsWith('/admin/billing'),
        });
    }

    return items;
});

interface Props {
    class?: string;
}

defineProps<Props>();
</script>

<template>
    <SidebarGroup
        v-if="footerItems.length > 0"
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
