<template>
    <div class="min-h-screen bg-gray-50 flex flex-col">
        <!-- Header Monitoring -->
        <header class="bg-white border-b border-gray-200 px-4 sm:px-6 lg:px-8 py-4">
            <div class="flex items-center gap-4">
                <div class="bg-indigo-600 p-2 rounded-lg">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                </div>
                <div>
                    <h1 class="text-xl font-bold text-gray-900">Proctor Monitoring</h1>
                    <p class="text-sm text-gray-500">Pantau aktivitas ujian siswa secara real-time</p>
                </div>
            </div>
        </header>

        <main class="flex-1 max-w-7xl w-full mx-auto p-4 sm:p-6 lg:p-8 flex flex-col gap-6">
            <!-- Filter Jadwal -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex items-center justify-between">
                <div class="flex-1 max-w-sm">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Pilih Jadwal Ujian Aktif</label>
                    <select v-model="selectedJadwalId" @change="loadStatusSiswa" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        <option value="">-- Pilih Jadwal --</option>
                        <option v-for="jadwal in jadwalAktif" :key="jadwal.id" :value="jadwal.id">
                            {{ jadwal.bank_soal?.mapel?.nama_mapel }} - {{ jadwal.bank_soal?.nama }} ({{ jadwal.tgl_mulai }})
                        </option>
                    </select>
                </div>
                
                <div class="flex items-center gap-4" v-if="selectedJadwalId">
                    <div class="text-sm text-gray-500">
                        <span class="inline-block w-2 h-2 rounded-full mr-2" :class="isRefreshing ? 'bg-amber-500' : 'bg-green-500'"></span>
                        {{ isRefreshing ? 'Menyegarkan...' : 'Real-time On' }}
                    </div>
                    <button @click="loadStatusSiswa" class="bg-indigo-50 text-indigo-700 hover:bg-indigo-100 px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                        Refresh Manual
                    </button>
                </div>
            </div>

            <!-- Tabel Status Siswa -->
            <div v-if="selectedJadwalId" class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Siswa</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mulai - Selesai</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Progress</th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-if="isLoading && !siswaList.length">
                                <td colspan="6" class="px-6 py-12 text-center text-gray-500">Memuat data...</td>
                            </tr>
                            <tr v-else-if="!siswaList.length">
                                <td colspan="6" class="px-6 py-12 text-center text-gray-500">Belum ada siswa yang login ke jadwal ini.</td>
                            </tr>
                            <tr v-for="(siswa, index) in siswaList" :key="siswa.id" class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ index + 1 }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="font-medium text-gray-900">{{ siswa.nama }}</div>
                                    <div class="text-sm text-gray-500">{{ siswa.nis }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span v-if="siswa.status === 0" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                        Belum Mulai
                                    </span>
                                    <span v-else-if="siswa.status === 1" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                        Sedang Ujian
                                    </span>
                                    <span v-else-if="siswa.status === 2" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                        Selesai
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <div v-if="siswa.status > 0">
                                        <div>{{ formatTime(siswa.mulai) }}</div>
                                        <div v-if="siswa.status === 2" class="text-green-600">{{ formatTime(siswa.selesai) }}</div>
                                        <div v-else class="text-blue-500">Sedang berjalan...</div>
                                    </div>
                                    <div v-else>-</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-3">
                                        <div class="w-full bg-gray-200 rounded-full h-2.5 max-w-[100px]">
                                            <div class="bg-blue-600 h-2.5 rounded-full" :style="{ width: calculateProgress(siswa) + '%' }"></div>
                                        </div>
                                        <span class="text-sm text-gray-600 font-medium">{{ siswa.dijawab }}/{{ siswa.total_soal }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex items-center justify-end gap-2">
                                        <!-- Reset Login -->
                                        <button v-if="siswa.status > 0" @click="resetLogin(siswa.id, siswa.nama)" 
                                            class="text-amber-600 hover:text-amber-900 bg-amber-50 hover:bg-amber-100 px-3 py-1.5 rounded-lg transition-colors"
                                            title="Reset Login (Siswa akan keluar dan harus login ulang)">
                                            Reset Login
                                        </button>
                                        
                                        <!-- Force Selesai -->
                                        <button v-if="siswa.status === 1" @click="forceSelesai(siswa.id, siswa.nama)" 
                                            class="text-red-600 hover:text-red-900 bg-red-50 hover:bg-red-100 px-3 py-1.5 rounded-lg transition-colors"
                                            title="Paksa Selesai Ujian">
                                            Paksa Selesai
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <div v-else class="bg-white rounded-xl shadow-sm border border-gray-100 p-12 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">Belum Ada Jadwal Terpilih</h3>
                <p class="mt-1 text-sm text-gray-500">Silakan pilih jadwal dari dropdown di atas untuk memulai monitoring.</p>
            </div>
        </main>
    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { status as statusSiswa, reset as resetLoginSiswa, forceSelesai as forceSelesaiUjian } from '@/routes/cbt/monitoring';

const props = defineProps({
    jadwalAktif: Array,
});

const selectedJadwalId = ref('');
const siswaList = ref([]);
const isLoading = ref(false);
const isRefreshing = ref(false);
let refreshInterval = null;

const formatTime = (timeStr) => {
    if (!timeStr) return '-';
    // Remove seconds for cleaner display if it's full time string
    return timeStr.substring(0, 5);
};

const calculateProgress = (siswa) => {
    if (siswa.total_soal === 0) return 0;
    return (siswa.dijawab / siswa.total_soal) * 100;
};

const loadStatusSiswa = async () => {
    if (!selectedJadwalId.value) {
        siswaList.value = [];
        return;
    }
    
    if (siswaList.value.length === 0) isLoading.value = true;
    else isRefreshing.value = true;

    try {
        const response = await fetch(statusSiswa({ jadwal: selectedJadwalId.value }).url, {
            headers: { 'Accept': 'application/json' }
        });
        const result = await response.json();
        siswaList.value = result.data;
    } catch (e) {
        console.error("Gagal memuat status siswa", e);
    } finally {
        isLoading.value = false;
        setTimeout(() => { isRefreshing.value = false; }, 500); // Visual delay for refresh indicator
    }
};

const resetLogin = async (durasiId, namaSiswa) => {
    if (!confirm(`Yakin ingin mereset login untuk ${namaSiswa}? Jawaban tersimpan tidak akan hilang, namun siswa harus login ulang.`)) {
        return;
    }

    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const response = await fetch(resetLoginSiswa({ durasi: durasiId }).url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            }
        });
        
        if (response.ok) {
            loadStatusSiswa(); // Refresh table
        } else {
            alert('Gagal mereset login.');
        }
    } catch (e) {
        alert('Terjadi kesalahan jaringan.');
    }
};

const forceSelesai = async (durasiId, namaSiswa) => {
    if (!confirm(`PERINGATAN: Ujian ${namaSiswa} akan diselesaikan secara paksa dan nilainya akan dihitung sekarang. Lanjutkan?`)) {
        return;
    }

    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const response = await fetch(forceSelesaiUjian({ durasi: durasiId }).url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            }
        });
        
        if (response.ok) {
            loadStatusSiswa(); // Refresh table
        } else {
            alert('Gagal menyelesaikan ujian secara paksa.');
        }
    } catch (e) {
        alert('Terjadi kesalahan jaringan.');
    }
};

onMounted(() => {
    // Auto refresh every 10 seconds if a jadwal is selected
    refreshInterval = setInterval(() => {
        if (selectedJadwalId.value) {
            loadStatusSiswa();
        }
    }, 10000);
});

onUnmounted(() => {
    if (refreshInterval) clearInterval(refreshInterval);
});
</script>
