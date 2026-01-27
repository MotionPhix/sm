<script setup lang="ts">
import AuthLayout from '@/layouts/auth/AuthSimpleLayout.vue';
import { Toaster } from 'vue-sonner';

defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <Toaster position="top-right" rich-colors />
    
    <AuthLayout :title="title" :description="description">
        <slot />
    </AuthLayout>
</template>
