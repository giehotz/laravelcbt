<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    materi: Object,
    kelas: Array,
    mapel: Array,
});

const isEdit = !!props.materi;

const form = useForm({
    judul: props.materi?.judul || '',
    deskripsi: props.materi?.deskripsi || '',
    mapel_id: props.materi?.mapel_id || '',
    kelas: props.materi?.kelas || [],
    youtube: props.materi?.youtube || '',
    file: [], // For new files
    existing_files: props.materi?.file || [], // State of existing files
});

const handleFileChange = (e) => {
    form.file = Array.from(e.target.files);
};

const removeExistingFile = (index) => {
    form.existing_files.splice(index, 1);
};

const submit = () => {
    // Basic youtube regex check on client side
    if (
        form.youtube &&
        !/^https?:\/\/(www\.)?(youtube\.com|youtu\.be)\/.+/.test(form.youtube)
    ) {
        alert('URL YouTube tidak valid. Mohon masukkan URL yang valid.');
        return;
    }

    if (isEdit) {
        // Inertia uses POST with _method=PUT to upload files on edit
        form.transform((data) => ({
            ...data,
            _method: 'PUT',
        })).post(route('lms.materi.update', props.materi.id), {
            preserveScroll: true,
        });
    } else {
        form.post(route('lms.materi.store'), {
            preserveScroll: true,
        });
    }
};

const getFileName = (path) => {
    return path.split('/').pop();
};
</script>

<template>
    <Head :title="isEdit ? 'Edit Materi' : 'Buat Materi'" />

    <div class="mx-auto max-w-4xl py-6 sm:px-6 lg:px-8">
        <div class="mb-4 flex items-center gap-4">
            <Link
                :href="route('lms.materi.index')"
                class="text-gray-500 hover:text-gray-700"
            >
                &larr; Kembali
            </Link>
            <h2 class="text-2xl leading-tight font-bold text-gray-900">
                {{ isEdit ? 'Edit Materi' : 'Buat Materi Baru' }}
            </h2>
        </div>

        <div class="bg-white shadow sm:rounded-lg">
            <form @submit.prevent="submit" class="space-y-6 p-6">
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700"
                            >Mata Pelajaran</label
                        >
                        <select
                            v-model="form.mapel_id"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                        >
                            <option value="" disabled>
                                -- Pilih Mapel --
                            </option>
                            <option
                                v-for="m in mapel"
                                :key="m.id"
                                :value="m.id"
                            >
                                {{ m.nama_mapel }}
                            </option>
                        </select>
                        <p
                            v-if="form.errors.mapel_id"
                            class="mt-2 text-sm text-red-600"
                        >
                            {{ form.errors.mapel_id }}
                        </p>
                    </div>

                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700"
                            >Judul Materi</label
                        >
                        <input
                            v-model="form.judul"
                            type="text"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                        />
                        <p
                            v-if="form.errors.judul"
                            class="mt-2 text-sm text-red-600"
                        >
                            {{ form.errors.judul }}
                        </p>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700"
                        >Pilih Kelas (Target Audien)</label
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
                                v-model="form.kelas"
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
                        v-if="form.errors.kelas"
                        class="mt-2 text-sm text-red-600"
                    >
                        {{ form.errors.kelas }}
                    </p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700"
                        >Deskripsi / Konten Materi</label
                    >
                    <textarea
                        v-model="form.deskripsi"
                        rows="6"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                    ></textarea>
                    <p
                        v-if="form.errors.deskripsi"
                        class="mt-2 text-sm text-red-600"
                    >
                        {{ form.errors.deskripsi }}
                    </p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700"
                        >URL Video YouTube (Opsional)</label
                    >
                    <input
                        v-model="form.youtube"
                        type="url"
                        placeholder="https://www.youtube.com/watch?v=..."
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                    />
                    <p class="mt-1 text-xs text-gray-500">
                        ID YouTube akan diekstrak secara otomatis saat
                        ditampilkan.
                    </p>
                    <p
                        v-if="form.errors.youtube"
                        class="mt-2 text-sm text-red-600"
                    >
                        {{ form.errors.youtube }}
                    </p>
                </div>

                <div>
                    <label
                        class="block border-t pt-4 text-sm font-medium text-gray-700"
                        >Lampiran File Baru</label
                    >
                    <input
                        type="file"
                        multiple
                        @change="handleFileChange"
                        class="mt-2 block w-full text-sm text-gray-500 file:mr-4 file:rounded-md file:border-0 file:bg-indigo-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-indigo-700 hover:file:bg-indigo-100"
                    />
                    <p class="mt-1 text-xs text-gray-500">
                        Maks. 50MB per file. Format: pdf, docx, xlsx, pptx,
                        jpg, png, mp4, mp3.
                    </p>
                    <template
                        v-for="(error, key) in form.errors"
                        :key="key"
                    >
                        <p
                            v-if="key.startsWith('file.')"
                            class="mt-2 text-sm text-red-600"
                        >
                            {{ error }}
                        </p>
                    </template>
                </div>

                <div v-if="isEdit && form.existing_files.length > 0">
                    <label
                        class="mb-2 block text-sm font-medium text-gray-700"
                        >File Terlampir Saat Ini</label
                    >
                    <ul class="divide-y divide-gray-200 rounded-md border">
                        <li
                            v-for="(file, index) in form.existing_files"
                            :key="index"
                            class="flex items-center justify-between py-3 pr-4 pl-3 text-sm"
                        >
                            <div class="flex w-0 flex-1 items-center">
                                <span class="ml-2 w-0 flex-1 truncate">
                                    {{ getFileName(file) }}
                                </span>
                            </div>
                            <div class="ml-4 flex-shrink-0">
                                <button
                                    type="button"
                                    @click="removeExistingFile(index)"
                                    class="font-medium text-red-600 hover:text-red-500"
                                >
                                    Hapus
                                </button>
                            </div>
                        </li>
                    </ul>
                    <p class="mt-1 text-xs text-gray-500">
                        Menghapus file dari daftar ini akan menghapus file
                        fisik di server setelah Anda klik Simpan.
                    </p>
                </div>

                <div class="flex justify-end pt-5">
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="inline-flex justify-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:outline-none"
                    >
                        {{
                            isEdit ? 'Simpan Perubahan' : 'Terbitkan Materi'
                        }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>
