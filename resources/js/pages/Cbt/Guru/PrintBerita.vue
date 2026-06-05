<template>
    <div class="min-h-screen bg-gray-100 print:bg-white p-4 print:p-0 flex flex-col items-center gap-8">
        
        <div v-for="ruang in ruangSesiList" :key="ruang.id" class="w-full max-w-5xl bg-white print:shadow-none shadow-md p-8 break-after-page">
            
            <!-- Kop -->
            <div class="flex items-center justify-between border-b-[3px] border-black pb-4 mb-6">
                <div v-if="kop?.logo_kiri" class="w-24 h-24">
                    <img :src="kop.logo_kiri" class="w-full h-full object-contain">
                </div>
                <div v-else class="w-24 h-24 bg-gray-100 flex items-center justify-center text-xs text-gray-400">Logo</div>
                
                <div class="text-center flex-1 px-4">
                    <div class="font-bold text-lg">{{ kop?.header_1 || 'PEMERINTAH PROVINSI' }}</div>
                    <div class="font-bold text-lg">{{ kop?.header_2 || 'DINAS PENDIDIKAN' }}</div>
                    <div class="font-bold text-2xl uppercase tracking-widest mt-1">{{ kop?.header_3 || 'NAMA SEKOLAH' }}</div>
                    <div class="text-base mt-2">{{ kop?.header_4 || 'Alamat Sekolah' }}</div>
                </div>

                <div v-if="kop?.logo_kanan" class="w-24 h-24">
                    <img :src="kop.logo_kanan" class="w-full h-full object-contain">
                </div>
                <div v-else class="w-24 h-24 bg-white"></div>
            </div>

            <div class="text-center mb-8">
                <h1 class="text-xl font-bold uppercase underline tracking-wider">BERITA ACARA PELAKSANAAN UJIAN</h1>
            </div>

            <div class="text-justify leading-relaxed mb-6">
                Pada hari ini <span class="font-bold border-b border-dotted border-black px-4 inline-block min-w-[150px]"></span> 
                tanggal <span class="font-bold border-b border-dotted border-black px-4 inline-block min-w-[150px]"></span> 
                bulan <span class="font-bold border-b border-dotted border-black px-4 inline-block min-w-[150px]"></span> 
                tahun <span class="font-bold border-b border-dotted border-black px-4 inline-block min-w-[100px]"></span>, 
                telah dilaksanakan ujian untuk mata pelajaran <span class="font-bold">{{ jadwal.bank_soal?.nama }}</span>.
            </div>

            <div class="mb-6 pl-4">
                <table class="w-full text-base">
                    <tbody>
                        <tr>
                            <td class="py-2 w-48 font-medium">Ruang Ujian</td>
                            <td class="py-2 w-4">:</td>
                            <td class="py-2 font-bold">{{ ruang.nama_ruang }}</td>
                        </tr>
                        <tr>
                            <td class="py-2 font-medium">Sesi</td>
                            <td class="py-2">:</td>
                            <td class="py-2 font-bold">{{ ruang.nama_sesi }}</td>
                        </tr>
                        <tr>
                            <td class="py-2 font-medium">Jumlah Peserta Terdaftar</td>
                            <td class="py-2">:</td>
                            <td class="py-2 font-bold">{{ ruang.peserta }} Orang</td>
                        </tr>
                        <tr>
                            <td class="py-2 font-medium">Jumlah Peserta Hadir</td>
                            <td class="py-2">:</td>
                            <td class="py-2 font-bold">{{ ruang.hadir }} Orang</td>
                        </tr>
                        <tr>
                            <td class="py-2 font-medium">Jumlah Peserta Absen</td>
                            <td class="py-2">:</td>
                            <td class="py-2 font-bold">{{ ruang.absen }} Orang</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="mb-8">
                <div class="font-bold mb-2">Catatan Selama Pelaksanaan Ujian:</div>
                <div class="border border-black min-h-[150px] p-4 text-gray-400 italic">
                    (Tuliskan jika ada kendala jaringan, mati listrik, kecurangan, atau hal lain yang terjadi)
                </div>
            </div>

            <div class="text-justify leading-relaxed mb-12">
                Demikian berita acara ini dibuat dengan sesungguhnya untuk dapat dipergunakan sebagaimana mestinya.
            </div>

            <div class="flex justify-between mt-12 px-8">
                <div class="text-center w-64">
                    <p>Proktor / Teknisi</p>
                    <br><br><br><br>
                    <p class="font-bold border-b border-black w-full"></p>
                    <p class="mt-1 text-sm">NIP. </p>
                </div>
                <div class="text-center w-64">
                    <p>Pengawas Ruang</p>
                    <br><br><br><br>
                    <p class="font-bold border-b border-black w-full"></p>
                    <p class="mt-1 text-sm">NIP. </p>
                </div>
            </div>
            
        </div>

        <div v-if="!ruangSesiList.length" class="w-full max-w-5xl bg-white p-12 text-center text-gray-500 font-bold border-2 border-dashed border-gray-300">
            Belum ada data pengaturan ruang/sesi untuk jadwal ini.
        </div>

        <!-- Print Button -->
        <div class="fixed bottom-8 right-8 print:hidden">
            <button onclick="window.print()" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-3 rounded-full shadow-lg font-bold flex items-center gap-2 transition-transform hover:scale-105">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                Cetak Berita Acara
            </button>
        </div>

    </div>
</template>

<script setup>
const props = defineProps({
    ruangSesiList: Array,
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
