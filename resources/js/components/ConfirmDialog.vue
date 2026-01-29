<script setup lang="ts">
import { ref, watch } from 'vue';
import {
  AlertDialog,
  AlertDialogAction,
  AlertDialogCancel,
  AlertDialogContent,
  AlertDialogDescription,
  AlertDialogFooter,
  AlertDialogHeader,
  AlertDialogTitle,
} from '@/components/ui/alert-dialog';

interface Props {
  open?: boolean;
  title?: string;
  description?: string;
  confirmText?: string;
  cancelText?: string;
  variant?: 'default' | 'destructive';
}

const props = withDefaults(defineProps<Props>(), {
  open: false,
  title: 'Are you sure?',
  description: 'This action cannot be undone.',
  confirmText: 'Continue',
  cancelText: 'Cancel',
  variant: 'default',
});

const emit = defineEmits<{
  (e: 'update:open', value: boolean): void;
  (e: 'confirm'): void;
  (e: 'cancel'): void;
}>();

const isOpen = ref(props.open);

watch(() => props.open, (newVal) => {
  isOpen.value = newVal;
});

watch(isOpen, (newVal) => {
  emit('update:open', newVal);
});

const handleConfirm = () => {
  emit('confirm');
  isOpen.value = false;
};

const handleCancel = () => {
  emit('cancel');
  isOpen.value = false;
};
</script>

<template>
  <AlertDialog v-model:open="isOpen">
    <AlertDialogContent>
      <AlertDialogHeader>
        <AlertDialogTitle>{{ title }}</AlertDialogTitle>
        <AlertDialogDescription>
          {{ description }}
        </AlertDialogDescription>
      </AlertDialogHeader>
      <AlertDialogFooter>
        <AlertDialogCancel @click="handleCancel">
          {{ cancelText }}
        </AlertDialogCancel>
        <AlertDialogAction
          :variant="variant"
          @click="handleConfirm"
        >
          {{ confirmText }}
        </AlertDialogAction>
      </AlertDialogFooter>
    </AlertDialogContent>
  </AlertDialog>
</template>
