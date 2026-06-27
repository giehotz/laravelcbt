<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { store as cbtCetakKartuStore, print as cbtCetakKartuPrint } from '@/routes/cbt/cetak-kartu';
import InputError from '@/components/InputError.vue';

const props = defineProps({
    kop: {
        type: Object,
        default: () => ({}),
    },
    kelasList: {
        type: Array,
        default: () => [],
    },
});

const form = useForm({
    header_1: props.kop?.header_1 || '<b>KARTU PESERTA</b>',
    header_2: props.kop?.header_2 || 'ASESMEN AKHIR SEMESTER',
    header_3: props.kop?.header_3 || 'NAMA SEKOLAH',
    header_4: props.kop?.header_4 || 'TAHUN PELAJARAN 2024/2025',
    tanggal: props.kop?.tanggal || 'Tanggamus, 01 Juni 2026',
});

const submit = () => {
    form.post(cbtCetakKartuStore.url(), {
        preserveScroll: true,
    });
};

const selectedKelas = ref('');
</script>

<template>
    <Head title="Cetak Kartu Peserta" />

    <div class="flex min-h-screen flex-col bg-gray-50">
        <!-- Header -->
        <header class="border-b border-gray-200 bg-white px-4 py-4 sm:px-6 lg:px-8">
            <div class="flex items-center gap-4">
                <div class="rounded-lg bg-indigo-600 p-2">
                    <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"></path>
                    </svg>
                </div>
                <div>
                    <h1 class="text-xl font-bold text-gray-900">
                        Cetak Kartu Peserta
                    </h1>
                    <p class="text-sm text-gray-500">
                        Atur format kartu, lihat preview, lalu pilih kelas untuk mencetak.
                    </p>
                </div>
            </div>
        </header>

        <main class="mx-auto flex w-full max-w-7xl flex-col gap-6 p-4 sm:p-6 lg:p-8">
            
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Kolom Kiri: Setting Kartu -->
                <div class="flex flex-col gap-6">
                    <div class="rounded-xl border border-gray-200 bg-white shadow-sm overflow-hidden">
                        <div class="bg-gray-50 px-4 py-3 border-b border-gray-200 flex justify-between items-center">
                            <h3 class="font-bold text-gray-700">Setting Kartu</h3>
                        </div>
                        <form @submit.prevent="submit" class="p-4 sm:p-6 space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Header 1</label>
                                <textarea
                                    v-model="form.header_1"
                                    rows="2"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                ></textarea>
                                <InputError :message="form.errors.header_1" class="mt-2" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Header 2</label>
                                <textarea
                                    v-model="form.header_2"
                                    rows="2"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                ></textarea>
                                <InputError :message="form.errors.header_2" class="mt-2" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Header 3</label>
                                <textarea
                                    v-model="form.header_3"
                                    rows="2"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                ></textarea>
                                <InputError :message="form.errors.header_3" class="mt-2" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Header 4</label>
                                <textarea
                                    v-model="form.header_4"
                                    rows="2"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                ></textarea>
                                <InputError :message="form.errors.header_4" class="mt-2" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Tanggal & Tanda Tangan</label>
                                <input
                                    type="text"
                                    v-model="form.tanggal"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                />
                                <InputError :message="form.errors.tanggal" class="mt-2" />
                            </div>

                            <div class="pt-4 flex justify-end">
                                <button
                                    type="submit"
                                    :disabled="form.processing"
                                    class="inline-flex items-center gap-2 rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600"
                                >
                                    <svg v-if="form.processing" class="animate-spin h-4 w-4" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <svg v-else class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
                                    </svg>
                                    Simpan Pengaturan
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Kolom Bawah: Cetak Kartu -->
                    <div class="rounded-xl border border-gray-200 bg-white shadow-sm overflow-hidden">
                        <div class="bg-gray-50 px-4 py-3 border-b border-gray-200">
                            <h3 class="font-bold text-gray-700">Cetak</h3>
                        </div>
                        <div class="p-4 sm:p-6 flex flex-col sm:flex-row items-end gap-4">
                            <div class="flex-1 w-full">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Pilih Kelas</label>
                                <select
                                    v-model="selectedKelas"
                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                >
                                    <option value="">-- Pilih Kelas --</option>
                                    <option
                                        v-for="kelas in kelasList"
                                        :key="kelas.id"
                                        :value="kelas.id"
                                    >
                                        {{ kelas.nama_kelas }}
                                    </option>
                                </select>
                            </div>
                            <a
                                v-if="selectedKelas"
                                :href="cbtCetakKartuPrint.url({ query: { kelas_id: selectedKelas } })"
                                target="_blank"
                                class="inline-flex items-center gap-2 rounded-md bg-emerald-600 px-6 py-2 text-sm font-bold text-white shadow-sm transition-colors hover:bg-emerald-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600 h-[38px] w-full sm:w-auto justify-center"
                            >
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                                </svg>
                                Cetak
                            </a>
                            <button
                                v-else
                                disabled
                                class="inline-flex items-center gap-2 rounded-md bg-gray-300 px-6 py-2 text-sm font-bold text-gray-500 shadow-sm cursor-not-allowed h-[38px] w-full sm:w-auto justify-center"
                            >
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                                </svg>
                                Cetak
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Kolom Kanan: Preview -->
                <div class="rounded-xl border border-gray-200 bg-white shadow-sm overflow-hidden h-fit">
                    <div class="bg-gray-50 px-4 py-3 border-b border-gray-200">
                        <h3 class="font-bold text-gray-700">Preview</h3>
                    </div>
                    <div class="p-4 sm:p-6 flex justify-center bg-gray-100 overflow-x-auto">
                        <!-- Komponen Preview Kartu (Mirip PrintKartu.vue tapi single) -->
                        <div class="w-[10cm] shrink-0 bg-white border-[1.5px] border-black p-3 relative font-sans">
                            <!-- Header Kartu -->
                            <div class="flex items-center justify-between border-b-[1.5px] border-black pb-2 mb-2">
                                <div class="h-12 w-12 flex-shrink-0 flex items-center justify-center">
                                    <img v-if="kop?.logo_kiri" :src="kop.logo_kiri" class="h-full w-full object-contain" />
                                    <div v-else class="h-10 w-10 bg-gray-100 flex items-center justify-center text-[10px] text-gray-400 border border-gray-200">Logo</div>
                                </div>
                                <div class="flex-1 px-1 text-center flex flex-col justify-center leading-snug">
                                    <div class="text-xs font-bold" v-html="form.header_1 || '<b>KARTU PESERTA</b>'"></div>
                                    <div class="text-[11px] font-bold uppercase" v-html="form.header_2 || 'ASESMEN AKHIR SEMESTER GENAP'"></div>
                                    <div class="text-[11px] font-bold uppercase" v-html="form.header_3 || 'MIN 2 TANGGAMUS'"></div>
                                    <div class="text-[9px]" v-html="form.header_4 || 'TAHUN PELAJARAN 2025/2026'"></div>
                                </div>
                                <div class="h-12 w-12 flex-shrink-0 flex items-center justify-center">
                                    <img v-if="kop?.logo_kanan" :src="kop.logo_kanan" class="h-full w-full object-contain" />
                                    <div v-else class="h-10 w-10 bg-gray-100 flex items-center justify-center text-[10px] text-gray-400 border border-gray-200">Logo</div>
                                </div>
                            </div>

                            <!-- Body Kartu -->
                            <div class="flex gap-2">
                                <div class="flex flex-col gap-1 w-[2.2cm]">
                                    <div class="h-[2.9cm] w-full border border-gray-400 bg-gray-50 flex items-center justify-center text-[10px] text-gray-400 overflow-hidden shrink-0">
                                        <span>Foto 3x4</span>
                                    </div>
                                </div>

                                <div class="flex-1 flex flex-col justify-between">
                                    <table class="w-full text-[10px] font-medium leading-tight">
                                        <tbody>
                                            <tr>
                                                <td class="w-[75px] py-[1.5px] align-top">Nomor Peserta</td>
                                                <td class="w-2 py-[1.5px] align-top">:</td>
                                                <td class="py-[1.5px] align-top font-bold">0000.00.000</td>
                                            </tr>
                                            <tr>
                                                <td class="py-[1.5px] align-top">Nama</td>
                                                <td class="py-[1.5px] align-top">:</td>
                                                <td class="py-[1.5px] align-top font-bold leading-tight">Nama Siswa</td>
                                            </tr>
                                            <tr>
                                                <td class="py-[1.5px] align-top">NIS-NISN</td>
                                                <td class="py-[1.5px] align-top">:</td>
                                                <td class="py-[1.5px] align-top">012334455</td>
                                            </tr>
                                            <tr>
                                                <td class="py-[1.5px] align-top">Kelas</td>
                                                <td class="py-[1.5px] align-top">:</td>
                                                <td class="py-[1.5px] align-top">IXA</td>
                                            </tr>
                                            <tr>
                                                <td class="py-[1.5px] align-top">Ruang/Sesi</td>
                                                <td class="py-[1.5px] align-top">:</td>
                                                <td class="py-[1.5px] align-top">1 / 2</td>
                                            </tr>
                                            <tr>
                                                <td class="py-[1.5px] align-top pt-1.5">Username</td>
                                                <td class="py-[1.5px] align-top pt-1.5">:</td>
                                                <td class="py-[1.5px] align-top pt-1.5 font-bold">umbk001</td>
                                            </tr>
                                            <tr>
                                                <td class="py-[1.5px] align-top">Password</td>
                                                <td class="py-[1.5px] align-top">:</td>
                                                <td class="py-[1.5px] align-top font-bold">umbk001</td>
                                            </tr>
                                        </tbody>
                                    </table>

                                    <div class="flex justify-end mt-1">
                                        <div class="text-[9px] text-center leading-tight">
                                            <p>{{ form.tanggal || 'Tanggamus, 01 Juni 2026' }}</p>
                                            <p>Kepala Madrasah</p>
                                            <div class="h-8 relative"></div>
                                            <p class="font-bold underline">Sipullah, M.Pd</p>
                                            <p>NIP. -</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </main>
    </div>
</template>
