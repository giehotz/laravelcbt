<script setup lang="ts">
import { useToast } from '@/composables/useToast';
import { CheckCircle2, AlertCircle, Info, X } from 'lucide-vue-next';

const { toasts, removeToast } = useToast();
</script>

<template>
    <div class="fixed bottom-4 right-4 z-50 flex flex-col gap-2 w-full max-w-sm pointer-events-none">
        <TransitionGroup
            name="toast"
            enter-active-class="transition duration-300 ease-out transform"
            enter-from-class="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
            enter-to-class="translate-y-0 opacity-100 sm:translate-x-0"
            leave-active-class="transition duration-200 ease-in transform"
            leave-from-class="opacity-100 scale-100"
            leave-to-class="opacity-0 scale-95"
        >
            <div
                v-for="toast in toasts"
                :key="toast.id"
                class="flex items-center gap-3 px-4 py-3 bg-white dark:bg-zinc-900 border border-neutral-200 dark:border-zinc-800 rounded-lg shadow-lg pointer-events-auto transition-all"
            >
                <!-- Icon mapping -->
                <div class="flex-shrink-0">
                    <CheckCircle2 v-if="toast.type === 'success'" class="w-5 h-5 text-emerald-500" />
                    <AlertCircle v-else-if="toast.type === 'error'" class="w-5 h-5 text-rose-500" />
                    <Info v-else class="w-5 h-5 text-blue-500" />
                </div>

                <!-- Message -->
                <div class="flex-1 text-sm font-medium text-neutral-800 dark:text-neutral-200">
                    {{ toast.message }}
                </div>

                <!-- Dismiss Button -->
                <button
                    @click="removeToast(toast.id)"
                    class="flex-shrink-0 p-1 text-neutral-400 hover:text-neutral-600 dark:hover:text-neutral-200 rounded-full transition-colors cursor-pointer"
                >
                    <X class="w-4 h-4" />
                </button>
            </div>
        </TransitionGroup>
    </div>
</template>
