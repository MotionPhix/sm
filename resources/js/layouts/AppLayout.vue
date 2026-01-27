<script setup lang="ts">
import AppLayout from '@/layouts/app/AppSidebarLayout.vue';
import type { BreadcrumbItemType } from '@/types';
import { Toaster } from 'vue-sonner';

interface Props {
    breadcrumbs?: BreadcrumbItemType[];
}

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});
</script>

<template>
    <Toaster position="top-right" rich-colors />
    
    <AppLayout
        :breadcrumbs="breadcrumbs">
        <template #act>
            <slot name="act" />
        </template>

        <slot />
    </AppLayout>
</template>
