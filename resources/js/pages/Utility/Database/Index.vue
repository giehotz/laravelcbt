<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { ref } from 'vue';

const props = defineProps({
    allowed_modules: Array
});

const backupForm = useForm({});
const truncateForm = useForm({
    module: '',
    confirmation: ''
});

const isTruncateModalOpen = ref(false);

const openTruncateModal = (moduleName) => {
    truncateForm.module = moduleName;
    truncateForm.confirmation = '';
    truncateForm.clearErrors();
    isTruncateModalOpen.value = true;
};

const closeTruncateModal = () => {
    isTruncateModalOpen.value = false;
    truncateForm.reset();
};

const executeBackup = () => {
    // Cannot use inertia form for download directly easily if it returns file, but let's try.
    // Standard way for file download in Inertia is window.location or standard form submit.
    // Since our controller returns download(), it's better to use standard form or a link.
    window.location.href = route('utility.database.backup');
};

const executeTruncate = () => {
    if (truncateForm.module !== truncateForm.confirmation) {
        truncateForm.setError('confirmation', 'Konfirmasi nama modul tidak cocok!');
        return;
    }

    truncateForm.post(route('utility.database.truncate'), {
        preserveScroll: true,
        onSuccess: () => {
            closeTruncateModal();
        }
    });
};
</script>

<template>
    <AppLayout>
        <Head title="Database Utilities" />

        <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
            <h2 class="text-2xl font-bold leading-tight text-gray-900 mb-6">
                Database Utilities
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Backup Card -->
                <div class="bg-white shadow sm:rounded-lg overflow-hidden">
                    <div class="px-4 py-5 sm:px-6 border-b border-gray-200 bg-gray-50 flex items-center">
                        <svg class="h-6 w-6 text-indigo-600 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
                        </svg>
                        <h3 class="text-lg leading-6 font-medium text-gray-900">
                            Backup Database
                        </h3>
                    </div>
                    <div class="p-6">
                        <p class="text-sm text-gray-600 mb-6">
                            Fitur ini akan mengekspor seluruh database aplikasi ke dalam file <span class="font-mono bg-gray-100 px-1 rounded">.sql</span> dan langsung mengunduhnya ke perangkat Anda. 
                            Pastikan Anda melakukan backup secara rutin sebelum melakukan perubahan besar atau pembersihan data.
                        </p>
                        <form method="POST" :action="route('utility.database.backup')">
                            <!-- CSRF token needed for normal form post if we don't use Inertia or window.location -->
                            <button type="button" @click="executeBackup" class="inline-flex items-center justify-center px-4 py-2 border border-transparent font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 shadow-sm">
                                <svg class="h-5 w-5 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                </svg>
                                Eksekusi Backup Sekarang
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Data Manager Card -->
                <div class="bg-white shadow sm:rounded-lg overflow-hidden border-t-4 border-red-500">
                    <div class="px-4 py-5 sm:px-6 border-b border-gray-200 bg-gray-50 flex items-center">
                        <svg class="h-6 w-6 text-red-600 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                        <h3 class="text-lg leading-6 font-medium text-gray-900">
                            Data Manager (Truncate)
                        </h3>
                    </div>
                    <div class="p-6">
                        <div class="bg-red-50 border-l-4 border-red-400 p-4 mb-6">
                            <div class="flex">
                                <div class="ml-3">
                                    <p class="text-sm text-red-700 font-semibold">
                                        Perhatian: Aksi ini bersifat permanen (TIDAK BISA DI-UNDO).
                                    </p>
                                    <p class="text-sm text-red-700 mt-1">
                                        Fungsi ini digunakan untuk mengosongkan tabel transaksional (seperti nilai, log ujian) guna persiapan semester baru. Tabel master (siswa, guru, soal) dilindungi dari penghapusan massal.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <div v-for="mod in allowed_modules" :key="mod" class="flex items-center justify-between border border-gray-200 p-4 rounded-md hover:bg-gray-50">
                                <div>
                                    <span class="font-medium text-gray-900 font-mono">{{ mod }}</span>
                                </div>
                                <button @click="openTruncateModal(mod)" class="text-red-600 hover:text-red-900 font-medium text-sm bg-red-100 px-3 py-1 rounded">
                                    Kosongkan Data
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Truncate Modal -->
        <div v-if="isTruncateModalOpen" class="fixed z-10 inset-0 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" @click="closeTruncateModal"></div>

                <!-- This element is to trick the browser into centering the modal contents. -->
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                                <svg class="h-6 w-6 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                    Konfirmasi Pengosongan Data: <span class="font-mono text-red-600">{{ truncateForm.module }}</span>
                                </h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500">
                                        Tindakan ini akan <strong class="text-red-600">MENGHAPUS SEMUA DATA</strong> pada modul ini secara permanen. Anda tidak dapat mengembalikan data ini kembali.
                                    </p>
                                    <div class="mt-4">
                                        <label class="block text-sm font-medium text-gray-700">
                                            Untuk melanjutkan, ketik ulang nama modul "<span class="font-mono font-bold">{{ truncateForm.module }}</span>":
                                        </label>
                                        <input 
                                            v-model="truncateForm.confirmation"
                                            type="text" 
                                            class="mt-1 shadow-sm focus:ring-red-500 focus:border-red-500 block w-full sm:text-sm border-gray-300 rounded-md font-mono"
                                            :placeholder="truncateForm.module"
                                        >
                                        <p v-if="truncateForm.errors.confirmation" class="mt-2 text-sm text-red-600">{{ truncateForm.errors.confirmation }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button 
                            type="button" 
                            @click="executeTruncate"
                            :disabled="truncateForm.processing || truncateForm.module !== truncateForm.confirmation"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 text-base font-medium text-white sm:ml-3 sm:w-auto sm:text-sm"
                            :class="truncateForm.module === truncateForm.confirmation ? 'bg-red-600 hover:bg-red-700 focus:ring-red-500 focus:outline-none focus:ring-2 focus:ring-offset-2' : 'bg-red-300 cursor-not-allowed'"
                        >
                            Kosongkan Permanen
                        </button>
                        <button 
                            type="button" 
                            @click="closeTruncateModal"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
                        >
                            Batal
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </AppLayout>
</template>
