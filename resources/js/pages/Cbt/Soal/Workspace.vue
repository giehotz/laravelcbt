<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { index as bankSoalIndex } from '@/routes/cbt/bank-soal';
import { index, store, update, destroy } from '@/routes/cbt/bank-soal/soal';
import RichEditor from '@/components/RichEditor.vue';
import { ArrowLeft, Save, Plus, Trash2 } from 'lucide-vue-next';
import { useConfirmStore } from '@/stores/confirm';

// Partials
import PgEditor from './Partials/PgEditor.vue';
import MenjodohkanEditor from './Partials/MenjodohkanEditor.vue';
import IsianEsaiEditor from './Partials/IsianEsaiEditor.vue';

const props = defineProps<{
    bank: any;
    soals: Array<any>;
    active_jenis?: number;
    active_soal_id?: number | null;
}>();

const confirmStore = useConfirmStore();

// State
const activeJenis = ref<number>(props.active_jenis || 1);
const activeNomor = ref<number>(1);
const formMode = ref<'create' | 'edit'>('create');
const activeSoalId = ref<number | null>(props.active_soal_id || null);

// Ref to matching editor to call validation
const menjodohkanEditorRef = ref<InstanceType<typeof MenjodohkanEditor> | null>(null);

const jenisTabs = [
    { id: 1, label: 'Pilihan Ganda', key: 'pg' },
    { id: 2, label: 'Pilihan Ganda Kompleks', key: 'kompleks' },
    { id: 3, label: 'Menjodohkan', key: 'jodohkan' },
    { id: 4, label: 'Isian Singkat', key: 'isian' },
    { id: 5, label: 'Essai/Uraian', key: 'esai' },
];

const currentTab = computed(() => jenisTabs.find(t => t.id === activeJenis.value)!);

const targetJml = computed(() => {
    return props.bank[`tampil_${currentTab.value.key}`] || 0;
});

const currentSoals = computed(() => {
    return props.soals.filter(s => s.jenis === activeJenis.value);
});

const getSoalByNomor = (nomor: number) => {
    return currentSoals.value.find(s => s.nomor_soal === nomor);
};

// Calculate how many number circles to show
const numberCircles = computed(() => {
    let max = targetJml.value;
    const existingMax = currentSoals.value.reduce((acc, curr) => Math.max(acc, curr.nomor_soal), 0);
    if (existingMax > max) max = existingMax;
    // Always show at least up to the activeNomor if they clicked + Tambah
    if (activeNomor.value > max) max = activeNomor.value;
    
    // We want to return an array of objects
    return Array.from({ length: max }, (_, i) => {
        const no = i + 1;
        const exists = !!getSoalByNomor(no);
        return { no, exists };
    });
});

const form = useForm({
    jenis: 1,
    nomor_soal: 1,
    soal: '',
    opsi_a: '',
    opsi_b: '',
    opsi_c: '',
    opsi_d: '',
    opsi_e: '',
    jawaban: '' as any,
    kesulitan: 1,
    timer: false,
    timer_menit: 0,
    tampilkan: true,
});

const loadForm = (nomor: number) => {
    activeNomor.value = nomor;
    const existing = getSoalByNomor(nomor);
    
    if (existing) {
        formMode.value = 'edit';
        activeSoalId.value = existing.id;
        form.jenis = existing.jenis;
        form.nomor_soal = existing.nomor_soal;
        form.soal = existing.soal || '';
        form.opsi_a = existing.opsi_a || '';
        form.opsi_b = existing.opsi_b || '';
        form.opsi_c = existing.opsi_c || '';
        form.opsi_d = existing.opsi_d || '';
        form.opsi_e = existing.opsi_e || '';
        
        if (existing.jenis === 2 || existing.jenis === 3) {
            form.jawaban = existing.jawaban || [];
        } else {
            form.jawaban = existing.jawaban || '';
        }
        form.kesulitan = existing.kesulitan || 1;
        form.timer = existing.timer || false;
        form.timer_menit = existing.timer_menit || 0;
        form.tampilkan = existing.tampilkan ?? true;
    } else {
        formMode.value = 'create';
        activeSoalId.value = null;
        form.reset();
        form.jenis = activeJenis.value;
        form.nomor_soal = nomor;
        form.kesulitan = 1;
        form.tampilkan = true;
        
        if (activeJenis.value === 2 || activeJenis.value === 3) {
            form.jawaban = [];
        } else {
            form.jawaban = '';
        }
    }
    form.clearErrors();
};

