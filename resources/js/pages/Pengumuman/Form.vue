<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';

const props = defineProps({
    kelas: Array
});

const form = useForm({
    kepada: {
        type: 'all',
        ids: []
    },
    text: ''
});

const submit = () => {
    form.post(route('pengumuman.store'), {
        preserveScroll: true
    });
};
</script>

<template>
    <AppLayout>
        <Head title="Buat Pengumuman" />

        <div class="max-w-4xl mx-auto py-6 sm:px-6 lg:px-8">
            <div class="mb-4 flex items-center gap-4">
                <Link :href="route('pengumuman.index')" class="text-gray-500 hover:text-gray-700">
                    &larr; Kembali
                </Link>
                <h2 class="text-2xl font-bold leading-tight text-gray-900">
                    Buat Pengumuman Baru
                </h2>
            </div>

            <div class="bg-white shadow sm:rounded-lg">
                <form @submit.prevent="submit" class="p-6 space-y-6">
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Tujuan Pengumuman</label>
                        <select v-model="form.kepada.type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            <option value="all">Semua Pengguna</option>
                            <option value="siswa">Semua Siswa</option>
                            <option value="guru">Semua Guru</option>
                            <option value="kelas">Kelas Tertentu</option>
                        </select>
                        <p v-if="form.errors['kepada.type']" class="mt-2 text-sm text-red-600">{{ form.errors['kepada.type'] }}</p>
                    </div>

                    <div v-if="form.kepada.type === 'kelas'">
                        <label class="block text-sm font-medium text-gray-700">Pilih Kelas</label>
                        <div class="mt-2 grid grid-cols-2 md:grid-cols-4 gap-4 border p-4 rounded-md h-48 overflow-y-auto">
                            <div v-for="k in kelas" :key="k.id" class="flex items-center">
                                <input
                                    :id="'kelas-'+k.id"
                                    type="checkbox"
                                    :value="k.id"
                                    v-model="form.kepada.ids"
                                    class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                                />
                                <label :for="'kelas-'+k.id" class="ml-2 block text-sm text-gray-900">
                                    {{ k.nama }}
                                </label>
                            </div>
                        </div>
                        <p v-if="form.errors['kepada.ids']" class="mt-2 text-sm text-red-600">{{ form.errors['kepada.ids'] }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Isi Pengumuman</label>
                        <textarea
                            v-model="form.text"
                            rows="6"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            placeholder="Tuliskan isi pengumuman di sini..."
                        ></textarea>
                        <p v-if="form.errors.text" class="mt-2 text-sm text-red-600">{{ form.errors.text }}</p>
                    </div>

                    <div class="pt-5 flex justify-end">
                        <button type="submit" :disabled="form.processing" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Terbitkan Pengumuman
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
