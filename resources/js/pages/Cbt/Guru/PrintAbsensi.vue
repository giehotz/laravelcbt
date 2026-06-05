<template>
    <div class="min-h-screen bg-white">
        <div class="p-8 max-w-5xl mx-auto">
            
            <!-- Kop -->
            <div class="flex items-center justify-between border-b-[3px] border-black pb-4 mb-6">
                <div v-if="kop?.logo_kiri" class="w-20 h-20">
                    <img :src="kop.logo_kiri" class="w-full h-full object-contain">
                </div>
                <div v-else class="w-20 h-20 bg-gray-100 flex items-center justify-center text-xs text-gray-400">Logo</div>
                
                <div class="text-center flex-1 px-4">
                    <div class="font-bold text-base">{{ kop?.header_1 || 'PEMERINTAH PROVINSI' }}</div>
                    <div class="font-bold text-base">{{ kop?.header_2 || 'DINAS PENDIDIKAN' }}</div>
                    <div class="font-bold text-xl uppercase tracking-wider mt-1">{{ kop?.header_3 || 'NAMA SEKOLAH' }}</div>
                    <div class="text-sm mt-1">{{ kop?.header_4 || 'Alamat Sekolah' }}</div>
                </div>

                <div v-if="kop?.logo_kanan" class="w-20 h-20">
                    <img :src="kop.logo_kanan" class="w-full h-full object-contain">
                </div>
                <div v-else class="w-20 h-20 bg-white"></div>
            </div>

            <div class="text-center mb-8">
                <h1 class="text-xl font-bold uppercase underline">DAFTAR HADIR PESERTA UJIAN</h1>
                <h2 class="text-lg font-bold mt-1">{{ jadwal.bank_soal?.nama }}</h2>
            </div>

            <div class="flex justify-between text-sm font-bold mb-4">
                <div>
                    <div>Tanggal Ujian : {{ jadwal.tgl_mulai }}</div>
                    <div>Waktu Ujian : {{ jadwal.tgl_mulai.split(' ')[1] || '-' }} s/d {{ jadwal.tgl_selesai.split(' ')[1] || '-' }}</div>
                </div>
                <div class="text-right">
                    <div>Ruang / Sesi : Terlampir di tabel</div>
                </div>
            </div>

            <table class="min-w-full border-collapse border border-black text-sm">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="border border-black px-4 py-2 text-center w-12">No</th>
                        <th class="border border-black px-4 py-2 text-left w-32">Nomor Peserta</th>
                        <th class="border border-black px-4 py-2 text-left">Nama Siswa</th>
                        <th class="border border-black px-4 py-2 text-center w-24">Ruang</th>
                        <th class="border border-black px-4 py-2 text-center w-24">Sesi</th>
                        <th class="border border-black px-4 py-2 text-left w-48" colspan="2">Tanda Tangan</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(siswa, index) in siswas" :key="siswa.id">
                        <td class="border border-black px-4 py-3 text-center">{{ index + 1 }}</td>
                        <td class="border border-black px-4 py-3 font-medium">{{ siswa.nomor_peserta || '-' }}</td>
                        <td class="border border-black px-4 py-3 font-medium">{{ siswa.nama }}</td>
                        <td class="border border-black px-4 py-3 text-center">{{ siswa.nama_ruang }}</td>
                        <td class="border border-black px-4 py-3 text-center">{{ siswa.nama_sesi }}</td>
                        <td class="border border-black px-2 py-3 w-24 border-r-0">
                            <span v-if="index % 2 === 0" class="text-xs">{{ index + 1 }}. ............</span>
                        </td>
                        <td class="border border-black px-2 py-3 w-24 border-l-0 text-right">
                            <span v-if="index % 2 !== 0" class="text-xs">{{ index + 1 }}. ............</span>
                        </td>
                    </tr>
                    <tr v-if="!siswas.length">
                        <td colspan="7" class="border border-black px-4 py-8 text-center text-gray-500 italic">Belum ada data siswa.</td>
                    </tr>
                </tbody>
            </table>

            <div class="mt-12 flex justify-between">
                <div class="text-center w-64">
                    <p>Pengawas 1</p>
                    <br><br><br>
                    <p class="font-bold border-b border-black w-full"></p>
                    <p class="mt-1 text-xs">NIP. </p>
                </div>
                <div class="text-center w-64">
                    <p>Pengawas 2</p>
                    <br><br><br>
                    <p class="font-bold border-b border-black w-full"></p>
                    <p class="mt-1 text-xs">NIP. </p>
                </div>
            </div>

            <!-- Print Button -->
            <div class="fixed bottom-8 right-8 print:hidden">
                <button onclick="window.print()" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-3 rounded-full shadow-lg font-bold flex items-center gap-2 transition-transform hover:scale-105">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
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
    jadwal: Object
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