const switchTab = (jenisId: number) => {
    activeJenis.value = jenisId;
    // Find the first empty number, or 1 if full
    let firstEmpty = 1;
    for (let i = 1; i <= targetJml.value; i++) {
        if (!props.soals.find(s => s.jenis === jenisId && s.nomor_soal === i)) {
            firstEmpty = i;
            break;
        }
    }
    loadForm(firstEmpty);
};

const submitForm = () => {
    if (activeJenis.value === 3) {
        const isValid = menjodohkanEditorRef.value?.validate();
        if (isValid === false) return;
    }
    
    if (formMode.value === 'edit' && activeSoalId.value) {
        form.put(update.url({ bank: props.bank.id, soal: activeSoalId.value }), {
            preserveScroll: true,
            preserveState: true,
            onSuccess: () => {
                alert('Soal berhasil diperbarui!');
            }
        });
    } else {
        form.post(store.url({ bank: props.bank.id }), {
            preserveScroll: true,
            preserveState: true,
            onSuccess: () => {
                alert('Soal berhasil disimpan!');
                loadForm(activeNomor.value); // Reload to get ID and switch mode to edit
            }
        });
    }
};

const deleteSoal = () => {
    if (!activeSoalId.value) return;
    confirmStore.requireConfirm({
        title: 'Hapus Soal',
        message: 'Apakah Anda yakin ingin menghapus soal ini?',
        onConfirm: () => {
            router.delete(destroy.url({ bank: props.bank.id, soal: activeSoalId.value! }), {
                preserveScroll: true,
                preserveState: true,
                onSuccess: () => {
                    loadForm(activeNomor.value); // Re-evaluate what should be here (it will become empty 'create' mode)
                }
            });
        }
    });
};

const tambahNomor = () => {
    const nextNo = numberCircles.value.length + 1;
    loadForm(nextNo);
};

const backToIndex = () => {
    router.visit(index.url({ bank: props.bank.id }));
};

// Initialization
if (props.active_soal_id) {
    const existing = props.soals.find(s => s.id === props.active_soal_id);
    if (existing) {
        loadForm(existing.nomor_soal);
    } else {
        loadForm(1);
    }
} else {
    // If it's a new create, find next empty for the active jenis
    let firstEmpty = 1;
    for (let i = 1; i <= targetJml.value; i++) {
        if (!props.soals.find(s => s.jenis === activeJenis.value && s.nomor_soal === i)) {
            firstEmpty = i;
            break;
        }
    }
    loadForm(firstEmpty);
}
</script>

