<template>
    <div
        class="flex min-h-screen flex-col items-center gap-8 bg-gray-100 p-4 print:bg-white print:p-0"
    >
        <div
            v-for="ruang in ruangSesiList"
            :key="ruang.id"
            class="w-full max-w-5xl break-after-page bg-white p-8 shadow-md print:shadow-none"
        >
            <!-- Kop -->
            <div
                class="mb-6 flex items-center justify-between border-b-[3px] border-black pb-4"
            >
                <div v-if="kop?.logo_kiri" class="h-24 w-24">
                    <img
                        :src="kop.logo_kiri"
                        class="h-full w-full object-contain"
                    />
                </div>
                <div
                    v-else
                    class="flex h-24 w-24 items-center justify-center bg-gray-100 text-xs text-gray-400"
                >
                    Logo
                </div>

                <div class="flex-1 px-4 text-center">
                    <div class="text-lg font-bold">
                        {{ kop?.header_1 || 'PEMERINTAH PROVINSI' }}
                    </div>
                    <div class="text-lg font-bold">
                        {{ kop?.header_2 || 'DINAS PENDIDIKAN' }}
                    </div>
                    <div
                        class="mt-1 text-2xl font-bold tracking-widest uppercase"
                    >
                        {{ kop?.header_3 || 'NAMA SEKOLAH' }}
                    </div>
                    <div class="mt-2 text-base">
                        {{ kop?.header_4 || 'Alamat Sekolah' }}
                    </div>
                </div>

                <div v-if="kop?.logo_kanan" class="h-24 w-24">
                    <img
                        :src="kop.logo_kanan"
                        class="h-full w-full object-contain"
                    />
                </div>
                <div v-else class="h-24 w-24 bg-white"></div>
            </div>

            <div class="mb-8 text-center">
                <h1
                    class="text-xl font-bold tracking-wider uppercase underline"
                >
                    BERITA ACARA PELAKSANAAN UJIAN
                </h1>
            </div>

            <div class="mb-6 text-justify leading-relaxed">
                Pada hari ini
                <span
                    class="inline-block min-w-[150px] border-b border-dotted border-black px-4 font-bold"
                ></span>
                tanggal
                <span
                    class="inline-block min-w-[150px] border-b border-dotted border-black px-4 font-bold"
                ></span>
                bulan
                <span
                    class="inline-block min-w-[150px] border-b border-dotted border-black px-4 font-bold"
                ></span>
                tahun
                <span
                    class="inline-block min-w-[100px] border-b border-dotted border-black px-4 font-bold"
                ></span
                >, telah dilaksanakan ujian untuk mata pelajaran
                <span class="font-bold">{{ jadwal.bank_soal?.nama }}</span
                >.
            </div>

            <div class="mb-6 pl-4">
                <table class="w-full text-base">
                    <tbody>
                        <tr>
                            <td class="w-48 py-2 font-medium">Ruang Ujian</td>
                            <td class="w-4 py-2">:</td>
                            <td class="py-2 font-bold">
                                {{ ruang.nama_ruang }}
                            </td>
                        </tr>
                        <tr>
                            <td class="py-2 font-medium">Sesi</td>
                            <td class="py-2">:</td>
                            <td class="py-2 font-bold">
                                {{ ruang.nama_sesi }}
                            </td>
                        </tr>
                        <tr>
                            <td class="py-2 font-medium">
                                Jumlah Peserta Terdaftar
                            </td>
                            <td class="py-2">:</td>
                            <td class="py-2 font-bold">
                                {{ ruang.peserta }} Orang
                            </td>
                        </tr>
                        <tr>
                            <td class="py-2 font-medium">
                                Jumlah Peserta Hadir
                            </td>
                            <td class="py-2">:</td>
                            <td class="py-2 font-bold">
                                {{ ruang.hadir }} Orang
                            </td>
                        </tr>
                        <tr>
                            <td class="py-2 font-medium">
                                Jumlah Peserta Absen
                            </td>
                            <td class="py-2">:</td>
                            <td class="py-2 font-bold">
                                {{ ruang.absen }} Orang
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="mb-8">
                <div class="mb-2 font-bold">
                    Catatan Selama Pelaksanaan Ujian:
                </div>
                <div
                    class="min-h-[150px] border border-black p-4 text-gray-400 italic"
                >
                    (Tuliskan jika ada kendala jaringan, mati listrik,
                    kecurangan, atau hal lain yang terjadi)
                </div>
            </div>

            <div class="mb-12 text-justify leading-relaxed">
                Demikian berita acara ini dibuat dengan sesungguhnya untuk dapat
                dipergunakan sebagaimana mestinya.
            </div>

            <div class="mt-12 flex justify-between px-8">
                <div class="w-64 text-center">
                    <p>Proktor / Teknisi</p>
                    <br /><br /><br /><br />
                    <p class="w-full border-b border-black font-bold"></p>
                    <p class="mt-1 text-sm">NIP.</p>
                </div>
                <div class="w-64 text-center">
                    <p>Pengawas Ruang</p>
                    <br /><br /><br /><br />
                    <p class="w-full border-b border-black font-bold"></p>
                    <p class="mt-1 text-sm">NIP.</p>
                </div>
            </div>
        </div>

        <div
            v-if="!ruangSesiList.length"
            class="w-full max-w-5xl border-2 border-dashed border-gray-300 bg-white p-12 text-center font-bold text-gray-500"
        >
            Belum ada data pengaturan ruang/sesi untuk jadwal ini.
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
                Cetak Berita Acara
            </button>
        </div>
    </div>
</template>

<script setup>
const props = defineProps({
    ruangSesiList: Array,
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
