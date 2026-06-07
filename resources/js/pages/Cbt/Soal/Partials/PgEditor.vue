<script setup lang="ts">
import { computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { Label } from '@/components/ui/label';
import RichEditor from '@/components/RichEditor.vue';

interface BankSoal {
    opsi: number; // 3 | 4 | 5
}

// Intentional shared mutable form reference for Inertia form state
const props = defineProps<{
    form: ReturnType<typeof useForm>;
    bank: BankSoal;
}>();

// Check if a specific option is checked for Ganda Kompleks (jenis === 2)
const isCorrectComplex = (opt: string): boolean => {
    return (
        Array.isArray(props.form.jawaban) && props.form.jawaban.includes(opt)
    );
};

// Reactively toggle option selection without direct prop mutation
const toggleComplexAnswer = (opt: string) => {
    const current = Array.isArray(props.form.jawaban)
        ? [...props.form.jawaban]
        : [];

    const idx = current.indexOf(opt);
    if (idx > -1) {
        current.splice(idx, 1);
    } else {
        current.push(opt);
        current.sort();
    }

    // Assign a new array reference so Vue detects the change
    props.form.jawaban = current;
};

// Counter display helper
const complexSelectedCount = computed(() => {
    return Array.isArray(props.form.jawaban) ? props.form.jawaban.length : 0;
});
</script>

<template>
    <div class="space-y-4">
        <div
            v-for="opt in ['A', 'B', 'C', 'D', 'E'].slice(0, bank.opsi)"
            :key="opt"
            class="flex flex-col"
        >
            <div class="mb-1">
                <Label
                    class="text-sm font-bold text-gray-700 dark:text-zinc-300"
                    >Jawaban {{ opt }}</Label
                >
            </div>
            <div
                class="relative overflow-hidden rounded-lg border border-gray-300 dark:border-zinc-700"
            >
                <RichEditor
                    v-model="(form as any)[`opsi_${opt.toLowerCase()}`]"
                    :minimal="true"
                />
            </div>
            <div
                v-if="(form.errors as any)[`opsi_${opt.toLowerCase()}`]"
                class="mt-1 text-xs text-red-500 dark:text-red-400"
            >
                {{ (form.errors as any)[`opsi_${opt.toLowerCase()}`] }}
            </div>
        </div>
    </div>

    <div
        class="mt-4 rounded-lg border border-gray-200 bg-gray-50 p-4 transition-colors duration-200 dark:border-zinc-800 dark:bg-zinc-900"
    >
        <Label
            class="mb-2 block text-sm font-bold text-gray-900 dark:text-zinc-100"
            >Jawaban Benar</Label
        >

        <!-- Pilihan Ganda Kompleks (Checkboxes) -->
        <template v-if="form.jenis === 2">
            <div class="mt-2 flex flex-wrap gap-3">
                <label
                    v-for="opt in ['A', 'B', 'C', 'D', 'E'].slice(0, bank.opsi)"
                    :key="opt"
                    class="flex cursor-pointer items-center gap-2 rounded-md border p-2 px-4 text-sm font-medium transition-all"
                    :class="[
                        isCorrectComplex(opt)
                            ? 'border-emerald-500 bg-emerald-50 font-bold text-emerald-700 dark:border-emerald-600 dark:bg-emerald-950/20 dark:text-emerald-400'
                            : 'border-gray-200 bg-white text-gray-600 hover:border-gray-300 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:border-zinc-600',
                    ]"
                >
                    <input
                        type="checkbox"
                        :value="opt"
                        :checked="isCorrectComplex(opt)"
                        @change="toggleComplexAnswer(opt)"
                        class="h-4 w-4 cursor-pointer rounded border-gray-300 bg-white text-emerald-600 focus:ring-emerald-500 dark:border-zinc-700 dark:bg-zinc-900"
                    />
                    Opsi {{ opt }}
                </label>
            </div>
            <div
                class="mt-2.5 text-[11px] font-medium text-neutral-500 italic dark:text-zinc-400"
            >
                {{ complexSelectedCount }} opsi terpilih sebagai jawaban benar.
            </div>
        </template>

        <!-- Pilihan Ganda Biasa (Single Select Dropdown) -->
        <select
            v-else
            v-model="form.jawaban"
            class="h-10 w-full rounded-md border-gray-300 bg-white px-3 text-neutral-900 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-zinc-700 dark:bg-zinc-950 dark:text-neutral-100"
        >
            <option value="">-- Pilih Jawaban Benar --</option>
            <option
                v-for="opt in ['A', 'B', 'C', 'D', 'E'].slice(0, bank.opsi)"
                :key="opt"
                :value="opt"
            >
                {{ opt }}
            </option>
        </select>

        <div
            v-if="form.errors.jawaban"
            class="mt-1 text-xs text-red-500 dark:text-red-400"
        >
            {{ form.errors.jawaban }}
        </div>
    </div>
</template>
