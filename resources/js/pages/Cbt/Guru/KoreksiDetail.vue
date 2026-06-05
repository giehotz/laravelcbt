<template>
    <div class="min-h-screen bg-gray-50 flex flex-col">
        <!-- Header -->
        <header class="bg-white border-b border-gray-200 px-4 sm:px-6 lg:px-8 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <Link :href="koreksiIndex().url" class="p-2 text-gray-500 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                    </Link>
                    <div>
                        <h1 class="text-xl font-bold text-gray-900">Koreksi Essai: {{ siswa.nama }}</h1>
                        <p class="text-sm text-gray-500">{{ siswa.nis }} | {{ jadwal.bank_soal?.nama }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-4 text-sm font-medium">
                    <div class="bg-blue-50 text-blue-700 px-4 py-2 rounded-lg border border-blue-100">
                        Nilai PG: <span class="font-bold text-lg">{{ nilai?.pg_nilai || 0 }}</span>
                    </div>
                    <div class="bg-emerald-50 text-emerald-700 px-4 py-2 rounded-lg border border-emerald-100">
                        Nilai Essai: <span class="font-bold text-lg">{{ nilai?.esai_nilai || 0 }}</span>
                    </div>
                </div>
            </div>
        </header>

        <main class="flex-1 max-w-4xl w-full mx-auto p-4 sm:p-6 lg:p-8 flex flex-col gap-6">
            <form @submit.prevent="submitKoreksi">
                
                <div v-if="!form.koreksi.length" class="bg-white p-8 rounded-xl shadow-sm border border-gray-100 text-center">
                    <p class="text-gray-500">Tidak ada soal essai untuk jadwal ujian ini.</p>
                </div>

                <div v-for="(item, index) in form.koreksi" :key="item.id" 
                    class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6">
                    
                    <div class="flex justify-between items-start mb-4 border-b border-gray-100 pb-4">
                        <h3 class="font-bold text-gray-800 text-lg">Soal {{ item.no_soal_alias }}</h3>
                        
                        <!-- Input Nilai Essai -->
                        <div class="flex items-center gap-3 bg-gray-50 p-3 rounded-lg border border-gray-200">
                            <label class="text-sm font-semibold text-gray-700 whitespace-nowrap">Point (0-100)</label>
                            <input type="number" v-model="item.point_esai" min="0" max="100" required
                                class="w-24 text-right rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 font-bold text-lg text-indigo-700">
                        </div>
                    </div>

                    <!-- Pertanyaan -->
                    <div class="prose max-w-none text-gray-800 mb-6 bg-blue-50 p-4 rounded-lg border border-blue-100">
                        <div class="text-xs font-bold text-blue-600 uppercase mb-2">Pertanyaan</div>
                        <div v-html="item.soal_html"></div>
                    </div>

                    <!-- Jawaban Siswa -->
                    <div class="prose max-w-none mb-6">
                        <div class="text-xs font-bold text-gray-500 uppercase mb-2">Jawaban Siswa</div>
                        <div v-if="item.jawaban_siswa" class="p-4 bg-gray-50 border border-gray-200 rounded-lg text-gray-800" v-html="item.jawaban_siswa"></div>
                        <div v-else class="p-4 bg-red-50 border border-red-100 rounded-lg text-red-600 italic">
                            Siswa tidak menjawab soal ini.
                        </div>
                    </div>

                    <!-- Kunci Jawaban (Referensi Guru) -->
                    <div class="prose max-w-none">
                        <div class="text-xs font-bold text-emerald-600 uppercase mb-2">Referensi Jawaban (Kunci)</div>
                        <div class="p-4 bg-emerald-50 border border-emerald-100 rounded-lg text-emerald-900" v-html="item.jawaban_benar || '-'"></div>
                    </div>
                </div>

                <div v-if="form.koreksi.length" class="sticky bottom-6 bg-white p-4 rounded-xl shadow-lg border border-gray-200 flex justify-between items-center z-10">
                    <div class="text-sm text-gray-500">
                        Total Point Diberikan: <span class="font-bold text-gray-900">{{ totalPoint }}</span> / {{ form.koreksi.length * 100 }}
                    </div>
                    <button type="submit" :disabled="form.processing"
                        class="bg-indigo-600 hover:bg-indigo-700 text-white px-8 py-3 rounded-xl font-bold shadow-sm transition-colors flex items-center gap-2">
                        <svg v-if="form.processing" class="animate-spin h-5 w-5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        Simpan Nilai Essai
                    </button>
                </div>
            </form>
        </main>
    </div>
</template>

<script setup>
import { computed } from 'vue';
import { useForm, Link } from '@inertiajs/vue3';
import { index as koreksiIndex, simpanKoreksi } from '@/routes/cbt/koreksi';

const props = defineProps({
    jadwal: Object,
    siswa: Object,
    soalSiswas: Array,
    nilai: Object
});

const form = useForm({
    koreksi: props.soalSiswas.map(ss => ({
        id: ss.id,
        no_soal_alias: ss.no_soal_alias,
        soal_html: ss.soal.soal,
        jawaban_siswa: ss.jawaban_siswa,
        jawaban_benar: ss.soal.jawaban, // Kunci referensi guru
        point_esai: ss.point_esai || 0
    }))
});

const totalPoint = computed(() => {
    return form.koreksi.reduce((acc, curr) => acc + Number(curr.point_esai || 0), 0);
});

const submitKoreksi = () => {
    form.post(simpanKoreksi({ jadwal: props.jadwal.id, siswa: props.siswa.id }).url, {
        preserveScroll: true,
        onSuccess: () => {
            alert('Nilai essai berhasil disimpan!');
        }
    });
};
</script>
