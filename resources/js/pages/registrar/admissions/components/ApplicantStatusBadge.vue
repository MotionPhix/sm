<script setup lang="ts">
defineProps<{
    status: 'applied' | 'admitted' | 'rejected'
}>()

const variants = {
    applied: 'bg-yellow-100 text-yellow-800',
    admitted: 'bg-green-100 text-green-800',
    rejected: 'bg-red-100 text-red-800',
}
</script>

<template>
    <span
        class="inline-flex items-center rounded-full px-3 py-1 text-xs font-medium"
        :class="variants[status]">
        {{ status }}
    </span>
</template>
