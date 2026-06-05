<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { Label } from '@/components/ui/label';

// Intentional shared mutable form reference for Inertia form state
defineProps<{
    form: ReturnType<typeof useForm>;
}>();
</script>

<template>
    <div class="flex flex-col flex-1">
        <div class="mb-2">
            <Label class="text-sm font-bold text-gray-700 uppercase tracking-wider">Kunci Jawaban / Pedoman Penilaian</Label>
        </div>
        <textarea v-model="form.jawaban" class="flex-1 w-full p-4 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 min-h-[300px]" placeholder="Ketik pedoman jawaban di sini..."></textarea>
        <div v-if="form.errors.jawaban" class="text-xs text-red-500 mt-1">{{ form.errors.jawaban }}</div>
    </div>
</template>