<template>
    <Head :title="`Editor Soal - ${bank.kode}`" />

    <div class="bg-gray-100 min-h-screen pb-12">
            <!-- Header section -->
            <div class="bg-white border-b border-gray-200 sticky top-0 z-30 shadow-sm">
                <div class="max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8 py-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <Button variant="outline" size="sm" @click="backToIndex" class="shrink-0 h-9 px-3">
                                <ArrowLeft class="w-4 h-4 mr-2" /> Kembali
                            </Button>
                            <div>
                                <h1 class="text-xl font-bold text-gray-900 leading-tight">Buat Soal</h1>
                                <p class="text-sm text-gray-500 font-medium">
                                    <span class="font-bold text-gray-700">Bank Soal:</span> {{ bank.kode }} 
                                    <span class="mx-2">|</span> 
                                    PG: {{ bank.jml_pg }} <span class="mx-2">|</span> 
                                    Kompleks: {{ bank.jml_kompleks }} <span class="mx-2">|</span>
                                    Jodohkan: {{ bank.jml_jodohkan }} <span class="mx-2">|</span>
                                    Isian: {{ bank.jml_isian }} <span class="mx-2">|</span>
                                    Esai: {{ bank.jml_esai }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8 mt-6">
                <!-- Tab Navigation -->
                <div class="bg-white rounded-t-xl border border-gray-200 border-b-0 overflow-x-auto">
                    <div class="flex">
                        <button 
                            v-for="tab in jenisTabs" 
                            :key="tab.id"
                            @click="switchTab(tab.id)"
                            class="px-6 py-4 text-sm font-semibold whitespace-nowrap border-b-2 transition-colors"
                            :class="activeJenis === tab.id ? 'border-blue-500 text-blue-600 bg-blue-50' : 'border-transparent text-gray-500 hover:text-gray-700 hover:bg-gray-50'"
                        >
                            {{ tab.label }}
                        </button>
                    </div>
                </div>

                <!-- Main Editor Area -->
                <div class="bg-white rounded-b-xl border border-gray-200 shadow-sm p-6">
                    
                    <!-- Alert Target -->
                    <div class="mb-6 p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg text-sm font-medium">
                        {{ currentTab.label }}, jumlah soal seharusnya: <strong>{{ targetJml }}</strong>
                    </div>

                    <!-- Number Navigation -->
                    <div class="mb-8 flex items-center gap-4">
                        <div class="text-sm font-bold text-gray-700 whitespace-nowrap">
                            {{ currentTab.key === 'pg' ? 'PG' : currentTab.key.toUpperCase() }} NOMOR:
                        </div>
                        <div class="flex flex-wrap gap-2 items-center">
                            <button 
                                v-for="circle in numberCircles" 
                                :key="circle.no"
                                @click="loadForm(circle.no)"
                                class="w-9 h-9 rounded-full flex items-center justify-center text-sm font-bold transition-all border-2"
                                :class="[
                                    activeNomor === circle.no 
                                        ? 'ring-4 ring-blue-200 border-blue-500 bg-blue-500 text-white shadow-md' // Active
                                        : circle.exists 
                                            ? 'border-green-500 bg-green-500 text-white hover:bg-green-600' // Exists
                                            : 'border-gray-300 bg-white text-gray-500 hover:border-blue-400' // Empty
                                ]"
                            >
                                {{ circle.no }}
                            </button>
                            
                            <button @click="tambahNomor" class="ml-2 h-9 px-4 rounded-full border border-green-500 text-green-600 flex items-center gap-1 hover:bg-green-50 transition-colors text-sm font-semibold">
                                <Plus class="w-4 h-4" /> Tambah {{ currentTab.key === 'pg' ? 'PG' : currentTab.key.toUpperCase() }}
                            </button>
                        </div>
                    </div>

                    <!-- Editor Header -->
                    <div class="flex justify-between items-center mb-6 pb-4 border-b border-gray-200">
                        <h2 class="text-xl font-bold text-gray-800">
                            Soal {{ currentTab.key === 'pg' ? 'PG' : currentTab.key.toUpperCase() }} Nomor: <span class="text-blue-600">{{ activeNomor }}</span>
                            <span v-if="formMode === 'create'" class="ml-2 text-sm font-normal text-amber-600 bg-amber-50 px-2 py-1 rounded">(Baru)</span>
                        </h2>
                        <div class="flex gap-2">
                            <Button v-if="formMode === 'edit'" variant="outline" class="border-red-200 text-red-600 hover:bg-red-50" @click="deleteSoal">
                                <Trash2 class="w-4 h-4 mr-2" /> Hapus
                            </Button>
                            <Button @click="submitForm" :disabled="form.processing" class="bg-blue-600 hover:bg-blue-700 text-white font-bold px-6">
                                <Save class="w-4 h-4 mr-2" /> Simpan
                            </Button>
                        </div>
                    </div>

                    <!-- Split Layout Editor -->
                    <div class="flex flex-col lg:flex-row gap-8">
                        
                        <!-- Left Panel: Main Question -->
                        <div class="w-full lg:w-1/2 flex flex-col">
                            <div class="mb-2 flex justify-between items-end">
                                <Label class="text-sm font-bold text-gray-700 uppercase tracking-wider">Soal</Label>
                                <span v-if="form.errors.soal" class="text-xs text-red-500">{{ form.errors.soal }}</span>
                            </div>
                            <div class="border border-gray-300 rounded-lg overflow-hidden flex-1 min-h-[300px]">
                                <RichEditor v-model="form.soal" :minimal="false" class="h-full border-0" />
                            </div>
                        </div>

                        <!-- Right Panel: Answers -->
                        <div class="w-full lg:w-1/2 flex flex-col gap-6">
                            
                            <!-- PG/Kompleks Answers -->
                            <PgEditor 
                                v-if="activeJenis === 1 || activeJenis === 2" 
                                :form="form" 
                                :bank="bank" 
                            />

                            <!-- Menjodohkan Editor (Relational & Manual Drawing) -->
                            <MenjodohkanEditor 
                                v-else-if="activeJenis === 3" 
                                ref="menjodohkanEditorRef"
                                :form="form" 
                                :soal="getSoalByNomor(activeNomor)" 
                            />

                            <!-- Isian / Esai -->
                            <IsianEsaiEditor 
                                v-else-if="activeJenis === 4 || activeJenis === 5" 
                                :form="form" 
                            />

                        </div>

                    </div>
                </div>
            </div>
    </div>
</template>

<style scoped>
/* Optional specific overrides if RichEditor needs height adjustments in split view */
:deep(.ql-container) {
    min-height: 200px;
    height: auto;
}
:deep(.ql-editor) {
    min-height: 200px;
}
</style>
