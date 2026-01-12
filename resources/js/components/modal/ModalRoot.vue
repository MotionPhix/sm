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
        'flex flex-col max-h-[85vh] bg-white dark:bg-gray-900 rounded-xl overflow-hidden',
        props.class,
      )
    ">
    <slot />
  </div>
</template>
