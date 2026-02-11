<script setup lang="ts">
import { cn } from '@/lib/utils';
import type { HTMLAttributes } from 'vue';

const props = defineProps<{
  class?: HTMLAttributes['class']
}>()
</script>

<template>
  <div
    :class="
      cn(
        'flex flex-col max-h-[85vh] bg-background rounded-xl overflow-hidden',
        props.class,
      )
    ">
    <slot />
  </div>
</template>
