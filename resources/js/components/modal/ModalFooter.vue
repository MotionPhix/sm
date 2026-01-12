<script setup lang="ts">
import type { HTMLAttributes } from 'vue'
import { cn } from '@/lib/utils'

const props = defineProps<{
  class?: HTMLAttributes['class']
  align?: 'left' | 'center' | 'right' | 'between'
}>()
</script>

<template>
  <div
    :class="
      cn(
        'flex items-center gap-3 px-6 py-4 border-t border-sage-100 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-900/50',
        align === 'left' ? 'justify-start' : '',
        align === 'center' ? 'justify-center' : '',
        align === 'right' ? 'justify-end' : '',
        align === 'between' ? 'justify-between' : '',
        !align ? 'justify-end' : '',
        props.class,
      )
    "
  >
    <slot />
  </div>
</template>
