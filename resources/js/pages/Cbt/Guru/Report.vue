<template>
    <div class="flex min-h-screen flex-col bg-gray-50">
        <!-- Header -->
        <header
            class="border-b border-gray-200 bg-white px-4 py-4 sm:px-6 lg:px-8"
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
                            d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"
                        ></path>
                    </svg>
                </div>
                <div>
                    <h1 class="text-xl font-bold text-gray-900">
                        Cetak Laporan & Dokumen Ujian
                    </h1>
                    <p class="text-sm text-gray-500">
                        Generate PDF / Print Kartu, Absensi, Berita Acara, dan
                        Rekap Nilai.
                    </p>
                </div>
            </div>
        </header>

        <main
            class="mx-auto flex w-full max-w-7xl flex-1 flex-col gap-6 p-4 sm:p-6 lg:p-8"
        >
            <div
                class="rounded-xl border border-gray-100 bg-white p-6 shadow-sm"
            >
                <div class="max-w-xl">
                    <label class="mb-2 block text-sm font-medium text-gray-700"
                        >Pilih Jadwal Ujian</label
                    >
                    <select
                        v-model="selectedJadwalId"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
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

            <div
                v-if="selectedJadwalId"
                class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4"
            >
                <!-- Cetak Kartu -->
                <a
                    :href="cetakKartu({ jadwal_id: selectedJadwalId }).url"
                    target="_blank"
                    class="group flex flex-col items-center rounded-xl border border-gray-200 bg-white p-6 text-center shadow-sm transition-all hover:border-indigo-500 hover:shadow-md"
                >
                    <div
                        class="mb-4 rounded-full bg-indigo-50 p-4 transition-colors group-hover:bg-indigo-100"
                    >
                        <svg
                            class="h-8 w-8 text-indigo-600"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"
                            ></path>
                        </svg>
                    </div>
                    <h3
                        class="font-bold text-gray-900 transition-colors group-hover:text-indigo-600"
                    >
                        Kartu Peserta
                    </h3>
                    <p class="mt-2 text-sm text-gray-500">
                        Cetak kartu ujian untuk siswa yang terdaftar di jadwal
                        ini.
                    </p>
                </a>

                <!-- Cetak Daftar Hadir -->
                <a
                    :href="
                        cetakDaftarHadir({ jadwal_id: selectedJadwalId }).url
                    "
                    target="_blank"
                    class="group flex flex-col items-center rounded-xl border border-gray-200 bg-white p-6 text-center shadow-sm transition-all hover:border-blue-500 hover:shadow-md"
                >
                    <div
                        class="mb-4 rounded-full bg-blue-50 p-4 transition-colors group-hover:bg-blue-100"
                    >
                        <svg
                            class="h-8 w-8 text-blue-600"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"
                            ></path>
                        </svg>
                    </div>
                    <h3
                        class="font-bold text-gray-900 transition-colors group-hover:text-blue-600"
                    >
                        Daftar Hadir
                    </h3>
                    <p class="mt-2 text-sm text-gray-500">
                        Cetak absensi siswa per ruang dan sesi.
                    </p>
                </a>

                <!-- Cetak Berita Acara -->
                <a
                    :href="
                        cetakBeritaAcara({ jadwal_id: selectedJadwalId }).url
                    "
                    target="_blank"
                    class="group flex flex-col items-center rounded-xl border border-gray-200 bg-white p-6 text-center shadow-sm transition-all hover:border-amber-500 hover:shadow-md"
                >
                    <div
                        class="mb-4 rounded-full bg-amber-50 p-4 transition-colors group-hover:bg-amber-100"
                    >
                        <svg
                            class="h-8 w-8 text-amber-600"
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
                    <h3
                        class="font-bold text-gray-900 transition-colors group-hover:text-amber-600"
                    >
                        Berita Acara
                    </h3>
                    <p class="mt-2 text-sm text-gray-500">
                        Dokumen berita acara pelaksanaan ujian untuk pengawas.
                    </p>
                </a>

                <!-- Rekap Nilai -->
                <a
                    :href="rekapNilai({ jadwal_id: selectedJadwalId }).url"
                    target="_blank"
                    class="group flex flex-col items-center rounded-xl border border-gray-200 bg-white p-6 text-center shadow-sm transition-all hover:border-emerald-500 hover:shadow-md"
                >
                    <div
                        class="mb-4 rounded-full bg-emerald-50 p-4 transition-colors group-hover:bg-emerald-100"
                    >
                        <svg
                            class="h-8 w-8 text-emerald-600"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"
                            ></path>
                        </svg>
                    </div>
                    <h3
                        class="font-bold text-gray-900 transition-colors group-hover:text-emerald-600"
                    >
                        Rekap Nilai
                    </h3>
                    <p class="mt-2 text-sm text-gray-500">
                        Daftar hasil nilai akhir seluruh siswa untuk jadwal ini.
                    </p>
                </a>
            </div>

            <div
                v-else
                class="mt-6 rounded-xl border border-gray-100 bg-white p-12 text-center shadow-sm"
            >
                <svg
                    class="mx-auto h-12 w-12 text-gray-400"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"
                    ></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">
                    Pilih Jadwal
                </h3>
                <p class="mt-1 text-sm text-gray-500">
                    Silakan pilih jadwal terlebih dahulu untuk memunculkan menu
                    cetak.
                </p>
            </div>
        </main>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import {
    cetakKartu,
    cetakDaftarHadir,
    cetakBeritaAcara,
    rekapNilai,
} from '@/routes/cbt/report';

const props = defineProps({
    jadwals: Array,
});

const selectedJadwalId = ref('');
</script>
