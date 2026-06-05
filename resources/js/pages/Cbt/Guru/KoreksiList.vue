<template>
    <div class="min-h-screen bg-gray-50 flex flex-col">
        <!-- Header -->
        <header class="bg-white border-b border-gray-200 px-4 sm:px-6 lg:px-8 py-4">
            <div class="flex items-center gap-4">
                <div class="bg-indigo-600 p-2 rounded-lg">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                </div>
                <div>
                    <h1 class="text-xl font-bold text-gray-900">Koreksi Jawaban Essai</h1>
                    <p class="text-sm text-gray-500">Pilih jadwal ujian dan siswa untuk memberikan nilai pada soal essai.</p>
                </div>
            </div>
        </header>

        <main class="flex-1 max-w-7xl w-full mx-auto p-4 sm:p-6 lg:p-8 flex flex-col gap-6">
            <!-- Filter Jadwal -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <div class="max-w-md">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Pilih Jadwal Ujian (Yang memiliki essai)</label>
                    <select v-model="selectedJadwalId" @change="loadSiswaList" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        <option value="">-- Pilih Jadwal --</option>
                        <option v-for="jadwal in jadwals" :key="jadwal.id" :value="jadwal.id">
                            {{ jadwal.bank_soal?.nama }} ({{ jadwal.tgl_mulai }})
                        </option>
                    </select>
                </div>
            </div>

            <!-- Tabel Siswa -->
            <div v-if="selectedJadwalId" class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Siswa</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nilai PG</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nilai Essai</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status Koreksi</th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-if="isLoading" class="animate-pulse">
                                <td colspan="6" class="px-6 py-12 text-center text-gray-500">Memuat data siswa...</td>
                            </tr>
                            <tr v-else-if="!siswaList.length">
                                <td colspan="6" class="px-6 py-12 text-center text-gray-500">Belum ada siswa yang menyelesaikan ujian ini.</td>
                            </tr>
                            <tr v-for="(siswa, index) in siswaList" :key="siswa.id" class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ index + 1 }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="font-medium text-gray-900">{{ siswa.nama }}</div>
                                    <div class="text-sm text-gray-500">{{ siswa.nis }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-bold">
                                    {{ siswa.pg_nilai || 0 }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-bold">
                                    {{ siswa.esai_nilai || 0 }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span v-if="siswa.dikoreksi" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                        Sudah Dikoreksi
                                    </span>
                                    <span v-else class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-amber-100 text-amber-800">
                                        Belum Dikoreksi
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <Link :href="koreksiSiswa({ jadwal: selectedJadwalId, siswa: siswa.id }).url" 
                                        class="text-indigo-600 hover:text-indigo-900 bg-indigo-50 hover:bg-indigo-100 px-4 py-2 rounded-lg transition-colors">
                                        Koreksi Essai
                                    </Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <div v-else class="bg-white rounded-xl shadow-sm border border-gray-100 p-12 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">Belum Ada Jadwal Terpilih</h3>
                <p class="mt-1 text-sm text-gray-500">Pilih jadwal ujian terlebih dahulu untuk melihat daftar siswa.</p>
            </div>
        </main>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { dataSiswa, koreksiSiswa } from '@/routes/cbt/koreksi';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    jadwals: Array,
});

const selectedJadwalId = ref('');
const siswaList = ref([]);
const isLoading = ref(false);

const loadSiswaList = async () => {
    if (!selectedJadwalId.value) {
        siswaList.value = [];
        return;
    }
    
    isLoading.value = true;
    try {
        const response = await fetch(dataSiswa({ jadwal: selectedJadwalId.value }).url, {
            headers: { 'Accept': 'application/json' }
        });
        const result = await response.json();
        siswaList.value = result.data;
    } catch (e) {
        console.error("Gagal memuat data siswa", e);
        alert('Gagal memuat daftar siswa.');
    } finally {
        isLoading.value = false;
    }
};
</script>
