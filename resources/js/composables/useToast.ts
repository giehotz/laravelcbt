import { ref } from 'vue';

export interface ToastMessage {
    id: number;
    message: string;
    type: 'success' | 'error' | 'info';
}

const toasts = ref<ToastMessage[]>([]);
let nextId = 0;

export function useToast() {
    const addToast = (
        message: string,
        type: 'success' | 'error' | 'info' = 'success',
        duration = 3000,
    ) => {
        const id = nextId++;
        toasts.value.push({ id, message, type });

        setTimeout(() => {
            removeToast(id);
        }, duration);
    };

    const removeToast = (id: number) => {
        const index = toasts.value.findIndex((t) => t.id === id);
        if (index !== -1) {
            toasts.value.splice(index, 1);
        }
    };

    return {
        toasts,
        addToast,
        removeToast,
    };
}
