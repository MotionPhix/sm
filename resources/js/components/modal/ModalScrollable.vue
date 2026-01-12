<script setup lang="ts">
import type { HTMLAttributes } from 'vue'
import { cn } from '@/lib/utils'

const props = defineProps<{
  class?: HTMLAttributes['class']
}>()
</script>

<template>
  <div
    :class="
      cn(
        'flex-1 overflow-y-auto px-6 py-5',
        'scrollbar-thin !scrollbar-thumb-rounded-none scrollbar-track-transparent scrollbar-thumb-sage-200 dark:scrollbar-thumb-gray-700 hover:scrollbar-thumb-sage-300 dark:hover:scrollbar-thumb-gray-600',
        props.class,
      )
    ">
    <slot />
  </div>
</template>

<style scoped>
/* Custom scrollbar for webkit browsers */
::-webkit-scrollbar {
  width: 4px;
}

::-webkit-scrollbar-track {
  background: transparent;
}

::-webkit-scrollbar-thumb {
  background: oklch(0.85 0.04 145);
}

::-webkit-scrollbar-thumb:hover {
  background: oklch(0.80 0.06 145);
}

.dark ::-webkit-scrollbar-thumb {
  background: oklch(0.30 0.02 150);
}

.dark ::-webkit-scrollbar-thumb:hover {
  background: oklch(0.35 0.03 150);
}
</style>
