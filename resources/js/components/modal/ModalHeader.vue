<script setup lang="ts">
import type { Component } from 'vue'
import { X } from 'lucide-vue-next'
import { cn } from '@/lib/utils'

const props = defineProps<{
  title: string
  description?: string
  icon?: Component
  iconClass?: string
  onClose?: () => void
  class?: string
}>()
</script>

<template>
  <div
    :class="
      cn(
        'flex items-start justify-between gap-4 px-6 py-5 border-b border-sage-100 dark:border-gray-800 bg-gradient-to-r from-sage-50/50 to-transparent dark:from-sage-950/10',
        props.class,
      )
    "
  >
    <div class="flex items-start gap-3 flex-1 min-w-0">
      <div
        v-if="icon"
        :class="
          cn(
            'flex-shrink-0 w-10 h-10 rounded-lg bg-sage-100 dark:bg-sage-900/30 flex items-center justify-center text-sage-600 dark:text-sage-400',
            iconClass,
          )
        "
      >
        <component :is="icon" class="w-5 h-5" />
      </div>

      <div class="flex-1 min-w-0">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 truncate">
          {{ title }}
        </h2>
        <p v-if="description" class="text-sm text-gray-600 dark:text-gray-400">
          {{ description }}
        </p>
      </div>
    </div>

    <button
      v-if="onClose"
      type="button"
      @click="onClose"
      class="flex-shrink-0 w-8 h-8 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 flex items-center justify-center text-gray-500 dark:text-gray-400 transition-colors">
      <X class="w-5 h-5" />
    </button>
  </div>
</template>
