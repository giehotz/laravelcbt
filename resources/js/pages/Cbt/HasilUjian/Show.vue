<template>
    <div class="flex min-h-screen flex-col bg-gray-50">
        <!-- Header -->
        <header
            class="border-b border-gray-200 bg-white px-4 py-4 sm:px-6 lg:px-8"
        >
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <Link
                        :href="hasilUjianIndex().url"
                        class="rounded-lg bg-gray-100 p-2 text-gray-500 hover:bg-gray-200"
                    >
                        <svg
                            class="h-5 w-5"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M15 19l-7-7 7-7"
                            ></path>
                        </svg>
                    </Link>
                    <div>
                        <h1 class="text-xl font-bold text-gray-900">
                            Detail Hasil Ujian: {{ jadwal.bank_soal?.nama }}
                        </h1>
                        <p class="text-sm text-gray-500">
                            {{ jadwal.tgl_mulai }} s/d {{ jadwal.tgl_selesai }}
                        </p>
                    </div>
                </div>
            </div>
        </header>

        <main
            class="mx-auto flex w-full max-w-7xl flex-1 flex-col gap-6 p-4 sm:p-6 lg:p-8"
        >
            <!-- Summary Cards -->
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <div class="rounded-xl border border-gray-100 bg-white p-6 shadow-sm">
                    <h3 class="text-sm font-medium text-gray-500">Total Peserta Ujian</h3>
                    <p class="mt-2 text-3xl font-bold text-gray-900">{{ nilais.length }}</p>
                </div>
                <div class="rounded-xl border border-gray-100 bg-white p-6 shadow-sm">
                    <h3 class="text-sm font-medium text-gray-500">Rata-rata Nilai (Semua)</h3>
                    <p class="mt-2 text-3xl font-bold text-indigo-600">{{ averageNilai }}</p>
                </div>
                <div class="rounded-xl border border-gray-100 bg-white p-6 shadow-sm">
                    <h3 class="text-sm font-medium text-gray-500">Nilai Tertinggi</h3>
                    <p class="mt-2 text-3xl font-bold text-emerald-600">{{ maxNilai }}</p>
                </div>
            </div>

            <!-- Perbaikan Nilai (Katrol) -->
            <div class="rounded-xl border border-gray-100 bg-white p-6 shadow-sm">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Perbaikan Nilai (Katrol)</h3>
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Target Nilai Tertinggi</label>
                        <div class="mt-1">
                            <input
                                v-model.number="katrolMax"
                                type="number"
                                min="0"
                                max="100"
                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                placeholder="Misal: 100"
                            />
                        </div>
                        <p class="mt-1 text-xs text-gray-500">Maksimal 100 (misal 80 - 100)</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Target Nilai Terendah</label>
                        <div class="mt-1">
                            <input
                                v-model.number="katrolMin"
                                type="number"
                                min="0"
                                max="100"
                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                placeholder="Misal: 60"
                            />
                        </div>
                        <p class="mt-1 text-xs text-gray-500">Dibawah nilai tertinggi (misal 60)</p>
                    </div>
                </div>
            </div>

            <!-- Data Table -->
            <div
                class="rounded-xl border border-gray-100 bg-white shadow-sm overflow-hidden"
            >
                <div class="p-4 border-b border-gray-200">
                    <input
                        v-model="searchQuery"
                        type="text"
                        placeholder="Cari nama siswa..."
                        class="block w-full max-w-md rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                    />
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th rowspan="2" scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b border-gray-200">
                                    No.
                                </th>
                                <th rowspan="2" scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b border-gray-200">
                                    No. Peserta
                                </th>
                                <th rowspan="2" scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b border-gray-200">
                                    Nama
                                </th>
                                <th rowspan="2" scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b border-gray-200">
                                    Sesi
                                </th>
                                <th rowspan="2" scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b border-gray-200">
                                    Ruang
                                </th>
                                <th rowspan="2" scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b border-gray-200">
                                    Mulai
                                </th>
                                <th rowspan="2" scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b border-gray-200">
                                    Durasi
                                </th>
                                <th colspan="2" scope="col" class="px-3 py-2 text-center text-xs font-medium text-gray-500 uppercase tracking-wider border-b border-gray-200">
                                    PG
                                </th>
                                <th rowspan="2" scope="col" class="px-3 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider border-b border-gray-200">
                                    Skor PG
                                </th>
                                <th colspan="2" scope="col" class="px-3 py-2 text-center text-xs font-medium text-gray-500 uppercase tracking-wider border-b border-gray-200">
                                    Nilai
                                </th>
                                <th rowspan="2" scope="col" class="px-3 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider border-b border-gray-200">
                                    Aksi
                                </th>
                            </tr>
                            <tr>
                                <th scope="col" class="px-3 py-2 text-center text-xs font-medium text-gray-500 uppercase tracking-wider border-b border-gray-200">
                                    B
                                </th>
                                <th scope="col" class="px-3 py-2 text-center text-xs font-medium text-gray-500 uppercase tracking-wider border-b border-gray-200">
                                    S
                                </th>
                                <th scope="col" class="px-3 py-2 text-center text-xs font-medium text-gray-500 uppercase tracking-wider border-b border-gray-200">
                                    Asli
                                </th>
                                <th scope="col" class="px-3 py-2 text-center text-xs font-medium text-gray-500 uppercase tracking-wider border-b border-gray-200">
                                    Katrol
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr
                                v-for="(nilai, index) in filteredNilais"
                                :key="nilai.id"
                                class="hover:bg-gray-50 transition-colors"
                            >
                                <td class="px-3 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ index + 1 }}
                                </td>
                                <td class="px-3 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ nilai.siswa?.nis || '-' }}
                                </td>
                                <td class="px-3 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ nilai.siswa?.nama || 'Unknown' }}
                                </td>
                                <td class="px-3 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ (nilai.siswa?.sesi_siswa && nilai.siswa.sesi_siswa.length > 0) ? nilai.siswa.sesi_siswa[0].sesi?.nama : '-' }}
                                </td>
                                <td class="px-3 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ (nilai.siswa?.sesi_siswa && nilai.siswa.sesi_siswa.length > 0) ? nilai.siswa.sesi_siswa[0].ruang?.nama : '-' }}
                                </td>
                                <td class="px-3 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ (nilai.siswa?.durasi && nilai.siswa.durasi.length > 0 && nilai.siswa.durasi[0].mulai) ? nilai.siswa.durasi[0].mulai : '-' }}
                                </td>
                                <td class="px-3 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <span v-if="nilai.siswa?.durasi && nilai.siswa.durasi.length > 0 && nilai.siswa.durasi[0].lama_ujian">
                                        {{ nilai.siswa.durasi[0].lama_ujian }} m
                                    </span>
                                    <span v-else>-</span>
                                </td>
                                <td class="px-3 py-4 whitespace-nowrap text-sm text-center text-emerald-600 font-medium">
                                    {{ nilai.pg_benar || 0 }}
                                </td>
                                <td class="px-3 py-4 whitespace-nowrap text-sm text-center text-red-600 font-medium">
                                    {{ (jadwal.bank_soal?.tampil_pg || 0) - (nilai.pg_benar || 0) }}
                                </td>
                                <td class="px-3 py-4 whitespace-nowrap text-sm text-center font-bold text-gray-900 bg-gray-50">
                                    {{ nilai.pg_nilai || 0 }}
                                </td>
                                <td class="px-3 py-4 whitespace-nowrap text-sm text-center font-bold text-gray-900">
                                    {{ nilai.total_nilai || 0 }}
                                </td>
                                <td class="px-3 py-4 whitespace-nowrap text-sm text-center font-bold text-gray-900 bg-gray-50">
                                    {{ calculateKatrol(nilai.total_nilai || 0) }}
                                </td>
                                <td class="px-3 py-4 whitespace-nowrap text-sm text-center font-medium">
                                    <button class="text-indigo-600 hover:text-indigo-900 text-xs bg-indigo-50 px-2 py-1 rounded">Lihat</button>
                                </td>
                            </tr>
                            <tr v-if="filteredNilais.length === 0">
                                <td colspan="13" class="px-3 py-12 text-center text-sm text-gray-500 italic">
                                    Data nilai tidak ditemukan.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/app/AdminLayout.vue';
