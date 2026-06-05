import { defineStore } from 'pinia';
import { ref } from 'vue';

export const useConfirmStore = defineStore('confirm', () => {
    const isOpen = ref(false);
    const title = ref('Konfirmasi');
    const message = ref('Apakah Anda yakin?');
    const confirmLabel = ref('Ya, Hapus');
    const cancelLabel = ref('Batal');
    const onConfirmCallback = ref<(() => void) | null>(null);
    const onCancelCallback = ref<(() => void) | null>(null);
    const variant = ref<'danger' | 'warning' | 'info'>('danger');

    const requireConfirm = (options: {
        title?: string;
        message: string;
        confirmLabel?: string;
        cancelLabel?: string;
        variant?: 'danger' | 'warning' | 'info';
        onConfirm: () => void;
        onCancel?: () => void;
    }) => {
        title.value = options.title || 'Konfirmasi';
        message.value = options.message;
        confirmLabel.value = options.confirmLabel || 'Ya, Hapus';
        cancelLabel.value = options.cancelLabel || 'Batal';
        variant.value = options.variant || 'danger';
        onConfirmCallback.value = options.onConfirm;
        onCancelCallback.value = options.onCancel || null;
        isOpen.value = true;
    };

    const confirm = () => {
        if (onConfirmCallback.value) {
            onConfirmCallback.value();
        }
        isOpen.value = false;
    };

    const cancel = () => {
        if (onCancelCallback.value) {
            onCancelCallback.value();
        }
        isOpen.value = false;
    };

    return {
        isOpen,
        title,
        message,
        confirmLabel,
        cancelLabel,
        variant,
        requireConfirm,
        confirm,
        cancel,
    };
});
