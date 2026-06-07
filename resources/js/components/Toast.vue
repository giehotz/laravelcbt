<script setup lang="ts">
import { useToast } from '@/composables/useToast';
import { CheckCircle2, AlertCircle, Info, X } from 'lucide-vue-next';

const { toasts, removeToast } = useToast();
</script>

<template>
    <div
        class="pointer-events-none fixed right-4 bottom-4 z-50 flex w-full max-w-sm flex-col gap-2"
    >
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
                class="pointer-events-auto flex items-center gap-3 rounded-lg border border-neutral-200 bg-white px-4 py-3 shadow-lg transition-all dark:border-zinc-800 dark:bg-zinc-900"
            >
                <!-- Icon mapping -->
                <div class="flex-shrink-0">
                    <CheckCircle2
                        v-if="toast.type === 'success'"
                        class="h-5 w-5 text-emerald-500"
                    />
                    <AlertCircle
                        v-else-if="toast.type === 'error'"
                        class="h-5 w-5 text-rose-500"
                    />
                    <Info v-else class="h-5 w-5 text-blue-500" />
                </div>

                <!-- Message -->
                <div
                    class="flex-1 text-sm font-medium text-neutral-800 dark:text-neutral-200"
                >
                    {{ toast.message }}
                </div>

                <!-- Dismiss Button -->
                <button
                    @click="removeToast(toast.id)"
                    class="flex-shrink-0 cursor-pointer rounded-full p-1 text-neutral-400 transition-colors hover:text-neutral-600 dark:hover:text-neutral-200"
                >
                    <X class="h-4 w-4" />
                </button>
            </div>
        </TransitionGroup>
    </div>
</template>
