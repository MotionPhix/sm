<script setup lang="ts">
import AppContent from '@/components/AppContent.vue';
import AppShell from '@/components/AppShell.vue';
import AppSidebar from '@/components/AppSidebar.vue';
import AppSidebarHeader from '@/components/AppSidebarHeader.vue';
import type { BreadcrumbItemType } from '@/types';

interface Props {
    breadcrumbs?: BreadcrumbItemType[];
}

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});
</script>

<template>
    <AppShell variant="sidebar">
        <AppSidebar />

        <AppContent
            variant="sidebar" class="overflow-hidden">
            <AppSidebarHeader :breadcrumbs="breadcrumbs">
                <template #actions>
                    <slot name="act" />
                </template>
            </AppSidebarHeader>

            <div
                class="flex absolute top-16 flex-col inset-0 overflow-y-auto scrollbar-thin p-5">
                <slot />
            </div>
        </AppContent>

<!--        <AppContent-->
<!--            variant="sidebar"-->
<!--            class="overflow-x-hidden">-->
<!--            <AppSidebarHeader-->
<!--                :breadcrumbs="breadcrumbs">-->
<!--                <template #actions>-->
<!--                    <slot name="actions" />-->
<!--                </template>-->
<!--            </AppSidebarHeader>-->

<!--            <slot />-->
<!--        </AppContent>-->
    </AppShell>
</template>
