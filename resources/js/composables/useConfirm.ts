import { ref } from 'vue';

interface ConfirmOptions {
  title?: string;
  description?: string;
  confirmText?: string;
  cancelText?: string;
  variant?: 'default' | 'destructive';
}

export function useConfirm() {
  const isOpen = ref(false);
  const title = ref('Are you sure?');
  const description = ref('This action cannot be undone.');
  const confirmText = ref('Continue');
  const cancelText = ref('Cancel');
  const variant = ref<'default' | 'destructive'>('default');

  let resolvePromise: ((value: boolean) => void) | null = null;

  const confirm = (options?: ConfirmOptions): Promise<boolean> => {
    return new Promise((resolve) => {
      resolvePromise = resolve;

      if (options?.title) title.value = options.title;
      if (options?.description) description.value = options.description;
      if (options?.confirmText) confirmText.value = options.confirmText;
      if (options?.cancelText) cancelText.value = options.cancelText;
      if (options?.variant) variant.value = options.variant;

      isOpen.value = true;
    });
  };

  const handleConfirm = () => {
    if (resolvePromise) {
      resolvePromise(true);
      resolvePromise = null;
    }
    isOpen.value = false;
  };

  const handleCancel = () => {
    if (resolvePromise) {
      resolvePromise(false);
      resolvePromise = null;
    }
    isOpen.value = false;
  };

  return {
    isOpen,
    title,
    description,
    confirmText,
    cancelText,
    variant,
    confirm,
    handleConfirm,
    handleCancel,
  };
}
