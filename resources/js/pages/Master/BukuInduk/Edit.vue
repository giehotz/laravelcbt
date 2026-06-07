<script setup>
import { Head, useForm, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { ref } from 'vue';

const props = defineProps({
    siswa: Object,
});

const form = useForm({
    siswa: {
        nama_ayah: props.siswa.nama_ayah || '',
        tgl_lahir_ayah: props.siswa.tgl_lahir_ayah || '',
        pendidikan_ayah: props.siswa.pendidikan_ayah || '',
        pekerjaan_ayah: props.siswa.pekerjaan_ayah || '',
        nohp_ayah: props.siswa.nohp_ayah || '',
        alamat_ayah: props.siswa.alamat_ayah || '',
        nama_ibu: props.siswa.nama_ibu || '',
        tgl_lahir_ibu: props.siswa.tgl_lahir_ibu || '',
        pendidikan_ibu: props.siswa.pendidikan_ibu || '',
        pekerjaan_ibu: props.siswa.pekerjaan_ibu || '',
        nohp_ibu: props.siswa.nohp_ibu || '',
        alamat_ibu: props.siswa.alamat_ibu || '',
        nama_wali: props.siswa.nama_wali || '',
        tgl_lahir_wali: props.siswa.tgl_lahir_wali || '',
        pendidikan_wali: props.siswa.pendidikan_wali || '',
        pekerjaan_wali: props.siswa.pekerjaan_wali || '',
        nohp_wali: props.siswa.nohp_wali || '',
        alamat_wali: props.siswa.alamat_wali || '',
    },
    buku_induk: {
        uid: props.siswa.buku_induk?.uid || '',
        nama_panggilan: props.siswa.buku_induk?.nama_panggilan || '',
        bahasa: props.siswa.buku_induk?.bahasa || '',
        jml_saudara_kandung: props.siswa.buku_induk?.jml_saudara_kandung || 0,
        jml_saudara_tiri: props.siswa.buku_induk?.jml_saudara_tiri || 0,
        jml_saudara_angkat: props.siswa.buku_induk?.jml_saudara_angkat || 0,
        yatim: props.siswa.buku_induk?.yatim || 0,
        tinggal_bersama: props.siswa.buku_induk?.tinggal_bersama || '1',
        jarak: props.siswa.buku_induk?.jarak || '',
        gol_darah: props.siswa.buku_induk?.gol_darah || '',
        penyakit: props.siswa.buku_induk?.penyakit || '',
        kelainan_fisik: props.siswa.buku_induk?.kelainan_fisik || '',
        kegemaran: props.siswa.buku_induk?.kegemaran || '',
        beasiswa: props.siswa.buku_induk?.beasiswa || '',
        no_ijazah_sebelumnya:
            props.siswa.buku_induk?.no_ijazah_sebelumnya || '',
        tahun_lulus_sebelumnya:
            props.siswa.buku_induk?.tahun_lulus_sebelumnya || '',
        pindahan_dari: props.siswa.buku_induk?.pindahan_dari || '',
        alasan_kepindahan: props.siswa.buku_induk?.alasan_kepindahan || '',
        status: props.siswa.buku_induk?.status ?? 1,
        tahun_lulus: props.siswa.buku_induk?.tahun_lulus || '',
        no_ijazah: props.siswa.buku_induk?.no_ijazah || '',
        kelas_akhir: props.siswa.buku_induk?.kelas_akhir || '',
        catatan_penting: props.siswa.buku_induk?.catatan_penting || '',
    },
});

const activeTab = ref('dataDiri');

const tabs = [
    { id: 'dataDiri', name: 'Data Diri Fisik' },
    { id: 'orangTua', name: 'Data Orang Tua & Wali' },
    { id: 'riwayat', name: 'Riwayat Sekolah' },
    { id: 'statusAkhir', name: 'Status Akhir' },
];

const submit = () => {
    form.put(route('master.buku-induk.update', props.siswa.id), {
        preserveScroll: true,
        onSuccess: () => {
            // Toast notification should be handled by default AppLayout flash message
        },
    });
};
</script>

<template>
    <AppLayout>
        <Head title="Lengkapi Buku Induk" />

        <div class="mx-auto max-w-7xl py-6 sm:px-6 lg:px-8">
            <div class="mb-4 flex items-center gap-4">
                <Link
                    :href="route('master.buku-induk.index')"
                    class="text-gray-500 hover:text-gray-700"
                >
                    &larr; Kembali
                </Link>
                <h2 class="text-2xl leading-tight font-bold text-gray-900">
                    Lengkapi Buku Induk: {{ siswa.nama }}
                </h2>
            </div>

            <div class="overflow-hidden bg-white shadow sm:rounded-lg">
                <!-- Tab Headers -->
                <div class="border-b border-gray-200">
                    <nav class="-mb-px flex" aria-label="Tabs">
                        <button
                            v-for="tab in tabs"
                            :key="tab.id"
                            @click="activeTab = tab.id"
                            :class="[
                                activeTab === tab.id
                                    ? 'border-indigo-500 text-indigo-600'
                                    : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700',
                                'w-1/4 border-b-2 px-1 py-4 text-center text-sm font-medium',
                            ]"
                        >
                            {{ tab.name }}
                        </button>
                    </nav>
                </div>

                <!-- Form Content -->
                <form @submit.prevent="submit" class="p-6">
                    <!-- TAB 1: DATA DIRI -->
                    <div v-show="activeTab === 'dataDiri'" class="space-y-6">
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700"
                                    >Nama Panggilan</label
                                >
                                <input
                                    v-model="form.buku_induk.nama_panggilan"
                                    type="text"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                />
                            </div>
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700"
                                    >Bahasa Sehari-hari</label
                                >
                                <input
                                    v-model="form.buku_induk.bahasa"
                                    type="text"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                />
                            </div>
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700"
                                    >Jumlah Sdr. Kandung</label
                                >
                                <input
                                    v-model="
                                        form.buku_induk.jml_saudara_kandung
                                    "
                                    type="number"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                />
                            </div>
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700"
                                    >Jumlah Sdr. Tiri</label
                                >
                                <input
                                    v-model="form.buku_induk.jml_saudara_tiri"
                                    type="number"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                />
                            </div>
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700"
                                    >Status Yatim</label
                                >
                                <select
                                    v-model="form.buku_induk.yatim"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                >
                                    <option :value="0">
                                        Ada Orang Tua Lengkap
                                    </option>
                                    <option :value="1">Yatim</option>
                                    <option :value="2">Yatim Piatu</option>
                                </select>
                            </div>
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700"
                                    >Tinggal Bersama</label
                                >
                                <select
                                    v-model="form.buku_induk.tinggal_bersama"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                >
                                    <option value="1">Orang Tua</option>
                                    <option value="2">Saudara</option>
                                    <option value="3">Wali</option>
                                    <option value="4">Asrama</option>
                                    <option value="5">Kost</option>
                                    <option value="6">Lainnya</option>
                                </select>
                            </div>
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700"
                                    >Jarak ke Sekolah (km)</label
                                >
                                <input
                                    v-model="form.buku_induk.jarak"
                                    type="text"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                />
                            </div>
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700"
                                    >Golongan Darah</label
                                >
                                <select
                                    v-model="form.buku_induk.gol_darah"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                >
                                    <option value="">-- Pilih --</option>
                                    <option value="A">A</option>
                                    <option value="B">B</option>
                                    <option value="AB">AB</option>
                                    <option value="O">O</option>
                                </select>
                            </div>
                            <div class="md:col-span-2">
                                <label
                                    class="block text-sm font-medium text-gray-700"
                                    >Penyakit yang Pernah Diderita</label
                                >
                                <textarea
                                    v-model="form.buku_induk.penyakit"
                                    rows="2"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                ></textarea>
                            </div>
                            <div class="md:col-span-2">
                                <label
                                    class="block text-sm font-medium text-gray-700"
                                    >Kelainan Fisik</label
                                >
                                <input
                                    v-model="form.buku_induk.kelainan_fisik"
                                    type="text"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                />
                            </div>
                            <div class="md:col-span-2">
                                <label
                                    class="block text-sm font-medium text-gray-700"
                                    >Kegemaran / Hobi</label
                                >
                                <textarea
                                    v-model="form.buku_induk.kegemaran"
                                    rows="2"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                ></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- TAB 2: ORANG TUA & WALI -->
                    <div v-show="activeTab === 'orangTua'" class="space-y-8">
                        <!-- Ayah -->
                        <div>
                            <h3
                                class="mb-4 border-b pb-2 text-lg font-medium text-gray-900"
                            >
                                Data Ayah
                            </h3>
                            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700"
                                        >Nama Ayah</label
                                    >
                                    <input
                                        v-model="form.siswa.nama_ayah"
                                        type="text"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    />
                                </div>
                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700"
                                        >Tgl Lahir / Umur Ayah</label
                                    >
                                    <input
                                        v-model="form.siswa.tgl_lahir_ayah"
                                        type="text"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    />
                                </div>
                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700"
                                        >Pendidikan Ayah</label
                                    >
                                    <input
                                        v-model="form.siswa.pendidikan_ayah"
                                        type="text"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    />
                                </div>
                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700"
                                        >Pekerjaan Ayah</label
                                    >
                                    <input
                                        v-model="form.siswa.pekerjaan_ayah"
                                        type="text"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    />
                                </div>
                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700"
                                        >No HP Ayah</label
                                    >
                                    <input
                                        v-model="form.siswa.nohp_ayah"
                                        type="text"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    />
                                </div>
                                <div class="md:col-span-2">
                                    <label
                                        class="block text-sm font-medium text-gray-700"
                                        >Alamat Ayah</label
                                    >
                                    <textarea
                                        v-model="form.siswa.alamat_ayah"
                                        rows="2"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    ></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Ibu -->
                        <div>
                            <h3
                                class="mb-4 border-b pb-2 text-lg font-medium text-gray-900"
                            >
                                Data Ibu
                            </h3>
                            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700"
                                        >Nama Ibu</label
                                    >
                                    <input
                                        v-model="form.siswa.nama_ibu"
                                        type="text"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    />
                                </div>
                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700"
                                        >Tgl Lahir / Umur Ibu</label
                                    >
                                    <input
                                        v-model="form.siswa.tgl_lahir_ibu"
                                        type="text"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    />
                                </div>
                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700"
                                        >Pendidikan Ibu</label
                                    >
                                    <input
                                        v-model="form.siswa.pendidikan_ibu"
                                        type="text"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    />
                                </div>
                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700"
                                        >Pekerjaan Ibu</label
                                    >
                                    <input
                                        v-model="form.siswa.pekerjaan_ibu"
                                        type="text"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    />
                                </div>
                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700"
                                        >No HP Ibu</label
                                    >
                                    <input
                                        v-model="form.siswa.nohp_ibu"
                                        type="text"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    />
                                </div>
                                <div class="md:col-span-2">
                                    <label
                                        class="block text-sm font-medium text-gray-700"
                                        >Alamat Ibu</label
                                    >
                                    <textarea
                                        v-model="form.siswa.alamat_ibu"
                                        rows="2"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    ></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Wali -->
                        <div>
                            <h3
                                class="mb-4 border-b pb-2 text-lg font-medium text-gray-900"
                            >
                                Data Wali (Opsional)
                            </h3>
                            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700"
                                        >Nama Wali</label
                                    >
                                    <input
                                        v-model="form.siswa.nama_wali"
                                        type="text"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    />
                                </div>
                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700"
                                        >Pekerjaan Wali</label
                                    >
                                    <input
                                        v-model="form.siswa.pekerjaan_wali"
                                        type="text"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    />
                                </div>
                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700"
                                        >No HP Wali</label
                                    >
                                    <input
                                        v-model="form.siswa.nohp_wali"
                                        type="text"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    />
                                </div>
                                <div class="md:col-span-2">
                                    <label
                                        class="block text-sm font-medium text-gray-700"
                                        >Alamat Wali</label
                                    >
                                    <textarea
                                        v-model="form.siswa.alamat_wali"
                                        rows="2"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    ></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- TAB 3: RIWAYAT SEKOLAH -->
                    <div v-show="activeTab === 'riwayat'" class="space-y-6">
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700"
                                    >No Ijazah Sebelumnya</label
                                >
                                <input
                                    v-model="
                                        form.buku_induk.no_ijazah_sebelumnya
                                    "
                                    type="text"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                />
                            </div>
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700"
                                    >Tahun Lulus Sebelumnya</label
                                >
                                <input
                                    v-model="
                                        form.buku_induk.tahun_lulus_sebelumnya
                                    "
                                    type="text"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                />
                            </div>
                            <div class="md:col-span-2">
                                <label
                                    class="block text-sm font-medium text-gray-700"
                                    >Pindahan Dari (Jika siswa pindahan)</label
                                >
                                <input
                                    v-model="form.buku_induk.pindahan_dari"
                                    type="text"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                />
                            </div>
                            <div class="md:col-span-2">
                                <label
                                    class="block text-sm font-medium text-gray-700"
                                    >Alasan Kepindahan</label
                                >
                                <input
                                    v-model="form.buku_induk.alasan_kepindahan"
                                    type="text"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- TAB 4: STATUS AKHIR -->
                    <div v-show="activeTab === 'statusAkhir'" class="space-y-6">
                        <div
                            class="mb-4 border-l-4 border-yellow-400 bg-yellow-50 p-4"
                        >
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg
                                        class="h-5 w-5 text-yellow-400"
                                        viewBox="0 0 20 20"
                                        fill="currentColor"
                                    >
                                        <path
                                            fill-rule="evenodd"
                                            d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                            clip-rule="evenodd"
                                        />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-yellow-700">
                                        Perhatian: Mengubah status menjadi
                                        Lulus, Pindah, atau Keluar akan
                                        mempengaruhi aktifitas akun siswa ini.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700"
                                    >Status Akhir Siswa</label
                                >
                                <select
                                    v-model="form.buku_induk.status"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                >
                                    <option :value="1">Aktif</option>
                                    <option :value="2">Lulus</option>
                                    <option :value="3">Pindah</option>
                                    <option :value="4">Keluar / DO</option>
                                </select>
                            </div>

                            <template v-if="form.buku_induk.status === 2">
                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700"
                                        >Tahun Lulus</label
                                    >
                                    <input
                                        v-model="form.buku_induk.tahun_lulus"
                                        type="number"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    />
                                </div>
                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700"
                                        >No Ijazah</label
                                    >
                                    <input
                                        v-model="form.buku_induk.no_ijazah"
                                        type="text"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    />
                                </div>
                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700"
                                        >Kelas Akhir</label
                                    >
                                    <input
                                        v-model="form.buku_induk.kelas_akhir"
                                        type="text"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    />
                                </div>
                            </template>

                            <div class="md:col-span-2">
                                <label
                                    class="block text-sm font-medium text-gray-700"
                                    >Catatan Penting</label
                                >
                                <textarea
                                    v-model="form.buku_induk.catatan_penting"
                                    rows="3"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                ></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div
                        class="mt-8 flex justify-end border-t border-gray-200 pt-5"
                    >
                        <Link
                            :href="route('master.buku-induk.index')"
                            class="mr-3 rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:outline-none"
                        >
                            Batal
                        </Link>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="inline-flex justify-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:outline-none"
                        >
                            Simpan Buku Induk
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
