<template>
    <div class="min-h-screen bg-gray-100 print:bg-white p-4 print:p-0 flex justify-center">
        <div class="w-full max-w-5xl bg-white print:shadow-none shadow-md">
            
            <div class="grid grid-cols-1 md:grid-cols-2 print:grid-cols-2 gap-4 p-4 print:p-0">
                
                <div v-for="(siswa, index) in siswas" :key="siswa.id" 
                    class="border-2 border-black p-4 break-inside-avoid print:mb-4">
                    
                    <!-- Kop -->
                    <div class="flex items-center justify-between border-b-2 border-black pb-3 mb-4">
                        <div v-if="kop?.logo_kiri" class="w-16 h-16">
                            <img :src="kop.logo_kiri" class="w-full h-full object-contain">
                        </div>
                        <div v-else class="w-16 h-16 bg-gray-100 flex items-center justify-center text-xs text-gray-400">Logo</div>
                        
                        <div class="text-center flex-1 px-2">
                            <div class="font-bold text-sm">{{ kop?.header_1 || 'PEMERINTAH PROVINSI' }}</div>
                            <div class="font-bold text-sm">{{ kop?.header_2 || 'DINAS PENDIDIKAN' }}</div>
                            <div class="font-bold text-base uppercase">{{ kop?.header_3 || 'NAMA SEKOLAH' }}</div>
                            <div class="text-xs">{{ kop?.header_4 || 'Alamat Sekolah' }}</div>
                        </div>

                        <div v-if="kop?.logo_kanan" class="w-16 h-16">
                            <img :src="kop.logo_kanan" class="w-full h-full object-contain">
                        </div>
                        <div v-else class="w-16 h-16 bg-white"></div>
                    </div>

                    <div class="text-center font-bold text-lg mb-4 uppercase underline">
                        KARTU PESERTA UJIAN
                    </div>

                    <div class="flex gap-4">
                        <!-- Foto (Placeholder) -->
                        <div class="w-24 h-32 border-2 border-gray-300 flex items-center justify-center bg-gray-50 text-gray-400 text-sm">
                            Foto 3x4
                        </div>

                        <!-- Data Siswa -->
                        <div class="flex-1">
                            <table class="w-full text-sm font-medium">
                                <tbody>
                                    <tr>
                                        <td class="py-1 w-32">Nomor Peserta</td>
                                        <td class="py-1 w-2">:</td>
                                        <td class="py-1 font-bold">{{ siswa.nomor_peserta || '-' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="py-1">Nama Siswa</td>
                                        <td class="py-1">:</td>
                                        <td class="py-1 font-bold">{{ siswa.nama }}</td>
                                    </tr>
                                    <tr>
                                        <td class="py-1">NIS / NISN</td>
                                        <td class="py-1">:</td>
                                        <td class="py-1">{{ siswa.nis || '-' }} / {{ siswa.nisn || '-' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="py-1">Password</td>
                                        <td class="py-1">:</td>
                                        <td class="py-1 font-bold text-red-600 tracking-wider">{{ siswa.password_cbt || '******' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="py-1">Jadwal Ujian</td>
                                        <td class="py-1">:</td>
                                        <td class="py-1">{{ jadwal.bank_soal?.nama }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="mt-4 flex justify-between items-end text-sm">
                        <div>
                            <div class="font-bold border border-black px-4 py-2 inline-block">
                                RUANG: {{ siswa.ruang_nama || '...' }}
                            </div>
                        </div>
                        <div class="text-center w-40">
                            <p>Kepala Sekolah,</p>
                            <br><br><br>
                            <p class="font-bold border-b border-black w-full"></p>
                            <p class="mt-1 text-xs">NIP. </p>
                        </div>
                    </div>
                </div>

                <div v-if="!siswas.length" class="col-span-2 text-center p-12 text-gray-500 font-bold border-2 border-dashed border-gray-300">
                    Belum ada data siswa untuk jadwal ini.
                </div>

            </div>
            
            <!-- Print Button -->
            <div class="fixed bottom-8 right-8 print:hidden">
                <button onclick="window.print()" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-3 rounded-full shadow-lg font-bold flex items-center gap-2 transition-transform hover:scale-105">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
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
    jadwal: Object
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
