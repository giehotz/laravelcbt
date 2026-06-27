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
                            d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"
                        ></path>
                    </svg>
                </div>
                <div>
                    <h1 class="text-xl font-bold text-gray-900">Hasil Ujian</h1>
                    <p class="text-sm text-gray-500">
                        Pantau riwayat pelaksanaan ujian dan perolehan nilai siswa.
                    </p>
                </div>
            </div>
        </header>

        <main
            class="mx-auto flex w-full max-w-7xl flex-1 flex-col gap-6 p-4 sm:p-6 lg:p-8"
        >
            <!-- Data Table -->
            <div
                class="rounded-xl border border-gray-100 bg-white shadow-sm overflow-hidden"
            >
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th
                                    scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                >
                                    No
                                </th>
                                <th
                                    scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                >
                                    Mata Pelajaran (Bank Soal)
                                </th>
                                <th
                                    scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                >
                                    Jenis Ujian
                                </th>
                                <th
                                    scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                >
                                    Waktu Pelaksanaan
                                </th>
                                <th
                                    scope="col"
                                    class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider"
                                >
                                    Aksi
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr
                                v-for="(jadwal, index) in jadwals.data"
                                :key="jadwal.id"
                                class="hover:bg-gray-50 transition-colors"
                            >
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ (Number(jadwals.current_page) - 1) * Number(jadwals.per_page) + Number(index) + 1 }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">
                                        {{ jadwal.bank_soal?.nama || '-' }}
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        Kode: {{ jadwal.bank_soal?.kode || '-' }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        {{ jadwal.jenis?.nama || '-' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ jadwal.tgl_mulai }} s/d {{ jadwal.tgl_selesai }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <Link
                                        :href="hasilUjianShow({ jadwal: jadwal.id }).url"
                                        class="text-indigo-600 hover:text-indigo-900 bg-indigo-50 px-3 py-2 rounded-lg transition-colors hover:bg-indigo-100"
                                    >
                                        Lihat Hasil
                                    </Link>
                                </td>
                            </tr>
                            <tr v-if="jadwals.data.length === 0">
                                <td colspan="5" class="px-6 py-12 text-center text-sm text-gray-500 italic">
                                    Belum ada data jadwal ujian yang tersedia.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination Placeholder -->
                <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                    <div class="flex items-center justify-between">
                        <div class="text-sm text-gray-700">
                            Menampilkan <span class="font-medium">{{ jadwals.from || 0 }}</span> sampai <span class="font-medium">{{ jadwals.to || 0 }}</span> dari <span class="font-medium">{{ jadwals.total }}</span> hasil
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/app/AdminLayout.vue';
import { show as hasilUjianShow } from '@/routes/cbt/hasil-ujian';

defineOptions({ layout: AdminLayout });

defineProps<{
    jadwals: any;
}>();
</script>
