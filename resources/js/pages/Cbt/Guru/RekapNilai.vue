<template>
    <div class="min-h-screen bg-white">
        <!-- Header Print (Hidden in screen if using standard print, but let's just make it visible) -->
        <div class="mx-auto max-w-5xl p-8">
            <div
                class="mb-8 flex items-center justify-between border-b-2 border-black pb-4"
            >
                <div class="w-full text-center">
                    <h1 class="text-2xl font-bold uppercase">
                        Rekapitulasi Nilai Ujian
                    </h1>
                    <h2 class="mt-1 text-xl font-bold">
                        {{ jadwal.bank_soal?.nama }}
                    </h2>
                    <p class="mt-2 text-sm">
                        {{ jadwal.tgl_mulai }} s/d {{ jadwal.tgl_selesai }}
                    </p>
                </div>
            </div>

            <table
                class="min-w-full border-collapse border border-black text-sm"
            >
                <thead>
                    <tr class="bg-gray-100">
                        <th
                            class="w-12 border border-black px-4 py-2 text-center"
                        >
                            No
                        </th>
                        <th class="border border-black px-4 py-2 text-left">
                            NIS
                        </th>
                        <th class="border border-black px-4 py-2 text-left">
                            Nama Siswa
                        </th>
                        <th
                            class="w-24 border border-black px-4 py-2 text-center"
                        >
                            Jml Benar (PG)
                        </th>
                        <th
                            class="w-24 border border-black px-4 py-2 text-center"
                        >
                            Skor PG
                        </th>
                        <th
                            class="w-24 border border-black px-4 py-2 text-center"
                        >
                            Skor Essai
                        </th>
                        <th
                            class="w-28 border border-black bg-gray-200 px-4 py-2 text-center"
                        >
                            Total Nilai
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(nilai, index) in nilais" :key="nilai.id">
                        <td class="border border-black px-4 py-2 text-center">
                            {{ index + 1 }}
                        </td>
                        <td class="border border-black px-4 py-2">
                            {{ nilai.nis }}
                        </td>
                        <td class="border border-black px-4 py-2 font-medium">
                            {{ nilai.nama }}
                        </td>
                        <td class="border border-black px-4 py-2 text-center">
                            {{ nilai.pg_benar }}
                        </td>
                        <td class="border border-black px-4 py-2 text-center">
                            {{ nilai.pg_nilai }}
                        </td>
                        <td class="border border-black px-4 py-2 text-center">
                            {{ nilai.esai_nilai }}
                        </td>
                        <td
                            class="border border-black bg-gray-50 px-4 py-2 text-center font-bold"
                        >
                            {{ nilai.total_nilai }}
                        </td>
                    </tr>
                    <tr v-if="!nilais.length">
                        <td
                            colspan="7"
                            class="border border-black px-4 py-8 text-center text-gray-500 italic"
                        >
                            Belum ada data nilai.
                        </td>
                    </tr>
                </tbody>
            </table>

            <div class="mt-12 flex justify-end">
                <div class="w-64 text-center">
                    <p>................., ..........................</p>
                    <p class="mt-1">Guru Mata Pelajaran</p>
                    <br /><br /><br />
                    <p
                        class="inline-block min-w-[200px] border-b border-black font-bold"
                    ></p>
                    <p class="mt-1">NIP.</p>
                </div>
            </div>

            <!-- Print Button (Hidden when printing) -->
            <div class="fixed right-8 bottom-8 print:hidden">
                <button
                    onclick="window.print()"
                    class="flex items-center gap-2 rounded-full bg-indigo-600 px-6 py-3 font-bold text-white shadow-lg transition-transform hover:scale-105 hover:bg-indigo-700"
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
                            d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"
                        ></path>
                    </svg>
                    Cetak Halaman
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
const props = defineProps({
    nilais: Array,
    jadwal: Object,
});
</script>

<style>
@media print {
    body {
        background-color: white;
    }
    @page {
        margin: 1.5cm;
    }
}
</style>