import { index as hasilUjianIndex } from '@/routes/cbt/hasil-ujian';

defineOptions({ layout: AdminLayout });

const props = defineProps<{
    jadwal: any;
    nilais: any[];
}>();

const searchQuery = ref('');

const katrolMax = ref(100);
const katrolMin = ref(60);

const maxAsli = computed(() => {
    if (!props.nilais || props.nilais.length === 0) return 0;
    return Math.max(...props.nilais.map(n => n.total_nilai || 0));
});

const minAsli = computed(() => {
    if (!props.nilais || props.nilais.length === 0) return 0;
    return Math.min(...props.nilais.map(n => n.total_nilai || 0));
});

const calculateKatrol = (asli: number) => {
    // Pastikan tidak membagi dengan nol
    if (maxAsli.value === minAsli.value) {
        return maxAsli.value > 0 ? katrolMax.value : 0;
    }
    
    let k = ((asli - minAsli.value) / (maxAsli.value - minAsli.value)) * (katrolMax.value - katrolMin.value) + katrolMin.value;
    
    // Pembulatan ke 2 desimal atau bulat
    return Math.round(k * 100) / 100;
};

const filteredNilais = computed(() => {
    if (!searchQuery.value) {
return props.nilais;
}

    const query = searchQuery.value.toLowerCase();

    return props.nilais.filter(n => {
        const nama = n.siswa?.nama?.toLowerCase() || '';
        const nis = n.siswa?.nis?.toLowerCase() || '';

        return nama.includes(query) || nis.includes(query);
    });
});

const averageNilai = computed(() => {
    if (!props.nilais || props.nilais.length === 0) {
return 0;
}

    const total = props.nilais.reduce((sum, n) => sum + (n.total_nilai || 0), 0);

    return (total / props.nilais.length).toFixed(2);
});

const maxNilai = computed(() => {
    if (!props.nilais || props.nilais.length === 0) {
return 0;
}

    return Math.max(...props.nilais.map(n => n.total_nilai || 0));
});
</script>
