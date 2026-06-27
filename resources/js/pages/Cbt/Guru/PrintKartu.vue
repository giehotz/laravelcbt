<template>
    <div class="min-h-screen bg-gray-100 p-4 print:bg-white print:p-0 flex justify-center font-sans">
        <div class="w-full max-w-[210mm] bg-white shadow-md print:shadow-none print:max-w-none">
            <!-- 2 columns grid for A4 paper -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 p-4 print:grid-cols-2 print:p-0 print:gap-3">
                <div
                    v-for="(siswa, index) in siswas"
                    :key="siswa.id"
                    class="break-inside-avoid border-[1.5px] border-black p-3 relative bg-white"
                >
                    <!-- Header Kartu -->
                    <div class="flex items-center justify-between border-b-[1.5px] border-black pb-2 mb-2">
                        <!-- Logo Kiri -->
                        <div class="h-12 w-12 sm:h-14 sm:w-14 flex-shrink-0 flex items-center justify-center">
                            <img v-if="kop?.logo_kiri" :src="kop.logo_kiri" class="h-full w-full object-contain" />
                            <div v-else class="h-10 w-10 bg-gray-100 flex items-center justify-center text-[10px] text-gray-400 border border-gray-200">Logo</div>
                        </div>

                        <!-- Header Text -->
                        <div class="flex-1 px-1 text-center flex flex-col justify-center leading-snug">
                            <div class="text-xs sm:text-[13px] font-bold" v-html="kop?.header_1 || '<b>KARTU PESERTA</b>'"></div>
                            <div class="text-[11px] sm:text-xs font-bold uppercase" v-html="kop?.header_2 || 'ASESMEN AKHIR SEMESTER GENAP'"></div>
                            <div class="text-[11px] sm:text-xs font-bold uppercase" v-html="kop?.header_3 || 'MIN 2 TANGGAMUS'"></div>
                            <div class="text-[9px] sm:text-[10px]" v-html="kop?.header_4 || 'TAHUN PELAJARAN 2025/2026'"></div>
                        </div>

                        <!-- Logo Kanan -->
                        <div class="h-12 w-12 sm:h-14 sm:w-14 flex-shrink-0 flex items-center justify-center">
                            <img v-if="kop?.logo_kanan" :src="kop.logo_kanan" class="h-full w-full object-contain" />
                            <div v-else class="h-10 w-10 bg-gray-100 flex items-center justify-center text-[10px] text-gray-400 border border-gray-200">Logo</div>
                        </div>
                    </div>

                    <!-- Body Kartu -->
                    <div class="flex gap-3">
                        <!-- Foto -->
                        <div class="flex flex-col gap-1 w-[2.2cm] sm:w-[2.5cm]">
                            <div class="h-[2.9cm] sm:h-[3.33cm] w-full border border-gray-400 bg-gray-50 flex items-center justify-center text-[10px] sm:text-xs text-gray-400 overflow-hidden shrink-0">
                                <img v-if="siswa.foto" :src="siswa.foto" class="h-full w-full object-cover" />
                                <span v-else>Foto 3x4</span>
                            </div>
                        </div>

                        <!-- Data -->
                        <div class="flex-1 flex flex-col justify-between">
                            <table class="w-full text-[10px] sm:text-[11px] font-medium leading-tight">
                                <tbody>
                                    <tr>
                                        <td class="w-[75px] sm:w-[85px] py-[1.5px] align-top">Nomor Peserta</td>
                                        <td class="w-2 py-[1.5px] align-top">:</td>
                                        <td class="py-[1.5px] align-top font-bold">{{ siswa.nomor_peserta || '-' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="py-[1.5px] align-top">Nama</td>
                                        <td class="py-[1.5px] align-top">:</td>
                                        <td class="py-[1.5px] align-top font-bold line-clamp-2 leading-tight">{{ siswa.nama }}</td>
                                    </tr>
                                    <tr>
                                        <td class="py-[1.5px] align-top">NIS-NISN</td>
                                        <td class="py-[1.5px] align-top">:</td>
                                        <td class="py-[1.5px] align-top">{{ siswa.nis || '-' }} - {{ siswa.nisn || '-' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="py-[1.5px] align-top">Kelas</td>
                                        <td class="py-[1.5px] align-top">:</td>
                                        <td class="py-[1.5px] align-top">{{ siswa.nama_kelas || '-' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="py-[1.5px] align-top">Ruang/Sesi</td>
                                        <td class="py-[1.5px] align-top">:</td>
                                        <td class="py-[1.5px] align-top">{{ siswa.nama_ruang || '-' }} / {{ siswa.nama_sesi || '-' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="py-[1.5px] align-top pt-1.5">Username</td>
                                        <td class="py-[1.5px] align-top pt-1.5">:</td>
                                        <td class="py-[1.5px] align-top pt-1.5 font-bold">{{ siswa.username || '-' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="py-[1.5px] align-top">Password</td>
                                        <td class="py-[1.5px] align-top">:</td>
                                        <td class="py-[1.5px] align-top font-bold">{{ siswa.password_cbt || '******' }}</td>
                                    </tr>
                                </tbody>
                            </table>

                            <!-- TTD -->
                            <div class="flex justify-end mt-1">
                                <div class="text-[9px] sm:text-[10px] text-center leading-tight">
                                    <p>{{ kop?.tanggal || 'Tanggamus, 01 Juni 2026' }}</p>
                                    <p>Kepala Madrasah</p>
                                    <div class="h-8 sm:h-10 relative">
                                        <!-- Placeholder for signature or stamp if any -->
                                    </div>
                                    <p class="font-bold underline">Kepala Sekolah</p>
                                    <p>NIP. -</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div
                    v-if="!siswas.length"
                    class="col-span-1 sm:col-span-2 border-2 border-dashed border-gray-300 p-12 text-center font-bold text-gray-500"
                >
                    Belum ada data siswa untuk jadwal ini.
                </div>
            </div>

            <!-- Print Button -->
            <div class="fixed right-8 bottom-8 print:hidden z-50">
                <button
                    onclick="window.print()"
                    class="flex items-center gap-2 rounded-full bg-indigo-600 px-6 py-3 font-bold text-white shadow-lg transition-transform hover:scale-105 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
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
    siswas: {
        type: Array,
        default: () => [],
    },
    kop: {
        type: Object,
        default: () => ({}),
    },
    jadwal: {
        type: Object,
        default: () => ({}),
    },
});
</script>

<style>
@media print {
    @page {
        size: A4;
        margin: 1cm;
    }
    body {
        background-color: white;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }
}
</style>
