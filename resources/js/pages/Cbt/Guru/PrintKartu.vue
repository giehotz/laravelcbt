<template>
    <div
        class="flex min-h-screen justify-center bg-gray-100 p-4 print:bg-white print:p-0"
    >
        <div class="w-full max-w-5xl bg-white shadow-md print:shadow-none">
            <div
                class="grid grid-cols-1 gap-4 p-4 md:grid-cols-2 print:grid-cols-2 print:p-0"
            >
                <div
                    v-for="(siswa, index) in siswas"
                    :key="siswa.id"
                    class="break-inside-avoid border-2 border-black p-4 print:mb-4"
                >
                    <!-- Kop -->
                    <div
                        class="mb-4 flex items-center justify-between border-b-2 border-black pb-3"
                    >
                        <div v-if="kop?.logo_kiri" class="h-16 w-16">
                            <img
                                :src="kop.logo_kiri"
                                class="h-full w-full object-contain"
                            />
                        </div>
                        <div
                            v-else
                            class="flex h-16 w-16 items-center justify-center bg-gray-100 text-xs text-gray-400"
                        >
                            Logo
                        </div>

                        <div class="flex-1 px-2 text-center">
                            <div class="text-sm font-bold">
                                {{ kop?.header_1 || 'PEMERINTAH PROVINSI' }}
                            </div>
                            <div class="text-sm font-bold">
                                {{ kop?.header_2 || 'DINAS PENDIDIKAN' }}
                            </div>
                            <div class="text-base font-bold uppercase">
                                {{ kop?.header_3 || 'NAMA SEKOLAH' }}
                            </div>
                            <div class="text-xs">
                                {{ kop?.header_4 || 'Alamat Sekolah' }}
                            </div>
                        </div>

                        <div v-if="kop?.logo_kanan" class="h-16 w-16">
                            <img
                                :src="kop.logo_kanan"
                                class="h-full w-full object-contain"
                            />
                        </div>
                        <div v-else class="h-16 w-16 bg-white"></div>
                    </div>

                    <div
                        class="mb-4 text-center text-lg font-bold uppercase underline"
                    >
                        KARTU PESERTA UJIAN
                    </div>

                    <div class="flex gap-4">
                        <!-- Foto (Placeholder) -->
                        <div
                            class="flex h-32 w-24 items-center justify-center border-2 border-gray-300 bg-gray-50 text-sm text-gray-400"
                        >
                            Foto 3x4
                        </div>

                        <!-- Data Siswa -->
                        <div class="flex-1">
                            <table class="w-full text-sm font-medium">
                                <tbody>
                                    <tr>
                                        <td class="w-32 py-1">Nomor Peserta</td>
                                        <td class="w-2 py-1">:</td>
                                        <td class="py-1 font-bold">
                                            {{ siswa.nomor_peserta || '-' }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="py-1">Nama Siswa</td>
                                        <td class="py-1">:</td>
                                        <td class="py-1 font-bold">
                                            {{ siswa.nama }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="py-1">NIS / NISN</td>
                                        <td class="py-1">:</td>
                                        <td class="py-1">
                                            {{ siswa.nis || '-' }} /
                                            {{ siswa.nisn || '-' }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="py-1">Password</td>
                                        <td class="py-1">:</td>
                                        <td
                                            class="py-1 font-bold tracking-wider text-red-600"
                                        >
                                            {{ siswa.password_cbt || '******' }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="py-1">Jadwal Ujian</td>
                                        <td class="py-1">:</td>
                                        <td class="py-1">
                                            {{ jadwal.bank_soal?.nama }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="mt-4 flex items-end justify-between text-sm">
                        <div>
                            <div
                                class="inline-block border border-black px-4 py-2 font-bold"
                            >
                                RUANG: {{ siswa.ruang_nama || '...' }}
                            </div>
                        </div>
                        <div class="w-40 text-center">
                            <p>Kepala Sekolah,</p>
                            <br /><br /><br />
                            <p
                                class="w-full border-b border-black font-bold"
                            ></p>
                            <p class="mt-1 text-xs">NIP.</p>
                        </div>
                    </div>
                </div>

                <div
                    v-if="!siswas.length"
                    class="col-span-2 border-2 border-dashed border-gray-300 p-12 text-center font-bold text-gray-500"
                >
                    Belum ada data siswa untuk jadwal ini.
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
                    Cetak Kartu
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
        margin: 0.5cm;
    }
}
</style>
