<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { index, store } from '@/routes/pengumuman';

const props = defineProps({
    kelas: Array,
});

const form = useForm({
    kepada: {
        type: 'all',
        ids: [],
    },
    text: '',
});

const submit = () => {
    form.post(store.url(), {
        preserveScroll: true,
    });
};
</script>

<template>
    <div>
        <Head title="Buat Pengumuman" />

        <div class="mx-auto max-w-4xl py-6 sm:px-6 lg:px-8">
            <div class="mb-4 flex items-center gap-4">
                <Link
                    :href="index.url()"
                    class="text-gray-500 hover:text-gray-700"
                >
                    &larr; Kembali
                </Link>
                <h2 class="text-2xl leading-tight font-bold text-gray-900">
                    Buat Pengumuman Baru
                </h2>
            </div>

            <div class="bg-white shadow sm:rounded-lg">
                <form @submit.prevent="submit" class="space-y-6 p-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700"
                            >Tujuan Pengumuman</label
                        >
                        <select
                            v-model="form.kepada.type"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                        >
                            <option value="all">Semua Pengguna</option>
                            <option value="siswa">Semua Siswa</option>
                            <option value="guru">Semua Guru</option>
                            <option value="kelas">Kelas Tertentu</option>
                        </select>
                        <p
                            v-if="form.errors['kepada.type']"
                            class="mt-2 text-sm text-red-600"
                        >
                            {{ form.errors['kepada.type'] }}
                        </p>
                    </div>

                    <div v-if="form.kepada.type === 'kelas'">
                        <label class="block text-sm font-medium text-gray-700"
                            >Pilih Kelas</label
                        >
                        <div
                            class="mt-2 grid h-48 grid-cols-2 gap-4 overflow-y-auto rounded-md border p-4 md:grid-cols-4"
                        >
                            <div
                                v-for="k in kelas"
                                :key="k.id"
                                class="flex items-center"
                            >
                                <input
                                    :id="'kelas-' + k.id"
                                    type="checkbox"
                                    :value="k.id"
                                    v-model="form.kepada.ids"
                                    class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                />
                                <label
                                    :for="'kelas-' + k.id"
                                    class="ml-2 block text-sm text-gray-900"
                                >
                                    {{ k.nama }}
                                </label>
                            </div>
                        </div>
                        <p
                            v-if="form.errors['kepada.ids']"
                            class="mt-2 text-sm text-red-600"
                        >
                            {{ form.errors['kepada.ids'] }}
                        </p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700"
                            >Isi Pengumuman</label
                        >
                        <textarea
                            v-model="form.text"
                            rows="6"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            placeholder="Tuliskan isi pengumuman di sini..."
                        ></textarea>
                        <p
                            v-if="form.errors.text"
                            class="mt-2 text-sm text-red-600"
                        >
                            {{ form.errors.text }}
                        </p>
                    </div>

                    <div class="flex justify-end pt-5">
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="inline-flex justify-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:outline-none"
                        >
                            Terbitkan Pengumuman
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>
