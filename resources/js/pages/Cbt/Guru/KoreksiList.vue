<template>
    <div class="flex min-h-screen flex-col bg-gray-50 dark:bg-zinc-950 text-foreground">
        <!-- Header -->
        <header
            class="border-b border-gray-200 bg-white px-4 py-4 sm:px-6 lg:px-8 dark:border-zinc-800 dark:bg-zinc-900"
        >
            <div class="flex items-center gap-4">
                <div class="rounded-lg bg-indigo-600 p-2">
                    <svg
                        class="h-6 w-6 text-white"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"
                        ></path>
                    </svg>
                </div>
                <div>
                    <h1 class="text-xl font-bold text-gray-900 dark:text-zinc-100">
                        Koreksi Jawaban Essai
                    </h1>
                    <p class="text-sm text-gray-500 dark:text-zinc-400">
                        Pilih jadwal ujian dan siswa untuk memberikan nilai pada
                        soal essai.
                    </p>
                </div>
            </div>
        </header>

        <main
            class="mx-auto flex w-full max-w-7xl flex-1 flex-col gap-6 p-4 sm:p-6 lg:p-8"
        >
            <!-- Filter Jadwal -->
            <div
                class="rounded-xl border border-gray-100 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900"
            >
                <div class="max-w-md">
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-zinc-300"
                        >Pilih Jadwal Ujian (Yang memiliki essai)</label
                    >
                    <select
                        v-model="selectedJadwalId"
                        @change="loadSiswaList"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                    >
                        <option value="">-- Pilih Jadwal --</option>
                        <option
                            v-for="jadwal in jadwals"
                            :key="jadwal.id"
                            :value="jadwal.id"
                        >
                            {{ jadwal.bank_soal?.nama }} ({{
                                jadwal.tgl_mulai
                            }})
                        </option>
                    </select>
                </div>
            </div>

            <!-- Tabel Siswa -->
            <div
                v-if="selectedJadwalId"
                class="overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900"
            >
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-zinc-800">
                        <thead class="bg-gray-50 dark:bg-zinc-800/50">
                            <tr>
                                <th
                                    scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-zinc-400"
                                >
                                    No
                                </th>
                                <th
                                    scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-zinc-400"
                                >
                                    Siswa
                                </th>
                                <th
                                    scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-zinc-400"
                                >
                                    Nilai PG
                                </th>
                                <th
                                    scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-zinc-400"
                                >
                                    Nilai Essai
                                </th>
                                <th
                                    scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-zinc-400"
                                >
                                    Status Koreksi
                                </th>
                                <th
                                    scope="col"
                                    class="px-6 py-3 text-right text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-zinc-400"
                                >
                                    Aksi
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white dark:divide-zinc-800 dark:bg-zinc-900">
                            <tr v-if="isLoading" class="animate-pulse">
                                <td
                                    colspan="6"
                                    class="px-6 py-12 text-center text-gray-500 dark:text-zinc-400"
                                >
                                    Memuat data siswa...
                                </td>
                            </tr>
                            <tr v-else-if="!siswaList.length">
                                <td
                                    colspan="6"
                                    class="px-6 py-12 text-center text-gray-500 dark:text-zinc-400"
                                >
                                    Belum ada siswa yang menyelesaikan ujian
                                    ini.
                                </td>
                            </tr>
                            <tr
                                v-for="(siswa, index) in siswaList"
                                :key="siswa.id"
                                class="hover:bg-gray-50 dark:hover:bg-zinc-800/30"
                            >
                                <td
                                    class="px-6 py-4 text-sm whitespace-nowrap text-gray-500 dark:text-zinc-400"
                                >
                                    {{ index + 1 }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="font-medium text-gray-900 dark:text-zinc-100">
                                        {{ siswa.nama }}
                                    </div>
                                    <div class="text-sm text-gray-500 dark:text-zinc-400">
                                        {{ siswa.nis }}
                                    </div>
                                </td>
                                <td
                                    class="px-6 py-4 text-sm font-bold whitespace-nowrap text-gray-900 dark:text-zinc-100"
                                >
                                    {{ siswa.pg_nilai || 0 }}
                                </td>
                                <td
                                    class="px-6 py-4 text-sm font-bold whitespace-nowrap text-gray-900 dark:text-zinc-100"
                                >
                                    {{ siswa.esai_nilai || 0 }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        v-if="siswa.dikoreksi"
                                        class="inline-flex rounded-full bg-green-100 px-2 text-xs leading-5 font-semibold text-green-800 dark:bg-emerald-950/40 dark:text-emerald-400"
                                    >
                                        Sudah Dikoreksi
                                    </span>
                                    <span
                                        v-else
                                        class="inline-flex rounded-full bg-amber-100 px-2 text-xs leading-5 font-semibold text-amber-800 dark:bg-amber-950/40 dark:text-amber-400"
                                    >
                                        Belum Dikoreksi
                                    </span>
                                </td>
                                <td
                                    class="px-6 py-4 text-right text-sm font-medium whitespace-nowrap"
                                >
                                    <Link
                                        :href="
                                            koreksiSiswa({
                                                jadwal: selectedJadwalId,
                                                siswa: siswa.id,
                                            }).url
                                        "
                                        class="rounded-lg bg-indigo-50 px-4 py-2 text-indigo-600 transition-colors hover:bg-indigo-100 hover:text-indigo-900 dark:bg-indigo-950/40 dark:text-indigo-400 dark:hover:bg-indigo-900/60 dark:hover:text-indigo-200"
                                    >
                                        Koreksi Essai
                                    </Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div
                v-else
                class="rounded-xl border border-gray-100 bg-white p-12 text-center shadow-sm dark:border-zinc-800 dark:bg-zinc-900"
            >
                <svg
                    class="mx-auto h-12 w-12 text-gray-400 dark:text-zinc-500"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"
                    />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-zinc-200">
                    Belum Ada Jadwal Terpilih
                </h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-zinc-400">
                    Pilih jadwal ujian terlebih dahulu untuk melihat daftar
                    siswa.
                </p>
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
        const response = await fetch(
            dataSiswa({ jadwal: selectedJadwalId.value }).url,
            {
                headers: { Accept: 'application/json' },
            },
        );
        const result = await response.json();
        siswaList.value = result.data;
    } catch (e) {
        console.error('Gagal memuat data siswa', e);
        alert('Gagal memuat daftar siswa.');
    } finally {
        isLoading.value = false;
    }
};
</script>
