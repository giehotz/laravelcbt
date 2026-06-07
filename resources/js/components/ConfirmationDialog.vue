<script setup lang="ts">
import { useConfirmStore } from '@/stores/confirm';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';

const confirmStore = useConfirmStore();
</script>

<template>
    <Dialog
        :open="confirmStore.isOpen"
        @update:open="(val) => !val && confirmStore.cancel()"
    >
        <DialogContent
            class="rounded-2xl border border-neutral-200 bg-white p-6 shadow-2xl sm:max-w-[420px] dark:border-zinc-800 dark:bg-zinc-950"
        >
            <DialogHeader class="space-y-2">
                <DialogTitle
                    class="flex items-center gap-2 text-lg font-bold text-neutral-900 dark:text-zinc-50"
                >
                    <span
                        v-if="confirmStore.variant === 'danger'"
                        class="h-2.5 w-2.5 animate-pulse rounded-full bg-red-500"
                    ></span>
                    <span
                        v-else-if="confirmStore.variant === 'warning'"
                        class="h-2.5 w-2.5 animate-pulse rounded-full bg-amber-500"
                    ></span>
                    <span
                        v-else
                        class="h-2.5 w-2.5 animate-pulse rounded-full bg-blue-500"
                    ></span>
                    {{ confirmStore.title }}
                </DialogTitle>
                <DialogDescription
                    class="text-sm text-neutral-500 dark:text-zinc-400"
                >
                    {{ confirmStore.message }}
                </DialogDescription>
            </DialogHeader>
            <DialogFooter
                class="mt-4 flex gap-2 border-t border-neutral-100 pt-4 sm:justify-end dark:border-zinc-900"
            >
                <Button
                    type="button"
                    variant="outline"
                    class="h-9 rounded-lg px-4 text-xs font-bold transition-all"
                    @click="confirmStore.cancel"
                >
                    {{ confirmStore.cancelLabel }}
                </Button>
                <Button
                    type="button"
                    :variant="
                        confirmStore.variant === 'danger'
                            ? 'destructive'
                            : 'default'
                    "
                    class="h-9 rounded-lg px-4 text-xs font-bold text-white transition-all"
                    :class="{
                        'bg-red-600 hover:bg-red-700 dark:bg-red-700 dark:hover:bg-red-800':
                            confirmStore.variant === 'danger',
                        'bg-amber-500 hover:bg-amber-600 dark:bg-amber-600 dark:hover:bg-amber-700':
                            confirmStore.variant === 'warning',
                        'bg-neutral-900 hover:bg-neutral-800 dark:bg-neutral-50 dark:text-neutral-950 dark:hover:bg-neutral-200':
                            confirmStore.variant === 'info',
                    }"
                    @click="confirmStore.confirm"
                >
                    {{ confirmStore.confirmLabel }}
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
