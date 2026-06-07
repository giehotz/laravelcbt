<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { Label } from '@/components/ui/label';

// Intentional shared mutable form reference for Inertia form state
defineProps<{
    form: ReturnType<typeof useForm>;
}>();
</script>

<template>
    <div class="flex flex-1 flex-col">
        <div class="mb-2">
            <Label
                class="text-sm font-bold tracking-wider text-gray-700 uppercase dark:text-zinc-300"
                >Kunci Jawaban / Pedoman Penilaian</Label
            >
        </div>
        <textarea
            v-model="form.jawaban"
            class="min-h-[300px] w-full flex-1 rounded-lg border border-gray-300 bg-white p-4 text-gray-900 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-200"
            placeholder="Ketik pedoman jawaban di sini..."
        ></textarea>
        <div v-if="form.errors.jawaban" class="mt-1 text-xs text-red-500">
            {{ form.errors.jawaban }}
        </div>
    </div>
</template>
