<template>
    <div class="min-h-screen bg-white">
        <div class="mx-auto max-w-5xl p-8">
            <!-- Kop -->
            <div
                class="mb-6 flex items-center justify-between border-b-[3px] border-black pb-4"
            >
                <div v-if="kop?.logo_kiri" class="h-20 w-20">
                    <img
                        :src="kop.logo_kiri"
                        class="h-full w-full object-contain"
                    />
                </div>
                <div
                    v-else
                    class="flex h-20 w-20 items-center justify-center bg-gray-100 text-xs text-gray-400"
                >
                    Logo
                </div>

                <div class="flex-1 px-4 text-center">
                    <div class="text-base font-bold">
                        {{ kop?.header_1 || 'PEMERINTAH PROVINSI' }}
                    </div>
                    <div class="text-base font-bold">
                        {{ kop?.header_2 || 'DINAS PENDIDIKAN' }}
                    </div>
                    <div
                        class="mt-1 text-xl font-bold tracking-wider uppercase"
                    >
                        {{ kop?.header_3 || 'NAMA SEKOLAH' }}
                    </div>
                    <div class="mt-1 text-sm">
                        {{ kop?.header_4 || 'Alamat Sekolah' }}
                    </div>
                </div>

                <div v-if="kop?.logo_kanan" class="h-20 w-20">
                    <img
                        :src="kop.logo_kanan"
                        class="h-full w-full object-contain"
                    />
                </div>
                <div v-else class="h-20 w-20 bg-white"></div>
            </div>

            <div class="mb-8 text-center">
                <h1 class="text-xl font-bold uppercase underline">
                    DAFTAR HADIR PESERTA UJIAN
                </h1>
                <h2 class="mt-1 text-lg font-bold">
                    {{ jadwal.bank_soal?.nama }}
                </h2>
            </div>

            <div class="mb-4 flex justify-between text-sm font-bold">
                <div>
                    <div>Tanggal Ujian : {{ jadwal.tgl_mulai }}</div>
                    <div>
                        Waktu Ujian :
                        {{ jadwal.tgl_mulai.split(' ')[1] || '-' }} s/d
                        {{ jadwal.tgl_selesai.split(' ')[1] || '-' }}
                    </div>
                </div>
                <div class="text-right">
                    <div>Ruang / Sesi : Terlampir di tabel</div>
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
                        <th
                            class="w-32 border border-black px-4 py-2 text-left"
                        >
                            Nomor Peserta
                        </th>
                        <th class="border border-black px-4 py-2 text-left">
                            Nama Siswa
                        </th>
                        <th
                            class="w-24 border border-black px-4 py-2 text-center"
                        >
                            Ruang
                        </th>
                        <th
                            class="w-24 border border-black px-4 py-2 text-center"
                        >
                            Sesi
                        </th>
                        <th
                            class="w-48 border border-black px-4 py-2 text-left"
                            colspan="2"
                        >
                            Tanda Tangan
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(siswa, index) in siswas" :key="siswa.id">
                        <td class="border border-black px-4 py-3 text-center">
                            {{ index + 1 }}
                        </td>
                        <td class="border border-black px-4 py-3 font-medium">
                            {{ siswa.nomor_peserta || '-' }}
                        </td>
                        <td class="border border-black px-4 py-3 font-medium">
                            {{ siswa.nama }}
                        </td>
                        <td class="border border-black px-4 py-3 text-center">
                            {{ siswa.nama_ruang }}
                        </td>
                        <td class="border border-black px-4 py-3 text-center">
                            {{ siswa.nama_sesi }}
                        </td>
                        <td
                            class="w-24 border border-r-0 border-black px-2 py-3"
                        >
                            <span v-if="index % 2 === 0" class="text-xs"
                                >{{ index + 1 }}. ............</span
                            >
                        </td>
                        <td
                            class="w-24 border border-l-0 border-black px-2 py-3 text-right"
                        >
                            <span v-if="index % 2 !== 0" class="text-xs"
                                >{{ index + 1 }}. ............</span
                            >
                        </td>
                    </tr>
                    <tr v-if="!siswas.length">
                        <td
                            colspan="7"
                            class="border border-black px-4 py-8 text-center text-gray-500 italic"
                        >
                            Belum ada data siswa.
                        </td>
                    </tr>
                </tbody>
            </table>

            <div class="mt-12 flex justify-between">
                <div class="w-64 text-center">
                    <p>Pengawas 1</p>
                    <br /><br /><br />
                    <p class="w-full border-b border-black font-bold"></p>
                    <p class="mt-1 text-xs">NIP.</p>
                </div>
                <div class="w-64 text-center">
                    <p>Pengawas 2</p>
                    <br /><br /><br />
                    <p class="w-full border-b border-black font-bold"></p>
                    <p class="mt-1 text-xs">NIP.</p>
                </div>
            </div>

            <!-- Print Button -->
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
                    Cetak Daftar Hadir
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
const props = defineProps({
    siswas: Array,
    kop: Object,
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
