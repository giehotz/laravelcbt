<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { index as bankSoalIndex } from '@/routes/cbt/bank-soal';
import { create, edit, destroy } from '@/routes/cbt/bank-soal/soal';
import { ArrowLeft, Plus, Edit2, Trash2, HelpCircle } from 'lucide-vue-next';
import MatchingPreview from '@/components/Cbt/MatchingPreview.vue';
import { useConfirmStore } from '@/stores/confirm';

const props = defineProps<{
    bank: any;
    soals: Array<any>;
}>();

const confirmStore = useConfirmStore();

const confirmDelete = (id: number) => {
    confirmStore.requireConfirm({
        title: 'Hapus Soal',
        message: 'Apakah Anda yakin ingin menghapus soal ini?',
        onConfirm: () => {
            router.delete(destroy.url({ bank: props.bank.id, soal: id }));
        }
    });
};

const getJenisLabel = (jenis: number) => {
    const map: Record<number, string> = {
        1: 'Pilihan Ganda',
        2: 'Ganda Kompleks',
        3: 'Menjodohkan',
        4: 'Isian Singkat',
        5: 'Uraian/Esai'
    };
    return map[jenis] || 'Unknown';
};

const activeJenisFilter = ref<number | null>(null);

const jenisTabs = [
    { id: null, label: 'Semua Soal', key: 'all' },
    { id: 1, label: 'Pilihan Ganda', key: 'pg' },
    { id: 2, label: 'Pilihan Ganda Kompleks', key: 'kompleks' },
    { id: 3, label: 'Menjodohkan', key: 'jodohkan' },
    { id: 4, label: 'Isian Singkat', key: 'isian' },
    { id: 5, label: 'Essai/Uraian', key: 'esai' },
];

const getCountForJenis = (jenis: number | null) => {
    if (jenis === null) return props.soals.length;
    return props.soals.filter(s => s.jenis === jenis).length;
};

const filteredSoals = computed(() => {
    let list = [...props.soals];
    if (activeJenisFilter.value !== null) {
        list = list.filter(s => s.jenis === activeJenisFilter.value);
    }
    return list.sort((a, b) => {
        if (a.jenis !== b.jenis) return a.jenis - b.jenis;
        return a.nomor_soal - b.nomor_soal;
    });
});
</script>

