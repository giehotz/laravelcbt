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
    return Array.isArray(props.form.jawaban) && props.form.jawaban.includes(opt);
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
        <div v-for="opt in ['A', 'B', 'C', 'D', 'E'].slice(0, bank.opsi)" :key="opt" class="flex flex-col">
            <div class="mb-1">
                <Label class="text-sm font-bold text-gray-700">Jawaban {{ opt }}</Label>
            </div>
            <div class="border border-gray-300 rounded-lg overflow-hidden relative">
                <RichEditor v-model="(form as any)[`opsi_${opt.toLowerCase()}`]" :minimal="true" />
            </div>
            <div v-if="(form.errors as any)[`opsi_${opt.toLowerCase()}`]" class="text-xs text-red-500 mt-1">
                {{ (form.errors as any)[`opsi_${opt.toLowerCase()}`] }}
            </div>
        </div>
    </div>

    <div class="p-4 bg-gray-50 border border-gray-200 rounded-lg mt-4">
        <Label class="text-sm font-bold text-gray-900 block mb-2">Jawaban Benar</Label>
        
        <!-- Pilihan Ganda Kompleks (Checkboxes) -->
        <template v-if="form.jenis === 2">
            <div class="flex flex-wrap gap-3 mt-2">
                <label 
                    v-for="opt in ['A', 'B', 'C', 'D', 'E'].slice(0, bank.opsi)" 
                    :key="opt"
                    class="flex items-center gap-2 cursor-pointer p-2 px-4 rounded-md border transition-all text-sm font-medium"
                    :class="[
                        isCorrectComplex(opt)
                            ? 'border-emerald-500 bg-emerald-50 text-emerald-700 font-bold'
                            : 'border-gray-200 hover:border-gray-300 bg-white text-gray-600'
                    ]"
                >
                    <input 
                        type="checkbox" 
                        :value="opt" 
                        :checked="isCorrectComplex(opt)"
                        @change="toggleComplexAnswer(opt)"
                        class="rounded text-emerald-600 focus:ring-emerald-500 border-gray-300 w-4 h-4 cursor-pointer"
                    />
                    Opsi {{ opt }}
                </label>
            </div>
            <div class="text-[11px] text-neutral-500 mt-2.5 font-medium italic">
                {{ complexSelectedCount }} opsi terpilih sebagai jawaban benar.
            </div>
        </template>
        
        <!-- Pilihan Ganda Biasa (Single Select Dropdown) -->
        <select v-else v-model="form.jawaban" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 h-10 px-3">
            <option value="">-- Pilih Jawaban Benar --</option>
            <option v-for="opt in ['A', 'B', 'C', 'D', 'E'].slice(0, bank.opsi)" :key="opt" :value="opt">{{ opt }}</option>
        </select>
        
        <div v-if="form.errors.jawaban" class="text-xs text-red-500 mt-1">{{ form.errors.jawaban }}</div>
    </div>
</template>
