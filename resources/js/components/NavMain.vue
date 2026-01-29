<script setup lang="ts">
import {
    SidebarGroup,
    SidebarGroupLabel,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { toUrl } from '@/lib/utils';
import { useActiveRoute } from '@/composables/useActiveRoute';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps<{
    items: NavItem[];
}>();

const { isNavItemActive } = useActiveRoute();

const isItemActive = computed(() => (item: NavItem) => {
    const urlToCheck = toUrl(item.href);
    const alternateUrls = props.items
        .filter((i) => toUrl(i.href) !== urlToCheck)
        .map((i) => toUrl(i.href));
    return isNavItemActive(urlToCheck, alternateUrls);
});
</script>

<template>
    <SidebarGroup class="px-2 py-0">
        <SidebarGroupLabel>Platform</SidebarGroupLabel>
        <SidebarMenu>
            <SidebarMenuItem v-for="item in props.items" :key="item.title">
                <SidebarMenuButton
                    v-if="item.href"
                    as-child
                    :is-active="isItemActive(item)"
                    :tooltip="item.title"
                >
                    <Link :href="item.href">
                        <component :is="item.icon" />
                        <span>{{ item.title }}</span>
                    </Link>
                </SidebarMenuButton>
            </SidebarMenuItem>
        </SidebarMenu>
    </SidebarGroup>
</template>