<template>
    <Head :title="`Preview Soal - ${bank.kode}`" />

    <div class="bg-gray-100 min-h-screen pb-12">
        <!-- Header -->
        <div class="bg-white border-b border-gray-200 sticky top-0 z-30 shadow-sm">
            <div class="max-w-[1200px] mx-auto px-4 sm:px-6 lg:px-8 py-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <Button variant="outline" size="sm" @click="$inertia.visit(bankSoalIndex.url())" class="shrink-0 h-9 px-3">
                            <ArrowLeft class="w-4 h-4 mr-2" /> Kembali
                        </Button>
                        <div>
                            <h1 class="text-xl font-bold text-gray-900 leading-tight">Daftar Soal (Preview)</h1>
                            <p class="text-sm text-gray-500 font-medium">
                                <span class="font-bold text-gray-700">Bank Soal:</span> {{ bank.kode }} 
                                <span class="mx-2">|</span> 
                                PG: {{ bank.jml_pg }} / {{ bank.tampil_pg }} <span class="mx-2">|</span> 
                                Kompleks: {{ bank.jml_kompleks }} / {{ bank.tampil_kompleks }} <span class="mx-2">|</span>
                                Jodohkan: {{ bank.jml_jodohkan }} / {{ bank.tampil_jodohkan }} <span class="mx-2">|</span>
                                Isian: {{ bank.jml_isian }} / {{ bank.tampil_isian }} <span class="mx-2">|</span>
                                Esai: {{ bank.jml_esai }} / {{ bank.tampil_esai }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="max-w-[1200px] mx-auto px-4 sm:px-6 lg:px-8 mt-6">
            
            <!-- Tambah Soal Actions -->
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4 mb-6 flex flex-wrap gap-3 items-center">
                <span class="text-sm font-bold text-gray-700 mr-2">Tambah Soal:</span>
                <Button v-if="bank.jml_pg < bank.tampil_pg || bank.tampil_pg > 0" @click="router.get(create.url({ bank: bank.id }), { jenis: 1 })" variant="outline" class="border-blue-200 text-blue-700 hover:bg-blue-50">
                    <Plus class="w-4 h-4 mr-1" /> Pilihan Ganda
                </Button>
                <Button v-if="bank.jml_kompleks < bank.tampil_kompleks || bank.tampil_kompleks > 0" @click="router.get(create.url({ bank: bank.id }), { jenis: 2 })" variant="outline" class="border-indigo-200 text-indigo-700 hover:bg-indigo-50">
                    <Plus class="w-4 h-4 mr-1" /> Kompleks
                </Button>
                <Button v-if="bank.jml_jodohkan < bank.tampil_jodohkan || bank.tampil_jodohkan > 0" @click="router.get(create.url({ bank: bank.id }), { jenis: 3 })" variant="outline" class="border-purple-200 text-purple-700 hover:bg-purple-50">
                    <Plus class="w-4 h-4 mr-1" /> Jodohkan
                </Button>
                <Button v-if="bank.jml_isian < bank.tampil_isian || bank.tampil_isian > 0" @click="router.get(create.url({ bank: bank.id }), { jenis: 4 })" variant="outline" class="border-amber-200 text-amber-700 hover:bg-amber-50">
                    <Plus class="w-4 h-4 mr-1" /> Isian Singkat
                </Button>
                <Button v-if="bank.jml_esai < bank.tampil_esai || bank.tampil_esai > 0" @click="router.get(create.url({ bank: bank.id }), { jenis: 5 })" variant="outline" class="border-rose-200 text-rose-700 hover:bg-rose-50">
                    <Plus class="w-4 h-4 mr-1" /> Esai / Uraian
                </Button>
            </div>

            <!-- Tab Navigation Filters -->
            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden mb-6 shadow-sm">
                <div class="flex border-b border-gray-100 overflow-x-auto">
                    <button 
                        v-for="tab in jenisTabs" 
                        :key="tab.key"
                        @click="activeJenisFilter = tab.id"
                        class="px-5 py-3 text-xs font-bold whitespace-nowrap border-b-2 transition-colors flex items-center gap-2"
                        :class="activeJenisFilter === tab.id ? 'border-blue-500 text-blue-600 bg-blue-50/50' : 'border-transparent text-gray-500 hover:text-gray-700 hover:bg-gray-50/50'"
                    >
                        {{ tab.label }}
                        <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full"
                            :class="activeJenisFilter === tab.id ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-600'">
                            {{ getCountForJenis(tab.id) }}
                        </span>
                    </button>
                </div>
            </div>

            <!-- Empty State -->
            <div v-if="filteredSoals.length === 0" class="bg-white border border-gray-200 rounded-xl p-12 text-center shadow-sm">
                <HelpCircle class="w-16 h-16 text-gray-300 mx-auto mb-4" />
                <h3 class="text-lg font-bold text-gray-700">Belum Ada Soal</h3>
                <p class="text-gray-500 mt-2">
                    {{ activeJenisFilter === null ? 'Silakan klik salah satu tombol Tambah Soal di atas untuk mulai membuat soal di Bank ini.' : 'Tidak ada soal dengan tipe ini di dalam Bank Soal.' }}
                </p>
            </div>

            <!-- List Soal -->
            <div class="space-y-6">
                <div v-for="item in filteredSoals" :key="item.id" class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm relative group hover:border-blue-300 transition-colors">
                    
                    <!-- Toolbar (Edit/Hapus) -->
                    <div class="absolute top-4 right-4 flex gap-2">
                        <Button variant="outline" size="sm" class="h-8 px-2 border-gray-200 text-gray-600 hover:text-blue-600 hover:border-blue-300 hover:bg-blue-50" @click="router.get(edit.url({ bank: bank.id, soal: item.id }))">
                            <Edit2 class="w-4 h-4" /> <span class="ml-1 text-xs">Edit</span>
                        </Button>
                        <Button variant="outline" size="sm" class="h-8 px-2 border-gray-200 text-gray-600 hover:text-red-600 hover:border-red-300 hover:bg-red-50" @click="confirmDelete(item.id)">
                            <Trash2 class="w-4 h-4" /> <span class="ml-1 text-xs">Hapus</span>
                        </Button>
                    </div>

                    <!-- Header Soal -->
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-10 h-10 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center font-bold text-lg border border-blue-200 shadow-sm">
                            {{ item.nomor_soal }}
                        </div>
                        <div>
                            <span class="px-2 py-1 rounded text-xs font-bold uppercase tracking-wider bg-gray-100 text-gray-700 border border-gray-200">
                                {{ getJenisLabel(item.jenis) }}
                            </span>
                        </div>
                    </div>

                    <!-- Teks Soal -->
                    <div class="prose max-w-none text-gray-800 mb-6" v-html="item.soal"></div>

                    <!-- Pilihan Ganda -->
                    <div v-if="item.jenis === 1 || item.jenis === 2" class="space-y-3 pl-2">
                        <div v-for="opt in ['A', 'B', 'C', 'D', 'E'].slice(0, bank.opsi)" :key="opt" class="flex gap-4">
                            <div class="pt-1">
                                <div class="w-7 h-7 rounded-full border-2 flex items-center justify-center text-sm font-bold"
                                    :class="item.jawaban === opt ? 'bg-green-500 border-green-500 text-white' : 'border-gray-300 text-gray-500'">
                                    {{ opt }}
                                </div>
                            </div>
                            <div class="flex-1 prose max-w-none text-gray-700 pt-1" :class="item.jawaban === opt ? 'font-medium' : ''" v-html="item[`opsi_${opt.toLowerCase()}`]"></div>
                        </div>
                    </div>

                    <!-- Menjodohkan -->
                    <div v-else-if="item.jenis === 3" class="mt-4">
                        <div class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Kunci Pasangan Menjodohkan (Garis Penghubung):</div>
                        <MatchingPreview :jawaban="item.pairs" />
                    </div>

                    <!-- Isian Singkat / Esai -->
                    <div v-else-if="item.jenis === 4 || item.jenis === 5" class="mt-4 p-4 bg-gray-50 border border-gray-200 rounded-lg">
                        <div class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Kunci Jawaban / Pedoman Penilaian:</div>
                        <div class="whitespace-pre-wrap text-gray-700 text-sm">{{ item.jawaban || '-' }}</div>
                    </div>

                </div>
            </div>

        </div>
    </div>
</template>
